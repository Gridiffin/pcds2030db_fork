<?php
// Comprehensive test for duplicate prevention fix
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/agencies/programs.php';

// Mock session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'agency';

echo "=== Comprehensive Duplicate Prevention Test ===\n";

function runTest($testName, $testFunction) {
    echo "\n--- Test: $testName ---\n";
    try {
        $result = $testFunction();
        if ($result) {
            echo "✅ PASSED: $testName\n";
        } else {
            echo "❌ FAILED: $testName\n";
        }
        return $result;
    } catch (Exception $e) {
        echo "💥 ERROR in $testName: " . $e->getMessage() . "\n";
        return false;
    }
}

// Test 1: Basic program creation works
$test1 = function() {
    $data = [
        'program_name' => 'Test Basic Creation ' . time(),
        'description' => 'Basic test',
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31'
    ];
    
    $result = create_wizard_program_draft($data);
    return isset($result['success']) && $result['success'] && isset($result['program_id']);
};

// Test 2: Auto-save creates program when program_id is 0
$test2 = function() {
    $data = [
        'program_id' => 0,
        'program_name' => 'Test Auto-save New ' . time(),
        'description' => 'Auto-save test new'
    ];
    
    $result = auto_save_program_draft($data);
    return isset($result['success']) && $result['success'] && isset($result['program_id']);
};

// Test 3: Auto-save updates existing program when program_id > 0  
$test3 = function() {
    // First create a program
    $data = [
        'program_name' => 'Test Auto-save Update ' . time(),
        'description' => 'Original description'
    ];
    
    $create_result = create_wizard_program_draft($data);
    if (!$create_result['success']) return false;
    
    $program_id = $create_result['program_id'];
    
    // Now update it via auto-save
    $update_data = [
        'program_id' => $program_id,
        'program_name' => 'Test Auto-save Update ' . time() . ' UPDATED',
        'description' => 'Updated description'
    ];
    
    $update_result = auto_save_program_draft($update_data);
    return isset($update_result['success']) && $update_result['success'] && 
           $update_result['program_id'] == $program_id;
};

// Test 4: Manual save with program_id updates instead of creating duplicate
$test4 = function() {
    // Create program via auto-save
    $data = [
        'program_id' => 0,
        'program_name' => 'Test Manual Update ' . time(),
        'description' => 'Original via auto-save'
    ];
    
    $auto_result = auto_save_program_draft($data);
    if (!$auto_result['success']) return false;
    
    $program_id = $auto_result['program_id'];
    
    // Simulate manual save with program_id (fixed logic)
    $manual_data = [
        'program_name' => 'Test Manual Update ' . time() . ' MANUAL',
        'description' => 'Updated via manual save',
        'target' => 'Manual target',
        'status_description' => 'Manual status'
    ];
    
    // This simulates the fixed logic in create_program.php
    $manual_result = update_wizard_program_draft($program_id, $manual_data);
    
    return isset($manual_result['success']) && $manual_result['success'] && 
           $manual_result['program_id'] == $program_id;
};

// Test 5: Complete workflow simulation (the actual duplicate bug scenario)
$test5 = function() {
    $pdo = get_db_connection();
    
    // Count programs before
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs");
    $before_count = $stmt->fetch()['count'];
    
    $timestamp = time();
    
    // Step 1: Auto-save (user typing, creates program)
    $auto_data = [
        'program_id' => 0,
        'program_name' => "Workflow Test $timestamp",
        'description' => 'User is typing...'
    ];
    
    $auto_result = auto_save_program_draft($auto_data);
    if (!$auto_result['success']) return false;
    
    $program_id = $auto_result['program_id'];
    
    // Step 2: Manual save (user clicks Save Draft)
    // Simulate $_POST data that create_program.php would receive
    $_POST = [
        'program_id' => $program_id,  // This is the key fix!
        'program_name' => "Workflow Test $timestamp",
        'description' => 'Final description',
        'target' => 'Final target',
        'status_description' => 'Final status'
    ];
    
    // Simulate the FIXED logic from create_program.php
    $post_program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
    
    if ($post_program_id > 0) {
        // UPDATE path (fixed)
        $manual_result = update_wizard_program_draft($post_program_id, $_POST);
    } else {
        // CREATE path (old buggy behavior)
        $manual_result = create_wizard_program_draft($_POST);
    }
    
    if (!$manual_result['success']) return false;
    
    // Verify only 1 program was created
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM programs");
    $after_count = $stmt->fetch()['count'];
    
    $programs_created = $after_count - $before_count;
    echo "Programs created in workflow: $programs_created (should be 1)\n";
    
    return $programs_created == 1 && $manual_result['program_id'] == $program_id;
};

// Run all tests
$tests = [
    'Basic Program Creation' => $test1,
    'Auto-save Creates New Program' => $test2, 
    'Auto-save Updates Existing Program' => $test3,
    'Manual Save Updates Existing Program' => $test4,
    'Complete Workflow (Duplicate Prevention)' => $test5
];

$passed = 0;
$total = count($tests);

foreach ($tests as $name => $test) {
    if (runTest($name, $test)) {
        $passed++;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "TEST RESULTS: $passed/$total tests passed\n";

if ($passed == $total) {
    echo "🎉 ALL TESTS PASSED! Duplicate issue should be fixed.\n";
} else {
    echo "⚠️  Some tests failed. Review the results above.\n";
}

echo "\n=== Test Complete ===\n";
?>
