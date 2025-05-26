<?php
/**
 * Test Reports Navigation
 * 
 * Simple test to debug why the reports navigation is redirecting to dashboard
 */

// Include config
require_once '../../../config/config.php';

echo "<h1>Reports Navigation Debug</h1>";
echo "<p><strong>APP_URL:</strong> " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "</p>";
echo "<p><strong>Current Page:</strong> " . basename($_SERVER['PHP_SELF']) . "</p>";
echo "<p><strong>Full URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Generated Reports URL:</strong> " . APP_URL . "/app/views/admin/reports/generate_reports.php</p>";

// Check if user is logged in and is admin
session_start();
echo "<p><strong>Session User ID:</strong> " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";
echo "<p><strong>Session Role:</strong> " . ($_SESSION['role'] ?? 'NOT SET') . "</p>";

// Include functions to test is_admin()
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admin_functions.php';

echo "<p><strong>is_admin() result:</strong> " . (is_admin() ? 'TRUE' : 'FALSE') . "</p>";

if (!is_admin()) {
    echo "<p><strong style='color: red;'>WARNING: User is not admin! This would redirect to login.</strong></p>";
    echo "<p><strong>Redirect URL would be:</strong> " . APP_URL . "/login.php</p>";
} else {
    echo "<p><strong style='color: green;'>User is admin - no redirect needed.</strong></p>";
}

echo "<hr>";
echo "<p><a href='" . APP_URL . "/app/views/admin/reports/generate_reports.php'>Test Direct Link to Generate Reports</a></p>";
echo "<p><a href='" . APP_URL . "/app/views/admin/dashboard/dashboard.php'>Go to Dashboard</a></p>";
?>
