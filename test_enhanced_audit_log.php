<?php
/**
 * Test script for enhanced audit log functionality
 * This script demonstrates field-level tracking capabilities
 */

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/admins/index.php';
require_once 'app/lib/audit_log.php';

echo "<h1>Enhanced Audit Log Test</h1>";

// Test 1: Create operation with field tracking
echo "<h2>1. Testing Create Operation</h2>";
$new_data = [
    'program_name' => 'Enhanced Forestry Program',
    'description' => 'This is a test program for enhanced audit logging',
    'status' => 'active',
    'budget' => 100000,
    'start_date' => '2024-01-01'
];

$audit_id = log_detailed_data_operation('create', 'program', 999, [], $new_data, 1);
if ($audit_id) {
    echo "<p style='color: green;'>✅ Create operation logged successfully (ID: $audit_id)</p>";
    
    // Show field changes
    $field_changes = get_audit_field_changes($audit_id);
    echo "<p>Field changes recorded: " . count($field_changes) . "</p>";
    foreach ($field_changes as $change) {
        echo "<li>" . format_field_change($change) . "</li>";
    }
} else {
    echo "<p style='color: red;'>❌ Create operation failed</p>";
}

echo "<hr>";

// Test 2: Update operation with field tracking
echo "<h2>2. Testing Update Operation</h2>";
$old_data = [
    'program_name' => 'Enhanced Forestry Program',
    'description' => 'This is a test program for enhanced audit logging',
    'status' => 'active',
    'budget' => 100000,
    'start_date' => '2024-01-01'
];

$updated_data = [
    'program_name' => 'Updated Enhanced Forestry Program',
    'description' => 'This is an updated test program for enhanced audit logging',
    'status' => 'completed',
    'budget' => 150000,
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31'
];

$audit_id = log_detailed_data_operation('update', 'program', 999, $old_data, $updated_data, 1);
if ($audit_id) {
    echo "<p style='color: green;'>✅ Update operation logged successfully (ID: $audit_id)</p>";
    
    // Show field changes
    $field_changes = get_audit_field_changes($audit_id);
    echo "<p>Field changes recorded: " . count($field_changes) . "</p>";
    foreach ($field_changes as $change) {
        echo "<li>" . format_field_change($change) . "</li>";
    }
} else {
    echo "<p style='color: red;'>❌ Update operation failed</p>";
}

echo "<hr>";

// Test 3: Delete operation with field tracking
echo "<h2>3. Testing Delete Operation</h2>";
$data_to_delete = [
    'program_name' => 'Updated Enhanced Forestry Program',
    'description' => 'This is an updated test program for enhanced audit logging',
    'status' => 'completed',
    'budget' => 150000,
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31'
];

$audit_id = log_detailed_data_operation('delete', 'program', 999, $data_to_delete, [], 1);
if ($audit_id) {
    echo "<p style='color: green;'>✅ Delete operation logged successfully (ID: $audit_id)</p>";
    
    // Show field changes
    $field_changes = get_audit_field_changes($audit_id);
    echo "<p>Field changes recorded: " . count($field_changes) . "</p>";
    foreach ($field_changes as $change) {
        echo "<li>" . format_field_change($change) . "</li>";
    }
} else {
    echo "<p style='color: red;'>❌ Delete operation failed</p>";
}

echo "<hr>";

// Test 4: Basic log_data_operation vs Enhanced log_detailed_data_operation
echo "<h2>4. Comparing Basic vs Enhanced Audit Logging</h2>";

echo "<h3>Basic Audit Logging (log_data_operation):</h3>";
$basic_audit_id = log_data_operation('update', 'program', 888, ['status' => 'active'], 1);
if ($basic_audit_id) {
    echo "<p style='color: green;'>✅ Basic operation logged successfully (ID: $basic_audit_id)</p>";
} else {
    echo "<p style='color: red;'>❌ Basic operation failed</p>";
}

echo "<h3>Enhanced Audit Logging (log_detailed_data_operation):</h3>";
$enhanced_audit_id = log_detailed_data_operation('update', 'program', 777, 
    ['program_name' => 'Old Name', 'status' => 'inactive'], 
    ['program_name' => 'New Name', 'status' => 'active'], 1);
if ($enhanced_audit_id) {
    echo "<p style='color: green;'>✅ Enhanced operation logged successfully (ID: $enhanced_audit_id)</p>";
    
    // Show field changes
    $field_changes = get_audit_field_changes($enhanced_audit_id);
    echo "<p>Field changes recorded: " . count($field_changes) . "</p>";
    foreach ($field_changes as $change) {
        echo "<li>" . format_field_change($change) . "</li>";
    }
} else {
    echo "<p style='color: red;'>❌ Enhanced operation failed</p>";
}

echo "<hr>";

// Test 5: Show recent audit logs with field changes
echo "<h2>5. Recent Audit Logs with Field Changes</h2>";
$recent_logs = get_audit_logs([], 5, 0);
if ($recent_logs['logs']) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Action</th><th>User</th><th>Field Changes</th><th>Date</th></tr>";
    
    foreach ($recent_logs['logs'] as $log) {
        $field_changes_count = $log['field_changes_count'] ?? 0;
        $field_changes_summary = $log['field_changes_summary'] ?? '';
        
        echo "<tr>";
        echo "<td>" . $log['id'] . "</td>";
        echo "<td>" . htmlspecialchars($log['action']) . "</td>";
        echo "<td>" . htmlspecialchars($log['user_name']) . "</td>";
        echo "<td>" . ($field_changes_count > 0 ? "$field_changes_count changes" : "No changes") . "</td>";
        echo "<td>" . $log['created_at'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No recent audit logs found.</p>";
}

echo "<hr>";

// Test 6: Test field type detection
echo "<h2>6. Field Type Detection Test</h2>";
$test_values = [
    'text' => 'Hello World',
    'number' => 123,
    'float' => 123.45,
    'date' => '2024-01-01',
    'boolean' => true,
    'json' => '{"key": "value"}',
    'null' => null
];

foreach ($test_values as $description => $value) {
    $type = get_field_type($value);
    echo "<p><strong>$description:</strong> " . var_export($value, true) . " → <code>$type</code></p>";
}

echo "<hr>";
echo "<p><strong>Enhanced audit log test completed!</strong></p>";
echo "<p>You can now view detailed field changes in the audit log interface.</p>";
?> 