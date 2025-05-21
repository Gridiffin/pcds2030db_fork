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
 * Utility script to add is_standard_dates column to reporting_periods table
 */

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';

// Check if the column exists
$check_query = "SHOW COLUMNS FROM `reporting_periods` LIKE 'is_standard_dates'";
$result = $conn->query($check_query);

if ($result->num_rows === 0) {
    // Column doesn't exist, add it
    $alter_query = "ALTER TABLE `reporting_periods` ADD COLUMN `is_standard_dates` BOOLEAN DEFAULT 1";
    
    if ($conn->query($alter_query)) {
        echo "Column 'is_standard_dates' added successfully to reporting_periods table.";
        
        // Update existing periods to mark which ones follow standard quarter dates
        $update_query = "UPDATE reporting_periods SET is_standard_dates = 
        (
            CASE 
                WHEN (quarter = 1 AND start_date = CONCAT(year, '-01-01') AND end_date = CONCAT(year, '-03-31')) THEN 1
                WHEN (quarter = 2 AND start_date = CONCAT(year, '-04-01') AND end_date = CONCAT(year, '-06-30')) THEN 1
                WHEN (quarter = 3 AND start_date = CONCAT(year, '-07-01') AND end_date = CONCAT(year, '-09-30')) THEN 1
                WHEN (quarter = 4 AND start_date = CONCAT(year, '-10-01') AND end_date = CONCAT(year, '-12-31')) THEN 1
                ELSE 0
            END
        )";
        
        if ($conn->query($update_query)) {
            echo "<br>Existing periods updated successfully.";
        } else {
            echo "<br>Error updating existing periods: " . $conn->error;
        }
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column 'is_standard_dates' already exists.";
}
?>
