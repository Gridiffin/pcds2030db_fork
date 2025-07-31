<?php
/**
 * Simple debug - just show server variables without requiring config
 */

echo "=== SERVER DEBUG INFO ===<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "<br>";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'not set') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "<br>";
echo "PHP_SAPI: " . php_sapi_name() . "<br>";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'not set') . "<br>";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'not set') . "<br>";

echo "<br>=== PATH DETECTION ===<br>";
$current_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
echo "Current Host: " . $current_host . "<br>";
echo "Is Production Check: " . (($current_host === 'www.sarawakforestry.com' || $current_host === 'sarawakforestry.com') ? 'YES' : 'NO') . "<br>";

echo "<br>=== EXPECTED VALUES ===<br>";
if ($current_host === 'www.sarawakforestry.com' || $current_host === 'sarawakforestry.com') {
    echo "Should use APP_URL: https://www.sarawakforestry.com/pcds2030<br>";
    echo "Should use BASE_URL: /pcds2030<br>";
    echo "Should use DB: Production settings<br>";
} else {
    echo "Should use APP_URL: Local development<br>";
    echo "Should use BASE_URL: Local development<br>";
    echo "Should use DB: Local settings<br>";
}
?>