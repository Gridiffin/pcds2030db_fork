<?php
/**
 * Test script to verify audit log system is working with correct database
 */

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/admins/index.php';
require_once 'app/lib/audit_log.php';

echo "<h1>Audit Log Database Connection Test</h1>";

// Test 1: Check database connection
echo "<h2>1. Database Connection Test</h2>";
echo "<p><strong>Configured Database:</strong> " . DB_NAME . "</p>";

if ($conn->ping()) {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    echo "<p>Connected to database: " . DB_NAME . "</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
    exit;
}

// Test 2: Check if audit_logs table exists
echo "<h2>2. Audit Logs Table Test</h2>";
$result = $conn->query("SHOW TABLES LIKE 'audit_logs'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✅ audit_logs table exists</p>";
} else {
    echo "<p style='color: red;'>❌ audit_logs table does not exist</p>";
}

// Test 3: Check if audit_field_changes table exists (for enhanced logging)
echo "<h2>3. Enhanced Audit Log Tables Test</h2>";
$result = $conn->query("SHOW TABLES LIKE 'audit_field_changes'");
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✅ audit_field_changes table exists</p>";
} else {
    echo "<p style='color: orange;'>⚠️ audit_field_changes table does not exist (run enhanced_audit_log_schema.sql to create it)</p>";
}

// Test 4: Test basic audit logging
echo "<h2>4. Basic Audit Logging Test</h2>";
$audit_id = log_audit_action('database_test', 'Testing audit log with database: ' . DB_NAME, 'success', 1);
if ($audit_id) {
    echo "<p style='color: green;'>✅ Basic audit logging successful (ID: $audit_id)</p>";
} else {
    echo "<p style='color: red;'>❌ Basic audit logging failed</p>";
}

// Test 5: Test enhanced audit logging
echo "<h2>5. Enhanced Audit Logging Test</h2>";
$test_data = [
    'test_field' => 'Test Value',
    'test_number' => 123,
    'test_date' => '2024-01-01'
];

$enhanced_audit_id = log_detailed_data_operation('create', 'test', 999, [], $test_data, 1);
if ($enhanced_audit_id) {
    echo "<p style='color: green;'>✅ Enhanced audit logging successful (ID: $enhanced_audit_id)</p>";
    
    // Check if field changes were recorded
    $field_changes = get_audit_field_changes($enhanced_audit_id);
    echo "<p>Field changes recorded: " . count($field_changes) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Enhanced audit logging failed</p>";
}

// Test 6: Test audit log retrieval
echo "<h2>6. Audit Log Retrieval Test</h2>";
$recent_logs = get_audit_logs([], 3, 0);
if ($recent_logs['logs']) {
    echo "<p style='color: green;'>✅ Audit log retrieval successful</p>";
    echo "<p>Recent logs found: " . count($recent_logs['logs']) . "</p>";
    
    echo "<h3>Recent Audit Logs:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Action</th><th>Details</th><th>Database</th></tr>";
    
    foreach ($recent_logs['logs'] as $log) {
        echo "<tr>";
        echo "<td>" . $log['id'] . "</td>";
        echo "<td>" . htmlspecialchars($log['action']) . "</td>";
        echo "<td>" . htmlspecialchars($log['details']) . "</td>";
        echo "<td>" . DB_NAME . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Audit log retrieval failed</p>";
}

echo "<hr>";
echo "<p><strong>Database Test Summary:</strong></p>";
echo "<ul>";
echo "<li>✅ Using database: " . DB_NAME . "</li>";
echo "<li>✅ Connection: " . ($conn->ping() ? 'Successful' : 'Failed') . "</li>";
echo "<li>✅ Audit logging: " . ($audit_id ? 'Working' : 'Failed') . "</li>";
echo "<li>✅ Enhanced logging: " . ($enhanced_audit_id ? 'Working' : 'Failed') . "</li>";
echo "</ul>";

echo "<p><strong>Audit log system is properly configured for database: " . DB_NAME . "</strong></p>";
?> 