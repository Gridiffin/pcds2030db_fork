<?php
/**
 * Debug configuration - temporary file to check what values are being detected
 */

require_once __DIR__ . '/app/config/config.php';

echo "=== CONFIG DEBUG INFO ===<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "<br>";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'not set') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "<br>";
echo "PHP_SAPI: " . php_sapi_name() . "<br>";
echo "<br>";
echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'not defined') . "<br>";
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'not defined') . "<br>";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'not defined') . "<br>";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'not defined') . "<br>";
echo "<br>";
echo "Current Host Variable: " . $current_host . "<br>";
echo "Is Production?: " . (($current_host === 'www.sarawakforestry.com' || $current_host === 'sarawakforestry.com') ? 'YES' : 'NO') . "<br>";
?>