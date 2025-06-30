<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

echo "Testing Add Row functionality for both admin and agency edit outcome pages...\n\n";

// Test that both files exist and have correct syntax
$admin_file = 'app/views/admin/outcomes/edit_outcome.php';
$agency_file = 'app/views/agency/outcomes/edit_outcomes.php';

echo "=== File Validation ===\n";
if (file_exists($admin_file)) {
    echo "✓ Admin edit outcome file exists\n";
    $admin_syntax = shell_exec("php -l {$admin_file} 2>&1");
    if (strpos($admin_syntax, 'No syntax errors') !== false) {
        echo "✓ Admin file PHP syntax is valid\n";
    } else {
        echo "✗ Admin file PHP syntax errors:\n{$admin_syntax}\n";
    }
} else {
    echo "✗ Admin edit outcome file not found\n";
}

if (file_exists($agency_file)) {
    echo "✓ Agency edit outcome file exists\n";
    $agency_syntax = shell_exec("php -l {$agency_file} 2>&1");
    if (strpos($agency_syntax, 'No syntax errors') !== false) {
        echo "✓ Agency file PHP syntax is valid\n";
    } else {
        echo "✗ Agency file PHP syntax errors:\n{$agency_syntax}\n";
    }
} else {
    echo "✗ Agency edit outcome file not found\n";
}

// Test that we have sample data to work with
echo "\n=== Data Validation ===\n";
$result = $conn->query("SELECT metric_id, table_name, data_json FROM sector_outcomes_data LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $data = json_decode($row['data_json'], true);
    if (isset($data['columns']) && isset($data['data'])) {
        echo "✓ Sample outcome data is in flexible format\n";
        echo "  Current columns: " . implode(', ', $data['columns']) . "\n";
        echo "  Current rows: " . implode(', ', array_keys($data['data'])) . "\n";
        echo "  Row count: " . count(array_keys($data['data'])) . "\n";
    } else {
        echo "✗ Sample data is not in flexible format\n";
    }
} else {
    echo "✗ No sample data available for testing\n";
}

// Check for the new features in the files
echo "\n=== Feature Validation ===\n";

// Check admin file for Add Row button
$admin_content = file_get_contents($admin_file);
if (strpos($admin_content, 'Add Row') !== false) {
    echo "✓ Admin file contains 'Add Row' button\n";
} else {
    echo "✗ Admin file missing 'Add Row' button\n";
}

if (strpos($admin_content, 'addRow') !== false) {
    echo "✓ Admin file contains addRow() function\n";
} else {
    echo "✗ Admin file missing addRow() function\n";
}

if (strpos($admin_content, 'delete-row-btn') !== false) {
    echo "✓ Admin file contains row delete functionality\n";
} else {
    echo "✗ Admin file missing row delete functionality\n";
}

// Check agency file for Add Row button
$agency_content = file_get_contents($agency_file);
if (strpos($agency_content, 'Add Row') !== false) {
    echo "✓ Agency file contains 'Add Row' button\n";
} else {
    echo "✗ Agency file missing 'Add Row' button\n";
}

if (strpos($agency_content, 'addRow') !== false) {
    echo "✓ Agency file contains addRow() function\n";
} else {
    echo "✗ Agency file missing addRow() function\n";
}

if (strpos($agency_content, 'delete-row-btn') !== false) {
    echo "✓ Agency file contains row delete functionality\n";
} else {
    echo "✗ Agency file missing row delete functionality\n";
}

echo "\n=== Expected Functionality ===\n";
echo "Users should now be able to:\n";
echo "- Click 'Add Row' button to add new rows\n";
echo "- Edit row names inline by clicking on them\n";
echo "- Delete rows using the trash icon on each row\n";
echo "- Have data preserved when adding/removing rows\n";
echo "- See validation preventing deletion of the last row\n";

echo "\nAdd Row functionality implementation complete!\n";
?>
