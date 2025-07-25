<?php
require_once 'app/config/config.php';
require_once 'app/helpers/vite-helpers.php';

echo "<h1>Vite Assets Debug</h1>";
echo "<h2>Environment Variables</h2>";
echo "<pre>";
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'not set') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'not set') . "\n";
echo "__FILE__: " . __FILE__ . "\n";
echo "dirname(dirname(dirname(__FILE__))): " . dirname(dirname(dirname(__FILE__))) . "\n";
echo "</pre>";

echo "<h2>Detected Bundle Name</h2>";
$bundle_name = detect_bundle_name();
echo "<p>Bundle: <strong>$bundle_name</strong></p>";

echo "<h2>Vite Assets Output</h2>";
$_GET['debug_vite'] = true; // Enable debug mode
$assets = vite_assets();
echo "<pre>" . htmlspecialchars($assets) . "</pre>";

echo "<h2>File Existence Check</h2>";
$dist_path = dirname(__FILE__) . '/dist/';
$css_file = $dist_path . 'css/' . $bundle_name . '.bundle.css';
$js_file = $dist_path . 'js/' . $bundle_name . '.bundle.js';

echo "<pre>";
echo "Dist path: $dist_path\n";
echo "CSS file: $css_file\n";
echo "CSS exists: " . (file_exists($css_file) ? 'YES' : 'NO') . "\n";
echo "JS file: $js_file\n";
echo "JS exists: " . (file_exists($js_file) ? 'YES' : 'NO') . "\n";
echo "</pre>";

if (file_exists($css_file)) {
    echo "<h2>CSS File Size</h2>";
    echo "<p>" . filesize($css_file) . " bytes</p>";
}
?>