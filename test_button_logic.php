<?php
/**
 * Test the button logic fix
 */

// Test data from our debug
$program = [
    'submission_id' => 139,
    'is_draft' => 0,
    'status' => ''
];

echo "Test program data:\n";
print_r($program);

echo "\nOLD LOGIC:\n";
echo "isset(submission_id): " . (isset($program['submission_id']) ? 'YES' : 'NO') . "\n";
echo "!empty(is_draft): " . (!empty($program['is_draft']) ? 'YES' : 'NO') . "\n";
echo "isset(status) && status !== null: " . (isset($program['status']) && $program['status'] !== null ? 'YES' : 'NO') . "\n";
echo "OLD - Should show Resubmit button: " . (isset($program['submission_id']) && !empty($program['is_draft']) ? 'YES' : 'NO') . "\n";
echo "OLD - Should show Unsubmit button: " . (isset($program['submission_id']) && isset($program['status']) && $program['status'] !== null ? 'YES' : 'NO') . "\n";

echo "\nNEW LOGIC:\n";
echo "is_draft == 1: " . ($program['is_draft'] == 1 ? 'YES' : 'NO') . "\n";
echo "is_draft == 0: " . ($program['is_draft'] == 0 ? 'YES' : 'NO') . "\n";
echo "NEW - Should show Resubmit button: " . (isset($program['submission_id']) && $program['is_draft'] == 1 ? 'YES' : 'NO') . "\n";
echo "NEW - Should show Unsubmit button: " . (isset($program['submission_id']) && $program['is_draft'] == 0 ? 'YES' : 'NO') . "\n";

echo "\nTest with draft program:\n";
$draft_program = [
    'submission_id' => 140,
    'is_draft' => 1,
    'status' => ''
];

echo "NEW - Draft should show Resubmit button: " . (isset($draft_program['submission_id']) && $draft_program['is_draft'] == 1 ? 'YES' : 'NO') . "\n";
echo "NEW - Draft should show Unsubmit button: " . (isset($draft_program['submission_id']) && $draft_program['is_draft'] == 0 ? 'YES' : 'NO') . "\n";
?>