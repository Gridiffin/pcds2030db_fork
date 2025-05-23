<?php
/**
 * File Download Handler
 * 
 * This script handles secure file downloads for reports
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get file path from URL
$file_path = $_GET['file'] ?? '';
$type = $_GET['type'] ?? 'pptx'; // Default to PPTX files

// Validate file path to prevent directory traversal
$file_path = basename($file_path); // Only get the filename, not the path

// Define allowed file types and their directories
$allowed_types = [
    'pptx' => 'app/reports/pptx/',
    'pdf' => 'app/reports/pdf/',
    'excel' => 'app/reports/excel/',
    'report' => 'app/reports/'  // Add support for 'report' type
];

// Validate file type
if (!isset($allowed_types[$type])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid file type: ' . $type);
}

// If type is 'report', determine the actual subfolder based on file extension
if ($type === 'report') {
    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    // Map file extension to subdirectory
    $subfolder_map = [
        'pptx' => 'pptx/',
        'pdf' => 'pdf/',
        'xlsx' => 'excel/',
        'xls' => 'excel/'
    ];
      // Set default to pptx if extension is not recognized
    $subfolder = $subfolder_map[$file_extension] ?? 'pptx/';
    $allowed_types['report'] = 'app/reports/' . $subfolder;
}

// Construct the full file path
$full_path = __DIR__ . '/' . $allowed_types[$type] . $file_path;

// Check if file exists
if (!file_exists($full_path)) {
    header('HTTP/1.1 404 Not Found');
    exit('File not found: ' . $full_path);
}

// Define MIME types based on file extension
$mime_types = [
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'pdf' => 'application/pdf',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'xls' => 'application/vnd.ms-excel'
];

// Get file extension
$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// Set default MIME type if extension is not recognized
$mime_type = $mime_types[$file_extension] ?? 'application/octet-stream';

// Set appropriate headers for file download
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $file_path . '"');
header('Content-Length: ' . filesize($full_path));
header('Cache-Control: max-age=0');

// Disable output buffering
if (ob_get_level()) {
    ob_end_clean();
}

// Read the file and output it to the browser
readfile($full_path);
exit;