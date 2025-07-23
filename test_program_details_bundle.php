<?php
/**
 * Quick Test: Program Details Bundle Loading
 * 
 * This test verifies that program_details.php properly loads the programs CSS bundle
 * and displays correctly formatted content.
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/config/config.php';

echo "<h2>Program Details Bundle Loading Test</h2>";
echo "<p>Testing that program_details.php loads the programs CSS bundle correctly.</p>";

// Check if the bundle file exists
$bundle_path = PROJECT_ROOT_PATH . 'dist/css/programs.bundle.css';
if (file_exists($bundle_path)) {
    echo "<p style='color: green;'>✅ programs.bundle.css exists at: $bundle_path</p>";
    echo "<p>Bundle size: " . number_format(filesize($bundle_path) / 1024, 2) . " KB</p>";
} else {
    echo "<p style='color: red;'>❌ programs.bundle.css NOT found at: $bundle_path</p>";
}

// Check base.php layout
$base_layout_path = PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
if (file_exists($base_layout_path)) {
    echo "<p style='color: green;'>✅ base.php layout exists at: $base_layout_path</p>";
} else {
    echo "<p style='color: red;'>❌ base.php layout NOT found at: $base_layout_path</p>";
}

// Check program_details.php file
$program_details_path = PROJECT_ROOT_PATH . 'app/views/agency/programs/program_details.php';
if (file_exists($program_details_path)) {
    echo "<p style='color: green;'>✅ program_details.php exists at: $program_details_path</p>";
    
    // Read the end of the file to verify the fix
    $file_content = file_get_contents($program_details_path);
    $last_lines = substr($file_content, -200); // Last 200 characters
    
    if (strpos($last_lines, 'require_once PROJECT_ROOT_PATH . \'views/layouts/base.php\';') !== false) {
        echo "<p style='color: green;'>✅ base.php is properly included</p>";
    } else {
        echo "<p style='color: red;'>❌ base.php include not found in file ending</p>";
    }
    
    // Check for extra closing PHP tags
    if (preg_match('/\?\>\s*\?\>\s*$/', $file_content)) {
        echo "<p style='color: red;'>❌ Extra closing PHP tag found - this will break bundle loading!</p>";
    } elseif (preg_match('/\?\>\s*$/', $file_content)) {
        echo "<p style='color: green;'>✅ Single closing PHP tag found - correct format</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ No closing PHP tag (open tag format)</p>";
    }
    
    // Check for bundle configuration
    if (strpos($file_content, '$cssBundle = \'programs\';') !== false) {
        echo "<p style='color: green;'>✅ CSS bundle configured as 'programs'</p>";
    } else {
        echo "<p style='color: red;'>❌ CSS bundle configuration not found</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ program_details.php NOT found at: $program_details_path</p>";
}

echo "<hr>";
echo "<h3>Bundle Loading Test URL</h3>";
echo "<p>To test the actual page, visit:</p>";
echo "<p><a href='" . APP_URL . "/app/views/agency/programs/program_details.php?id=1' target='_blank'>";
echo APP_URL . "/app/views/agency/programs/program_details.php?id=1</a></p>";
echo "<p><em>Note: Replace '1' with a valid program ID from your database.</em></p>";

echo "<hr>";
echo "<h3>Expected Browser Network Tab Behavior</h3>";
echo "<ul>";
echo "<li>✅ Single CSS request: <code>programs.bundle.css</code></li>";
echo "<li>✅ Bootstrap CSS from CDN</li>";
echo "<li>✅ Font Awesome CSS from CDN</li>";
echo "<li>❌ NO individual CSS files like main.css, programs.css, etc.</li>";
echo "</ul>";
?>
