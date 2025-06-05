<?php
/**
 * Audit Log System Test Suite
 * 
 * This script tests all audit logging scenarios to ensure
 * the system is working correctly across all integrated components.
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/lib/db_connect.php';
require_once __DIR__ . '/../app/lib/audit_log.php';

// Test configuration
const TEST_USER_ID = 999999; // Use a high ID to avoid conflicts
const TEST_IP = '127.0.0.1';

/**
 * Test results tracking
 */
$test_results = [
    'passed' => 0,
    'failed' => 0,
    'total' => 0,
    'failures' => []
];

/**
 * Helper function to run a test
 */
function run_test($test_name, $test_function) {
    global $test_results;
    
    $test_results['total']++;
    echo "Testing: $test_name... ";
    
    try {
        $result = $test_function();
        if ($result) {
            echo "✅ PASS\n";
            $test_results['passed']++;
        } else {
            echo "❌ FAIL\n";
            $test_results['failed']++;
            $test_results['failures'][] = $test_name;
        }
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        $test_results['failed']++;
        $test_results['failures'][] = "$test_name (Exception: " . $e->getMessage() . ")";
    }
}

/**
 * Test basic audit logging functionality
 */
function test_basic_audit_logging() {
    $result = log_audit_action('test_action', 'Test details', 'success', TEST_USER_ID);
    return $result === true;
}

/**
 * Test logging with failure status
 */
function test_failure_logging() {
    $result = log_audit_action('test_failure', 'Test failure details', 'failure', TEST_USER_ID);
    return $result === true;
}

/**
 * Test logging without details
 */
function test_no_details_logging() {
    $result = log_audit_action('test_no_details', '', 'success', TEST_USER_ID);
    return $result === true;
}

/**
 * Test invalid status handling
 */
function test_invalid_status() {
    $result = log_audit_action('test_invalid', 'Test details', 'invalid_status', TEST_USER_ID);
    return $result === false; // Should fail with invalid status
}

/**
 * Test empty action handling
 */
function test_empty_action() {
    $result = log_audit_action('', 'Test details', 'success', TEST_USER_ID);
    return $result === false; // Should fail with empty action
}

/**
 * Test login helper functions
 */
function test_login_helpers() {
    // Test successful login
    $result1 = log_login_attempt('test@example.com', true, TEST_USER_ID);
    
    // Test failed login
    $result2 = log_login_attempt('hacker@evil.com', false);
    
    // Test logout
    $result3 = log_logout(TEST_USER_ID);
    
    return $result1 && $result2 && $result3;
}

/**
 * Test data operation helpers
 */
function test_data_operation_helpers() {
    $result1 = log_data_operation('create', 'program', 123, 'Test program created', TEST_USER_ID);
    $result2 = log_data_operation('update', 'outcome', 456, 'Test outcome updated', TEST_USER_ID);
    $result3 = log_data_operation('delete', 'user', 789, 'Test user deleted', TEST_USER_ID);
    
    return $result1 && $result2 && $result3;
}

/**
 * Test export helpers
 */
function test_export_helpers() {    $result1 = log_export_operation('csv', 'programs', [], TEST_USER_ID);
    $result2 = log_export_operation('pdf', 'reports', [], TEST_USER_ID);
    $result3 = log_export_operation('xlsx', 'outcomes', [], TEST_USER_ID);
    
    return $result1 && $result2 && $result3;
}

/**
 * Test user management helpers
 */
function test_user_management_helpers() {    $result1 = log_data_operation('create', 'user', 1, ['info' => 'New user created: test@example.com'], TEST_USER_ID);
    $result2 = log_data_operation('update', 'user', 1, ['info' => 'User profile updated: test@example.com'], TEST_USER_ID);
    $result3 = log_data_operation('delete', 'user', 1, ['info' => 'User deleted: test@example.com'], TEST_USER_ID);
    $result4 = log_data_operation('status_change', 'user', 1, ['info' => 'User status changed: test@example.com'], TEST_USER_ID);
    
    return $result1 && $result2 && $result3 && $result4;
}

/**
 * Test audit log retrieval
 */
function test_audit_log_retrieval() {
    global $conn;
    
    // Insert a test log first
    log_audit_action('test_retrieval', 'Test for retrieval', 'success', TEST_USER_ID);
    
    // Test basic retrieval
    $logs = get_audit_logs();
    if (empty($logs)) {
        return false;
    }
    
    // Test filtered retrieval
    $filtered_logs = get_audit_logs(['user_id' => TEST_USER_ID], 10);
    if (empty($filtered_logs)) {
        return false;
    }
    
    // Test with date filter
    $date_filtered = get_audit_logs([
        'start_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'end_date' => date('Y-m-d H:i:s')
    ], 10);
    
    return !empty($date_filtered);
}

/**
 * Test database integrity
 */
function test_database_integrity() {
    global $conn;
    
    // Check if audit_logs table exists and has correct structure
    $result = $conn->query("DESCRIBE audit_logs");
    if (!$result || $result->num_rows == 0) {
        return false;
    }
    
    $required_columns = ['id', 'user_id', 'action', 'details', 'ip_address', 'status', 'created_at'];
    $existing_columns = [];
    
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    foreach ($required_columns as $column) {
        if (!in_array($column, $existing_columns)) {
            return false;
        }
    }
    
    return true;
}

/**
 * Test IP address capture
 */
