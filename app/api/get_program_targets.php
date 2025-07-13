<?php
/**
 * Get Program Targets API
 * 
 * Returns targets for selected programs to allow admin selection
 * Used for target selector feature in report generation
 */

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Set content type
header('Content-Type: application/json');

try {
    // Get parameters
    $period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;
    $sector_id = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : 1;
    $selected_program_ids_raw = isset($_GET['selected_program_ids']) ? $_GET['selected_program_ids'] : '';

    // Validate required parameters
    if (!$period_id) {
        throw new Exception('Missing required parameter: period_id');
    }

    if (empty($selected_program_ids_raw)) {
        throw new Exception('Missing required parameter: selected_program_ids');
    }

    // Parse selected program IDs
    $selected_program_ids = [];
    if (is_string($selected_program_ids_raw)) {
        $selected_program_ids = array_map('intval', explode(',', $selected_program_ids_raw));
    } elseif (is_array($selected_program_ids_raw)) {
        $selected_program_ids = array_map('intval', $selected_program_ids_raw);
    }

    // Filter out invalid IDs
    $selected_program_ids = array_filter($selected_program_ids, function($id) {
        return $id > 0;
    });

    if (empty($selected_program_ids)) {
        throw new Exception('No valid program IDs provided');
    }

    // Handle half-yearly period logic (same as report_data.php)
    $period_ids = [$period_id];

    // Get period details to check if it's half-yearly
    $period_query = "SELECT period_id, period_type, period_number, year FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($period_query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $period_result = $stmt->get_result();
    $period_data = $period_result->fetch_assoc();

    // Check if this is a half-yearly period
    if ($period_data && $period_data['period_type'] === 'half') {
        $period_number = (int)$period_data['period_number'];
        $year = $period_data['year'];
        
        if ($period_number == 1) { // Half Yearly 1 includes Q1 and Q2
            $q1q2_query = "SELECT period_id FROM reporting_periods WHERE year = ? AND period_type = 'quarter' AND period_number IN (1, 2)";
            $stmt = $conn->prepare($q1q2_query);
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $q1q2_result = $stmt->get_result();
            
            $period_ids = [$period_id];
            while ($row = $q1q2_result->fetch_assoc()) {
                $period_ids[] = $row['period_id'];
            }
        } elseif ($period_number == 2) { // Half Yearly 2 includes Q3 and Q4
            $q3q4_query = "SELECT period_id FROM reporting_periods WHERE year = ? AND period_type = 'quarter' AND period_number IN (3, 4)";
            $stmt = $conn->prepare($q3q4_query);
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $q3q4_result = $stmt->get_result();
            
            $period_ids = [$period_id];
            while ($row = $q3q4_result->fetch_assoc()) {
                $period_ids[] = $row['period_id'];
            }
        }
    }

    // Create the period IN clause
    $period_in_clause = implode(',', array_fill(0, count($period_ids), '?'));

    // Create placeholders for program IDs
    $program_placeholders = implode(',', array_fill(0, count($selected_program_ids), '?'));

    // Query to get programs with their targets (simplified for single submission per program/period)
    $programs_query = "SELECT p.program_id, p.program_name, ps.submission_id
                      FROM programs p
                      LEFT JOIN program_submissions ps ON p.program_id = ps.program_id 
                          AND ps.period_id IN ($period_in_clause) 
                          AND ps.is_draft = 0
                      WHERE p.program_id IN ($program_placeholders)
                      ORDER BY p.program_name";

    // Prepare parameters (period_ids for IN clause, then program_ids)
    $params = array_merge($period_ids, $selected_program_ids);
    $param_types = str_repeat('i', count($period_ids)) . str_repeat('i', count($selected_program_ids));

    $stmt = $conn->prepare($programs_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $programs_with_targets = [];

    while ($program = $result->fetch_assoc()) {
        $program_targets = [];

        // Fetch targets for this program's submission
        if ($program['submission_id']) {
            // Get period information for this submission
            $period_query = "SELECT rp.period_type, rp.period_number, rp.year 
                           FROM program_submissions ps 
                           JOIN reporting_periods rp ON ps.period_id = rp.period_id 
                           WHERE ps.submission_id = ?";
            $period_stmt = $conn->prepare($period_query);
            $period_stmt->bind_param("i", $program['submission_id']);
            $period_stmt->execute();
            $period_result = $period_stmt->get_result();
            $period_data = $period_result->fetch_assoc();
            $period_stmt->close();
            
            // Generate period label
            $period_label = '';
            if ($period_data) {
                if ($period_data['period_type'] === 'quarter') {
                    $period_label = "Q{$period_data['period_number']}";
                } elseif ($period_data['period_type'] === 'half') {
                    $period_label = "H{$period_data['period_number']}";
                } elseif ($period_data['period_type'] === 'yearly') {
                    $period_label = "Y{$period_data['year']}";
                }
            }
            
            $target_query = "SELECT target_id, target_number, target_description, status_indicator, status_description, remarks, start_date, end_date 
                           FROM program_targets 
                           WHERE submission_id = ? AND is_deleted = 0 
                           ORDER BY target_id";
            $target_stmt = $conn->prepare($target_query);
            $target_stmt->bind_param("i", $program['submission_id']);
            $target_stmt->execute();
            $target_result = $target_stmt->get_result();
            
            while ($target = $target_result->fetch_assoc()) {
                $program_targets[] = [
                    'target_id' => $target['target_id'],
                    'target_number' => $target['target_number'],
                    'target_text' => $target['target_description'], // Backward compatibility
                    'target_description' => $target['target_description'],
                    'status_indicator' => $target['status_indicator'],
                    'status_description' => $target['status_description'],
                    'remarks' => $target['remarks'],
                    'start_date' => $target['start_date'],
                    'end_date' => $target['end_date'],
                    'period_label' => $period_label,
                    'selected' => true // Default to selected
                ];
            }
            $target_stmt->close();
        }

        // Add program to results if it has targets
        if (!empty($program_targets)) {
            $programs_with_targets[] = [
                'program_id' => $program['program_id'],
                'program_name' => $program['program_name'],
                'target_count' => count($program_targets),
                'targets' => $program_targets
            ];
        }
    }

    // Return the response
    echo json_encode([
        'success' => true,
        'period_info' => $period_data,
        'programs' => $programs_with_targets,
        'total_programs' => count($programs_with_targets),
        'total_targets' => array_sum(array_column($programs_with_targets, 'target_count'))
    ]);

} catch (Exception $e) {
    error_log("Error in get_program_targets.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
