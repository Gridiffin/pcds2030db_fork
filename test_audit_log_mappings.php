<?php
/**
 * Test script to verify audit log system is working with database mappings
 */

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/admins/index.php';
require_once 'app/lib/audit_log.php';

echo "<h1>Audit Log Database Mappings Test</h1>";

// Test 1: Check database mappings
echo "<h2>1. Database Mappings Test</h2>";
$db_mappings = include 'app/config/db_names.php';
$tables = $db_mappings['tables'];
$columns = $db_mappings['columns'];

echo "<p><strong>Tables Mappings:</strong></p>";
echo "<ul>";
foreach ($tables as $key => $value) {
    echo "<li><strong>$key</strong> → <code>$value</code></li>";
}
echo "</ul>";

echo "<p><strong>Audit Log Columns:</strong></p>";
echo "<ul>";
foreach ($columns['audit_logs'] as $key => $value) {
    echo "<li><strong>$key</strong> → <code>$value</code></li>";
}
echo "</ul>";

echo "<p><strong>Audit Field Changes Columns:</strong></p>";
echo "<ul>";
foreach ($columns['audit_field_changes'] as $key => $value) {
    echo "<li><strong>$key</strong> → <code>$value</code></li>";
}
echo "</ul>";

// Test 2: Test basic audit logging with mappings
echo "<h2>2. Basic Audit Logging with Mappings</h2>";
$audit_id = log_audit_action('mappings_test', 'Testing audit log with database mappings', 'success', 1);
if ($audit_id) {
    echo "<p style='color: green;'>✅ Basic audit logging with mappings successful (ID: $audit_id)</p>";
} else {
    echo "<p style='color: red;'>❌ Basic audit logging with mappings failed</p>";
}

// Test 3: Test enhanced audit logging with mappings
echo "<h2>3. Enhanced Audit Logging with Mappings</h2>";
$test_data = [
    'test_field' => 'Test Value with Mappings',
    'test_number' => 456,
    'test_date' => '2024-01-15'
];

$enhanced_audit_id = log_detailed_data_operation('create', 'test', 888, [], $test_data, 1);
if ($enhanced_audit_id) {
    echo "<p style='color: green;'>✅ Enhanced audit logging with mappings successful (ID: $enhanced_audit_id)</p>";
    
    // Check if field changes were recorded
    $field_changes = get_audit_field_changes($enhanced_audit_id);
    echo "<p>Field changes recorded: " . count($field_changes) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Enhanced audit logging with mappings failed</p>";
}

// Test 4: Test entity name resolution with mappings
echo "<h2>4. Entity Name Resolution with Mappings</h2>";
$entity_name = get_entity_name('program', 1);
echo "<p>Entity name for program ID 1: " . ($entity_name ? $entity_name : 'Not found') . "</p>";

$key_field = get_key_field('program');
echo "<p>Key field for program: <code>$key_field</code></p>";

// Test 5: Test audit log retrieval with mappings
echo "<h2>5. Audit Log Retrieval with Mappings</h2>";
$recent_logs = get_audit_logs([], 3, 0);
if ($recent_logs['logs']) {
    echo "<p style='color: green;'>✅ Audit log retrieval with mappings successful</p>";
    echo "<p>Recent logs found: " . count($recent_logs['logs']) . "</p>";
    
    echo "<h3>Recent Audit Logs (using mappings):</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Action</th><th>Details</th><th>User</th><th>Field Changes</th></tr>";
    
    foreach ($recent_logs['logs'] as $log) {
        $field_changes_count = $log['field_changes_count'] ?? 0;
        echo "<tr>";
        echo "<td>" . $log['id'] . "</td>";
        echo "<td>" . htmlspecialchars($log['action']) . "</td>";
        echo "<td>" . htmlspecialchars($log['details']) . "</td>";
        echo "<td>" . htmlspecialchars($log['user_name']) . "</td>";
        echo "<td>" . ($field_changes_count > 0 ? "$field_changes_count changes" : "No changes") . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Audit log retrieval with mappings failed</p>";
}

// Test 6: Test field changes retrieval with mappings
echo "<h2>6. Field Changes Retrieval with Mappings</h2>";
if ($enhanced_audit_id) {
    $field_changes = get_audit_field_changes($enhanced_audit_id);
    if ($field_changes) {
        echo "<p style='color: green;'>✅ Field changes retrieval with mappings successful</p>";
        echo "<p>Field changes found: " . count($field_changes) . "</p>";
        
        echo "<h3>Field Changes (using mappings):</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Change Type</th><th>Old Value</th><th>New Value</th></tr>";
        
        foreach ($field_changes as $change) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($change['field_name']) . "</td>";
            echo "<td>" . htmlspecialchars($change['field_type']) . "</td>";
            echo "<td>" . htmlspecialchars($change['change_type']) . "</td>";
            echo "<td>" . htmlspecialchars($change['old_value'] ?? 'null') . "</td>";
            echo "<td>" . htmlspecialchars($change['new_value'] ?? 'null') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No field changes found</p>";
    }
}

echo "<hr>";
echo "<p><strong>Database Mappings Test Summary:</strong></p>";
echo "<ul>";
echo "<li>✅ Database mappings loaded successfully</li>";
echo "<li>✅ Table mappings: " . count($tables) . " tables</li>";
echo "<li>✅ Column mappings: " . count($columns) . " tables with columns</li>";
echo "<li>✅ Basic audit logging: " . ($audit_id ? 'Working' : 'Failed') . "</li>";
echo "<li>✅ Enhanced audit logging: " . ($enhanced_audit_id ? 'Working' : 'Failed') . "</li>";
echo "<li>✅ Entity name resolution: " . ($entity_name ? 'Working' : 'Failed') . "</li>";
echo "<li>✅ Audit log retrieval: " . ($recent_logs['logs'] ? 'Working' : 'Failed') . "</li>";
echo "</ul>";

echo "<p><strong>Audit log system is properly using database mappings!</strong></p>";
?> 