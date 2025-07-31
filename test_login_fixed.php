<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== FIXED LOGIN TEST ===<br>";

// Force session start immediately
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    echo "✅ Session started manually<br>";
} else {
    echo "Session status: " . session_status() . "<br>";
}

echo "Session ID after start: " . session_id() . "<br>";

// Load all required files
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/lib/db_connect.php';
require_once __DIR__ . '/app/lib/functions.php';
require_once __DIR__ . '/app/lib/session.php';
require_once __DIR__ . '/app/lib/admin_functions.php';

echo "All files loaded<br><br>";

// Process login if POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "=== PROCESSING LOGIN ===<br>";
    
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';
    
    echo "Username: " . htmlspecialchars($username) . "<br>";
    
    if (!empty($username) && !empty($password)) {
        $result = validate_login($username, $password);
        
        if (isset($result['success'])) {
            echo "✅ Login validation successful!<br>";
            
            // Set session variables
            $_SESSION['user_id'] = $result['user']['user_id'];
            $_SESSION['role'] = $result['user']['role'];
            $_SESSION['agency_id'] = $result['user']['agency_id'];
            $_SESSION['username'] = $result['user']['username'];
            $_SESSION['fullname'] = $result['user']['fullname'];
            
            echo "✅ Session variables set<br>";
            echo "Session contents:<br><pre>";
            print_r($_SESSION);
            echo "</pre>";
            
            // Force session save
            session_write_close();
            session_start();
            
            echo "✅ Session saved and restarted<br>";
            echo "Session after restart:<br><pre>";
            print_r($_SESSION);
            echo "</pre>";
            
            // Create redirect URL
            $redirect_url = ($_SESSION['role'] === 'admin') 
                ? APP_URL . '/app/views/admin/dashboard/dashboard.php'
                : APP_URL . '/app/views/agency/dashboard/dashboard.php';
            
            echo "Redirect URL: " . $redirect_url . "<br>";
            echo '<a href="' . $redirect_url . '">Click here to go to dashboard</a><br>';
            echo '<br><strong>Auto-redirecting in 3 seconds...</strong><br>';
            echo "<script>setTimeout(function(){ window.location.href = '" . $redirect_url . "'; }, 3000);</script>";
            
        } else {
            echo "❌ Login failed: " . ($result['error'] ?? 'Unknown error') . "<br>";
        }
    } else {
        echo "❌ Username and password required<br>";
    }
}
?>

<h3>Login Test Form</h3>
<form method="post">
    <label>Username: <input type="text" name="username" value="user" required></label><br><br>
    <label>Password: <input type="password" name="password" required></label><br><br>
    <button type="submit">Test Login</button>
</form>