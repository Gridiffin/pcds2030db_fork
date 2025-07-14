<?php
/**
 * Upload Program Attachment AJAX Handler
 * 
 * Handles file uploads for program attachments
 */

// Set JSON header
header('Content-Type: application/json');

if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_attachments.php';

// Verify user is logged in and is an agency
if (!is_agency()) {
    log_audit_action(
        'attachment_upload_unauthorized',
        'Unauthorized attachment upload attempt',
        'failure'
    );
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

try {
    // Validate required parameters
    if (!isset($_POST['program_id']) || !isset($_FILES['attachment_file']) || !isset($_POST['submission_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters (program_id, submission_id, or file)']);
        exit;
    }
    
    $program_id = intval($_POST['program_id']);
    $description = trim($_POST['description'] ?? '');
    $submission_id = intval($_POST['submission_id']);
    
    // Validate program ID and submission ID
    if ($program_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid program ID']);
        exit;
    }
    
    if ($submission_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid submission ID']);
        exit;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['attachment_file']) || $_FILES['attachment_file']['error'] === UPLOAD_ERR_NO_FILE) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded']);
        exit;
    }
    
    // Upload the attachment
    $result = upload_program_attachment($program_id, $_FILES['attachment_file'], $description, $submission_id);    if (isset($result['success']) && $result['success']) {
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'attachment' => [
                'attachment_id' => $result['attachment_id'],
                'filename' => $result['filename'],
                'original_filename' => $result['filename'], // Same as filename
                'file_size' => $result['file_size'],
                'file_size_formatted' => format_file_size($result['file_size']),
                'mime_type' => $result['mime_type'],
                'file_type' => $result['file_type'],
                'description' => $description,
                'upload_date' => $result['upload_date']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Upload failed'
        ]);
    }
    
} catch (Exception $e) {
    // Log the exception
    log_audit_action(
        'attachment_upload_exception',
        'Exception during attachment upload: ' . $e->getMessage(),
        'failure',
        $_SESSION['user_id']
    );
    
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred during upload'
    ]);
}
?>
