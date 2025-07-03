<?php
/**
 * Test file for Cascading Submission Logic
 * This file tests the new cascading submission functionality
 */

require_once 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/statistics.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Test data setup
$test_program_id = 276; // Using program ID from previous conversation
$test_periods = [3, 4]; // Q3 and Q4

echo "<h1>Cascading Submission Logic Test</h1>\n";

// Test 1: Check current state
echo "<h2>Test 1: Current State Check</h2>\n";
foreach ($test_periods as $period_id) {
    $query = "SELECT submission_id, period_id, is_draft, submission_date 
              FROM program_submissions 
              WHERE program_id = ? AND period_id = ? 
              ORDER BY submission_id DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $test_program_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $submission = $result->fetch_assoc();
        echo "Period {$period_id}: Submission ID: {$submission['submission_id']}, ";
        echo "Draft: " . ($submission['is_draft'] ? 'Yes' : 'No') . ", ";
        echo "Date: {$submission['submission_date']}<br>\n";
    } else {
        echo "Period {$period_id}: No submission found<br>\n";
    }
    $stmt->close();
}

// Test 2: Test Enhanced Unsubmit
echo "<h2>Test 2: Enhanced Unsubmit Function</h2>\n";
echo "Testing enhanced_unsubmit_program function...<br>\n";

// Test unsubmit on period 4 without cascade
$result = enhanced_unsubmit_program($test_program_id, 4, false);
echo "Unsubmit Period 4 (no cascade): ";
echo $result['success'] ? 'SUCCESS' : 'FAILED';
echo " - " . $result['message'] . "<br>\n";
echo "Affected periods: " . implode(', ', $result['affected_periods']) . "<br>\n";

// Test 3: Check state after unsubmit
echo "<h2>Test 3: State After Unsubmit</h2>\n";
foreach ($test_periods as $period_id) {
    $query = "SELECT submission_id, period_id, is_draft, submission_date 
              FROM program_submissions 
              WHERE program_id = ? AND period_id = ? 
              ORDER BY submission_id DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $test_program_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $submission = $result->fetch_assoc();
        echo "Period {$period_id}: Submission ID: {$submission['submission_id']}, ";
        echo "Draft: " . ($submission['is_draft'] ? 'Yes' : 'No') . ", ";
        echo "Date: {$submission['submission_date']}<br>\n";
    } else {
        echo "Period {$period_id}: No submission found<br>\n";
    }
    $stmt->close();
}

// Test 4: Simulate submission (this would test cascading logic)
echo "<h2>Test 4: Simulation Results</h2>\n";
echo "If a submission were made for ANY period, the cascading logic would:<br>\n";
echo "1. First update ALL OTHER drafts for the same program (any period_id != current) to is_draft = 0<br>\n";
echo "2. Then update/create the submission for the current period with is_draft = 0<br>\n";
echo "3. Log the cascading finalization action<br>\n";
echo "<br>\n";
echo "Examples:<br>\n";
echo "- Submit Q4 → finalizes Q1, Q2, Q3 drafts + Q4<br>\n";
echo "- Submit Q1 → finalizes Q2, Q3, Q4 drafts + Q1<br>\n";
echo "- Submit Q3 → finalizes Q1, Q2, Q4 drafts + Q3<br>\n";
echo "This ensures ALL quarters appear in half-yearly reports regardless of submission order.<br>\n";

echo "<h2>Test Complete</h2>\n";
echo "Note: This test file should be deleted after implementation is complete.<br>\n";
?>
