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
    $sql = "SELECT detail_id, detail_name, is_cumulative, is_draft, created_at, updated_at
            FROM outcomes_details 
            ORDER BY detail_name ASC";
    
    $result = $conn->query($sql);
    $outcomes = [];
    
    while ($row = $result->fetch_assoc()) {
        // Parse detail_json to get additional info if needed
        $outcomes[] = [
            'id' => $row['detail_id'],
            'name' => $row['detail_name'],
            'is_cumulative' => (bool)$row['is_cumulative'],
            'is_draft' => (bool)$row['is_draft'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $outcomes]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>
