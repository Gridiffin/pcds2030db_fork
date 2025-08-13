<?php
/**
 * Test script to verify get_initiative_number() function fix
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';

echo "<h2>Testing get_initiative_number() Function</h2>\n";

// Test 1: Function exists
if (function_exists('get_initiative_number')) {
    echo "✅ get_initiative_number() function exists<br>\n";
} else {
    echo "❌ get_initiative_number() function does not exist<br>\n";
    exit;
}

// Test 2: Test with null/empty value
$result = get_initiative_number(null);
if ($result === null) {
    echo "✅ Function correctly returns null for null input<br>\n";
} else {
    echo "❌ Function should return null for null input, got: " . var_export($result, true) . "<br>\n";
}

// Test 3: Test with invalid ID
$result = get_initiative_number(999999);
if ($result === null) {
    echo "✅ Function correctly returns null for invalid ID<br>\n";
} else {
    echo "❌ Function should return null for invalid ID, got: " . var_export($result, true) . "<br>\n";
}

// Test 4: Test with real initiative ID (if any exist)
$sql = "SELECT initiative_id, initiative_number FROM initiatives LIMIT 1";
$result_query = $conn->query($sql);
if ($result_query && $row = $result_query->fetch_assoc()) {
    $test_id = $row['initiative_id'];
    $expected_number = $row['initiative_number'];
    
    $actual_number = get_initiative_number($test_id);
    
    if ($actual_number === $expected_number) {
        echo "✅ Function correctly returns initiative number '{$actual_number}' for ID {$test_id}<br>\n";
    } else {
        echo "❌ Function returned '{$actual_number}' but expected '{$expected_number}' for ID {$test_id}<br>\n";
    }
} else {
    echo "ℹ️ No initiatives found in database to test with real data<br>\n";
}

echo "<br><strong>Testing complete!</strong><br>\n";
?>
