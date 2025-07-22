<?php
/**
 * Programs Module CSS Bundle Separation Test
 * 
 * Verifies that each programs page now uses its own specific CSS bundle
 * instead of the shared programs bundle.
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/config/config.php';

echo "<h2>Programs Module CSS Bundle Separation Test</h2>";
echo "<p>Verifying each page uses its specific CSS bundle.</p>";

// Bundle mapping (expected)
$expected_bundles = [
    'view_programs.php' => 'view-programs',
    'create_program.php' => 'create-program', 
    'edit_program.php' => 'edit-program',
    'add_submission.php' => 'add-submission',
    'program_details.php' => 'program-details',
    'edit_submission.php' => 'edit-submission',
    'view_submissions.php' => 'view-submissions',
    'view_other_agency_programs.php' => 'view-programs'
];

$programs_path = PROJECT_ROOT_PATH . 'app/views/agency/programs/';
$dist_path = PROJECT_ROOT_PATH . 'dist/css/';

echo "<h3>Bundle Assignment Verification:</h3>";
echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
echo "<tr style='background: #f5f5f5;'><th>PHP File</th><th>Expected Bundle</th><th>Actual Bundle</th><th>Bundle File Exists</th><th>Status</th></tr>";

$all_correct = true;

foreach ($expected_bundles as $php_file => $expected_bundle) {
    $file_path = $programs_path . $php_file;
    
    if (!file_exists($file_path)) {
        echo "<tr><td>$php_file</td><td colspan='4' style='color: red;'>❌ PHP file not found</td></tr>";
        $all_correct = false;
        continue;
    }
    
    $content = file_get_contents($file_path);
    
    // Extract actual bundle from $cssBundle = 'bundle-name';
    preg_match('/\$cssBundle = [\'"]([^\'"]+)[\'"];/', $content, $matches);
    $actual_bundle = $matches[1] ?? 'NOT FOUND';
    
    // Check if bundle file exists
    $bundle_file_path = $dist_path . $expected_bundle . '.bundle.css';
    $bundle_exists = file_exists($bundle_file_path);
    $bundle_size = $bundle_exists ? number_format(filesize($bundle_file_path) / 1024, 2) . ' kB' : 'N/A';
    
    // Status determination
    $status = '✅ Correct';
    $row_style = '';
    
    if ($actual_bundle !== $expected_bundle) {
        $status = '❌ Wrong Bundle';
        $row_style = 'background-color: #ffe6e6;';
        $all_correct = false;
    } else if (!$bundle_exists) {
        $status = '⚠️ Bundle Missing';
        $row_style = 'background-color: #fff3cd;';
        $all_correct = false;
    }
    
    echo "<tr style='$row_style'>";
    echo "<td>$php_file</td>";
    echo "<td>$expected_bundle</td>";
    echo "<td style='font-weight: bold;'>$actual_bundle</td>";
    echo "<td>" . ($bundle_exists ? "✅ $bundle_size" : '❌ Missing') . "</td>";
    echo "<td style='font-weight: bold;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Check for old programs bundle usage
echo "<h3>Old Bundle References Check:</h3>";
$old_bundle_found = false;
foreach (array_keys($expected_bundles) as $php_file) {
    $file_path = $programs_path . $php_file;
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        if (strpos($content, "\$cssBundle = 'programs';") !== false) {
            echo "<p style='color: red;'>❌ Found old 'programs' bundle in: $php_file</p>";
            $old_bundle_found = true;
        }
    }
}

if (!$old_bundle_found) {
    echo "<p style='color: green;'>✅ No old 'programs' bundle references found</p>";
}

// Bundle file listing
echo "<h3>Available CSS Bundles:</h3>";
$bundle_files = glob($dist_path . '*.bundle.css');
echo "<ul>";
foreach ($bundle_files as $bundle_file) {
    $bundle_name = basename($bundle_file);
    $bundle_size = number_format(filesize($bundle_file) / 1024, 2);
    $is_programs_related = strpos($bundle_name, 'program') !== false || 
                           strpos($bundle_name, 'view-programs') !== false ||
                           strpos($bundle_name, 'create-program') !== false ||
                           strpos($bundle_name, 'edit-program') !== false ||
                           strpos($bundle_name, 'add-submission') !== false ||
                           strpos($bundle_name, 'edit-submission') !== false ||
                           strpos($bundle_name, 'view-submissions') !== false;
    
    $highlight = $is_programs_related ? 'font-weight: bold; color: #0066cc;' : '';
    echo "<li style='$highlight'>$bundle_name ($bundle_size kB)</li>";
}
echo "</ul>";

// Overall result
echo "<hr>";
if ($all_correct && !$old_bundle_found) {
    echo "<h3 style='color: green;'>✅ SUCCESS: All Programs Pages Use Individual Bundles</h3>";
    echo "<p>Every programs page now loads its own specific CSS bundle. No shared 'programs' bundle usage found.</p>";
} else {
    echo "<h3 style='color: red;'>❌ ISSUES FOUND</h3>";
    echo "<p>Some programs pages are not using the correct individual bundles. Check the table above for details.</p>";
}

echo "<h3>Test Individual Pages:</h3>";
echo "<p>Visit these URLs to test bundle loading in browser Network tab:</p>";
echo "<ul>";
foreach (array_keys($expected_bundles) as $php_file) {
    $url = APP_URL . "/app/views/agency/programs/$php_file";
    $bundle_name = $expected_bundles[$php_file] . '.bundle.css';
    echo "<li><a href='$url' target='_blank'>$php_file</a> → Should load <code>$bundle_name</code></li>";
}
echo "</ul>";
?>
