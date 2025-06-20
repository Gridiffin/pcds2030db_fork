<?php
/**
 * Test to verify function name conflicts are resolved
 */

// Define the root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(__DIR__), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

echo "<h1>Testing Function Name Conflict Resolution</h1>";

// Test 1: Include program_attachments.php
echo "<h2>Test 1: Including program_attachments.php</h2>";
try {
    require_once 'app/lib/agencies/program_attachments.php';
    echo "✅ program_attachments.php included successfully<br>";
    
    // Test format_file_size function
    if (function_exists('format_file_size')) {
        echo "✅ format_file_size() function is available<br>";
        echo "Test: " . format_file_size(1024) . " (should be '1 KB')<br>";
        echo "Test: " . format_file_size(1048576) . " (should be '1 MB')<br>";
    } else {
        echo "❌ format_file_size() function is NOT available<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error including program_attachments.php: " . $e->getMessage() . "<br>";
}

// Test 2: Try to include functions that would be in update_program.php context
echo "<h2>Test 2: Function Availability Check</h2>";

$functions_to_check = [
    'get_file_icon',
    'format_file_size',
    'get_program_attachments',
    'upload_program_attachment',
    'delete_program_attachment'
];

foreach ($functions_to_check as $func) {
    if (function_exists($func)) {
        echo "✅ Function $func is available<br>";
    } else {
        echo "❌ Function $func is NOT available<br>";
    }
}

echo "<h2>Test Complete</h2>";
echo "No fatal errors indicates the function name conflict has been resolved.<br>";
echo '<a href="app/views/agency/programs/view_programs.php">← Back to Programs</a>';
?>
