<?php
/**
 * Enhanced Outcome Data API
 * 
 * Retrieves outcome data with support for cumulative calculations
 * and initiative-aware filtering
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../../config/config.php';
require_once '../../lib/db_connect.php';
require_once '../../lib/session.php';
require_once '../../lib/functions.php';
require_once '../../lib/outcome_automation.php';

// Set content type
header('Content-Type: application/json');

// Verify user is logged in
if (!is_logged_in()) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

// Get parameters
$outcome_id = isset($_GET['outcome_id']) ? intval($_GET['outcome_id']) : null;
$sector_id = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : null;
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;
$initiative_id = isset($_GET['initiative_id']) ? intval($_GET['initiative_id']) : null;

// Validate required parameters
if (!$outcome_id || !$sector_id || !$period_id) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: outcome_id, sector_id, period_id']);
    exit;
}

try {
    // Get outcome data with cumulative calculations
    $outcome_data = getOutcomeDataWithCumulative($outcome_id, $sector_id, $period_id);
    
    if (!$outcome_data) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['error' => 'Outcome not found']);
        exit;
    }
    
    // Get linked programs (if any)
    $linked_programs = getLinkedPrograms($outcome_id);
    
    // Filter linked programs by initiative if specified
    if ($initiative_id !== null) {
        $filtered_programs = [];
        foreach ($linked_programs as $program) {
            // Check if program belongs to the specified initiative
            $check_query = "SELECT initiative_id FROM programs WHERE program_id = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("i", $program['program_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $prog_data = $result->fetch_assoc();
            
            if ($prog_data && $prog_data['initiative_id'] == $initiative_id) {
                $filtered_programs[] = $program;
            }
        }
        $linked_programs = $filtered_programs;
    }
    
    // Get program completion status for linked programs in the specified period
    if (!empty($linked_programs)) {
        $program_ids = array_column($linked_programs, 'program_id');
        $placeholders = str_repeat('?,', count($program_ids) - 1) . '?';
        
        $status_query = "SELECT p.program_id, p.program_name,
                               COALESCE(JSON_UNQUOTE(JSON_EXTRACT(ps.content_json, '$.rating')), 'not-started') as status
                        FROM programs p
                        LEFT JOIN (
                            SELECT ps1.*
                            FROM program_submissions ps1
                            INNER JOIN (
                                SELECT program_id, MAX(submission_id) as max_id
                                FROM program_submissions
                                WHERE period_id = ? AND is_draft = 0
                                GROUP BY program_id
                            ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_id
                        ) ps ON p.program_id = ps.program_id
                        WHERE p.program_id IN ($placeholders)";
        
        $stmt = $conn->prepare($status_query);
        $params = array_merge([$period_id], $program_ids);
        $types = str_repeat('i', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $status_result = $stmt->get_result();
        
        $program_statuses = [];
        while ($row = $status_result->fetch_assoc()) {
            $program_statuses[$row['program_id']] = $row['status'];
        }
        
        // Add status to linked programs
        foreach ($linked_programs as &$program) {
            $program['current_status'] = $program_statuses[$program['program_id']] ?? 'not-started';
            $program['is_completed'] = in_array($program['current_status'], ['completed', 'target-achieved']);
        }
    }
    
    // Build response
    $response = [
        'success' => true,
        'outcome_id' => $outcome_id,
        'outcome_name' => $outcome_data['outcome_name'],
        'is_cumulative' => $outcome_data['is_cumulative'],
        'sector_id' => $sector_id,
        'period_id' => $period_id,
        'data_entries' => $outcome_data['data_entries'],
        'linked_programs' => $linked_programs,
        'program_count' => count($linked_programs)
    ];
    
    // Add cumulative total if applicable
    if ($outcome_data['is_cumulative']) {
        $response['cumulative_total'] = $outcome_data['cumulative_total'];
    }
    
    // Calculate completion statistics for linked programs
    if (!empty($linked_programs)) {
        $completed_count = 0;
        foreach ($linked_programs as $program) {
            if ($program['is_completed']) {
                $completed_count++;
            }
        }
        
        $response['completion_stats'] = [
            'total_programs' => count($linked_programs),
            'completed_programs' => $completed_count,
            'completion_percentage' => count($linked_programs) > 0 ? round(($completed_count / count($linked_programs)) * 100, 1) : 0
        ];
    }
    
    ob_end_clean();
    echo json_encode($response);

} catch (Exception $e) {
    ob_end_clean();
    error_log("Error in enhanced outcome data API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
