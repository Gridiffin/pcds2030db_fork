<?php
// Test version of manage_outcomes.php without authentication
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing manage_outcomes.php components</h1>";

// Test 1: Basic includes
echo "<h2>Test 1: Include files</h2>";

try {
    echo "Including config...<br>";
    require_once '../../../config/config.php';
    echo "✓ Config included successfully<br>";
    
    echo "Including db_connect...<br>";
    require_once ROOT_PATH . 'app/lib/db_connect.php';
    echo "✓ Database connection included<br>";
    
    echo "Including session...<br>";
    require_once ROOT_PATH . 'app/lib/session.php';
    echo "✓ Session included<br>";
    
    echo "Including functions...<br>";
    require_once ROOT_PATH . 'app/lib/functions.php';
    echo "✓ Functions included<br>";
    
    echo "Including admin functions...<br>";
    require_once ROOT_PATH . 'app/lib/admins/index.php';
    echo "✓ Admin functions included<br>";
    
} catch (Exception $e) {
    echo "✗ Include failed: " . $e->getMessage() . "<br>";
}

// Test 2: Function availability
echo "<h2>Test 2: Function availability</h2>";

if (function_exists('get_all_outcomes_data')) {
    echo "✓ get_all_outcomes_data function exists<br>";
} else {
    echo "✗ get_all_outcomes_data function missing<br>";
}

if (function_exists('is_admin')) {
    echo "✓ is_admin function exists<br>";
} else {
    echo "✗ is_admin function missing<br>";
}

// Test 3: Database connection
echo "<h2>Test 3: Database connection</h2>";

if (isset($conn) && $conn) {
    echo "✓ Database connection available<br>";
} else {
    echo "✗ Database connection not available<br>";
}

// Test 4: Call get_all_outcomes_data
echo "<h2>Test 4: Get outcomes data</h2>";

try {
    $outcomes = get_all_outcomes_data(0);
    echo "✓ get_all_outcomes_data called successfully<br>";
    echo "Type: " . gettype($outcomes) . "<br>";
    if (is_array($outcomes)) {
        echo "Count: " . count($outcomes) . "<br>";
    }
} catch (Exception $e) {
    echo "✗ get_all_outcomes_data failed: " . $e->getMessage() . "<br>";
}

echo "<h2>Test completed</h2>";
?>
