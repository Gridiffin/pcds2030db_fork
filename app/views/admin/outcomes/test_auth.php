<?php
// Test authentication status
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

echo "<h1>Authentication Status Test</h1>";

// Check session
echo "<h2>Session Status</h2>";
if (session_status() == PHP_SESSION_ACTIVE) {
    echo "✓ Session is active<br>";
    echo "Session ID: " . session_id() . "<br>";
    if (isset($_SESSION)) {
        echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";
    }
} else {
    echo "✗ Session is not active<br>";
}

// Check if user is logged in
echo "<h2>User Status</h2>";
if (function_exists('is_logged_in')) {
    if (is_logged_in()) {
        echo "✓ User is logged in<br>";
    } else {
        echo "✗ User is not logged in<br>";
    }
} else {
    echo "✗ is_logged_in function not found<br>";
}

// Check if user is admin
echo "<h2>Admin Status</h2>";
if (function_exists('is_admin')) {
    if (is_admin()) {
        echo "✓ User is admin<br>";
    } else {
        echo "✗ User is not admin<br>";
    }
} else {
    echo "✗ is_admin function not found<br>";
}

// Show redirect URL
echo "<h2>Redirect Information</h2>";
if (defined('APP_URL')) {
    echo "APP_URL: " . APP_URL . "<br>";
    echo "Login redirect would go to: " . APP_URL . "/login.php<br>";
} else {
    echo "✗ APP_URL not defined<br>";
}
?>
