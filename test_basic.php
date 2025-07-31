<?php
echo "PHP is working!<br>";
echo "Time: " . date('Y-m-d H:i:s') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test if we can include config
try {
    require_once __DIR__ . '/app/config/config.php';
    echo "✅ Config loaded successfully<br>";
    echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'not defined') . "<br>";
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "<br>";
}
?>