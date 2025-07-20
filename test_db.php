<?php
/**
 * Test database connection for notifications
 */

// Define PROJECT_ROOT_PATH
define('PROJECT_ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);

// Include files in correct order
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';

echo "Testing database connection...\n";

if (isset($conn) && $conn !== null) {
    echo "✅ Database connection exists\n";
    
    // Test a simple query
    $result = $conn->query("SELECT 1 as test");
    if ($result) {
        echo "✅ Database query successful\n";
        $result->close();
    } else {
        echo "❌ Database query failed\n";
    }
} else {
    echo "❌ Database connection is null\n";
}

echo "Test completed.\n";
?>
