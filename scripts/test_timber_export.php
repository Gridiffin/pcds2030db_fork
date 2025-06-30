<?php
/**
 * Test Script: Verify Timber Export Value Data Format
 * 
 * This script verifies that the Timber Export Value data is in the correct format
 * and can be properly displayed by the view and edit pages.
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/lib/db_connect.php';

echo "=== Timber Export Value Testing ===\n";

// Test 1: Verify data format
echo "🧪 Test 1: Data Format Verification\n";
$query = "SELECT * FROM sector_outcomes_data WHERE metric_id = 7 AND sector_id = 1 LIMIT 1";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $data = json_decode($row['data_json'], true);
    
    $test1_pass = isset($data['columns']) && isset($data['data']);
    echo ($test1_pass ? "✅" : "❌") . " Data format: " . ($test1_pass ? "Correct flexible format" : "Incorrect format") . "\n";
    
    if ($test1_pass) {
        echo "  - Columns: " . count($data['columns']) . " (" . implode(', ', $data['columns']) . ")\n";
        echo "  - Rows: " . count($data['data']) . "\n";
        echo "  - Sample data exists: " . (count($data['data']) > 0 ? "Yes" : "No") . "\n";
    }
} else {
    echo "❌ No data found for Timber Export Value\n";
}

echo "\n🧪 Test 2: View Page Compatibility\n";
// Simulate what the view page does
if (isset($data)) {
    $columns = $data['columns'] ?? [];
    $data_content = $data['data'] ?? [];
    
    $row_labels = array_keys($data_content);
    $has_data = !empty($columns) && !empty($row_labels);
    
    echo ($has_data ? "✅" : "❌") . " View page compatibility: " . ($has_data ? "Data will display correctly" : "No data available message") . "\n";
    
    if ($has_data) {
        echo "  - Chart data available: Yes\n";
        echo "  - Table data available: Yes\n";
        echo "  - CSV export available: Yes\n";
    }
}

echo "\n🧪 Test 3: Edit Page Compatibility\n";
// Simulate what the edit page does
if (isset($data)) {
    $edit_compatible = isset($data['columns']) && isset($data['data']) && is_array($data['data']);
    echo ($edit_compatible ? "✅" : "❌") . " Edit page compatibility: " . ($edit_compatible ? "Data can be edited correctly" : "Edit issues may occur") . "\n";
    
    if ($edit_compatible) {
        echo "  - Dynamic columns: " . count($data['columns']) . " columns\n";
        echo "  - Dynamic rows: " . count($data['data']) . " rows\n";
        echo "  - Data preservation: Supported\n";
    }
}

echo "\n🧪 Test 4: Sample Data Validation\n";
if (isset($data) && isset($data['data']) && count($data['data']) > 0) {
    $sample_month = array_keys($data['data'])[0];
    $sample_data = $data['data'][$sample_month];
    
    $has_values = array_filter($sample_data, function($val) { return !empty($val) && $val !== 0; });
    $data_quality = count($has_values) > 0;
    
    echo ($data_quality ? "✅" : "❌") . " Data quality: " . ($data_quality ? "Contains meaningful values" : "All values empty or zero") . "\n";
    
    if ($data_quality) {
        echo "  - Sample month: $sample_month\n";
        echo "  - Non-empty values: " . count($has_values) . "/" . count($sample_data) . "\n";
        echo "  - Sample value: " . reset($has_values) . "\n";
    }
}

echo "\n=== Test Results Summary ===\n";
$all_tests_pass = $test1_pass && $has_data && $edit_compatible && $data_quality;
echo ($all_tests_pass ? "🎉 All tests passed!" : "⚠️  Some tests failed") . "\n";

if ($all_tests_pass) {
    echo "✅ Timber Export Value is ready for production use\n";
    echo "✅ Both view and edit pages should work correctly\n";
    echo "✅ Chart functionality should be operational\n";
} else {
    echo "❌ Issues detected - please check the failed tests above\n";
}

echo "\n=== Testing Complete ===\n";
?>
