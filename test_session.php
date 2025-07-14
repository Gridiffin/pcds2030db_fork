<?php
/**
 * Session Management Test Script
 * Tests if agency_id is properly set in session after login
 */

// Include required files
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/lib/functions.php';

echo "<h2>Session Management Test</h2>\n";

// Test if session variables are properly set
session_start();

echo "<h3>Current Session Variables:</h3>\n";
echo "<pre>\n";
if (isset($_SESSION) && !empty($_SESSION)) {
    foreach ($_SESSION as $key => $value) {
        if ($key !== 'pw') { // Don't display password
            echo "$key: " . print_r($value, true) . "\n";
        }
    }
} else {
    echo "No session variables set.\n";
}
echo "</pre>\n";

// Check specifically for agency_id
if (isset($_SESSION['agency_id'])) {
    echo "<p style='color: green;'>✅ agency_id is set in session: " . $_SESSION['agency_id'] . "</p>\n";
} else {
    echo "<p style='color: red;'>❌ agency_id is NOT set in session</p>\n";
}

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ User is logged in with ID: " . $_SESSION['user_id'] . "</p>\n";
} else {
    echo "<p style='color: red;'>❌ User is NOT logged in</p>\n";
}

// Check role
if (isset($_SESSION['role'])) {
    echo "<p style='color: blue;'>ℹ️ User role: " . $_SESSION['role'] . "</p>\n";
} else {
    echo "<p style='color: red;'>❌ User role is NOT set</p>\n";
}

echo "<hr>\n";
echo "<p><strong>Instructions:</strong></p>\n";
echo "<ol>\n";
echo "<li>Log in as a regular user (not admin)</li>\n";
echo "<li>Refresh this page to see session variables</li>\n";
echo "<li>Verify that agency_id is properly set</li>\n";
echo "<li>Test the programs list page to ensure no infinite loading</li>\n";
echo "</ol>\n";

echo "<p><a href='login.php'>Go to Login</a> | <a href='app/views/agency/programs/view_programs.php'>Test Programs List</a></p>\n";
?>
