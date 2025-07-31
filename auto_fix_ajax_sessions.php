<?php
/**
 * Auto-Fix AJAX Session Issues
 * 
 * Automatically adds session_start() to all AJAX files that need it
 */

echo "=== AUTO-FIX AJAX SESSION ISSUES ===\n";

// Files that need fixing (from the scan results)
$files_to_fix = [
    'app/ajax/add_period.php',
    'app/ajax/admin_dashboard_data.php',
    'app/ajax/admin_manage_initiatives.php',
    'app/ajax/admin_outcomes.php',
    'app/ajax/admin_user_tables.php',
    'app/ajax/check_period_exists.php',
    'app/ajax/check_program_number_duplicate.php',
    'app/ajax/dashboard_data.php',
    'app/ajax/delete_period.php',
    'app/ajax/export_audit_logs.php',
    'app/ajax/get_audit_field_changes.php',
    'app/ajax/get_incomplete_targets.php',
    'app/ajax/get_program_draft_periods.php',
    'app/ajax/get_program_stats.php',
    'app/ajax/get_program_submission.php',
    'app/ajax/get_program_submissions.php',
    'app/ajax/get_program_submissions_list.php',
    'app/ajax/get_public_reports.php',
    'app/ajax/get_reporting_periods.php',
    'app/ajax/get_reports.php',
    'app/ajax/get_submission_by_period.php',
    'app/ajax/get_submission_preview.php',
    'app/ajax/get_target_progress.php',
    'app/ajax/load_audit_logs.php',
    'app/ajax/numbering.php',
    'app/ajax/simple_finalize.php',
    'app/ajax/upload_program_attachment.php',
    'app/ajax/agency/check_program_number.php'
];

$session_fix = "// Start session FIRST before any output\nif (session_status() == PHP_SESSION_NONE) {\n    session_start();\n}\n\n";

$fixed_count = 0;
$failed_files = [];

foreach ($files_to_fix as $file) {
    $filepath = __DIR__ . '/' . $file;
    
    if (!file_exists($filepath)) {
        echo "SKIP: $file (file not found)\n";
        continue;
    }
    
    $content = file_get_contents($filepath);
    
    // Check if already has session_start
    if (strpos($content, 'session_start()') !== false) {
        echo "SKIP: $file (already has session_start)\n";
        continue;
    }
    
    // Find the position after the opening <?php tag
    $php_tag_pos = strpos($content, '<?php');
    if ($php_tag_pos === false) {
        echo "SKIP: $file (no PHP opening tag found)\n";
        continue;
    }
    
    // Find the end of the first line (after <?php)
    $first_line_end = strpos($content, "\n", $php_tag_pos);
    if ($first_line_end === false) {
        $first_line_end = $php_tag_pos + 5; // Just after <?php
    } else {
        $first_line_end += 1; // Include the newline
    }
    
    // Insert session_start after the first line
    $new_content = substr($content, 0, $first_line_end) . $session_fix . substr($content, $first_line_end);
    
    // Write the fixed content back
    if (file_put_contents($filepath, $new_content)) {
        echo "FIXED: $file\n";
        $fixed_count++;
    } else {
        echo "FAILED: $file (could not write)\n";
        $failed_files[] = $file;
    }
}

echo "\n=== RESULTS ===\n";
echo "Files successfully fixed: $fixed_count\n";
echo "Files failed: " . count($failed_files) . "\n";

if (!empty($failed_files)) {
    echo "\nFailed files:\n";
    foreach ($failed_files as $file) {
        echo "- $file\n";
    }
}

echo "\nDone! Upload all fixed files to your server.\n";
?>