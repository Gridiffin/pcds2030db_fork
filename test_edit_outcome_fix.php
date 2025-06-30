<?php
/**
 * Test Admin Edit Outcome Page Loading
 * Verify that the page loads without syntax errors and data initializes correctly
 */

// Simulate checking the PHP syntax and data flow
echo "Testing Admin Edit Outcome Page...\n\n";

// Test 1: Check file exists and has no syntax errors
$file_path = __DIR__ . '/app/views/admin/outcomes/edit_outcome.php';
if (file_exists($file_path)) {
    echo "✅ File exists: $file_path\n";
    
    // Basic syntax check by running php -l
    $output = [];
    $return_code = 0;
    exec("php -l \"$file_path\" 2>&1", $output, $return_code);
    
    if ($return_code === 0) {
        echo "✅ PHP syntax is valid\n";
    } else {
        echo "❌ PHP syntax error:\n";
        echo implode("\n", $output) . "\n";
    }
} else {
    echo "❌ File not found: $file_path\n";
}

// Test 2: Check that required elements are present
$content = file_get_contents($file_path);

$checks = [
    'Data initialization from PHP' => 'json_encode($data_array[\'columns\']',
    'Add Row function' => 'function addRow()',
    'Add Column function' => 'function addColumn()',
    'Remove Row function' => 'function removeRow(',
    'Remove Column function' => 'function removeColumn(',
    'Event handlers for row editing' => 'function handleRowTitleEdit(',
    'Event handlers for data cells' => 'function handleDataCellEdit(',
    'Initial table render' => 'renderTable(true)',
    'Form submission handler' => 'editOutcomeForm\').addEventListener(\'submit\'',
    'Button styling consistency' => 'btn btn-primary.*addRowBtn'
];

echo "\nChecking required functionality:\n";
foreach ($checks as $description => $pattern) {
    if (preg_match('/' . str_replace(['(', ')', '[', ']', '\''], ['\(', '\)', '\[', '\]', '\''], $pattern) . '/', $content)) {
        echo "✅ $description\n";
    } else {
        echo "❌ Missing: $description\n";
    }
}

echo "\n🎯 Test completed!\n";
?>
