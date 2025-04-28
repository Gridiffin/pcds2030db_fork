<?php
/**
 * File Download Handler
 * 
 * This script handles secure file downloads for reports
 */

// Include necessary files
require_once 'config/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get file path from URL
$file_path = $_GET['file'] ?? '';
$type = $_GET['type'] ?? 'pptx'; // Default to PPTX files

// Validate file path to prevent directory traversal
$file_path = basename($file_path); // Only get the filename, not the path

// Define allowed file types and their directories
$allowed_types = [
    'pptx' => 'reports/pptx/',
    'pdf' => 'reports/pdf/',
    'excel' => 'reports/excel/'
];

// Validate file type
if (!isset($allowed_types[$type])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid file type');
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