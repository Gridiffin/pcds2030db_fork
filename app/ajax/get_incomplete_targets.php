<?php
/**
 * Get Incomplete Targets
 * 
 * AJAX endpoint to fetch incomplete targets from previous periods for a program.
 * Used to auto-fill new submissions with targets that haven't been completed yet.
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/agencies/programs.php';

// Set JSON header
header('Content-Type: application/json');

// Verify user is an agency
if (!is_agency()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Agency login required.']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

// Get and validate input parameters
$program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
$period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : 0;

if (!$program_id || !$period_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Program ID and Period ID are required.']);
    exit;
}

try {
    // Verify program exists and user has access
    $program = get_program_details($program_id);
    
    if (!$program) {
        http_response_code(404);
        echo json_encode(['error' => 'Program not found or access denied.']);
        exit;
    }

    // Get the current period info to determine which periods to look back at
    $current_period_query = "SELECT year, period_type, period_number 
                            FROM reporting_periods 
                            WHERE period_id = ?";
    $stmt = $conn->prepare($current_period_query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $current_period = $stmt->get_result()->fetch_assoc();
    
    if (!$current_period) {
        http_response_code(404);
        echo json_encode(['error' => 'Period not found.']);
        exit;
    }

    // Find incomplete targets from previous periods
    // We'll look at the most recent submission for this program and get incomplete targets
    $incomplete_targets_query = "
        SELECT DISTINCT 
            pt.target_id,
            pt.target_number,
            pt.target_description,
            pt.status_indicator,
            pt.status_description,
            pt.remarks,
            pt.start_date,
            pt.end_date,
            ps.submission_id,
            ps.updated_at as last_updated
        FROM program_targets pt
        JOIN program_submissions ps ON pt.submission_id = ps.submission_id
        JOIN reporting_periods rp ON ps.period_id = rp.period_id
        WHERE ps.program_id = ? 
        AND pt.is_deleted = 0 
        AND ps.is_deleted = 0
        AND pt.status_indicator != 'completed'
        AND (
            (rp.year < ?) OR 
            (rp.year = ? AND rp.period_number < ?)
        )
        ORDER BY ps.updated_at DESC, pt.target_id ASC
    ";
    
    $stmt = $conn->prepare($incomplete_targets_query);
    $stmt->bind_param("iiii", 
        $program_id, 
        $current_period['year'], 
        $current_period['year'], 
        $current_period['period_number']
    );
    $stmt->execute();
    $result = $stmt->get_result();
    
    $incomplete_targets = [];
    $seen_target_numbers = []; // Track to avoid duplicates
    
    while ($target = $result->fetch_assoc()) {
        $target_number = $target['target_number'] ?: '';
        
        // Skip if we've already seen this target number (take the most recent version)
        if (!empty($target_number) && in_array($target_number, $seen_target_numbers)) {
            continue;
        }
        
        // For targets without numbers, use description as key to avoid duplicates
        if (empty($target_number)) {
            $description_key = trim($target['target_description']);
            if (in_array($description_key, $seen_target_numbers)) {
                continue;
            }
            $seen_target_numbers[] = $description_key;
        } else {
            $seen_target_numbers[] = $target_number;
        }
        
        $incomplete_targets[] = [
            'target_number' => $target_number,
            'target_text' => $target['target_description'],
            'target_status' => $target['status_indicator'],
            'status_description' => $target['status_description'],
            'remarks' => $target['remarks'],
            'start_date' => $target['start_date'],
            'end_date' => $target['end_date'],
            'last_updated' => $target['last_updated']
        ];
    }

    // Prepare response
    $response = [
        'success' => true,
        'incomplete_targets' => $incomplete_targets,
        'count' => count($incomplete_targets),
        'period_info' => [
            'period_id' => $period_id,
            'year' => $current_period['year'],
            'period_type' => $current_period['period_type'],
            'period_number' => $current_period['period_number']
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_incomplete_targets.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while fetching incomplete targets.']);
    exit;
}
?> 