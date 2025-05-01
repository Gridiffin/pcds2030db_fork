<?php
/**
 * API Endpoint: Get Metric Data
 * 
 * Returns the data_json contents for a specified metric_id and sector_id
 */

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

// Get parameters
$metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 0;
$sector_id = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : 0;
$is_draft = isset($_GET['is_draft']) ? intval($_GET['is_draft']) : 1; // Default to draft mode

// Validate parameters
if ($metric_id <= 0 || $sector_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid metric_id or sector_id']);
    exit;
}

try {
    // Prepare query to get the metric data
    $query = "SELECT data_json, table_name FROM sector_metrics_data 
              WHERE metric_id = ? AND sector_id = ? AND is_draft = ?
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $metric_id, $sector_id, $is_draft);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Parse the JSON data
        $data = json_decode($row['data_json'], true);
        
        // Check if data_json is valid JSON
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'success' => false, 
                'error' => 'Invalid JSON data: ' . json_last_error_msg()
            ]);
            exit;
        }
        
        // Return the data
        echo json_encode([
            'success' => true,
            'data' => $data,
            'table_name' => $row['table_name']
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Metric data not found',
            'metric_id' => $metric_id,
            'sector_id' => $sector_id
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>