<?php
/**
 * File Download Handler - Fixed Version
 * 
 * This script handles secure file downloads for reports without corruption
 */

// Start output buffering immediately
ob_start();

// Include necessary files
require_once 'app/config/config.php';
require_once 'app/lib/session.php';
require_once 'app/lib/audit_log.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $requested_file = $_GET['file'] ?? 'unknown_file';
    log_audit_action(
        'file_download_denied',
        "Unauthorized download attempt for file: {$requested_file}",
        'failure'
    );
    ob_end_clean();
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get file path from URL parameter
$file_param = $_GET['file'] ?? '';
$type = $_GET['type'] ?? 'report'; // Keep for backwards compatibility

// Validate file parameter
if (empty($file_param)) {
    log_audit_action(
        'file_download_failed',
        "File name not provided.",
        'failure',
        $_SESSION['user_id']
    );
    ob_end_clean();
    header('HTTP/1.1 400 Bad Request');
    exit('File name not provided.');
}

// Construct the full file path
$full_path = __DIR__ . '/' . $file_param;

// Security: Use realpath to resolve the path and check it's within reports directory
$real_file_path = realpath($full_path);
$reports_dir = realpath(__DIR__ . '/app/reports/');

// Security check: ensure file is within the reports directory
if (!$real_file_path || !$reports_dir || strpos($real_file_path, $reports_dir) !== 0) {
    log_audit_action(
        'file_download_failed',
        "Invalid file path or path traversal attempt: {$file_param}",
        'failure',
        $_SESSION['user_id']
    );
    ob_end_clean();
    header('HTTP/1.1 403 Forbidden');
    exit('Invalid file path.');
}

// Check if file exists and is readable
if (!file_exists($real_file_path) || !is_readable($real_file_path)) {
    log_audit_action(
        'file_download_not_found',
        "File not found or not readable: {$file_param}",
        'failure',
        $_SESSION['user_id']
    );
    ob_end_clean();
    header('HTTP/1.1 404 Not Found');
    exit('File not found.');
}

// Get file info
$file_size = filesize($real_file_path);
$file_name = basename($real_file_path);

// Determine MIME type based on file extension
$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
$mime_types = [
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'pdf' => 'application/pdf',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'xls' => 'application/vnd.ms-excel'
];
$mime_type = $mime_types[$file_extension] ?? 'application/octet-stream';

// Log successful file download attempt
log_audit_action(
    'file_download',
    "File downloaded: {$file_name} (Size: {$file_size} bytes)",
    'success',
    $_SESSION['user_id']
);

// Clear any output that might have been generated
ob_end_clean();

// Set headers for download
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Output the file
readfile($real_file_path);
exit;
?>
