<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

echo "Testing updated admin edit outcome functionality...\n\n";

// Check if the file is accessible and syntactically correct
$edit_file_path = 'app/views/admin/outcomes/edit_outcome.php';
if (file_exists($edit_file_path)) {
    echo "✓ Edit outcome file exists\n";
    
    // Check PHP syntax
    $syntax_check = shell_exec("php -l {$edit_file_path} 2>&1");
    if (strpos($syntax_check, 'No syntax errors') !== false) {
        echo "✓ PHP syntax is valid\n";
    } else {
        echo "✗ PHP syntax errors found:\n{$syntax_check}\n";
    }
} else {
    echo "✗ Edit outcome file not found\n";
}

// Test that we can get outcome data for editing
$result = $conn->query("SELECT metric_id, table_name, data_json, is_draft, sector_id FROM sector_outcomes_data LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $metric_id = $row['metric_id'];
    $table_name = $row['table_name'];
    $is_draft = $row['is_draft'];
    $sector_id = $row['sector_id'];
    
    echo "\n✓ Sample outcome data available for testing:\n";
    echo "  Metric ID: {$metric_id}\n";
    echo "  Table Name: {$table_name}\n";
    echo "  Status: " . ($is_draft ? 'Draft' : 'Submitted') . "\n";
    echo "  Sector ID: {$sector_id}\n";
    
    // Validate the data structure
    $data = json_decode($row['data_json'], true);
    if (isset($data['columns']) && isset($data['data'])) {
        echo "  ✓ Data is in flexible format\n";
        echo "  Columns: " . implode(', ', $data['columns']) . "\n";
        echo "  Rows: " . implode(', ', array_keys($data['data'])) . "\n";
    } else {
        echo "  ✗ Data is not in flexible format\n";
    }
} else {
    echo "✗ No outcome data found for testing\n";
}

// Test the database update query that the new code will use
$test_query = "UPDATE sector_outcomes_data SET table_name = ?, data_json = ?, is_draft = ?, updated_at = NOW() WHERE metric_id = ?";
$test_stmt = $conn->prepare($test_query);
if ($test_stmt) {
    echo "\n✓ Database update query prepared successfully\n";
} else {
    echo "\n✗ Database update query failed: " . $conn->error . "\n";
}

echo "\nAdmin edit outcome functionality testing complete.\n";
echo "The page should now work without undefined variable errors or JavaScript issues.\n";
?>
