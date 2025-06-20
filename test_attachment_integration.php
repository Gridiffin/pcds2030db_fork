<?php
/**
 * Test script to verify attachment integration in update form
 */

// Define the root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(__DIR__), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/agencies/program_attachments.php';

echo "<h1>Testing Program Attachment Integration</h1>";

// Test 1: Check if program_attachments table exists
echo "<h2>Test 1: Database Table Check</h2>";
try {
    $result = $conn->query("SHOW TABLES LIKE 'program_attachments'");
    if ($result->num_rows > 0) {
        echo "✅ program_attachments table exists<br>";
    } else {
        echo "❌ program_attachments table does not exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "<br>";
}

// Test 2: Check if functions are available
echo "<h2>Test 2: Function Availability Check</h2>";
$functions = ['get_program_attachments', 'upload_program_attachment', 'delete_program_attachment', 'validate_attachment_file'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "✅ Function $func is available<br>";
    } else {
        echo "❌ Function $func is NOT available<br>";
    }
}

// Test 3: Check if upload directory exists
echo "<h2>Test 3: Upload Directory Check</h2>";
$upload_dir = 'uploads/programs/attachments';
if (is_dir($upload_dir)) {
    echo "✅ Upload directory exists: $upload_dir<br>";
    if (is_writable($upload_dir)) {
        echo "✅ Upload directory is writable<br>";
    } else {
        echo "❌ Upload directory is NOT writable<br>";
    }
} else {
    echo "❌ Upload directory does not exist: $upload_dir<br>";
}

// Test 4: Check if .htaccess file exists
$htaccess_file = $upload_dir . '/.htaccess';
if (file_exists($htaccess_file)) {
    echo "✅ .htaccess file exists in upload directory<br>";
    echo "Content: <pre>" . htmlspecialchars(file_get_contents($htaccess_file)) . "</pre>";
} else {
    echo "❌ .htaccess file does not exist in upload directory<br>";
}

// Test 5: Test get_program_attachments for a sample program
echo "<h2>Test 5: Sample Program Attachments</h2>";
try {
    // Get first program from database
    $result = $conn->query("SELECT program_id, program_name FROM programs LIMIT 1");
    if ($result->num_rows > 0) {
        $program = $result->fetch_assoc();
        echo "Testing with program: " . htmlspecialchars($program['program_name']) . " (ID: {$program['program_id']})<br>";
        
        $attachments = get_program_attachments($program['program_id']);
        echo "Found " . count($attachments) . " attachments<br>";
        
        if (!empty($attachments)) {
            echo "<ul>";
            foreach ($attachments as $attachment) {
                echo "<li>";
                echo htmlspecialchars($attachment['original_filename']);
                echo " (" . number_format($attachment['file_size'] / 1024, 1) . " KB)";
                echo " - " . $attachment['upload_date'];
                echo "</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "No programs found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error testing attachments: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Complete</h2>";
echo '<a href="app/views/agency/programs/view_programs.php">← Back to Programs</a>';
?>
