<?php
/**
 * Audit Log Test Script
 * 
 * This script helps to test the audit log system and diagnose any issues.
 * Run this script from the command line or browser to test various audit log operations.
 */

// Include necessary files
require_once __DIR__ . '/../app/config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Set up test environment
header('Content-Type: text/plain');
echo "=== Audit Log Test Script ===\n\n";

// Test 1: Test writing a log entry
echo "Test 1: Creating audit log entry... ";
$result = log_audit_action(
    'test_audit_action', 
    'This is a test audit entry from the test script', 
    'success', 
    0
);
echo $result ? "SUCCESS\n" : "FAILED\n";

// Test 2: Retrieve the log entry
echo "\nTest 2: Retrieving audit logs... ";
$filters = ['action_type' => 'test_audit_action'];
$logs = get_audit_logs($filters, 10, 0);
$found = !empty($logs['logs']);
echo $found ? "SUCCESS (Found " . count($logs['logs']) . " entries)\n" : "FAILED\n";

// Test 3: Test JSON encoding/decoding
echo "\nTest 3: Testing JSON handling... ";
$test_data = [
    'success' => true,
    'message' => 'Test message',
    'entries' => $logs['logs']
];

$json_encoded = json_encode($test_data);
$json_decoded = json_decode($json_encoded, true);

$json_success = $json_encoded && $json_decoded && $json_decoded['success'] === true;
echo $json_success ? "SUCCESS\n" : "FAILED\n";

if ($json_encoded) {
    echo "JSON sample: " . substr($json_encoded, 0, 100) . "...\n";
}

// Test 4: Database connection check
echo "\nTest 4: Testing database connection... ";
$db_test = $conn && !$conn->connect_error;
echo $db_test ? "SUCCESS\n" : "FAILED: " . ($conn->connect_error ?? "Unknown error") . "\n";

// Test 5: Output buffering test
echo "\nTest 5: Testing output buffering... ";
ob_start();
echo "This should be captured by the buffer";
$buffer_content = ob_get_clean();
$buffer_success = !empty($buffer_content);
echo $buffer_success ? "SUCCESS\n" : "FAILED\n";

// Test 6: Check for character encoding issues
echo "\nTest 6: Testing character encoding... ";
$special_chars = "Special characters: áéíóúñ @#$%^&*()";
$encoded = json_encode(['text' => $special_chars]);
$decoded = json_decode($encoded, true);
$encoding_ok = $decoded['text'] === $special_chars;
echo $encoding_ok ? "SUCCESS\n" : "FAILED\n";
echo "Original: $special_chars\n";
echo "After JSON roundtrip: {$decoded['text']}\n";

// Display recommendations
echo "\n=== Summary ===\n";
$all_passed = $result && $found && $json_success && $db_test && $buffer_success && $encoding_ok;
if ($all_passed) {
    echo "All tests PASSED. The audit log system appears to be functioning correctly.\n";
} else {
    echo "Some tests FAILED. Review the output above for specific issues.\n";
    
    // Specific recommendations
    if (!$result) {
        echo "- Issue with writing to the audit log. Check table permissions and structure.\n";
    }
    
    if (!$found) {
        echo "- Issue with retrieving audit logs. Check your SQL queries and filters.\n";
    }
    
    if (!$json_success) {
        echo "- Issue with JSON encoding/decoding. Check for data that can't be encoded to JSON.\n";
    }
    
    if (!$db_test) {
        echo "- Database connection issue. Check your connection settings.\n";
    }
    
    if (!$buffer_success) {
        echo "- Output buffering issue. Check that ob_start() and ob_get_clean() work properly.\n";
    }
    
    if (!$encoding_ok) {
        echo "- Character encoding issue. Check database and PHP character set configurations.\n";
    }
}

echo "\n=== Environment Info ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "MySQL Client Version: " . $conn->client_info . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'CLI') . "\n";
echo "Character Set: " . $conn->character_set_name() . "\n";
echo "Default Timezone: " . date_default_timezone_get() . "\n";

// Check if running in CLI or web
$is_cli = php_sapi_name() === 'cli';
echo "Running in: " . ($is_cli ? "Command Line (CLI)" : "Web Server") . "\n";

// End of script
echo "\n=== End of Test ===\n";
