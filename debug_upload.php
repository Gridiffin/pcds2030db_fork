<?php
/**
 * Debug script to test upload functionality
 */

// Check if upload directory exists and is writable
$upload_dir = __DIR__ . '/uploads/programs/attachments';
echo "Upload directory: " . $upload_dir . "\n";
echo "Directory exists: " . (is_dir($upload_dir) ? "YES" : "NO") . "\n";
echo "Directory writable: " . (is_writable($upload_dir) ? "YES" : "NO") . "\n";

// Check PHP upload settings
echo "\nPHP Upload Settings:\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "file_uploads: " . (ini_get('file_uploads') ? "Enabled" : "Disabled") . "\n";

// Test database connection
echo "\nDatabase Connection Test:\n";
try {
    require_once 'app/config/config.php';
    require_once 'app/lib/db_connect.php';
    
    if (isset($conn)) {
        echo "Database connection: SUCCESS\n";
    } else {
        echo "Database connection: FAILED - \$conn not set\n";
    }
} catch (Exception $e) {
    echo "Database connection: FAILED - " . $e->getMessage() . "\n";
}

// Test program_attachments library
echo "\nProgram Attachments Library Test:\n";
try {
    require_once 'app/lib/agencies/program_attachments.php';
    echo "Library loaded: SUCCESS\n";
} catch (Exception $e) {
    echo "Library loaded: FAILED - " . $e->getMessage() . "\n";
}
?>
