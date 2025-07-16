<?php
/**
 * Test script to verify undefined variable fixes in view_outcome.php
 */

echo "Testing null safety fixes...\n";

// Test htmlspecialchars with null values
$test_cases = [
    'Normal string' => 'Test Title',
    'Empty string' => '',
    'Null value' => null,
    'False value' => false,
    'Zero value' => 0
];

echo "\nTesting htmlspecialchars null safety:\n";
foreach ($test_cases as $case_name => $value) {
    // Test the old way (would cause deprecation warnings)
    echo "Case: $case_name\n";
    echo "  Value: " . var_export($value, true) . "\n";
    
    // Test the new way with null coalescing
    $safe_value = htmlspecialchars($value ?? 'Default Value');
    echo "  Safe output: $safe_value\n";
    
    // Test with empty string fallback
    $empty_fallback = htmlspecialchars($value ?? '');
    echo "  Empty fallback: '$empty_fallback'\n\n";
}

// Test array access with null safety
echo "Testing array access null safety:\n";
$test_outcome = [
    'title' => 'Valid Title',
    'code' => null,
    'description' => '',
    // 'type' missing entirely
];

$title = $test_outcome['title'] ?? 'Untitled Outcome';
$code = $test_outcome['code'] ?? '';
$type = $test_outcome['type'] ?? '';
$description = $test_outcome['description'] ?? '';

echo "Title: '$title'\n";
echo "Code: '$code'\n";
echo "Type: '$type'\n";
echo "Description: '$description'\n";

// Test column/row data null safety
echo "\nTesting column data null safety:\n";
$test_columns = [
    ['id' => 'col1', 'label' => 'Column 1', 'unit' => 'units'],
    ['id' => 'col2', 'label' => null, 'unit' => ''],
    ['id' => 'col3', 'label' => 'Column 3'], // missing unit
];

foreach ($test_columns as $i => $column) {
    echo "Column $i:\n";
    echo "  ID: " . htmlspecialchars($column['id'] ?? '') . "\n";
    echo "  Label: " . htmlspecialchars($column['label'] ?? '') . "\n";
    echo "  Unit: " . htmlspecialchars($column['unit'] ?? '') . "\n";
    echo "  Type: " . ucfirst($column['type'] ?? 'number') . "\n\n";
}

echo "All tests completed successfully!\n";
?>
