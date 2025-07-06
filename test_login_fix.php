<?php
/**
 * Test Login Fix Script
 * 
 * This script tests if the auto_manage_reporting_periods function works correctly
 * after fixing the period numbering issue.
 */

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/functions.php';

echo "Testing auto_manage_reporting_periods function...\n";

try {
    // Test the function that was causing the constraint violation
    $result = auto_manage_reporting_periods(true);
    
    if ($result === true) {
        echo "✓ auto_manage_reporting_periods executed successfully!\n";
    } else {
        echo "✗ auto_manage_reporting_periods returned unexpected result: " . var_export($result, true) . "\n";
    }
    
    // Check current periods
    echo "\nCurrent reporting periods:\n";
    $periods = get_all_reporting_periods();
    foreach ($periods as $period) {
        echo "- ID: {$period['period_id']}, Year: {$period['year']}, Type: {$period['period_type']}, Number: {$period['period_number']}, Status: {$period['status']}\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
?> 