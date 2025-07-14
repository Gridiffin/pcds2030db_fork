<?php
/**
 * Fix Half-Yearly Period Numbers Script
 * 
 * This script fixes existing half-yearly periods that have incorrect period numbers.
 * The constraint chk_valid_period_numbers expects half-yearly periods to have period_number 1-2,
 * but some existing records may have 5-6.
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/lib/db_connect.php';

echo "Starting half-yearly period number fix...\n";

// Check for existing half-yearly periods with incorrect numbers
$check_query = "SELECT period_id, year, period_type, period_number, start_date, end_date 
                FROM reporting_periods 
                WHERE period_type = 'half' AND period_number IN (5, 6)";
$result = $conn->query($check_query);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " half-yearly periods with incorrect period numbers:\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "- Period ID: {$row['period_id']}, Year: {$row['year']}, Number: {$row['period_number']}\n";
        
        // Determine the correct period number based on the date range
        $start_month = date('n', strtotime($row['start_date']));
        $correct_number = ($start_month <= 6) ? 1 : 2;
        
        echo "  Correcting to period_number: $correct_number\n";
        
        // Update the period number
        $update_query = "UPDATE reporting_periods 
                        SET period_number = ? 
                        WHERE period_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $correct_number, $row['period_id']);
        
        if ($stmt->execute()) {
            echo "  ✓ Updated successfully\n";
        } else {
            echo "  ✗ Error updating: " . $stmt->error . "\n";
        }
    }
} else {
    echo "No half-yearly periods with incorrect period numbers found.\n";
}

// Verify the fix
echo "\nVerifying fix...\n";
$verify_query = "SELECT period_id, year, period_type, period_number, start_date, end_date 
                 FROM reporting_periods 
                 WHERE period_type = 'half' 
                 ORDER BY year, period_number";
$verify_result = $conn->query($verify_query);

if ($verify_result->num_rows > 0) {
    echo "Current half-yearly periods:\n";
    while ($row = $verify_result->fetch_assoc()) {
        echo "- Period ID: {$row['period_id']}, Year: {$row['year']}, Number: {$row['period_number']}, Start: {$row['start_date']}\n";
    }
} else {
    echo "No half-yearly periods found.\n";
}

echo "\nHalf-yearly period number fix completed.\n";
?> 