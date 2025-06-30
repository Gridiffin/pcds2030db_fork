<?php
/**
 * Test chart data for admin view outcome
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing admin view outcome chart data...\n";

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/admins/outcomes.php';

// Test with metric_id 7
$metric_id = 7;
echo "Testing chart data preparation for metric_id: $metric_id\n";

$outcome_details = get_outcome_data_for_display($metric_id);

if (!$outcome_details) {
    echo "ERROR: No outcome details found\n";
    exit;
}

echo "Table name: " . $outcome_details['table_name'] . "\n";

// Test the chart data preparation logic (same as in view_outcome.php)
$table_structure_type = $outcome_details['table_structure_type'] ?? 'monthly';
$row_config = json_decode($outcome_details['row_config'] ?? '{}', true);
$column_config = json_decode($outcome_details['column_config'] ?? '{}', true);

// Parse the outcome data
$outcome_data = json_decode($outcome_details['data_json'] ?? '{}', true) ?? [];

// Determine if this is a flexible structure
$is_flexible = !empty($row_config) && !empty($column_config);

echo "Is flexible: " . ($is_flexible ? 'Yes' : 'No') . "\n";

if ($is_flexible) {
    $rows = $row_config['rows'] ?? [];
    $columns = $column_config['columns'] ?? [];
    
    echo "Chart structure preparation:\n";
    echo "  Rows: " . count($rows) . "\n";
    echo "  Columns: " . count($columns) . "\n";
    
    // Test JSON encoding for JavaScript
    $structure_json = json_encode([
        'rows' => $rows,
        'columns' => $columns
    ]);
    
    $data_json = json_encode($outcome_data);
    
    echo "Structure JSON length: " . strlen($structure_json) . "\n";
    echo "Data JSON length: " . strlen($data_json) . "\n";
    
    echo "Structure JSON preview: " . substr($structure_json, 0, 200) . "...\n";
    echo "Data JSON preview: " . substr($data_json, 0, 200) . "...\n";
    
    // Check if JSON encoding was successful
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "SUCCESS: JSON encoding successful for chart data\n";
    } else {
        echo "ERROR: JSON encoding failed: " . json_last_error_msg() . "\n";
    }
    
    // Test specific data values
    if (!empty($outcome_data)) {
        $first_month = array_keys($outcome_data)[0];
        $first_month_data = $outcome_data[$first_month];
        echo "First month: $first_month\n";
        echo "First month data: " . implode(', ', array_slice($first_month_data, 0, 3)) . "...\n";
    }
} else {
    echo "Would use legacy structure handling\n";
}

echo "\nChart data test completed successfully!\n";
?>
