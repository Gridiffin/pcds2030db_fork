<?php
/**
 * API Endpoint: Check if a metric exists
 * 
 * Checks if a metric_id + sector_id combination exists in the sector_metrics_data table
 * Used by the metric editor to determine if it needs to create or update records
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

// Validate parameters
if ($metric_id <= 0 || $sector_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid metric_id or sector_id']);
    exit;
}

// Check if the metric exists with data_json
try {
    $query = "SELECT id, data_json FROM sector_metrics_data 
              WHERE metric_id = ? AND sector_id = ? 
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $metric_id, $sector_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Check if data_json is valid JSON
        $has_valid_json = !empty($row['data_json']) && json_decode($row['data_json']) !== null;
        
        echo json_encode([
            'success' => true, 
            'exists' => true,
            'has_valid_json' => $has_valid_json,
            'id' => $row['id']
        ]);
    } else {
        echo json_encode(['success' => true, 'exists' => false]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>