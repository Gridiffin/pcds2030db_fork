<?php
/**
 * Development Migration Testing Script
 * 
 * This script helps validate the database migration in development environment
 * Run this after setting up development databases and before code refactoring
 */

// Include development configuration
require_once 'app/config/config_dev.php';

echo "<h1>PCDS2030 Development Migration Testing</h1>\n";
echo "<p>Environment: " . ENVIRONMENT . "</p>\n";
echo "<p>Current Database: " . DB_NAME . "</p>\n";

// Test 1: Basic Database Connection
echo "<h2>Test 1: Database Connection</h2>\n";
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>\n";
    exit;
} else {
    echo "<p style='color: green;'>✅ Database connection successful</p>\n";
}

// Test 2: Check if we're using old or new structure
echo "<h2>Test 2: Database Structure Detection</h2>\n";

// Check for old structure indicators
$old_structure_check = $conn->query("SHOW TABLES LIKE 'agency_groups'");
$new_structure_check = $conn->query("SHOW COLUMNS FROM agencies LIKE 'agency_group'");

if ($old_structure_check && $old_structure_check->num_rows > 0) {
    echo "<p style='color: blue;'>📊 Using OLD database structure (pcds2030_dashboard)</p>\n";
    $is_old_structure = true;
} elseif ($new_structure_check && $new_structure_check->num_rows > 0) {
    echo "<p style='color: blue;'>📊 Using NEW database structure (pcds2030_db)</p>\n";
    $is_old_structure = false;
} else {
    echo "<p style='color: orange;'>⚠️ Cannot determine database structure</p>\n";
    $is_old_structure = null;
}

// Test 3: Table Count and Basic Structure
echo "<h2>Test 3: Table Structure Analysis</h2>\n";

$tables_result = $conn->query("SHOW TABLES");
$table_count = $tables_result->num_rows;
echo "<p>Total tables found: <strong>$table_count</strong></p>\n";

echo "<h3>Available Tables:</h3>\n<ul>\n";
while ($row = $tables_result->fetch_row()) {
    echo "<li>" . $row[0] . "</li>\n";
}
echo "</ul>\n";

// Test 4: Critical Table Record Counts
echo "<h2>Test 4: Record Counts</h2>\n";

$critical_tables = ['users', 'agencies', 'programs', 'outcomes'];
if ($is_old_structure) {
    $critical_tables[] = 'agency_groups';
    $critical_tables[] = 'sectors';
}

echo "<table border='1' style='border-collapse: collapse;'>\n";
echo "<tr><th>Table</th><th>Record Count</th><th>Status</th></tr>\n";

foreach ($critical_tables as $table) {
    $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
    if ($count_result) {
        $count = $count_result->fetch_assoc()['count'];
        $status = $count > 0 ? "✅ Has data" : "⚠️ Empty";
        echo "<tr><td>$table</td><td>$count</td><td>$status</td></tr>\n";
    } else {
        echo "<tr><td>$table</td><td>N/A</td><td>❌ Table missing</td></tr>\n";
    }
}
echo "</table>\n";

// Test 5: Key Column Checks (based on structure)
echo "<h2>Test 5: Key Column Validation</h2>\n";

if ($is_old_structure === true) {
    echo "<h3>Old Structure - Checking problematic columns:</h3>\n";
    
    // Check for columns that will be removed/changed
    $problematic_columns = [
        'users' => ['sector_id', 'agency_group_id'],
        'programs' => ['owner_agency_id'],
        'agencies' => ['agency_group_id']
    ];
    
    foreach ($problematic_columns as $table => $columns) {
        echo "<h4>Table: $table</h4>\n";
        foreach ($columns as $column) {
            $check_result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            if ($check_result && $check_result->num_rows > 0) {
                echo "<p>✅ Column '$column' exists (will need migration)</p>\n";
                
                // Count non-null values
                $value_count = $conn->query("SELECT COUNT(*) as count FROM `$table` WHERE `$column` IS NOT NULL AND `$column` != ''");
                if ($value_count) {
                    $count = $value_count->fetch_assoc()['count'];
                    echo "<p>   → $count records have values in this column</p>\n";
                }
            } else {
                echo "<p>❌ Column '$column' missing</p>\n";
            }
        }
    }
    
} elseif ($is_old_structure === false) {
    echo "<h3>New Structure - Checking new columns:</h3>\n";
    
    // Check for new structure columns
    $new_columns = [
        'agencies' => ['agency_group', 'sector'],
        'users' => ['is_super_admin']
    ];
    
    foreach ($new_columns as $table => $columns) {
        echo "<h4>Table: $table</h4>\n";
        foreach ($columns as $column) {
            $check_result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            if ($check_result && $check_result->num_rows > 0) {
                echo "<p>✅ New column '$column' exists</p>\n";
            } else {
                echo "<p>❌ New column '$column' missing</p>\n";
            }
        }
    }
}

