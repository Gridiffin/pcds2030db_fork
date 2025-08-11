<?php
/**
 * Download Program Attachment Handler
 * 
 * Securely serves program attachment files for download
 */

if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_attachments.php';

// Include admin core functions for is_admin() function
require_once PROJECT_ROOT_PATH . 'app/lib/admins/core.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    log_audit_action(
        'attachment_download_unauthorized',
        'Unauthorized attachment download attempt',
        'failure'
    );
    header('HTTP/1.0 403 Forbidden');
    echo 'Access denied';
    exit;
}

// Get attachment ID from URL parameter
$attachment_id = $_GET['id'] ?? '';

// Validate attachment ID
if (empty($attachment_id) || !is_numeric($attachment_id)) {
    log_audit_action(
        'attachment_download_invalid_id',
        'Invalid attachment ID provided for download',
        'failure',
        $_SESSION['user_id']
    );
    header('HTTP/1.0 400 Bad Request');
    echo 'Invalid attachment ID';
    exit;
}

try {
    // Get attachment details with permission check
    $attachment = get_attachment_for_download(intval($attachment_id));
    
    if (!$attachment) {
        log_audit_action(
            'attachment_download_not_found',
            "Attachment not found or access denied: ID {$attachment_id}",
            'failure',
            $_SESSION['user_id']
        );
        header('HTTP/1.0 404 Not Found');
        echo 'Attachment not found';
        exit;
    }
    
    // Check if file exists
    $file_path = $attachment['file_path'];
    
    // Try different path resolutions for compatibility
    $possible_paths = [
        $file_path, // Try as-is first
        PROJECT_ROOT_PATH . ltrim($file_path, './'), // Remove ./ prefix and prepend PROJECT_ROOT_PATH
        PROJECT_ROOT_PATH . $file_path, // Direct prepend
        str_replace('../../', PROJECT_ROOT_PATH, $file_path) // Replace ../ with PROJECT_ROOT_PATH
    ];
    
    $actual_file_path = null;
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $actual_file_path = $path;
            break;
        }
    }
    
    if (!$actual_file_path) {
        log_audit_action(
            'attachment_download_file_missing',
            "Physical file missing for attachment ID {$attachment_id}. Tried paths: " . implode(', ', $possible_paths),
            'failure',
            $_SESSION['user_id']
        );
        header('HTTP/1.0 404 Not Found');
        echo 'File not found';
        exit;
    }
    
    // Log successful download
    log_audit_action(
        'attachment_downloaded',
        "Downloaded attachment '{$attachment['original_filename']}' from program ID {$attachment['program_id']}",
        'success',
        $_SESSION['user_id']
    );
    
    // Set headers for file download
    header('Content-Type: ' . $attachment['mime_type']);
    header('Content-Disposition: attachment; filename="' . addslashes($attachment['original_filename']) . '"');
    header('Content-Length: ' . $attachment['file_size']);
    header('Cache-Control: private');
    header('Pragma: private');
    header('Expires: 0');
    
    // Clear any output buffers
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Output file content
    readfile($actual_file_path);
    exit;
    
} catch (Exception $e) {
    // Log the exception
    log_audit_action(
        'attachment_download_exception',
        "Exception during attachment download (ID: {$attachment_id}): " . $e->getMessage(),
        'failure',
        $_SESSION['user_id']
    );
    
    header('HTTP/1.0 500 Internal Server Error');
    echo 'An error occurred while downloading the file';
    exit;
}
?>
