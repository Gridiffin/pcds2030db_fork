<?php
/**
 * Delete Program Attachment AJAX Handler
 * 
 * Handles deletion of program attachments
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

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    log_audit_action(
        'attachment_delete_unauthorized',
        'Unauthorized attachment deletion attempt',
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
    if (!isset($_POST['attachment_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing attachment ID']);
        exit;
    }
    
    $attachment_id = intval($_POST['attachment_id']);
    
    // Validate attachment ID
    if ($attachment_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid attachment ID']);
        exit;
    }
    
    // Delete the attachment
    $result = delete_program_attachment($attachment_id);
    
    if (isset($result['success']) && $result['success']) {
        echo json_encode([
            'success' => true,
            'message' => $result['message']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Delete failed'
        ]);
    }
    
} catch (Exception $e) {
    // Log the exception
    log_audit_action(
        'attachment_delete_exception',
        'Exception during attachment deletion: ' . $e->getMessage(),
        'failure',
        $_SESSION['user_id']
    );
    
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred during deletion'
    ]);
}
?>
