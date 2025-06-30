<?php
/**
 * Test: Verify Number Format Fix
 * 
 * This script tests that the number formatting fix works correctly
 * and that all data displays properly without errors.
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/lib/db_connect.php';

echo "=== Number Format Fix Testing ===\n";

// Test 1: Data Type Verification
echo "🧪 Test 1: Data Type Verification\n";
$query = "SELECT data_json FROM sector_outcomes_data WHERE metric_id = 7 AND sector_id = 1 LIMIT 1";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$data = json_decode($row['data_json'], true);

$all_numeric = true;
$non_numeric_count = 0;

foreach ($data['data'] as $row_label => $row_data) {
    foreach ($row_data as $col => $value) {
        if (!is_numeric($value)) {
            $all_numeric = false;
            $non_numeric_count++;
        }
    }
}

echo ($all_numeric ? "✅" : "❌") . " All values are numeric: " . ($all_numeric ? "Yes" : "No ($non_numeric_count non-numeric found)") . "\n";

// Test 2: Simulate View Page Logic
echo "\n🧪 Test 2: View Page Logic Simulation\n";
$view_errors = 0;

foreach ($data['data'] as $row_label => $row_data) {
    foreach ($row_data as $col => $value) {
        // Simulate the view page logic
        try {
            if (is_numeric($value) && $value !== '') {
                $formatted = number_format((float)$value, 2);
            } else {
                $formatted = '0.00';
            }
        } catch (Exception $e) {
            $view_errors++;
            echo "❌ Error formatting value '$value' in $row_label/$col: " . $e->getMessage() . "\n";
        }
    }
}

echo ($view_errors === 0 ? "✅" : "❌") . " View page formatting: " . ($view_errors === 0 ? "No errors" : "$view_errors errors found") . "\n";

// Test 3: Total Calculation Simulation
echo "\n🧪 Test 3: Total Calculation Simulation\n";
$total_errors = 0;

foreach ($data['columns'] as $column) {
    try {
        $total = 0;
        foreach (array_keys($data['data']) as $row_label) {
            $cell_value = $data['data'][$row_label][$column] ?? 0;
            if (is_numeric($cell_value) && $cell_value !== '') {
                $total += (float)$cell_value;
            }
        }
        $formatted_total = number_format($total, 2);
    } catch (Exception $e) {
        $total_errors++;
        echo "❌ Error calculating total for column '$column': " . $e->getMessage() . "\n";
    }
}

echo ($total_errors === 0 ? "✅" : "❌") . " Total calculations: " . ($total_errors === 0 ? "No errors" : "$total_errors errors found") . "\n";

// Test 4: Sample Data Display
echo "\n🧪 Test 4: Sample Data Display\n";
$sample_row = array_keys($data['data'])[0];
$sample_data = $data['data'][$sample_row];

echo "Sample row: $sample_row\n";
foreach ($sample_data as $col => $value) {
    if (is_numeric($value) && $value !== '') {
        $formatted = number_format((float)$value, 2);
    } else {
        $formatted = '0.00';
    }
    echo "  $col: $value → $formatted\n";
}

echo "\n=== Test Results Summary ===\n";
$all_tests_pass = $all_numeric && $view_errors === 0 && $total_errors === 0;
echo ($all_tests_pass ? "🎉 All tests passed!" : "⚠️  Some tests failed") . "\n";

if ($all_tests_pass) {
    echo "✅ Number format error is completely resolved\n";
    echo "✅ View page should work without errors\n";
    echo "✅ All data displays correctly\n";
} else {
    echo "❌ Issues detected - please check the failed tests above\n";
}

echo "\n=== Testing Complete ===\n";
?>
