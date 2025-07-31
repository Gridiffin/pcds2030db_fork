<?php
// Turn on error reporting to see what's breaking
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting login test...<br>";

try {
    echo "1. Loading config...<br>";
    require_once __DIR__ . '/app/config/config.php';
    echo "✅ Config loaded<br>";

    echo "2. Loading db_connect...<br>";
    require_once __DIR__ . '/app/lib/db_connect.php';
    echo "✅ DB connect loaded<br>";

    echo "3. Loading functions...<br>";
    require_once __DIR__ . '/app/lib/functions.php';
    echo "✅ Functions loaded<br>";

    echo "4. Loading session...<br>";
    require_once __DIR__ . '/app/lib/session.php';
    echo "✅ Session loaded<br>";

    echo "5. Loading admin_functions...<br>";
    require_once __DIR__ . '/app/lib/admin_functions.php';
    echo "✅ Admin functions loaded<br>";

    echo "6. All includes successful!<br>";
    
    // Test session
    echo "Session status: " . session_status() . "<br>";
    
    // Test if we can process login
    if ($_POST) {
        echo "7. Processing POST data...<br>";
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        echo "Username: " . htmlspecialchars($username) . "<br>";
        
        if (!empty($username) && !empty($password)) {
            echo "8. Calling validate_login...<br>";
            $result = validate_login($username, $password);
            
            if (isset($result['success'])) {
                echo "✅ Login successful!<br>";
                echo "User role: " . $result['user']['role'] . "<br>";
                
                // Set session
                $_SESSION['user_id'] = $result['user']['user_id'];
                $_SESSION['role'] = $result['user']['role'];
                $_SESSION['agency_id'] = $result['user']['agency_id'];
                
                echo "✅ Session set<br>";
                
                // Show where we would redirect
                if ($_SESSION['role'] === 'admin') {
                    $redirect = APP_URL . '/app/views/admin/dashboard/dashboard.php';
                } else {
                    $redirect = APP_URL . '/app/views/agency/dashboard/dashboard.php';
                }
                echo "Would redirect to: " . $redirect . "<br>";
                echo '<a href="' . $redirect . '">Click here to go to dashboard</a><br>';
                
            } else {
                echo "❌ Login failed: " . ($result['error'] ?? 'Unknown error') . "<br>";
            }
        }
    }

} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "❌ PHP ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}
?>

<h3>Test Login Form</h3>
<form method="post">
    <label>Username: <input type="text" name="username" value="user"></label><br><br>
    <label>Password: <input type="password" name="password"></label><br><br>
    <button type="submit">Test Login</button>
</form>