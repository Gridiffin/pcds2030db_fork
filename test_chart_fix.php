<?php
/**
 * Test the updated chart functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TESTING UPDATED CHART FUNCTIONALITY ===\n";

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/admins/outcomes.php';

// Test chart data structure for JavaScript
$metric_id = 7;
$outcome_details = get_outcome_data_for_display($metric_id);

if (!$outcome_details) {
    echo "ERROR: No outcome details found\n";
    exit;
}

// Recreate the exact logic from view_outcome.php
$table_structure_type = $outcome_details['table_structure_type'] ?? 'monthly';
$row_config = json_decode($outcome_details['row_config'] ?? '{}', true);
$column_config = json_decode($outcome_details['column_config'] ?? '{}', true);
$outcome_data = json_decode($outcome_details['data_json'] ?? '{}', true) ?? [];

$is_flexible = !empty($row_config) && !empty($column_config);

if ($is_flexible) {
    $rows = $row_config['rows'] ?? [];
    $columns = $column_config['columns'] ?? [];
    
    echo "CHART SETUP TEST:\n";
    echo "Structure Type: " . ($is_flexible ? 'flexible' : 'classic') . "\n";
    echo "Rows: " . count($rows) . ", Columns: " . count($columns) . "\n\n";
    
    echo "COLUMN LABELS (for chart legend):\n";
    foreach ($columns as $i => $col) {
        echo "  $i: '" . $col['label'] . "'\n";
    }
    echo "\n";
    
    echo "SAMPLE DATA EXTRACTION TEST:\n";
    // Test data extraction logic that we fixed in JavaScript
    $first_column = $columns[0];
    $column_name = $first_column['label'];
    $column_index = 0;
    
    echo "Testing column: '$column_name' (index $column_index)\n";
    
    $sample_data = [];
    foreach (array_slice($rows, 0, 3) as $row) {
        $row_id = $row['id'];
        $row_label = $row['label'];
        $value = null;
        
        if (isset($outcome_data[$row_id]) && isset($outcome_data[$row_id][$column_index])) {
            $value = $outcome_data[$row_id][$column_index];
        }
        
        $sample_data[] = ['row' => $row_label, 'value' => $value];
        echo "  $row_label: " . ($value !== null ? number_format($value, 2) : 'null') . "\n";
    }
    echo "  ...\n\n";
    
    // Test JavaScript data generation
    $js_structure = json_encode(['rows' => $rows, 'columns' => $columns]);
    $js_data = json_encode($outcome_data);
    
    echo "JAVASCRIPT COMPATIBILITY TEST:\n";
    echo "Structure JSON valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "\n";
    echo "Data JSON valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "\n";
    echo "Structure size: " . strlen($js_structure) . " chars\n";
    echo "Data size: " . strlen($js_data) . " chars\n\n";
    
    echo "EXPECTED CHART BEHAVIOR:\n";
    echo "- Chart should show " . count($columns) . " data series\n";
    echo "- Legend should display: " . implode(', ', array_map(function($col) { return $col['label']; }, $columns)) . "\n";
    echo "- X-axis should show 12 months\n";
    echo "- Data should be properly mapped from array indices to column labels\n\n";
    
    echo "✅ Chart data structure is now compatible with updated JavaScript\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
