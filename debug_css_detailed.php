<?php
// Debug what's happening on the actual edit program page
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/helpers/vite-helpers.php';

// Set debug mode
$_GET['debug_vite'] = '1';

// Simulate being on the admin edit program page
$_SERVER['REQUEST_URI'] = '/pcds2030_dashboard_fork/app/views/admin/programs/edit_program.php?id=1';
$_SERVER['SCRIPT_NAME'] = '/pcds2030_dashboard_fork/app/views/admin/programs/edit_program.php';

echo "<h1>CSS Debug for Admin Edit Program Page</h1>";

// Test bundle detection
echo "<h2>Bundle Detection</h2>";
$bundle_name = detect_bundle_name();
echo "<p><strong>Detected Bundle:</strong> $bundle_name</p>";

// Test asset generation
echo "<h2>Vite Assets Output</h2>";
ob_start();
$assets_html = vite_assets();
$debug_output = ob_get_clean();

echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;'>";
echo "<strong>Debug Output:</strong><br>";
echo "<pre>" . htmlentities($debug_output) . "</pre>";
echo "</div>";

echo "<div style='background: #e8f5e9; padding: 10px; margin: 10px 0; border: 1px solid #4caf50;'>";
echo "<strong>Generated Assets HTML:</strong><br>";
echo "<pre>" . htmlentities($assets_html) . "</pre>";
echo "</div>";

// Check file existence
echo "<h2>File Existence Check</h2>";
$css_file = PROJECT_ROOT_PATH . 'dist/css/' . $bundle_name . '.bundle.css';
$js_file = PROJECT_ROOT_PATH . 'dist/js/' . $bundle_name . '.bundle.js';

echo "<p><strong>CSS File Path:</strong> $css_file</p>";
echo "<p><strong>CSS Exists:</strong> " . (file_exists($css_file) ? '✅ YES' : '❌ NO') . "</p>";
if (file_exists($css_file)) {
    echo "<p><strong>CSS File Size:</strong> " . filesize($css_file) . " bytes</p>";
}

echo "<p><strong>JS File Path:</strong> $js_file</p>";
echo "<p><strong>JS Exists:</strong> " . (file_exists($js_file) ? '✅ YES' : '❌ NO') . "</p>";
if (file_exists($js_file)) {
    echo "<p><strong>JS File Size:</strong> " . filesize($js_file) . " bytes</p>";
}

// Check web-accessible URLs
echo "<h2>Web URL Check</h2>";
$css_url = '/pcds2030_dashboard_fork/dist/css/' . $bundle_name . '.bundle.css';
$js_url = '/pcds2030_dashboard_fork/dist/js/' . $bundle_name . '.bundle.js';

echo "<p><strong>CSS URL:</strong> <a href='$css_url' target='_blank'>$css_url</a></p>";
echo "<p><strong>JS URL:</strong> <a href='$js_url' target='_blank'>$js_url</a></p>";

// Test actual CSS content
echo "<h2>CSS Content Sample</h2>";
if (file_exists($css_file)) {
    $css_content = file_get_contents($css_file);
    $css_sample = substr($css_content, 0, 500);
    echo "<div style='background: #fff3e0; padding: 10px; margin: 10px 0; border: 1px solid #ff9800;'>";
    echo "<strong>First 500 characters of CSS:</strong><br>";
    echo "<pre style='font-size: 12px;'>" . htmlentities($css_sample) . "...</pre>";
    echo "</div>";
    
    echo "<p><strong>CSS file contains 'bootstrap':</strong> " . (strpos($css_content, 'bootstrap') !== false ? '✅ YES' : '❌ NO') . "</p>";
    echo "<p><strong>CSS file contains 'btn':</strong> " . (strpos($css_content, 'btn') !== false ? '✅ YES' : '❌ NO') . "</p>";
    echo "<p><strong>CSS file contains 'card':</strong> " . (strpos($css_content, 'card') !== false ? '✅ YES' : '❌ NO') . "</p>";
}

// Test if we can load the assets in this page
echo "<h2>Direct CSS Test</h2>";
echo $assets_html;
echo "<div class='alert alert-success'>";
echo "<i class='fas fa-check-circle me-2'></i>";
echo "If this alert has green background and icon, CSS is working!";
echo "</div>";

echo "<div class='card'>";
echo "<div class='card-header'>";
echo "<h5 class='card-title'>Test Card</h5>";
echo "</div>";
echo "<div class='card-body'>";
echo "<p>If this card has proper styling, CSS is working!</p>";
echo "<button class='btn btn-primary'>Test Button</button>";
echo "</div>";
echo "</div>";
?>
