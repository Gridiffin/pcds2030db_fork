<?php
/**
 * Fix AJAX Session Issues
 * 
 * This script identifies AJAX files that use session-dependent functions
 * but don't have explicit session_start() calls, causing blank page issues.
 */

echo "=== AJAX Session Fix Tool ===\n";

// Get all AJAX files that use session functions
$ajax_dir = __DIR__ . '/app/ajax/';
$session_functions = ['is_agency', 'is_admin', 'is_logged_in', 'is_focal_user'];

$files_to_fix = [];

// Scan AJAX directory
$files = glob($ajax_dir . '*.php');
foreach (glob($ajax_dir . '*/*.php') as $subfile) {
    $files[] = $subfile;
}

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Check if file uses session functions
    $uses_session = false;
    foreach ($session_functions as $func) {
        if (strpos($content, $func . '(') !== false) {
            $uses_session = true;
            break;
        }
    }
    
    // Check if file already has session_start
    $has_session_start = strpos($content, 'session_start()') !== false;
    
    if ($uses_session && !$has_session_start) {
        $files_to_fix[] = str_replace(__DIR__ . '/', '', $file);
        echo "NEEDS FIX: " . str_replace(__DIR__ . '/', '', $file) . "\n";
    } else if ($uses_session && $has_session_start) {
        echo "OK: " . str_replace(__DIR__ . '/', '', $file) . "\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total files needing session_start() fix: " . count($files_to_fix) . "\n";

foreach ($files_to_fix as $file) {
    echo "- $file\n";
}

echo "\nTo fix these files, add this code at the beginning of each file:\n";
echo "// Start session FIRST before any output\n";
echo "if (session_status() == PHP_SESSION_NONE) {\n";
echo "    session_start();\n";
echo "}\n";
?>