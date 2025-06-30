<?php
/**
 * Debug chart data for admin view outcome
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG: Chart Data Analysis ===\n";

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/admins/outcomes.php';

// Test with metric_id 7 (same as in screenshot)
$metric_id = 7;
echo "Analyzing chart data for metric_id: $metric_id\n\n";

$outcome_details = get_outcome_data_for_display($metric_id);

if (!$outcome_details) {
    echo "ERROR: No outcome details found\n";
    exit;
}

echo "1. TABLE DETAILS:\n";
echo "   Table name: " . $outcome_details['table_name'] . "\n";
echo "   Structure type: " . ($outcome_details['table_structure_type'] ?? 'monthly') . "\n\n";

// Recreate the exact logic from view_outcome.php
$table_structure_type = $outcome_details['table_structure_type'] ?? 'monthly';
$row_config = json_decode($outcome_details['row_config'] ?? '{}', true);
$column_config = json_decode($outcome_details['column_config'] ?? '{}', true);

echo "2. CONFIGURATION DATA:\n";
echo "   Row config: " . ($outcome_details['row_config'] ?? 'null') . "\n";
echo "   Column config: " . ($outcome_details['column_config'] ?? 'null') . "\n\n";

// Parse the outcome data
$outcome_data = json_decode($outcome_details['data_json'] ?? '{}', true) ?? [];

echo "3. PARSED OUTCOME DATA:\n";
echo "   Data JSON length: " . strlen($outcome_details['data_json'] ?? '') . "\n";
echo "   Parsed data keys: " . implode(', ', array_keys($outcome_data)) . "\n";
if (!empty($outcome_data)) {
    $first_month = array_keys($outcome_data)[0];
    echo "   Sample data ($first_month): " . implode(', ', array_slice($outcome_data[$first_month], 0, 3)) . "...\n";
}
echo "\n";

// Determine if this is a flexible structure
$is_flexible = !empty($row_config) && !empty($column_config);

echo "4. STRUCTURE ANALYSIS:\n";
echo "   Is flexible: " . ($is_flexible ? 'Yes' : 'No') . "\n";

if ($is_flexible) {
    $rows = $row_config['rows'] ?? [];
    $columns = $column_config['columns'] ?? [];
    
    echo "   Rows count: " . count($rows) . "\n";
    echo "   Columns count: " . count($columns) . "\n\n";
    
    echo "5. ROWS STRUCTURE:\n";
    foreach (array_slice($rows, 0, 3) as $i => $row) {
        echo "   Row $i: " . json_encode($row) . "\n";
    }
    echo "   ...\n\n";
    
    echo "6. COLUMNS STRUCTURE:\n";
    foreach ($columns as $i => $col) {
        echo "   Column $i: " . json_encode($col) . "\n";
    }
    echo "\n";
    
    // Test the exact structure being passed to JavaScript
    $chart_structure = [
        'rows' => $rows,
        'columns' => $columns
    ];
    
    echo "7. CHART STRUCTURE (JavaScript format):\n";
    $structure_json = json_encode($chart_structure, JSON_PRETTY_PRINT);
    echo "   " . str_replace("\n", "\n   ", $structure_json) . "\n\n";
    
    echo "8. CHART DATA (JavaScript format):\n";
    $data_json = json_encode($outcome_data, JSON_PRETTY_PRINT);
    echo "   " . str_replace("\n", "\n   ", substr($data_json, 0, 500)) . "...\n\n";
    
    echo "9. SPECIFIC COLUMN LABELS:\n";
    foreach ($columns as $i => $col) {
        $label = $col['label'] ?? 'MISSING_LABEL';
        $unit = $col['unit'] ?? '';
        echo "   Column $i: '$label'" . ($unit ? " ($unit)" : '') . "\n";
    }
    echo "\n";
    
    // Check for potential issues
    echo "10. POTENTIAL ISSUES CHECK:\n";
    
    // Check if column labels are missing
    $missing_labels = [];
    foreach ($columns as $i => $col) {
        if (empty($col['label'])) {
            $missing_labels[] = $i;
        }
    }
    
    if (!empty($missing_labels)) {
        echo "   ❌ Missing column labels at indices: " . implode(', ', $missing_labels) . "\n";
    } else {
        echo "   ✅ All column labels present\n";
    }
    
    // Check if data matches column count
    if (!empty($outcome_data)) {
        $first_month_data = reset($outcome_data);
        $data_columns = count($first_month_data);
        $config_columns = count($columns);
        
        if ($data_columns === $config_columns) {
            echo "   ✅ Data column count ($data_columns) matches config ($config_columns)\n";
        } else {
            echo "   ❌ Data column count ($data_columns) doesn't match config ($config_columns)\n";
        }
    }
    
    // Check for null/empty data
    $null_data_count = 0;
    foreach ($outcome_data as $month => $data) {
        foreach ($data as $value) {
            if ($value === null || $value === '') {
                $null_data_count++;
            }
        }
    }
    echo "   📊 Null/empty data points: $null_data_count\n";
    
} else {
    echo "   Using legacy structure (unexpected for this data)\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?>
