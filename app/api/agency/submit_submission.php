<?php
/**
 * Submit Submission API Endpoint
 * Handles submission submission for review
 */

header('Content-Type: application/json');

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/programs.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_permissions.php';

// Verify user is an agency
if (!is_agency()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get and validate input
    $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    
    if (!$submission_id || $action !== 'submit') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        exit;
    }
    
    // Get submission details
    $submission_query = "SELECT ps.*, p.program_name 
                         FROM program_submissions ps
                         JOIN programs p ON ps.program_id = p.program_id
                         WHERE ps.submission_id = ? AND ps.is_deleted = 0";
    
    $stmt = $conn->prepare($submission_query);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $submission = $stmt->get_result()->fetch_assoc();
    
    if (!$submission) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Submission not found']);
        exit;
    }
    
    // Check if user can edit this program
    if (!can_edit_program($submission['program_id'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'You do not have permission to submit this submission']);
        exit;
    }
    
    // Check if submission is already submitted
    if ($submission['is_submitted']) {
        echo json_encode(['status' => 'info', 'message' => 'This submission has already been submitted']);
        exit;
    }
    
    // Check if submission has content (is draft)
    if (!$submission['is_draft']) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Cannot submit an empty submission. Please add content first.']);
        exit;
    }
    
    // Submit the submission
    $update_query = "UPDATE program_submissions 
                     SET is_submitted = 1, 
                         submitted_at = NOW(),
                         submitted_by = ?,
                         updated_at = NOW()
                     WHERE submission_id = ?";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $_SESSION['username'], $submission_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Submission has been successfully submitted for review'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to submit submission']);
    }
    
} catch (Exception $e) {
    error_log("Submit submission error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while processing your request']);
}
?>