<?php
/**
 * Simple AJAX Test
 * 
 * This file tests the AJAX endpoint directly
 */

// Define PROJECT_ROOT_PATH
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/notifications.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>AJAX Endpoint Test</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>User not logged in. Session data:</p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    exit;
}

echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";

// Test the enhanced function directly
try {
    echo "<h2>Testing get_user_notifications_enhanced function:</h2>";
    
    $result = get_user_notifications_enhanced($_SESSION['user_id'], 1, 10, false, false, false);
    
    if ($result === false) {
        echo "<p style='color: red;'>Function returned false</p>";
    } else {
        echo "<p style='color: green;'>Function returned data:</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
}

// Test the original function
try {
    echo "<h2>Testing get_user_notifications function:</h2>";
    
    $result = get_user_notifications($_SESSION['user_id'], 1, 10, false);
    
    if ($result === false) {
        echo "<p style='color: red;'>Function returned false</p>";
    } else {
        echo "<p style='color: green;'>Function returned data:</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
}

// Test format_time_ago function
echo "<h2>Testing format_time_ago function:</h2>";
try {
    $test_time = date('Y-m-d H:i:s');
    $formatted = format_time_ago($test_time);
    echo "<p>Test time: $test_time</p>";
    echo "<p>Formatted: $formatted</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
}

echo "<h2>Database Connection Test:</h2>";
if (isset($conn) && $conn) {
    echo "<p style='color: green;'>Database connection successful</p>";
} else {
    echo "<p style='color: red;'>Database connection failed</p>";
}
?>