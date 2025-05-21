<?php
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
