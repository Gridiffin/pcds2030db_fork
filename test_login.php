<?php
/**
 * Test login functionality
 */

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/lib/db_connect.php';
require_once __DIR__ . '/app/lib/functions.php';

echo "=== LOGIN FUNCTIONALITY TEST ===<br>";

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "Username entered: " . htmlspecialchars($username) . "<br>";
    echo "Password entered: " . (empty($password) ? 'empty' : 'provided') . "<br>";
    
    if (!empty($username) && !empty($password)) {
        try {
            $result = validate_login($username, $password);
            echo "<br>Login validation result:<br>";
            echo "<pre>" . print_r($result, true) . "</pre>";
            
            if (isset($result['success'])) {
                echo "✅ Login successful!<br>";
                $user_role = $result['user']['role'] ?? 'unknown';
                echo "User role: " . $user_role . "<br>";
                
                if ($user_role === 'admin') {
                    $redirect_url = APP_URL . '/app/views/admin/dashboard/dashboard.php';
                } else {
                    $redirect_url = APP_URL . '/app/views/agency/dashboard/dashboard.php';
                }
                echo "Correct redirect URL: " . $redirect_url . "<br>";
            } else {
                echo "❌ Login failed: " . ($result['error'] ?? 'Unknown error') . "<br>";
            }
        } catch (Exception $e) {
            echo "❌ Login error: " . $e->getMessage() . "<br>";
        }
    }
} else {
    echo "Please test login below:<br>";
}
?>

<form method="post" action="">
    <label>Username: <input type="text" name="username" required></label><br><br>
    <label>Password: <input type="password" name="password" required></label><br><br>
    <button type="submit">Test Login</button>
</form>