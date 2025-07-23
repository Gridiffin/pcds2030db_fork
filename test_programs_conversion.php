<?php
/**
 * Quick Test: Programs Module Bundle Loading Verification
 * 
 * Tests that all programs module pages now use base.php layout
 * and load the programs CSS bundle correctly.
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/config/config.php';

echo "<h2>Programs Module Bundle Loading Verification</h2>";
echo "<p>Verifying all programs pages use base.php layout and programs bundle.</p>";

// Test files in programs module
$programs_files = [
    'view_programs.php',
    'create_program.php', 
    'edit_program.php',
    'add_submission.php',
    'program_details.php',
    'edit_submission.php',
    'view_submissions.php',
    'view_other_agency_programs.php'
];

$programs_path = PROJECT_ROOT_PATH . 'app/views/agency/programs/';

echo "<h3>File Analysis Results:</h3>";
echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr><th>File</th><th>Base.php</th><th>CSS Bundle</th><th>Header/Footer</th><th>Status</th></tr>";

foreach ($programs_files as $file) {
    $file_path = $programs_path . $file;
    
    if (!file_exists($file_path)) {
        echo "<tr><td>$file</td><td colspan='4' style='color: red;'>❌ File not found</td></tr>";
        continue;
    }
    
    $content = file_get_contents($file_path);
    
    // Check for base.php
    $has_base = strpos($content, "require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';") !== false;
    
    // Check for CSS bundle
    $has_css_bundle = strpos($content, "\$cssBundle = 'programs';") !== false;
    
    // Check for old header/footer (should be absent)
    $has_old_header = strpos($content, 'header.php') !== false;
    $has_old_footer = strpos($content, 'footer.php') !== false;
    
    // Status determination
    $status = '✅ Good';
    if (!$has_base) $status = '❌ Missing base.php';
    if (!$has_css_bundle) $status = '❌ Missing CSS bundle';
    if ($has_old_header || $has_old_footer) $status = '❌ Has old layout';
    
    echo "<tr>";
    echo "<td>$file</td>";
    echo "<td>" . ($has_base ? '✅' : '❌') . "</td>";
    echo "<td>" . ($has_css_bundle ? '✅' : '❌') . "</td>";
    echo "<td>" . ((!$has_old_header && !$has_old_footer) ? '✅ Clean' : '❌ Found old') . "</td>";
    echo "<td style='font-weight: bold;" . ($status === '✅ Good' ? 'color: green;' : 'color: red;') . "'>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Check bundle file existence
echo "<h3>Bundle File Verification:</h3>";
$bundle_path = PROJECT_ROOT_PATH . 'dist/css/programs.bundle.css';
if (file_exists($bundle_path)) {
    $bundle_size = number_format(filesize($bundle_path) / 1024, 2);
    echo "<p style='color: green;'>✅ programs.bundle.css exists ($bundle_size KB)</p>";
} else {
    echo "<p style='color: red;'>❌ programs.bundle.css NOT found</p>";
}

echo "<h3>Testing URLs:</h3>";
echo "<p>Test the converted pages:</p>";
echo "<ul>";
foreach ($programs_files as $file) {
    $url = APP_URL . "/app/views/agency/programs/$file";
    echo "<li><a href='$url' target='_blank'>$file</a></li>";
}
echo "</ul>";

echo "<hr>";
echo "<h3>Expected Network Behavior:</h3>";
echo "<ul>";
echo "<li>✅ Single CSS request: <code>programs.bundle.css</code></li>";
echo "<li>✅ Bootstrap CSS from CDN</li>";  
echo "<li>✅ Font Awesome CSS from CDN</li>";
echo "<li>❌ NO requests for main.css, header.php styles, etc.</li>";
echo "</ul>";

echo "<p><em>All programs pages should now have consistent styling and clean bundle loading!</em></p>";
?>
