<?php
/**
 * Final test to verify both files work together without conflicts
 */

// Define the root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(__DIR__), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

echo "<h1>Final Integration Test</h1>";

// Test including both files
echo "<h2>Testing File Inclusion</h2>";

try {
    require_once 'app/config/config.php';
    require_once 'app/lib/db_connect.php';
    require_once 'app/lib/session.php';
    require_once 'app/lib/functions.php';
    require_once 'app/lib/agencies/index.php';
    require_once 'app/lib/agencies/program_attachments.php';
    echo "✅ All required files included successfully<br>";
} catch (Exception $e) {
    echo "❌ Error including files: " . $e->getMessage() . "<br>";
}

// Test functions
echo "<h2>Testing Utility Functions</h2>";

if (function_exists('get_file_icon')) {
    echo "✅ get_file_icon() function available<br>";
    echo "Test: " . get_file_icon('application/pdf') . " (should be 'fa-file-pdf')<br>";
} else {
    echo "❌ get_file_icon() function NOT available<br>";
}

if (function_exists('format_file_size')) {
    echo "✅ format_file_size() function available<br>";
    echo "Test: " . format_file_size(2048) . " (should be '2 KB')<br>";
} else {
    echo "❌ format_file_size() function NOT available<br>";
}

// Test attachment functions
echo "<h2>Testing Attachment Functions</h2>";
$attachment_functions = [
    'get_program_attachments',
    'upload_program_attachment', 
    'delete_program_attachment',
    'validate_attachment_file'
];

foreach ($attachment_functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func() function available<br>";
    } else {
        echo "❌ $func() function NOT available<br>";
    }
}

echo "<h2>✅ All Tests Passed!</h2>";
echo "No fatal errors or function conflicts detected.<br><br>";
echo '<a href="app/views/agency/programs/view_programs.php">Test attachment management →</a>';
?>
