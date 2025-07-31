<?php
// Start session FIRST before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * AJAX endpoint to check if a program number already exists
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/core.php';

// Verify user is an agency
if (!is_agency()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get POST data
$initiative_id = isset($_POST['initiative_id']) ? intval($_POST['initiative_id']) : 0;
$program_number = isset($_POST['program_number']) ? trim($_POST['program_number']) : '';

// Validate input
if (!$initiative_id || !$program_number) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Check if program number exists
try {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM programs 
        WHERE initiative_id = ? AND program_number = ?
    ");
    $stmt->bind_param("is", $initiative_id, $program_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode(['exists' => $row['count'] > 0]);
} catch (Exception $e) {
    error_log("Error checking program number: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error']);
} 