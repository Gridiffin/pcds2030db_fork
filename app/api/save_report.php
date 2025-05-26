<?php
/**
 * Save Report API Endpoint
 * 
 * Saves a generated PPTX report file to the server and records it in the database.
 * This endpoint receives the generated report from the client-side JavaScript.
 * 
 * Only admin users are allowed to access this endpoint.
 */

// Include necessary files
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied. Only admin users can save reports.']);
    exit;
}

// Define upload directory - create if it doesn't exist
$upload_dir = ROOT_PATH . 'app/reports/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Define subdirectories for each report type
$pptx_dir = $upload_dir . 'pptx/';
$pdf_dir = $upload_dir . 'pdf/';

// Create subdirectories if they don't exist
if (!is_dir($pptx_dir)) {
    mkdir($pptx_dir, 0755, true);
}
if (!is_dir($pdf_dir)) {
    mkdir($pdf_dir, 0755, true);
}

// Get POST parameters
$period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : 0;
$sector_id = isset($_POST['sector_id']) ? intval($_POST['sector_id']) : 0;
$report_name = isset($_POST['report_name']) ? $_POST['report_name'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$is_public = isset($_POST['is_public']) ? intval($_POST['is_public']) : 0;

// Validate required parameters
if ($period_id <= 0 || $sector_id <= 0 || empty($report_name)) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

// Get period and sector details for validation
$period = get_reporting_period($period_id);
if (!$period) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid reporting period']);
    exit;
}

$sector_query = "SELECT sector_name FROM sectors WHERE sector_id = ?";
$stmt = $conn->prepare($sector_query);
$stmt->bind_param("i", $sector_id);
$stmt->execute();
$sector_result = $stmt->get_result();

if ($sector_result->num_rows === 0) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid sector']);
    exit;
}

$sector = $sector_result->fetch_assoc();

// Check if file was uploaded
if (!isset($_FILES['report_file']) || $_FILES['report_file']['error'] != UPLOAD_ERR_OK) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

// Process the uploaded file
$uploaded_file = $_FILES['report_file'];
$file_ext = strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));

// Validate file type
if ($file_ext !== 'pptx') {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid file type. Only PPTX files are allowed.']);
    exit;
}

// Generate unique filename
$timestamp = date('YmdHis');
$quarter = 'Q' . $period['quarter'] . '-' . $period['year'];
$sanitized_sector = preg_replace('/[^a-zA-Z0-9]/', '_', $sector['sector_name']);
$filename = $sanitized_sector . '_' . $quarter . '_' . $timestamp . '.pptx';
$filepath = $pptx_dir . $filename;

// Move uploaded file to destination
if (!move_uploaded_file($uploaded_file['tmp_name'], $filepath)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Failed to save the file']);
    exit;
}

// Record in database
$user_id = $_SESSION['user_id'] ?? 0;
$pptx_path = 'app/reports/pptx/' . $filename; // Relative path from project root for database storage

// Prepare SQL to insert record
$insert_query = "INSERT INTO reports (period_id, report_name, description, pptx_path, generated_by, is_public) 
                VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("isssii", $period_id, $report_name, $description, $pptx_path, $user_id, $is_public);

// Execute query
if (!$stmt->execute()) {
    // If database insert fails, delete the uploaded file
    unlink($filepath);
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Failed to record report in database: ' . $stmt->error]);
    exit;
}

// Get the newly created report id
$report_id = $conn->insert_id;

// Return success response
$response = [
    'success' => true,
    'message' => 'Report saved successfully',
    'report_id' => $report_id,
    'pptx_path' => $pptx_path,
    'filename' => $filename
];

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>