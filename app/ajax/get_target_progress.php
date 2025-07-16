<?php
/**
 * Get Target Progress AJAX Handler
 * 
 * Provides detailed target progress information for a program including:
 * - Individual target completion rates
 * - Target status breakdown
 * - Progress over time
 */

// Include necessary files
require_once '../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/agencies/programs.php';
require_once ROOT_PATH . 'app/lib/admins/core.php';
require_once ROOT_PATH . 'app/lib/admins/statistics.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in and is an agency or admin
if (!is_agency() && !is_admin()) {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Get program ID from request
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

if (!$program_id) {
    echo json_encode(['error' => 'Invalid program ID']);
    exit;
}

try {
    // Get target progress data
    $progress_data = get_target_progress_data($program_id);
    
    echo json_encode([
        'success' => true,
        'progress' => $progress_data
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_target_progress.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Internal server error']);
}

/**
 * Get detailed target progress data for a program
 */
function get_target_progress_data($program_id) {
    global $conn;
    
    $progress_data = [];
    
    // Get the latest submission for this program
    $latest_submission_query = "SELECT submission_id 
                               FROM program_submissions 
                               WHERE program_id = ? 
                               ORDER BY submission_id DESC 
                               LIMIT 1";
    
    $stmt = $conn->prepare($latest_submission_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return $progress_data;
    }
    
    $latest_submission = $result->fetch_assoc();
    $latest_submission_id = $latest_submission['submission_id'];
    
    // Get all targets for the latest submission
    $targets_query = "SELECT pt.target_id, pt.target_number, pt.target_description, 
                             pt.status_indicator, pt.status_description, pt.remarks,
                             pt.start_date, pt.end_date,
                             CASE 
                                 WHEN pt.status_indicator = 'completed' THEN 100
                                 WHEN pt.status_indicator = 'in_progress' THEN 50
                                 WHEN pt.status_indicator = 'delayed' THEN 25
                                 WHEN pt.status_indicator = 'not_started' THEN 0
                                 ELSE 0
                             END as percentage
                      FROM program_targets pt
                      WHERE pt.submission_id = ? AND pt.is_deleted = 0
                      ORDER BY pt.target_id ASC";
    
    $stmt = $conn->prepare($targets_query);
    $stmt->bind_param("i", $latest_submission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $progress_data[] = [
            'target_id' => $row['target_id'],
            'target_number' => $row['target_number'],
            'target_description' => $row['target_description'],
            'status_indicator' => $row['status_indicator'],
            'status_description' => $row['status_description'],
            'remarks' => $row['remarks'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'percentage' => $row['percentage']
        ];
    }
    
    return $progress_data;
}
?> 