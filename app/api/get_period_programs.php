<?php
/**
 * Get Programs By Period API
 * 
 * Returns program data for a specific reporting period
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

try {
    // Get programs that have submissions for this period or all programs if no submissions exist
    $programs_query = "SELECT DISTINCT p.program_id, p.program_name, s.sector_id, s.sector_name, u.agency_name, u.user_id as owner_agency_id
                      FROM programs p
                      LEFT JOIN program_submissions ps ON p.program_id = ps.program_id AND ps.period_id = ?
                      LEFT JOIN sectors s ON p.sector_id = s.sector_id
                      LEFT JOIN users u ON p.owner_agency_id = u.user_id
                      WHERE 
                            (ps.period_id IS NOT NULL OR NOT EXISTS (
                                SELECT 1 FROM program_submissions WHERE period_id = ?
                            ))
                            ". ($sector_id ? "AND p.sector_id = ? " : "") .
                            (!empty($agency_ids) ? "AND p.owner_agency_id IN (" . implode(",", array_fill(0, count($agency_ids), '?')) . ") " : "") .
                      "ORDER BY s.sector_name, u.agency_name, p.program_name";
    
    // Add debug logging
    error_log("Fetching programs for period_id: {$period_id}" . ($sector_id ? ", sector_id: {$sector_id}" : ", all sectors"));
    
    // Prepare statement with dynamic params
    $param_types = $sector_id ? 'iii' : 'ii';
    $params = [$period_id, $period_id];
    if ($sector_id) $params[] = $sector_id;
    if (!empty($agency_ids)) {
        $param_types .= str_repeat('i', count($agency_ids));
        $params = array_merge($params, $agency_ids);
    }
    $stmt = $conn->prepare($programs_query);
    $stmt->bind_param($param_types, ...$params);

    $stmt->execute();    $result = $stmt->get_result();
    $program_count = $result->num_rows;
    
    error_log("Found {$program_count} programs matching criteria");

    $programs = [];
    while ($program = $result->fetch_assoc()) {
        if (!isset($programs[$program['sector_id']])) {
            $programs[$program['sector_id']] = [
                'sector_name' => $program['sector_name'],
                'programs' => []
            ];
        }
        $programs[$program['sector_id']]['programs'][] = [
            'program_id' => $program['program_id'],
            'program_name' => $program['program_name']
        ];
    }
    
    // If filtering by sector but no programs found, still return the sector info
    if ($sector_id && empty($programs)) {
        // Get sector info
        $sector_query = "SELECT sector_id, sector_name FROM sectors WHERE sector_id = ?";
        $sector_stmt = $conn->prepare($sector_query);
        $sector_stmt->bind_param("i", $sector_id);
        $sector_stmt->execute();
        $sector_result = $sector_stmt->get_result();
        
        if ($sector_data = $sector_result->fetch_assoc()) {
            $programs[$sector_id] = [
                'sector_name' => $sector_data['sector_name'],
                'programs' => []
            ];
            error_log("No programs found for sector {$sector_data['sector_name']}, returning empty array");
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
