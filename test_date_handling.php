<?php
// Test date handling with NULL values to replicate the issue

echo "=== Testing Date Handling with NULL Values ===\n";

// Simulate the database values we found
$program = [
    'start_date' => null,
    'end_date' => null
];

echo "start_date value: " . var_export($program['start_date'], true) . "\n";
echo "end_date value: " . var_export($program['end_date'], true) . "\n";

// Test the exact code from the edit form
echo "\n=== Testing Form Value Generation ===\n";
$start_value = isset($program['start_date']) ? date('Y-m-d', strtotime($program['start_date'])) : '';
$end_value = isset($program['end_date']) ? date('Y-m-d', strtotime($program['end_date'])) : '';

echo "start_date form value: '" . $start_value . "'\n";
echo "end_date form value: '" . $end_value . "'\n";

// Test what happens if the isset check fails
echo "\n=== Testing Direct strtotime with NULL ===\n";
$direct_strtotime = strtotime(null);
echo "strtotime(null): " . var_export($direct_strtotime, true) . "\n";

if ($direct_strtotime !== false) {
    echo "date('Y-m-d', strtotime(null)): " . date('Y-m-d', $direct_strtotime) . "\n";
}

// Test what happens with different NULL-like values
echo "\n=== Testing Edge Cases ===\n";
$test_values = [null, '', '0000-00-00', '0000-00-00 00:00:00'];

foreach ($test_values as $test_value) {
    echo "Testing value: " . var_export($test_value, true) . "\n";
    echo "  isset(): " . (isset($test_value) ? 'true' : 'false') . "\n";
    echo "  empty(): " . (empty($test_value) ? 'true' : 'false') . "\n";
    echo "  strtotime(): " . var_export(strtotime($test_value), true) . "\n";
    
    if (strtotime($test_value) !== false) {
        echo "  date('Y-m-d'): " . date('Y-m-d', strtotime($test_value)) . "\n";
    }
    echo "\n";
}
?>
