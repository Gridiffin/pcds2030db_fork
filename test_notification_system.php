<?php
/**
 * Test Notification System
 * 
 * Quick test to verify notification system functionality
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';

echo "=== PCDS2030 NOTIFICATION SYSTEM TEST ===\n\n";

// Test 1: Check if notifications table exists and has correct structure
echo "1. Testing notifications table structure...\n";
$result = $conn->query("DESCRIBE notifications");
if ($result) {
    echo "✅ Notifications table exists with columns:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "❌ Notifications table not found\n";
}

echo "\n";

// Test 2: Check existing notifications and their action URLs
echo "2. Testing existing notifications...\n";
$result = $conn->query("SELECT notification_id, user_id, message, action_url, read_status FROM notifications ORDER BY created_at DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "✅ Found {$result->num_rows} notification(s):\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - ID: {$row['notification_id']}, User: {$row['user_id']}\n";
        echo "     Message: " . substr($row['message'], 0, 50) . "...\n";
        echo "     Action URL: {$row['action_url']}\n";
        echo "     Status: " . ($row['read_status'] ? 'Read' : 'Unread') . "\n\n";
    }
} else {
    echo "❌ No notifications found\n";
}

// Test 3: Check if all_notifications.php file exists and is accessible
echo "3. Testing all_notifications.php file...\n";
$notifications_file = PROJECT_ROOT_PATH . 'app/views/agency/users/all_notifications.php';
if (file_exists($notifications_file)) {
    echo "✅ All notifications file exists\n";
    
    // Test PHP syntax
    $output = shell_exec("php -l \"$notifications_file\" 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "✅ PHP syntax is valid\n";
    } else {
        echo "❌ PHP syntax errors found:\n$output\n";
    }
} else {
    echo "❌ All notifications file not found\n";
}

echo "\n";

// Test 4: Check if program_details.php exists (target of action URLs)
echo "4. Testing program details page...\n";
$program_details_file = PROJECT_ROOT_PATH . 'app/views/agency/programs/program_details.php';
if (file_exists($program_details_file)) {
    echo "✅ Program details file exists\n";
    
    // Test PHP syntax
    $output = shell_exec("php -l \"$program_details_file\" 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "✅ PHP syntax is valid\n";
    } else {
        echo "❌ PHP syntax errors found:\n$output\n";
    }
} else {
    echo "❌ Program details file not found\n";
}

echo "\n";

// Test 5: Validate action URLs format
echo "5. Testing action URL format...\n";
$result = $conn->query("SELECT action_url FROM notifications WHERE action_url IS NOT NULL");
if ($result && $result->num_rows > 0) {
    $valid_urls = 0;
    $total_urls = 0;
    
    while ($row = $result->fetch_assoc()) {
        $total_urls++;
        $url = $row['action_url'];
        
        // Check if URL contains the correct path structure
        if (strpos($url, '/app/views/agency/programs/program_details.php') !== false && strpos($url, 'id=') !== false) {
            $valid_urls++;
            echo "✅ Valid URL: $url\n";
        } else {
            echo "❌ Invalid URL: $url\n";
        }
    }
    
    echo "\nSummary: $valid_urls/$total_urls action URLs are valid\n";
} else {
    echo "❌ No action URLs found\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "If all tests pass, the notification system should be working correctly!\n";
?>
