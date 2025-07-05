<?php
/**
 * Current Database State Validation
 * 
 * Run this to confirm your database structure before and after migration
 */

// Use your existing config
require_once 'app/config/config.php';

echo "<h1>Database Structure Validation</h1>\n";
echo "<p>Database: " . DB_NAME . "</p>\n";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>\n";

// Test connection
if ($conn->connect_error) {
    die("<p style='color: red;'>❌ Connection failed: " . $conn->connect_error . "</p>");
}
echo "<p style='color: green;'>✅ Database connection successful</p>\n";

// Check for old vs new structure
echo "<h2>Structure Detection</h2>\n";

// Check for old structure indicators
$old_indicators = [
    'agency_groups table' => "SHOW TABLES LIKE 'agency_groups'",
    'sectors table' => "SHOW TABLES LIKE 'sectors'",
    'users.sector_id' => "SHOW COLUMNS FROM users LIKE 'sector_id'",
    'users.agency_group_id' => "SHOW COLUMNS FROM users LIKE 'agency_group_id'",
    'agencies.agency_group_id' => "SHOW COLUMNS FROM agencies LIKE 'agency_group_id'",
    'programs.owner_agency_id' => "SHOW COLUMNS FROM programs LIKE 'owner_agency_id'"
];

// Check for new structure indicators  
$new_indicators = [
    'agencies.agency_group' => "SHOW COLUMNS FROM agencies LIKE 'agency_group'",
    'agencies.sector' => "SHOW COLUMNS FROM agencies LIKE 'sector'",
    'users.is_super_admin' => "SHOW COLUMNS FROM users LIKE 'is_super_admin'",
    'programs.agency_id' => "SHOW COLUMNS FROM programs LIKE 'agency_id'"
];

echo "<h3>Old Structure Elements:</h3>\n";
$old_count = 0;
foreach ($old_indicators as $name => $query) {
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ Found: $name</p>\n";
        $old_count++;
    } else {
        echo "<p>❌ Missing: $name</p>\n";
    }
}

echo "<h3>New Structure Elements:</h3>\n";
$new_count = 0;
foreach ($new_indicators as $name => $query) {
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ Found: $name</p>\n";
        $new_count++;
    } else {
        echo "<p>❌ Missing: $name</p>\n";
    }
}

// Determine status
echo "<h2>Migration Status</h2>\n";
if ($old_count > 0 && $new_count == 0) {
    echo "<div style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;'>\n";
    echo "<h3>📋 PRE-MIGRATION STATE</h3>\n";
    echo "<p><strong>Status:</strong> Old database structure detected</p>\n";
    echo "<p><strong>Next Step:</strong> Run the migration script</p>\n";
    echo "<p><strong>Action:</strong> Execute <code>master_migration_script.sql</code></p>\n";
    echo "</div>\n";
} elseif ($old_count == 0 && $new_count > 0) {
    echo "<div style='background: #d4edda; padding: 10px; border-left: 4px solid #28a745;'>\n";
    echo "<h3>✅ POST-MIGRATION STATE</h3>\n";
    echo "<p><strong>Status:</strong> New database structure detected</p>\n";
    echo "<p><strong>Next Step:</strong> Begin code refactoring</p>\n";
    echo "<p><strong>Action:</strong> Update PHP files to use new structure</p>\n";
    echo "</div>\n";
} elseif ($old_count > 0 && $new_count > 0) {
    echo "<div style='background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>\n";
    echo "<h3>⚠️ MIXED STATE - MIGRATION INCOMPLETE</h3>\n";
    echo "<p><strong>Status:</strong> Partial migration detected</p>\n";
    echo "<p><strong>Problem:</strong> Both old and new elements present</p>\n";
    echo "<p><strong>Action:</strong> Check migration script for errors</p>\n";
    echo "</div>\n";
} else {
    echo "<div style='background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545;'>\n";
    echo "<h3>❌ UNKNOWN STATE</h3>\n";
    echo "<p><strong>Problem:</strong> Cannot determine database structure</p>\n";
    echo "<p><strong>Action:</strong> Check database connection and table existence</p>\n";
    echo "</div>\n";
}

// Record counts
echo "<h2>Table Record Counts</h2>\n";
$tables = ['users', 'agencies', 'programs', 'outcomes'];

// Add old tables if they exist
if ($old_count > 0) {
    $tables = array_merge($tables, ['agency_groups', 'sectors']);
}

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>Table</th><th>Count</th><th>Status</th></tr>\n";

foreach ($tables as $table) {
    $count_query = "SELECT COUNT(*) as count FROM `$table`";
    $result = $conn->query($count_query);
    
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        $status = $count > 0 ? "✅ Has data" : "⚠️ Empty";
        echo "<tr><td>$table</td><td>$count</td><td>$status</td></tr>\n";
    } else {
        echo "<tr><td>$table</td><td>N/A</td><td>❌ Not found</td></tr>\n";
    }
}
echo "</table>\n";

// Quick test queries
echo "<h2>Quick Functionality Tests</h2>\n";

// Test user query (adapt based on structure)
echo "<h3>User Query Test:</h3>\n";
try {
    if ($old_count > 0) {
        $user_query = "SELECT id, username, email, user_type, sector_id, agency_group_id FROM users LIMIT 1";
    } else {
        $user_query = "SELECT id, username, email, user_type, is_super_admin FROM users LIMIT 1";
    }
    
    $result = $conn->query($user_query);
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ User query successful</p>\n";
        $user = $result->fetch_assoc();
        echo "<pre>" . json_encode($user, JSON_PRETTY_PRINT) . "</pre>\n";
    } else {
        echo "<p>⚠️ No users found</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ User query failed: " . $e->getMessage() . "</p>\n";
}

// Test agency query
echo "<h3>Agency Query Test:</h3>\n";
try {
    if ($old_count > 0) {
        $agency_query = "SELECT id, agency_name, agency_group_id FROM agencies LIMIT 1";
    } else {
        $agency_query = "SELECT id, agency_name, agency_group, sector FROM agencies LIMIT 1";
    }
    
    $result = $conn->query($agency_query);
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ Agency query successful</p>\n";
        $agency = $result->fetch_assoc();
        echo "<pre>" . json_encode($agency, JSON_PRETTY_PRINT) . "</pre>\n";
    } else {
        echo "<p>⚠️ No agencies found</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Agency query failed: " . $e->getMessage() . "</p>\n";
}

echo "<hr>\n";
echo "<p><em>Validation completed at: " . date('Y-m-d H:i:s') . "</em></p>\n";

$conn->close();
?>