function test_ip_capture() {
    global $conn;
    
    // Log an action
    log_audit_action('test_ip', 'Testing IP capture', 'success', TEST_USER_ID);
    
    // Check if IP was captured
    $stmt = $conn->prepare("SELECT ip_address FROM audit_logs WHERE user_id = ? AND action = 'test_ip' ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param('i', TEST_USER_ID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return !empty($row['ip_address']);
    }
    
    return false;
}

/**
 * Test error scenarios
 */
function test_error_scenarios() {
    // Test with database connection issues (simulate by using invalid query)
    // This is more of a manual test, but we can test parameter validation
    
    $result1 = log_audit_action(null, 'Test', 'success', TEST_USER_ID); // null action
    $result2 = log_audit_action('test', 'Test', 'invalid', TEST_USER_ID); // invalid status
    
    // Both should return false
    return ($result1 === false && $result2 === false);
}

/**
 * Test large data handling
 */
function test_large_data_handling() {
    // Test with very long details
    $long_details = str_repeat('A', 5000); // 5KB of text
    $result = log_audit_action('test_large_data', $long_details, 'success', TEST_USER_ID);
    
    return $result === true;
}

/**
 * Test special characters and encoding
 */
function test_special_characters() {
    $special_details = "Special chars: üñíçødé 中文 العربية русский ♠♣♥♦";
    $result = log_audit_action('test_special_chars', $special_details, 'success', TEST_USER_ID);
    
    return $result === true;
}

/**
 * Test concurrent logging
 */
function test_concurrent_access() {
    // Simulate multiple rapid logs
    $results = [];
    for ($i = 0; $i < 10; $i++) {
        $results[] = log_audit_action('test_concurrent', "Concurrent test $i", 'success', TEST_USER_ID);
    }
    
    // All should succeed
    return !in_array(false, $results);
}

/**
 * Integration test for authentication flow
 */
function test_auth_integration() {
    // Simulate complete auth flow
    $login_result = log_login_attempt('integration@test.com', true, TEST_USER_ID);
    $activity_result = log_audit_action('page_access', 'User accessed dashboard', 'success', TEST_USER_ID);    $logout_result = log_logout(TEST_USER_ID);
    
    return $login_result && $activity_result && $logout_result;
}

/**
 * Integration test for CRUD operations
 */
function test_crud_integration() {
    // Simulate CRUD operations
    $create_result = log_data_operation('create', 'program', 999, 'Test program created', TEST_USER_ID);
    $read_result = log_audit_action('data_access', 'Program data accessed', 'success', TEST_USER_ID);
    $update_result = log_data_operation('update', 'program', 999, 'Test program updated', TEST_USER_ID);
    $delete_result = log_data_operation('delete', 'program', 999, 'Test program deleted', TEST_USER_ID);
    
    return $create_result && $read_result && $update_result && $delete_result;
}

/**
 * Clean up test data
 */
function cleanup_test_data() {
    global $conn;
    
    echo "Cleaning up test data... ";
    
    $stmt = $conn->prepare("DELETE FROM audit_logs WHERE user_id = ?");
    $stmt->bind_param('i', TEST_USER_ID);
    
    if ($stmt->execute()) {
        echo "✅ Done\n";
    } else {
        echo "❌ Failed\n";
    }
    
    $stmt->close();
}

/**
 * Display test summary
 */
function display_test_summary() {
    global $test_results;
    
    echo "\n=== Test Summary ===\n";
    echo "Total tests: {$test_results['total']}\n";
    echo "Passed: {$test_results['passed']}\n";
    echo "Failed: {$test_results['failed']}\n";
    
    if ($test_results['failed'] > 0) {
        echo "\nFailed tests:\n";
        foreach ($test_results['failures'] as $failure) {
            echo "  - $failure\n";
        }
    }
    
    $success_rate = ($test_results['passed'] / $test_results['total']) * 100;
    echo "\nSuccess rate: " . number_format($success_rate, 1) . "%\n";
    
    if ($success_rate >= 90) {
        echo "🎉 Excellent! Audit logging system is working well.\n";
    } elseif ($success_rate >= 75) {
        echo "⚠️  Good, but some issues need attention.\n";
    } else {
        echo "❌ Poor performance. Significant issues detected.\n";
    }
}

// Run all tests
echo "=== Audit Log System Test Suite ===\n";
echo "Starting comprehensive testing...\n\n";

// Basic functionality tests
run_test("Basic audit logging", 'test_basic_audit_logging');
run_test("Failure status logging", 'test_failure_logging');
run_test("Logging without details", 'test_no_details_logging');
run_test("Invalid status handling", 'test_invalid_status');
run_test("Empty action handling", 'test_empty_action');

// Helper function tests
run_test("Login helper functions", 'test_login_helpers');
run_test("Data operation helpers", 'test_data_operation_helpers');
run_test("Export helpers", 'test_export_helpers');
run_test("User management helpers", 'test_user_management_helpers');

// Data retrieval tests
run_test("Audit log retrieval", 'test_audit_log_retrieval');

// Database and infrastructure tests
run_test("Database integrity", 'test_database_integrity');
run_test("IP address capture", 'test_ip_capture');

// Error handling tests
run_test("Error scenario handling", 'test_error_scenarios');

// Stress and edge case tests
run_test("Large data handling", 'test_large_data_handling');
run_test("Special characters", 'test_special_characters');
run_test("Concurrent access", 'test_concurrent_access');

// Integration tests
run_test("Authentication integration", 'test_auth_integration');
run_test("CRUD operations integration", 'test_crud_integration');

echo "\n";
cleanup_test_data();
display_test_summary();

echo "\nTesting completed.\n";
?>