// Test 6: Basic Query Test
echo "<h2>Test 6: Basic Application Queries</h2>\n";

// Test user login query
echo "<h3>User Authentication Query Test:</h3>\n";
try {
    $user_test = $conn->query("SELECT id, username, email, user_type FROM users LIMIT 1");
    if ($user_test && $user_test->num_rows > 0) {
        echo "<p>✅ Basic user query works</p>\n";
        $user = $user_test->fetch_assoc();
        echo "<p>Sample user: " . $user['username'] . " (Type: " . $user['user_type'] . ")</p>\n";
    } else {
        echo "<p>⚠️ No users found or query failed</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ User query failed: " . $e->getMessage() . "</p>\n";
}

// Test agency query
echo "<h3>Agency Query Test:</h3>\n";
try {
    if ($is_old_structure) {
        $agency_test = $conn->query("SELECT id, agency_name, agency_group_id FROM agencies LIMIT 1");
    } else {
        $agency_test = $conn->query("SELECT id, agency_name, agency_group FROM agencies LIMIT 1");
    }
    
    if ($agency_test && $agency_test->num_rows > 0) {
        echo "<p>✅ Basic agency query works</p>\n";
        $agency = $agency_test->fetch_assoc();
        echo "<p>Sample agency: " . $agency['agency_name'] . "</p>\n";
    } else {
        echo "<p>⚠️ No agencies found or query failed</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Agency query failed: " . $e->getMessage() . "</p>\n";
}

// Summary and Next Steps
echo "<h2>Summary and Next Steps</h2>\n";

if ($is_old_structure === true) {
    echo "<div style='background-color: #e6f3ff; padding: 10px; border-left: 4px solid #0066cc;'>\n";
    echo "<h3>✅ Development Environment Ready for Migration</h3>\n";
    echo "<p><strong>Current Status:</strong> Using old database structure</p>\n";
    echo "<p><strong>Next Steps:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Create the target database: <code>pcds2030_db_dev</code></li>\n";
    echo "<li>Import new structure from: <code>app/database/newpcds2030db.sql</code></li>\n";
    echo "<li>Run migration script: <code>master_migration_script.sql</code></li>\n";
    echo "<li>Update config to use new database</li>\n";
    echo "<li>Re-run this test script to validate migration</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
    
} elseif ($is_old_structure === false) {
    echo "<div style='background-color: #e6ffe6; padding: 10px; border-left: 4px solid #00cc00;'>\n";
    echo "<h3>✅ Migration Completed - Ready for Code Refactoring</h3>\n";
    echo "<p><strong>Current Status:</strong> Using new database structure</p>\n";
    echo "<p><strong>Next Steps:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Begin code refactoring (see codebase_impact_analysis.md)</li>\n";
    echo "<li>Test each module as you update it</li>\n";
    echo "<li>Use this script to validate queries as you go</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
    
} else {
    echo "<div style='background-color: #ffe6e6; padding: 10px; border-left: 4px solid #cc0000;'>\n";
    echo "<h3>⚠️ Database Structure Unclear</h3>\n";
    echo "<p>Please check your database setup and try again.</p>\n";
    echo "</div>\n";
}

echo "<hr>\n";
echo "<p><em>Script completed at: " . date('Y-m-d H:i:s') . "</em></p>\n";

$conn->close();
?>
