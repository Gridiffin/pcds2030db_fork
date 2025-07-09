<?php
/**
 * Test script to verify agency programs functionality with new schema
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

echo "<h1>Agency Programs Test - New Schema</h1>\n";

// Test 1: Check if we can get agency programs
echo "<h2>Test 1: Agency Programs List</h2>\n";

// Simulate a logged-in agency user (user_id = 2, which is stidc1)
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'focal';
$_SESSION['agency_id'] = 1; // STIDC

try {
    $programs = get_agency_programs_list(2);
    echo "<p>✅ Successfully retrieved " . count($programs) . " programs</p>\n";
    
    if (count($programs) > 0) {
        echo "<h3>Programs found:</h3>\n";
        echo "<ul>\n";
        foreach ($programs as $program) {
            echo "<li>ID: {$program['program_id']} - {$program['program_name']} (Status: {$program['status_indicator']})</li>\n";
        }
        echo "</ul>\n";
    } else {
        echo "<p>No programs found for this user</p>\n";
    }
} catch (Exception $e) {
    echo "<p>❌ Error getting programs: " . $e->getMessage() . "</p>\n";
}

// Test 2: Try to create a new program
echo "<h2>Test 2: Create New Program</h2>\n";

$test_program_data = [
    'program_name' => 'Test Program - New Schema',
    'description' => 'This is a test program created with the new schema',
    'start_date' => '2025-01-01',
    'end_date' => '2025-12-31',
    'targets' => [
        [
            'target' => 'Complete test target 1',
            'status_description' => 'In progress'
        ],
        [
            'target' => 'Complete test target 2', 
            'status_description' => 'Not started'
        ]
    ]
];

try {
    $result = create_agency_program($test_program_data);
    
    if (isset($result['success']) && $result['success']) {
        echo "<p>✅ Successfully created program with ID: {$result['program_id']}</p>\n";
        
        // Test 3: Get program details
        echo "<h2>Test 3: Get Program Details</h2>\n";
        $program_details = get_program_details($result['program_id']);
        
        if ($program_details) {
            echo "<p>✅ Successfully retrieved program details</p>\n";
            echo "<h3>Program Details:</h3>\n";
            echo "<ul>\n";
            echo "<li>Name: {$program_details['program_name']}</li>\n";
            echo "<li>Description: {$program_details['program_description']}</li>\n";
            echo "<li>Status: {$program_details['status_indicator']}</li>\n";
            echo "<li>Rating: {$program_details['rating']}</li>\n";
            echo "</ul>\n";
        } else {
            echo "<p>❌ Failed to get program details</p>\n";
        }
        
    } else {
        echo "<p>❌ Failed to create program: " . ($result['error'] ?? 'Unknown error') . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p>❌ Error creating program: " . $e->getMessage() . "</p>\n";
}

// Test 4: Check database tables
echo "<h2>Test 4: Database Schema Verification</h2>\n";

$tables_to_check = ['programs', 'program_submissions', 'program_targets', 'program_user_assignments'];

foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ Table '$table' exists</p>\n";
    } else {
        echo "<p>❌ Table '$table' missing</p>\n";
    }
}

echo "<h2>Test Complete</h2>\n";
echo "<p>If all tests passed, the agency programs functionality should be working with the new schema.</p>\n";
?> 