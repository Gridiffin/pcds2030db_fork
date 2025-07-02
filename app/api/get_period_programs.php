<?php
/**
 * Get Programs By Period API
 * 
 * Returns program data for a specific reporting period.
 * Only includes finalized (non-draft) program submissions.
 * Now includes initiative information for initiative-based reporting.
 * 
 * Update 2025-06-18: 
 * - Modified to exclude draft programs from report generation.
 * - Fixed duplicate program issue by selecting only the latest submission for each program.
 * 
 * Update 2025-01-26:
 * - Added initiative support: includes initiative_id, initiative_name, initiative_number
 * - Added initiative_id filter parameter for filtering by specific initiative
 * - Updated ordering to prioritize initiatives, then sectors, then agencies
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied. Only admin users can access this API.']);
    exit;
}

// Get period_id parameter
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;
$sector_id = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : null;
$initiative_id = isset($_GET['initiative_id']) ? intval($_GET['initiative_id']) : null;

$agency_ids = [];
if (isset($_GET['agency_ids']) && $_GET['agency_ids'] !== '') {
    $agency_ids = array_filter(array_map('intval', explode(',', $_GET['agency_ids'])));
}

// Validate period_id
if (!$period_id) {
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Missing period_id parameter']);
    exit;
}

// Handle half-yearly period logic
$period_ids = [$period_id];

// Get period details to check if it's half-yearly
$period_query = "SELECT period_id, quarter, year FROM reporting_periods WHERE period_id = ?";
$stmt = $conn->prepare($period_query);
$stmt->bind_param("i", $period_id);
$stmt->execute();
$period_result = $stmt->get_result();
$period_data = $period_result->fetch_assoc();

// Check if this is a half-yearly period based on quarter value
if ($period_data && isset($period_data['quarter'])) {
    $quarter = (int)$period_data['quarter'];
    $year = $period_data['year'];
    
    if ($quarter == 5) { // Half Yearly 1 includes Q1 and Q2
        // Find all Q1 and Q2 periods for the same year
        $q1q2_query = "SELECT period_id FROM reporting_periods WHERE year = ? AND quarter IN (1, 2)";
        $stmt = $conn->prepare($q1q2_query);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $q1q2_result = $stmt->get_result();
        
        $period_ids = [$period_id]; // Always include the original period
        while ($row = $q1q2_result->fetch_assoc()) {
            $period_ids[] = $row['period_id'];
        }
        
        error_log("Half Yearly 1 ($year) selected: Including period_ids " . implode(", ", $period_ids));
    } elseif ($quarter == 6) { // Half Yearly 2 includes Q3 and Q4
        // Find all Q3 and Q4 periods for the same year
        $q3q4_query = "SELECT period_id FROM reporting_periods WHERE year = ? AND quarter IN (3, 4)";
        $stmt = $conn->prepare($q3q4_query);
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $q3q4_result = $stmt->get_result();
        
        $period_ids = [$period_id]; // Always include the original period
        while ($row = $q3q4_result->fetch_assoc()) {
            $period_ids[] = $row['period_id'];
        }
        
        error_log("Half Yearly 2 ($year) selected: Including period_ids " . implode(", ", $period_ids));
    }
}

try {    // Get programs that have non-draft submissions for this period (only latest submission per program)
    // Updated to include initiative information and half-yearly period logic
    
    // Create the period IN clause
    $period_in_clause = implode(',', array_fill(0, count($period_ids), '?'));
    
    $programs_query = "SELECT DISTINCT p.program_id, p.program_name, p.program_number, p.initiative_id,
                      i.initiative_name, i.initiative_number, 
                      s.sector_id, s.sector_name, u.agency_name, u.user_id as owner_agency_id
                      FROM programs p
                      LEFT JOIN (
                          SELECT ps1.program_id
                          FROM program_submissions ps1
                          INNER JOIN (
                              SELECT program_id, MAX(submission_date) as latest_date, MAX(submission_id) as latest_submission_id
                              FROM program_submissions
                              WHERE period_id IN ($period_in_clause) AND is_draft = 0
                              GROUP BY program_id
                          ) ps2 ON ps1.program_id = ps2.program_id 
                               AND ps1.submission_date = ps2.latest_date 
                               AND ps1.submission_id = ps2.latest_submission_id
                          WHERE ps1.period_id IN ($period_in_clause) AND ps1.is_draft = 0
                      ) ps ON p.program_id = ps.program_id
                      LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
                      LEFT JOIN sectors s ON p.sector_id = s.sector_id
                      LEFT JOIN users u ON p.owner_agency_id = u.user_id                      WHERE 
                            (ps.program_id IS NOT NULL)
                            ". ($sector_id ? "AND p.sector_id = ? " : "") .
                            ($initiative_id ? "AND p.initiative_id = ? " : "") .
                            (!empty($agency_ids) ? "AND p.owner_agency_id IN (" . implode(",", array_fill(0, count($agency_ids), '?')) . ") " : "") .
                      "ORDER BY i.initiative_name, s.sector_name, u.agency_name, p.program_name";
      // Add debug logging
    $period_ids_str = implode(',', $period_ids);
    error_log("Fetching programs for period_ids: {$period_ids_str}" . ($sector_id ? ", sector_id: {$sector_id}" : ", all sectors") . ($initiative_id ? ", initiative_id: {$initiative_id}" : ", all initiatives"));
    error_log("Fixed duplicate submission query - using MAX(submission_id) for tie-breaking");
    
    // Prepare statement with dynamic params - need period_ids twice for the nested subquery (once for each IN clause)
    $param_types = str_repeat('i', count($period_ids) * 2); // Period IDs repeated for two IN clauses
    $params = array_merge($period_ids, $period_ids); // First set for first IN clause, second set for second IN clause
    
    if ($sector_id) {
        $param_types .= 'i';
        $params[] = $sector_id;
    }
    if ($initiative_id) {
        $param_types .= 'i';
        $params[] = $initiative_id;
    }
    if (!empty($agency_ids)) {
        $param_types .= str_repeat('i', count($agency_ids));
        $params = array_merge($params, $agency_ids);
    }
    
    $stmt = $conn->prepare($programs_query);
    $stmt->bind_param($param_types, ...$params);

    $stmt->execute();
    $result = $stmt->get_result();
    $program_count = $result->num_rows;
    
    error_log("Found {$program_count} non-draft programs matching criteria");    $programs = [];
    while ($program = $result->fetch_assoc()) {
        if (!isset($programs[$program['sector_id']])) {
            $programs[$program['sector_id']] = [
                'sector_name' => $program['sector_name'],
                'programs' => []
            ];
        }
          $programs[$program['sector_id']]['programs'][] = [
            'program_id' => $program['program_id'],
            'program_name' => $program['program_name'],
            'program_number' => $program['program_number'],
            'initiative_id' => $program['initiative_id'],
            'initiative_name' => $program['initiative_name'],
            'initiative_number' => $program['initiative_number'],
            'agency_name' => $program['agency_name'],
            'owner_agency_id' => $program['owner_agency_id']
        ];
    }
    
    // If filtering by sector but no programs found, still return the sector info
    if ($sector_id && empty($programs)) {
        // Get sector info
        $sector_query = "SELECT sector_id, sector_name FROM sectors WHERE sector_id = ?";
        $sector_stmt = $conn->prepare($sector_query);
        $sector_stmt->bind_param("i", $sector_id);
        $sector_stmt->execute();
        $sector_result = $sector_stmt->get_result();        if ($sector_data = $sector_result->fetch_assoc()) {
            $programs[$sector_id] = [
                'sector_name' => $sector_data['sector_name'],
                'programs' => []
            ];
            error_log("No non-draft programs found for sector {$sector_data['sector_name']}, returning empty array");
        }
    }

    // Return the programs
    ob_end_clean(); // Clear any buffered output
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'programs' => $programs]);

} catch (Exception $e) {
    // Handle any errors
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
