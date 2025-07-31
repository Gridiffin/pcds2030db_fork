<?php
/**
 * Test actual config loading
 */

echo "=== BEFORE CONFIG LOAD ===<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "<br>";

// Load the config
if (file_exists(__DIR__ . '/app/config/config.php')) {
    echo "Config file exists - loading...<br>";
    require_once __DIR__ . '/app/config/config.php';
    
    echo "<br>=== AFTER CONFIG LOAD ===<br>";
    echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "<br>";
    echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "<br>";
    echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "<br>";
    echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NOT DEFINED') . "<br>";
    
    // Test the functions
    if (function_exists('view_url')) {
        echo "<br>=== FUNCTION TESTS ===<br>";
        echo "view_url('admin', 'dashboard/dashboard.php'): " . view_url('admin', 'dashboard/dashboard.php') . "<br>";
        echo "asset_url('css', 'main.css'): " . asset_url('css', 'main.css') . "<br>";
    }
    
} else {
    echo "Config file NOT FOUND at: " . __DIR__ . '/app/config/config.php<br>';
}

// Check what login.php might be doing
echo "<br>=== LOGIN REDIRECT TEST ===<br>";
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $redirect_url = APP_URL . '/app/views/admin/dashboard/dashboard.php';
} else {
    $redirect_url = APP_URL . '/app/views/agency/dashboard/dashboard.php';
}
echo "Would redirect to: " . $redirect_url . "<br>";
?>