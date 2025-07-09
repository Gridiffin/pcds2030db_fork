<?php
/**
 * Test script to verify agency programs functionality
 */

// Include necessary files
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/agencies/programs.php';

// Start session
session_start();

// Test database connection
if (!$conn) {
    die("Database connection failed");
}

echo "<h1>Agency Programs Test</h1>\n";

// Test 1: Check if we can get agency programs
echo "<h2>Test 1: Agency Programs List</h2>\n";

// Simulate a logged-in agency user (user_id = 2, which is stidc1)
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'focal';
$_SESSION['agency_id'] = 1; // STIDC

try {
    $programs = get_agency_programs_list(2);
    echo "<p>✅ Successfully retrieved " . count($programs) . " programs for user 2</p>\n";
    
    if (count($programs) > 0) {
        echo "<p>First program: " . htmlspecialchars($programs[0]['program_name']) . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p>❌ Error getting programs: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Test 2: Check if we can create a test program
echo "<h2>Test 2: Program Creation</h2>\n";

$test_data = [
    'program_name' => 'Test Program ' . date('Y-m-d H:i:s'),
    'program_number' => 'TEST-' . date('Ymd'),
    'rating' => 'not-started',
    'start_date' => '2025-01-01',
    'end_date' => '2025-12-31'
];

try {
    $result = create_agency_program($test_data);
    if (isset($result['success']) && $result['success']) {
        echo "<p>✅ Successfully created test program with ID: " . $result['program_id'] . "</p>\n";
        
        // Clean up - delete the test program
        $delete_query = "DELETE FROM programs WHERE program_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $result['program_id']);
        $delete_stmt->execute();
        echo "<p>✅ Test program cleaned up</p>\n";
    } else {
        echo "<p>❌ Failed to create program: " . htmlspecialchars($result['error'] ?? 'Unknown error') . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p>❌ Error creating program: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Test 3: Check database schema
echo "<h2>Test 3: Database Schema Check</h2>\n";

$schema_check = $conn->query("DESCRIBE programs");
if ($schema_check) {
    echo "<p>✅ Programs table exists</p>\n";
    $columns = [];
    while ($row = $schema_check->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    $required_columns = ['program_id', 'program_name', 'agency_id', 'users_assigned'];
    $missing_columns = array_diff($required_columns, $columns);
    
    if (empty($missing_columns)) {
        echo "<p>✅ All required columns present</p>\n";
    } else {
        echo "<p>❌ Missing columns: " . implode(', ', $missing_columns) . "</p>\n";
    }
    
    // Check for deprecated columns
    $deprecated_columns = ['sector_id', 'owner_agency_id', 'agency_group', 'is_assigned', 'edit_permissions'];
    $found_deprecated = array_intersect($deprecated_columns, $columns);
    
    if (empty($found_deprecated)) {
        echo "<p>✅ No deprecated columns found</p>\n";
    } else {
        echo "<p>⚠️ Found deprecated columns: " . implode(', ', $found_deprecated) . "</p>\n";
    }
} else {
    echo "<p>❌ Could not describe programs table</p>\n";
}

echo "<h2>Test Complete</h2>\n";
echo "<p>If all tests passed, agency programs functionality should be working correctly.</p>\n";
?> 