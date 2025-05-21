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
 * Utility script to add updated_at column to reporting_periods table
 */

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';

// Check if the column exists
$check_query = "SHOW COLUMNS FROM `reporting_periods` LIKE 'updated_at'";
$result = $conn->query($check_query);

if ($result->num_rows === 0) {
    // Column doesn't exist, add it
    $alter_query = "ALTER TABLE `reporting_periods` 
                    ADD COLUMN `updated_at` TIMESTAMP NOT NULL 
                    DEFAULT CURRENT_TIMESTAMP 
                    ON UPDATE CURRENT_TIMESTAMP";
    
    if ($conn->query($alter_query)) {
        echo "Column 'updated_at' added successfully to reporting_periods table.";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column 'updated_at' already exists.";
}
?>
