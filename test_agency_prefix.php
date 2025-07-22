<?php
/**
 * Agency Bundle Prefix Verification Test
 * 
 * Verifies that all programs pages use the "agency-" prefixed bundle names
 */

if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/config/config.php';

echo "<h2>Agency Bundle Prefix Verification</h2>";
echo "<p>Verifying all programs pages use 'agency-' prefixed bundle names.</p>";

$expected_bundles = [
    'view_programs.php' => 'agency-view-programs',
    'create_program.php' => 'agency-create-program', 
    'edit_program.php' => 'agency-edit-program',
    'add_submission.php' => 'agency-add-submission',
    'program_details.php' => 'agency-program-details',
    'edit_submission.php' => 'agency-edit-submission',
    'view_submissions.php' => 'agency-view-submissions',
    'view_other_agency_programs.php' => 'agency-view-programs'
];

$programs_path = PROJECT_ROOT_PATH . 'app/views/agency/programs/';
$dist_path = PROJECT_ROOT_PATH . 'dist/css/';

echo "<h3>Results:</h3>";
echo "<table border='1' cellpadding='6' cellspacing='0'>";
echo "<tr style='background: #f5f5f5;'><th>PHP File</th><th>Expected Bundle</th><th>Actual Bundle</th><th>Bundle Exists</th><th>Status</th></tr>";

$all_correct = true;

foreach ($expected_bundles as $php_file => $expected_bundle) {
    $file_path = $programs_path . $php_file;
    
    if (!file_exists($file_path)) {
        echo "<tr><td>$php_file</td><td colspan='4' style='color: red;'>❌ File not found</td></tr>";
        continue;
    }
    
    $content = file_get_contents($file_path);
    preg_match('/\$cssBundle = [\'"]([^\'"]+)[\'"];/', $content, $matches);
    $actual_bundle = $matches[1] ?? 'NOT FOUND';
    
    $bundle_file = $dist_path . $expected_bundle . '.bundle.css';
    $bundle_exists = file_exists($bundle_file);
    $bundle_size = $bundle_exists ? number_format(filesize($bundle_file) / 1024, 2) . ' kB' : 'N/A';
    
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
    echo "<td>" . ($bundle_exists ? "✅ $bundle_size" : '❌') . "</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Check all dist bundles with agency prefix
echo "<h3>All Agency Bundles in dist/css/:</h3>";
$all_bundles = glob($dist_path . 'agency-*.bundle.css');
if ($all_bundles) {
    echo "<ul>";
    foreach ($all_bundles as $bundle) {
        $bundle_name = basename($bundle);
        $size = number_format(filesize($bundle) / 1024, 2);
        $is_programs = strpos($bundle_name, 'program') !== false || 
                       strpos($bundle_name, 'view-programs') !== false ||
                       strpos($bundle_name, 'submission') !== false;
        $highlight = $is_programs ? 'color: #0066cc; font-weight: bold;' : '';
        echo "<li style='$highlight'>$bundle_name ($size kB)" . 
             ($is_programs ? ' <em>(programs module)</em>' : '') . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ No agency-prefixed bundles found!</p>";
}

echo "<hr>";
if ($all_correct) {
    echo "<h3 style='color: green;'>✅ SUCCESS: All Programs Pages Use Agency-Prefixed Bundles</h3>";
} else {
    echo "<h3 style='color: red;'>❌ Some Issues Found</h3>";
}
?>
