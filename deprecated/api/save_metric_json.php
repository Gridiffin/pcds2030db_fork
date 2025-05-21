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
 * API Endpoint: Save Metric JSON Data
 * 
 * Handles saving the JSON structure for metric data
 * Used by the metric editor to initialize or update the JSON structure
 * @deprecated Use save_outcome_json.php instead
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

// Get JSON data from request body
$input = json_decode(file_get_contents('php://input'), true);

// Validate required parameters
if (empty($input['metric_id']) || empty($input['sector_id']) || empty($input['data_json'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$metric_id = intval($input['metric_id']);
$sector_id = intval($input['sector_id']);
$table_name = $input['table_name'] ?? 'Table_' . $metric_id;
$data_json = json_encode($input['data_json']);
$is_draft = isset($input['is_draft']) ? intval($input['is_draft']) : 1;

try {    // Check if the record already exists in outcomes table (new system)
    $check_query = "SELECT id FROM sector_outcomes_data 
                   WHERE metric_id = ? AND sector_id = ? 
                   LIMIT 1";
    
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $metric_id, $sector_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {        // Record exists, update it in outcomes table (new system)
        $query = "UPDATE sector_outcomes_data 
                 SET table_name = ?, data_json = ?, updated_at = NOW() 
                 WHERE metric_id = ? AND sector_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $table_name, $data_json, $metric_id, $sector_id);
    } else {        // Create new record in outcomes table (new system)
        $query = "INSERT INTO sector_outcomes_data 
                 (metric_id, sector_id, table_name, data_json, is_draft) 
                 VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissi", $metric_id, $sector_id, $table_name, $data_json, $is_draft);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Metric data saved successfully'
        ]);
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>