<?php
/**
 * Debug script to check timber export data in outcomes table
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/outcomes.php';

echo "<h2>Debug: Timber Export Data in Outcomes Table</h2>\n";

// Check if outcomes table exists
$check_table = "SHOW TABLES LIKE 'outcomes'";
$result = $conn->query($check_table);
if ($result->num_rows == 0) {
    echo "❌ outcomes table does not exist!<br>\n";
    exit;
} else {
    echo "✅ outcomes table exists<br>\n";
}

// Get all outcomes
echo "<h3>All Outcomes in Database:</h3>\n";
$outcomes = get_all_outcomes();
if (empty($outcomes)) {
    echo "❌ No outcomes found in database<br>\n";
} else {
    echo "✅ Found " . count($outcomes) . " outcomes:<br>\n";
    foreach ($outcomes as $outcome) {
        echo "- ID: {$outcome['id']}, Code: '{$outcome['code']}', Title: '{$outcome['title']}'<br>\n";
        
        // Check if this is timber export data
        if ($outcome['code'] === 'timber_export') {
            echo "  🎯 This is timber export data!<br>\n";
            echo "  Data structure: <pre>" . print_r($outcome['data'], true) . "</pre><br>\n";
        }
    }
}

// Specifically look for timber export
echo "<h3>Specific Timber Export Check:</h3>\n";
$timber_export = get_outcome_by_code('timber_export');
if ($timber_export) {
    echo "✅ Found timber export outcome:<br>\n";
    echo "Title: {$timber_export['title']}<br>\n";
    echo "Type: {$timber_export['type']}<br>\n";
    echo "Data: <pre>" . print_r($timber_export['data'], true) . "</pre><br>\n";
} else {
    echo "❌ No timber export outcome found with code 'timber_export'<br>\n";
}

// Check if there are any outcomes with 'timber' in the code or title
echo "<h3>Search for anything with 'timber' in name:</h3>\n";
$timber_related = [];
foreach ($outcomes as $outcome) {
    if (stripos($outcome['code'], 'timber') !== false || stripos($outcome['title'], 'timber') !== false) {
        $timber_related[] = $outcome;
    }
}

if (!empty($timber_related)) {
    echo "✅ Found " . count($timber_related) . " timber-related outcomes:<br>\n";
    foreach ($timber_related as $outcome) {
        echo "- ID: {$outcome['id']}, Code: '{$outcome['code']}', Title: '{$outcome['title']}'<br>\n";
    }
} else {
    echo "❌ No timber-related outcomes found<br>\n";
}

echo "<br><strong>Debug complete!</strong><br>\n";
?>
