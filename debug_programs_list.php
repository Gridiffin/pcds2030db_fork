<?php
/**
 * Debug script to test get_admin_programs_list function
 */

// Include necessary files
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/admins/statistics.php';

// Get current period
$current_period = get_current_reporting_period();
$period_id = $current_period['period_id'] ?? null;

echo "Current Period ID: " . $period_id . "\n";
echo "Current Period: " . json_encode($current_period) . "\n\n";

// Get programs list
$programs = get_admin_programs_list($period_id);

echo "Number of programs returned: " . count($programs) . "\n\n";

if (!empty($programs)) {
    echo "First program data:\n";
    print_r($programs[0]);
    
    echo "\nChecking for submission data in first program:\n";
    echo "submission_id: " . (isset($programs[0]['submission_id']) ? $programs[0]['submission_id'] : 'NOT SET') . "\n";
    echo "is_draft: " . (isset($programs[0]['is_draft']) ? $programs[0]['is_draft'] : 'NOT SET') . "\n";
    echo "status: " . (isset($programs[0]['status']) ? "'" . $programs[0]['status'] . "'" : 'NOT SET') . "\n";
    echo "status is empty: " . (empty($programs[0]['status']) ? 'YES' : 'NO') . "\n";
    
    // Let's also check the content_json directly
    $submission_id = $programs[0]['submission_id'];
    $query = "SELECT content_json FROM program_submissions WHERE submission_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo "\nContent JSON: " . $row['content_json'] . "\n";
    }
    
    // Test button logic
    echo "\nButton logic test:\n";
    echo "isset(submission_id): " . (isset($programs[0]['submission_id']) ? 'YES' : 'NO') . "\n";
    echo "!empty(is_draft): " . (!empty($programs[0]['is_draft']) ? 'YES' : 'NO') . "\n";
    echo "isset(status) && status !== null: " . (isset($programs[0]['status']) && $programs[0]['status'] !== null ? 'YES' : 'NO') . "\n";
    echo "Should show Resubmit button: " . (isset($programs[0]['submission_id']) && !empty($programs[0]['is_draft']) ? 'YES' : 'NO') . "\n";
    echo "Should show Unsubmit button: " . (isset($programs[0]['submission_id']) && isset($programs[0]['status']) && $programs[0]['status'] !== null ? 'YES' : 'NO') . "\n";
} else {
    echo "No programs returned!\n";
}
?>