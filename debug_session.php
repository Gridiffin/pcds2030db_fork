<?php
/**
 * Session debug test
 */

// Load config and libs like login.php does
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/lib/db_connect.php';
require_once __DIR__ . '/app/lib/functions.php';
require_once __DIR__ . '/app/lib/session.php';
require_once __DIR__ . '/app/lib/admin_functions.php';

echo "=== SESSION DEBUG ===<br>";
echo "Session status: " . session_status() . " (1=disabled, 2=active)<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Session name: " . session_name() . "<br>";
echo "<br>";

echo "=== SESSION CONTENTS ===<br>";
if (empty($_SESSION)) {
    echo "Session is empty<br>";
} else {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

echo "<br>=== is_logged_in() TEST ===<br>";
if (function_exists('is_logged_in')) {
    $logged_in = is_logged_in();
    echo "is_logged_in() result: " . ($logged_in ? 'TRUE' : 'FALSE') . "<br>";
} else {
    echo "is_logged_in() function not found<br>";
}

echo "<br>=== MANUAL SESSION TEST ===<br>";
if ($_POST && $_POST['action'] == 'set_session') {
    $_SESSION['user_id'] = 12;
    $_SESSION['role'] = 'focal';
    $_SESSION['agency_id'] = 5;
    
    echo "✅ Session variables set manually<br>";
    echo "Now check is_logged_in(): " . (function_exists('is_logged_in') ? (is_logged_in() ? 'TRUE' : 'FALSE') : 'function not found') . "<br>";
}

echo "<br>=== LOGIN TEST FORM ===<br>";
if ($_POST && $_POST['action'] == 'test_login') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    echo "Testing login for: " . htmlspecialchars($username) . "<br>";
    
    $result = validate_login($username, $password);
    if (isset($result['success'])) {
        echo "✅ validate_login() successful<br>";
        
        // Set session like login.php does
        $_SESSION['user_id'] = $result['user']['user_id'];
        $_SESSION['role'] = $result['user']['role'];
        $_SESSION['agency_id'] = $result['user']['agency_id'];
        
        echo "✅ Session variables set<br>";
        echo "Now is_logged_in(): " . (function_exists('is_logged_in') ? (is_logged_in() ? 'TRUE' : 'FALSE') : 'function not found') . "<br>";
        
        echo "<br>Session contents after login:<br>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
    } else {
        echo "❌ Login failed: " . ($result['error'] ?? 'Unknown error') . "<br>";
    }
}
?>

<form method="post">
    <input type="hidden" name="action" value="set_session">
    <button type="submit">Set Session Manually</button>
</form>

<br>

<form method="post">
    <input type="hidden" name="action" value="test_login">
    <label>Username: <input type="text" name="username" value="user"></label><br>
    <label>Password: <input type="password" name="password"></label><br>
    <button type="submit">Test Full Login</button>
</form>