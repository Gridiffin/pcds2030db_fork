<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PCDS2030 Header Edit Button Debug ===\n\n";

// Simulate the conditions from view_submissions.php
echo "Debugging header edit button visibility...\n\n";

// Test case 1: Both submission and period_id exist (should show button)
echo "Test 1: Valid submission and period_id\n";
$submission = ['period_id' => 123, 'period_display' => 'Q2-2025', 'submitted_at' => '2025-07-26 09:17:00'];
$period_id = 123;
$program_id = 456;

echo "- \$submission: " . (isset($submission) && $submission ? 'EXISTS' : 'NULL/FALSE') . "\n";
echo "- \$period_id: " . ($period_id ?? 'NULL') . "\n";
echo "- \$program_id: " . ($program_id ?? 'NULL') . "\n";

if ($submission && $period_id) {
    echo "- Header edit button should be: VISIBLE ✓\n";
    $edit_url = "edit_submission.php?program_id={$program_id}&period_id={$period_id}";
    echo "- Button URL: {$edit_url}\n";
} else {
    echo "- Header edit button should be: HIDDEN ✗\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test case 2: submission exists but period_id is null
echo "Test 2: Submission exists but period_id is null\n";
$submission = ['period_id' => 123, 'period_display' => 'Q2-2025'];
$period_id = null;  // This could be the issue
$program_id = 456;

echo "- \$submission: " . (isset($submission) && $submission ? 'EXISTS' : 'NULL/FALSE') . "\n";
echo "- \$period_id: " . ($period_id ?? 'NULL') . "\n";
echo "- \$program_id: " . ($program_id ?? 'NULL') . "\n";

if ($submission && $period_id) {
    echo "- Header edit button should be: VISIBLE ✓\n";
} else {
    echo "- Header edit button should be: HIDDEN ✗\n";
    echo "- Reason: " . (!$submission ? 'No submission data' : 'No period_id') . "\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test case 3: submission is null/false
echo "Test 3: No submission data\n";
$submission = null;  // This could also be the issue
$period_id = 123;
$program_id = 456;

echo "- \$submission: " . (isset($submission) && $submission ? 'EXISTS' : 'NULL/FALSE') . "\n";
echo "- \$period_id: " . ($period_id ?? 'NULL') . "\n";
echo "- \$program_id: " . ($program_id ?? 'NULL') . "\n";

if ($submission && $period_id) {
    echo "- Header edit button should be: VISIBLE ✓\n";
} else {
    echo "- Header edit button should be: HIDDEN ✗\n";
    echo "- Reason: " . (!$submission ? 'No submission data' : 'No period_id') . "\n";
}

echo "\n=== Analysis ===\n";
echo "From the screenshot, the edit button is missing from the header.\n";
echo "This means the condition (\$submission && \$period_id) is evaluating to false.\n\n";

echo "Possible causes:\n";
echo "1. \$period_id might be null (not passed in URL or not set properly)\n";
echo "2. \$submission might be null/false (no submission data loaded)\n";
echo "3. The condition logic might need adjustment\n\n";

echo "Solutions:\n";
echo "1. Check URL parameters - ensure period_id is being passed correctly\n";
echo "2. Verify submission data is being loaded properly\n";
echo "3. Add debug output to view_submissions.php to see actual values\n";
echo "4. Consider if the button should show even without period_id (for latest submission)\n";

echo "\n=== Test Complete ===\n";
?>
