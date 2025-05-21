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
 * Get Reporting Periods API
 * 
 * Returns all reporting periods
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

try {
    // Get all reporting periods
    $periods_query = "SELECT * FROM reporting_periods ORDER BY year DESC, quarter DESC";
    $periods_result = $conn->query($periods_query);
    
    $periods = [];
    while ($period = $periods_result->fetch_assoc()) {
        $periods[] = $period;
    }
    
    // Return the periods
    ob_end_clean(); // Clear any buffered output
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'periods' => $periods]);
    
} catch (Exception $e) {
    // Handle any errors
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
