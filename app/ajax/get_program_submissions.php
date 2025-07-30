<?php
/**
 * Get Program Submissions API
 * 
 * Returns all submissions for a given program
 */

require_once '../config/config.php';
require_once '../lib/session.php';
require_once '../lib/db_connect.php';
require_once '../lib/functions.php';
require_once '../lib/agencies/programs.php';
require_once '../lib/agencies/program_permissions.php';

// Set JSON content type
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Verify user is an agency
if (!is_agency()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Get program ID from request
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

if (!$program_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Program ID is required']);
    exit;
}

try {
    // Check if user can view this program
    if (!can_view_program($program_id)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'You do not have permission to view this program']);
        exit;
    }
    
    // Get program submission history
    $submission_history = get_program_edit_history($program_id);
    
    if (!$submission_history || !isset($submission_history['submissions'])) {
        echo json_encode([
            'success' => true,
            'submissions' => []
        ]);
        exit;
    }
    
    // Return the submissions
    echo json_encode([
        'success' => true,
        'submissions' => $submission_history['submissions']
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_program_submissions.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred while fetching submissions']);
}
?>