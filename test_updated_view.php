<?php
/**
 * Test the updated admin view_outcome.php logic
 */

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing updated admin view_outcome.php logic...\n";

// Simulate the updated includes and setup
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/admins/outcomes.php';

// Test with the same metric_id
$metric_id = 7;
echo "Testing with metric_id: $metric_id\n";

// Get outcome details
$outcome_details = get_outcome_data_for_display($metric_id);

if (!$outcome_details) {
    echo "ERROR: get_outcome_data_for_display returned null\n";
    exit;
}

echo "SUCCESS: Outcome details retrieved\n";

// Test the new flexible structure logic
$table_structure_type = $outcome_details['table_structure_type'] ?? 'monthly';
$row_config = json_decode($outcome_details['row_config'] ?? '{}', true);
$column_config = json_decode($outcome_details['column_config'] ?? '{}', true);

// Parse the outcome data
$outcome_data = json_decode($outcome_details['data_json'] ?? '{}', true) ?? [];

// Determine if this is a flexible structure
$is_flexible = !empty($row_config) && !empty($column_config);

echo "Table structure type: $table_structure_type\n";
echo "Is flexible: " . ($is_flexible ? 'Yes' : 'No') . "\n";

if ($is_flexible) {
    // New flexible structure
    $rows = $row_config['rows'] ?? [];
    $columns = $column_config['columns'] ?? [];
    
    echo "Rows count: " . count($rows) . "\n";
    echo "Columns count: " . count($columns) . "\n";
    
    if (!empty($columns)) {
        echo "Column labels: ";
        foreach ($columns as $col) {
            echo $col['label'] . " ";
        }
        echo "\n";
    }
    
    // Test data organization
    $table_data = [];
    foreach ($rows as $row_def) {
        $row_data = ['row' => $row_def, 'metrics' => []];
        
        // Add data for each metric in this row
        if (isset($outcome_data[$row_def['id']])) {
            $row_data['metrics'] = $outcome_data[$row_def['id']];
        }
        
        $table_data[] = $row_data;
    }
    
    echo "Table data rows count: " . count($table_data) . "\n";
    
    // Test first row
    if (!empty($table_data)) {
        $first_row = $table_data[0];
        echo "First row label: " . $first_row['row']['label'] . "\n";
        echo "First row metrics count: " . count($first_row['metrics']) . "\n";
        if (!empty($first_row['metrics'])) {
            echo "First row first metric: " . $first_row['metrics'][0] . "\n";
        }
    }
} else {
    echo "Using legacy structure (not expected for this test)\n";
}

echo "\nTest completed successfully!\n";
?>
