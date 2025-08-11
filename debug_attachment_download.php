<?php
/**
 * Debug Program Attachment Download
 * 
 * Debug file to check attachment download issues
 */

if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(__DIR__), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_attachments.php';

echo "<h2>Debug: Program Attachment Download</h2>";

// Check if user is logged in
echo "<h3>Session Check:</h3>";
echo "Session started: " . (session_id() ? 'Yes' : 'No') . "<br>";
echo "User logged in: " . (isset($_SESSION['user_id']) ? 'Yes (User ID: ' . $_SESSION['user_id'] . ')' : 'No') . "<br>";
echo "User role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
echo "Agency ID: " . ($_SESSION['agency_id'] ?? 'Not set') . "<br>";

// Get attachment ID from URL parameter
$attachment_id = $_GET['id'] ?? '';
echo "<h3>Request Parameters:</h3>";
echo "Attachment ID: " . htmlspecialchars($attachment_id) . "<br>";

if (empty($attachment_id) || !is_numeric($attachment_id)) {
    echo "<p style='color: red;'>Invalid attachment ID</p>";
    exit;
}

// Test the get_attachment_for_download function
echo "<h3>Database Query Test:</h3>";
try {
    $attachment = get_attachment_for_download(intval($attachment_id));
    
    if ($attachment) {
        echo "<p style='color: green;'>Attachment found in database!</p>";
        echo "<pre>";
        print_r($attachment);
        echo "</pre>";
        
        // Check if file exists
        echo "<h3>File System Check:</h3>";
        $file_path = $attachment['file_path'];
        echo "File path from DB: " . htmlspecialchars($file_path) . "<br>";
        
        // Try different path constructions
        $paths_to_check = [
            $file_path,
            PROJECT_ROOT_PATH . $file_path,
            PROJECT_ROOT_PATH . ltrim($file_path, './'),
            str_replace('../', '', PROJECT_ROOT_PATH . $file_path)
        ];
        
        foreach ($paths_to_check as $i => $path) {
            echo "Path " . ($i + 1) . ": " . htmlspecialchars($path) . " - ";
            if (file_exists($path)) {
                echo "<span style='color: green;'>EXISTS</span><br>";
            } else {
                echo "<span style='color: red;'>NOT FOUND</span><br>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>Attachment not found in database or access denied</p>";
        
        // Check raw database query
        echo "<h3>Raw Database Check:</h3>";
        $stmt = $conn->prepare("SELECT * FROM program_attachments WHERE attachment_id = ?");
        $stmt->bind_param("i", intval($attachment_id));
        $stmt->execute();
        $raw_result = $stmt->get_result();
        
        if ($raw_attachment = $raw_result->fetch_assoc()) {
            echo "<p>Raw attachment record found:</p>";
            echo "<pre>";
            print_r($raw_attachment);
            echo "</pre>";
        } else {
            echo "<p>No attachment record found in database</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h3>Environment Info:</h3>";
echo "PROJECT_ROOT_PATH: " . PROJECT_ROOT_PATH . "<br>";
echo "APP_URL: " . APP_URL . "<br>";
echo "Current working directory: " . getcwd() . "<br>";
echo "PHP version: " . PHP_VERSION . "<br>";
?>
