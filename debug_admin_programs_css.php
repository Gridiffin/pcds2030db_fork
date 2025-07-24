<?php
/**
 * Debug script to check admin programs CSS loading
 */

// Define the project root path
define('PROJECT_ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);

// Include config
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

echo "<h2>Admin Programs CSS Debug</h2>";

// Check APP_URL
echo "<p><strong>APP_URL:</strong> " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "</p>";

// Check if CSS bundle exists
$cssBundle = 'admin-programs';
$cssPath = PROJECT_ROOT_PATH . 'dist/css/' . $cssBundle . '.bundle.css';
$cssUrl = APP_URL . '/dist/css/' . $cssBundle . '.bundle.css';

echo "<p><strong>CSS Bundle:</strong> $cssBundle</p>";
echo "<p><strong>CSS File Path:</strong> $cssPath</p>";
echo "<p><strong>CSS URL:</strong> $cssUrl</p>";

if (file_exists($cssPath)) {
    $size = round(filesize($cssPath) / 1024, 2);
    echo "<p style='color: green;'>✅ CSS file exists ($size KB)</p>";
} else {
    echo "<p style='color: red;'>❌ CSS file NOT found</p>";
}

// Check JS bundle
$jsPath = PROJECT_ROOT_PATH . 'dist/js/' . $cssBundle . '.bundle.js';
$jsUrl = APP_URL . '/dist/js/' . $cssBundle . '.bundle.js';

echo "<p><strong>JS File Path:</strong> $jsPath</p>";
echo "<p><strong>JS URL:</strong> $jsUrl</p>";

if (file_exists($jsPath)) {
    $size = round(filesize($jsPath), 2);
    echo "<p style='color: green;'>✅ JS file exists ($size bytes)</p>";
} else {
    echo "<p style='color: red;'>❌ JS file NOT found</p>";
}

// Test the actual HTML output that would be generated
echo "<h3>Generated HTML Link Tags:</h3>";
echo "<pre>";
echo htmlspecialchars('<link rel="stylesheet" href="' . $cssUrl . '">');
echo "\n";
echo htmlspecialchars('<script type="module" src="' . $jsUrl . '"></script>');
echo "</pre>";

// Check if base.php exists
$basePath = PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
echo "<p><strong>Base layout:</strong> " . ($basePath) . "</p>";
if (file_exists($basePath)) {
    echo "<p style='color: green;'>✅ Base layout exists</p>";
} else {
    echo "<p style='color: red;'>❌ Base layout NOT found</p>";
}

// Show current working directory
echo "<p><strong>Current working directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>PROJECT_ROOT_PATH:</strong> " . PROJECT_ROOT_PATH . "</p>";

// Test direct CSS access
echo "<h3>Test Direct CSS Access:</h3>";
echo "<p>Try accessing: <a href='$cssUrl' target='_blank'>$cssUrl</a></p>";
?>
