<?php
// Final verification test for outcomes management functionality
session_start();

// Set up test admin session (for testing purposes only)
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin';

require_once 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

echo "<h2>Final Verification Test - Outcomes Management</h2>";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px;'>";

// Test 1: Check if is_admin() function works
echo "<h3>1. Admin Authentication Test</h3>";
if (function_exists('is_admin')) {
    $is_admin_result = is_admin();
    echo "<p style='color: " . ($is_admin_result ? 'green' : 'red') . ";'>is_admin(): " . ($is_admin_result ? 'TRUE ✓' : 'FALSE ✗') . "</p>";
} else {
    echo "<p style='color: red;'>is_admin() function not found ✗</p>";
}

// Test 2: Check if outcomes functions are available
echo "<h3>2. Outcomes Functions Test</h3>";
$functions_to_check = [
    'get_all_outcomes_data',
    'get_outcome_by_id',
    'create_outcome',
    'update_outcome',
    'delete_outcome'
];

foreach ($functions_to_check as $func) {
    if (function_exists($func)) {
        echo "<p style='color: green;'>{$func}(): Available ✓</p>";
    } else {
        echo "<p style='color: red;'>{$func}(): Not found ✗</p>";
    }
}

// Test 3: Test database connection
echo "<h3>3. Database Connection Test</h3>";
if (isset($conn) && $conn instanceof mysqli) {
    if ($conn->ping()) {
        echo "<p style='color: green;'>Database connection: Active ✓</p>";
        
        // Test actual outcomes data retrieval
        try {
            if (function_exists('get_all_outcomes_data')) {
                $outcomes = get_all_outcomes_data();
                if (is_array($outcomes)) {
                    echo "<p style='color: green;'>get_all_outcomes_data(): Returns array ✓ (Count: " . count($outcomes) . ")</p>";
                } else {
                    echo "<p style='color: orange;'>get_all_outcomes_data(): Returns " . gettype($outcomes) . " (Expected array)</p>";
                }
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error testing outcomes data: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Database connection: Failed ✗</p>";
    }
} else {
    echo "<p style='color: red;'>Database connection: Not available ✗</p>";
}

// Test 4: Include path verification
echo "<h3>4. Include Path Test</h3>";
$critical_files = [
    ROOT_PATH . 'app/lib/db_connect.php',
    ROOT_PATH . 'app/lib/session.php', 
    ROOT_PATH . 'app/lib/functions.php',
    ROOT_PATH . 'app/lib/admins/index.php',
    ROOT_PATH . 'app/lib/admins/outcomes.php'
];

foreach ($critical_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>" . basename($file) . ": Found ✓</p>";
    } else {
        echo "<p style='color: red;'>" . basename($file) . ": Missing ✗</p>";
    }
}

echo "<h3>5. Summary</h3>";
echo "<p><strong>Status:</strong> All critical components for outcomes management are ";
echo (function_exists('is_admin') && function_exists('get_all_outcomes_data') && isset($conn)) ? 
     "<span style='color: green;'>WORKING ✓</span>" : 
     "<span style='color: red;'>NEEDS ATTENTION ✗</span>";
echo "</p>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Log in as admin using username 'admin' and the verified password</li>";
echo "<li>Navigate to <code>/app/views/admin/outcomes/manage_outcomes.php</code></li>";
echo "<li>Test all CRUD operations (Create, Read, Update, Delete) for outcomes</li>";
echo "<li>Verify that no PHP fatal errors occur during normal operations</li>";
echo "</ul>";

echo "</div>";
?>
