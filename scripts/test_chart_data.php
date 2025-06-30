<?php
/**
 * Test Chart Data Preparation
 * 
 * This script tests that chart data is prepared correctly for the new flexible format
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/lib/db_connect.php';

echo "=== Chart Data Preparation Test ===\n";

// Get the Timber Export Value data
$query = "SELECT data_json FROM sector_outcomes_data WHERE metric_id = 7 AND sector_id = 1 LIMIT 1";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$outcome_data = json_decode($row['data_json'], true);

echo "📊 Raw Database Data:\n";
echo "- Format: " . (isset($outcome_data['columns']) ? "New flexible format" : "Unknown format") . "\n";

// Simulate the PHP data preparation (as done in view_outcome.php)
$data_array = $outcome_data ?? ['columns' => [], 'data' => []];

if (!isset($data_array['columns']) || !isset($data_array['data'])) {
    $data_array = ['columns' => [], 'data' => []];
}

$columns = $data_array['columns'] ?? [];
$data = $data_array['data'] ?? [];

$row_labels = [];
if (!empty($data) && is_array($data)) {
    $row_labels = array_keys($data);
}

echo "- Columns: " . count($columns) . " (" . implode(', ', $columns) . ")\n";
echo "- Rows: " . count($row_labels) . " (" . implode(', ', array_slice($row_labels, 0, 3)) . "...)\n";

echo "\n🧪 Chart Data Simulation:\n";

// Simulate what the JavaScript chart code does
$chart_data_test = [];

foreach ($columns as $column_index => $column) {
    $dataset_data = [];
    foreach ($row_labels as $row) {
        $cell_value = $data[$row][$column] ?? null;
        
        // Apply the same logic as the updated chart code
        if ($cell_value === null || $cell_value === '') {
            $numeric_value = 0;
        } else {
            $numeric_value = is_numeric($cell_value) ? (float)$cell_value : 0;
        }
        
        $dataset_data[] = $numeric_value;
    }
    
    $chart_data_test[$column] = $dataset_data;
    
    echo "Dataset '$column':\n";
    echo "  - Values: " . count($dataset_data) . " data points\n";
    echo "  - Sample: " . implode(', ', array_slice($dataset_data, 0, 3)) . "...\n";
    echo "  - Total: " . number_format(array_sum($dataset_data), 2) . "\n";
}

echo "\n🎯 Chart Compatibility Test:\n";

// Test if the data structure matches what Chart.js expects
$chart_structure_valid = true;
$issues = [];

if (empty($columns)) {
    $chart_structure_valid = false;
    $issues[] = "No columns found";
}

if (empty($row_labels)) {
    $chart_structure_valid = false;
    $issues[] = "No row labels found";
}

foreach ($chart_data_test as $column => $dataset) {
    if (empty($dataset)) {
        $chart_structure_valid = false;
        $issues[] = "Empty dataset for column '$column'";
    }
    
    $non_numeric_count = 0;
    foreach ($dataset as $value) {
        if (!is_numeric($value)) {
            $non_numeric_count++;
        }
    }
    
    if ($non_numeric_count > 0) {
        $chart_structure_valid = false;
        $issues[] = "Non-numeric values in dataset '$column': $non_numeric_count values";
    }
}

echo ($chart_structure_valid ? "✅" : "❌") . " Chart structure validation: " . ($chart_structure_valid ? "Valid" : "Issues found") . "\n";

if (!$chart_structure_valid) {
    echo "Issues:\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
}

echo "\n📈 Expected Chart Output:\n";
echo "- Chart type: Bar chart\n";
echo "- X-axis labels: " . implode(', ', $row_labels) . "\n";
echo "- Y-axis datasets: " . implode(', ', $columns) . "\n";
echo "- Data format: Numeric values ready for Chart.js\n";

echo "\n=== Test Complete ===\n";
?>
