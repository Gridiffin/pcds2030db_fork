<?php
/**
 * Test script to debug admin outcomes view/edit data issues
 */

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/admins/outcomes.php';

echo "Testing admin outcomes data retrieval...\n";

// Test 1: Check if we have any outcomes in the database
$query = "SELECT COUNT(*) as count FROM sector_outcomes_data";
$result = $conn->query($query);
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total outcomes in database: " . $row['count'] . "\n";
} else {
    echo "Error querying database: " . $conn->error . "\n";
    exit;
}

// Test 2: Get a sample metric_id
$query = "SELECT metric_id, table_name, sector_id, period_id FROM sector_outcomes_data LIMIT 3";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "\nFound outcomes:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  Metric ID: {$row['metric_id']}, Table: {$row['table_name']}, Sector: {$row['sector_id']}, Period: {$row['period_id']}\n";
        
        // Test get_outcome_data_for_display function
        $outcome_data = get_outcome_data_for_display($row['metric_id']);
        
        if ($outcome_data) {
            echo "    SUCCESS: get_outcome_data_for_display returned data\n";
            echo "    Keys: " . implode(', ', array_keys($outcome_data)) . "\n";
            echo "    Table name: " . ($outcome_data['table_name'] ?? 'null') . "\n";
            echo "    Sector name: " . ($outcome_data['sector_name'] ?? 'null') . "\n";
            echo "    Data JSON length: " . (isset($outcome_data['data_json']) ? strlen($outcome_data['data_json']) : 'null') . "\n";
            echo "    Parsed data: " . (isset($outcome_data['parsed_data']) ? 'Yes' : 'No') . "\n";
        } else {
            echo "    ERROR: get_outcome_data_for_display returned null\n";
        }
        echo "\n";
    }
} else {
    echo "No outcomes found in database.\n";
}

// Test 3: Check database structure
echo "Checking sector_outcomes_data table structure:\n";
$query = "DESCRIBE sector_outcomes_data";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']} - {$row['Type']}\n";
    }
} else {
    echo "Error describing table: " . $conn->error . "\n";
}
?>
