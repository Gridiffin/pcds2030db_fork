<?php
/**
 * Get All Outcomes API
 * 
 * Returns all available outcomes for frontend dropdown selection
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/admins/outcomes.php'; // Use new backend functions

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

// Clear any buffered output and set JSON header
ob_end_clean();
header('Content-Type: application/json');

try {
    $outcomes = get_all_outcomes();
    $result = [];
    foreach ($outcomes as $row) {
        $result[] = [
            'id' => $row['id'],
            'code' => $row['code'],
            'type' => $row['type'],
            'title' => $row['title'],
            'description' => $row['description'],
            'data' => $row['data'],
            'updated_at' => $row['updated_at']
        ];
    }
    echo json_encode(['success' => true, 'data' => $result]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>
