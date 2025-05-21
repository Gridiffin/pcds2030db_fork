<?php

// BEGIN LOGGING CODE - FOR CLEANUP ANALYSIS - TEMPORARY
if (!function_exists('log_file_access')) {
    function log_file_access() {
        $logFile = dirname(__DIR__, 1) . '/file_access_log.txt';
        $timestamp = date('Y-m-d H:i:s');
        $file = str_replace('\\', '/', __FILE__);
        $file = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        
        $logMessage = "$timestamp | $file | $ip | $method | $uri\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        return true;
    }
}
log_file_access();
// END LOGGING CODE

/**
 * API Endpoint: Check if an outcome exists
 * 
 * Checks if a metric_id + sector_id combination exists in the sector_outcomes_data table
 * Used by the outcome editor to determine if it needs to create or update records
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

// Check if the outcome exists with data_json
try {
    $query = "SELECT id, data_json FROM sector_outcomes_data 
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
