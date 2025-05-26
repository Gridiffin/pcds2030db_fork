<?php
/**
 * Test Session and Authentication
 * 
 * Debug authentication issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Session and Authentication Debug</h1>";

// Start session and check session variables
session_start();
echo "<h2>Session Status</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";

echo "<h2>Session Variables</h2>";
if (empty($_SESSION)) {
    echo "<strong style='color: red;'>Session is empty - this means user is not logged in!</strong><br>";
} else {
    foreach ($_SESSION as $key => $value) {
        echo "$key: " . (is_array($value) ? json_encode($value) : $value) . "<br>";
    }
}

echo "<h2>Include Files Test</h2>";
try {
    require_once '../../../config/config.php';
    echo "✅ Config included successfully<br>";
    echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "<br>";
    echo "ROOT_PATH: " . (defined('ROOT_PATH') ? ROOT_PATH : 'NOT DEFINED') . "<br>";
} catch (Exception $e) {
    echo "❌ Config include failed: " . $e->getMessage() . "<br>";
}

try {
    require_once ROOT_PATH . 'app/lib/functions.php';
    echo "✅ Functions included successfully<br>";
} catch (Exception $e) {
    echo "❌ Functions include failed: " . $e->getMessage() . "<br>";
}

try {
    require_once ROOT_PATH . 'app/lib/admins/core.php';
    echo "✅ Admin core included successfully<br>";
} catch (Exception $e) {
    echo "❌ Admin core include failed: " . $e->getMessage() . "<br>";
}

echo "<h2>Authentication Functions Test</h2>";

// Test is_logged_in function
if (function_exists('is_logged_in')) {
    $logged_in = is_logged_in();
    echo "is_logged_in(): " . ($logged_in ? 'TRUE' : 'FALSE') . "<br>";
} else {
    echo "❌ is_logged_in() function not found<br>";
}

// Test is_admin function
if (function_exists('is_admin')) {
    $is_admin = is_admin();
    echo "is_admin(): " . ($is_admin ? 'TRUE' : 'FALSE') . "<br>";
} else {
    echo "❌ is_admin() function not found<br>";
}

echo "<h2>Login Status Summary</h2>";
if (empty($_SESSION)) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<strong>NOT LOGGED IN</strong><br>";
    echo "The user needs to log in first. The authentication redirect is working correctly.";
    echo "</div>";
} else {
    $role = $_SESSION['role'] ?? 'Unknown';
    if ($role === 'admin') {
        echo "<div style='background: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<strong>LOGGED IN AS ADMIN</strong><br>";
        echo "User should be able to access admin pages.";
        echo "</div>";
    } else {
        echo "<div style='background: #ffeaa7; color: #6c757d; padding: 10px; border: 1px solid #ffd93d; border-radius: 5px;'>";
        echo "<strong>LOGGED IN AS: $role</strong><br>";
        echo "User is logged in but not as admin. Access to admin pages will be denied.";
        echo "</div>";
    }
}

echo "<h2>Test Links</h2>";
echo '<a href="' . APP_URL . '/login.php" style="margin-right: 10px;">Go to Login</a>';
echo '<a href="' . APP_URL . '/app/views/admin/dashboard/dashboard.php" style="margin-right: 10px;">Admin Dashboard</a>';
echo '<a href="manage_outcomes.php" style="margin-right: 10px;">Manage Outcomes (Original)</a>';
echo '<a href="manage_outcomes_debug.php">Manage Outcomes (Debug Version)</a>';
?>
