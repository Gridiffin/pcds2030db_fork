<?php
// Debug the asset loading system
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Asset Loading Debug</h1>";

// Simulate the admin/programs path
$_SERVER['REQUEST_URI'] = '/pcds2030_dashboard_fork/app/views/admin/programs/edit_program.php?id=1';
$_SERVER['SCRIPT_NAME'] = '/pcds2030_dashboard_fork/app/views/admin/programs/edit_program.php';
$_GET['debug_vite'] = '1';

echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";

// Include the vite helpers
require_once 'app/helpers/vite-helpers.php';

echo "<h2>Bundle Detection Test</h2>";
$bundle_name = detect_bundle_name();
echo "<p><strong>Detected Bundle:</strong> $bundle_name</p>";

echo "<h2>Asset Loading Test</h2>";
$assets = vite_assets();
echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px 0;'>";
echo "<strong>Generated HTML:</strong><br>";
echo "<pre>" . htmlentities($assets) . "</pre>";
echo "</div>";

echo "<h2>File Existence Check</h2>";
$css_file = 'dist/css/' . $bundle_name . '.bundle.css';
$js_file = 'dist/js/' . $bundle_name . '.bundle.js';

echo "<p><strong>CSS File:</strong> $css_file - " . (file_exists($css_file) ? '✅ EXISTS' : '❌ NOT FOUND') . "</p>";
echo "<p><strong>JS File:</strong> $js_file - " . (file_exists($js_file) ? '✅ EXISTS' : '❌ NOT FOUND') . "</p>";

if (file_exists($css_file)) {
    echo "<p><strong>CSS File Size:</strong> " . filesize($css_file) . " bytes</p>";
}
?>
