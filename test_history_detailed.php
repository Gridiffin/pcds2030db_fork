<?php
// Test the program history functionality directly
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/agencies/programs.php';

echo "Testing Program History Functionality" . PHP_EOL . PHP_EOL;

// Test with a program that has multiple submissions
$program_id = 168; // We know this exists from our earlier test
echo "Testing program ID: $program_id" . PHP_EOL;

// Test get_program_edit_history function
echo "1. Testing get_program_edit_history()..." . PHP_EOL;
$history = get_program_edit_history($program_id);

if ($history && isset($history['submissions'])) {
    echo "   ✓ Function returned data with " . count($history['submissions']) . " submissions" . PHP_EOL;
    
    if (!empty($history['submissions'])) {
        $first_submission = $history['submissions'][0];
        echo "   ✓ First submission details:" . PHP_EOL;
        echo "     - Submission ID: " . ($first_submission['submission_id'] ?? 'N/A') . PHP_EOL;
        echo "     - Period: " . ($first_submission['period_display'] ?? 'N/A') . PHP_EOL;
        echo "     - Is Draft: " . (isset($first_submission['is_draft']) ? ($first_submission['is_draft'] ? 'Yes' : 'No') : 'N/A') . PHP_EOL;
        echo "     - Content JSON: " . (isset($first_submission['content_json']) ? 'Present' : 'Missing') . PHP_EOL;
        
        if (isset($first_submission['content_json'])) {
            $content = json_decode($first_submission['content_json'], true);
            if ($content) {
                echo "     - Content keys: " . implode(', ', array_keys($content)) . PHP_EOL;
            }
        }
    }
} else {
    echo "   ✗ Function failed or returned no data" . PHP_EOL;
}

echo PHP_EOL;

// Test get_field_edit_history function
echo "2. Testing get_field_edit_history()..." . PHP_EOL;
if ($history && isset($history['submissions']) && count($history['submissions']) > 1) {
    $field_history = get_field_edit_history($history['submissions'], 'rating');
    
    if ($field_history && !empty($field_history)) {
        echo "   ✓ Field history for 'rating' returned " . count($field_history) . " changes" . PHP_EOL;
        foreach ($field_history as $change) {
            echo "     - " . ($change['period_display'] ?? 'Unknown') . ": " . ($change['value'] ?? 'N/A') . " (Draft: " . ($change['is_draft'] ? 'Yes' : 'No') . ")" . PHP_EOL;
        }
    } else {
        echo "   ✗ No field history found for 'rating'" . PHP_EOL;
    }
} else {
    echo "   ⚠ Not enough submissions to test field history" . PHP_EOL;
}

echo PHP_EOL;

// Test JSON structure
echo "3. Testing JSON content structure..." . PHP_EOL;
if ($history && isset($history['submissions']) && !empty($history['submissions'])) {
    foreach ($history['submissions'] as $i => $submission) {
        echo "   Submission " . ($i + 1) . ":" . PHP_EOL;
        if (isset($submission['content_json'])) {
            $content = json_decode($submission['content_json'], true);
            if ($content) {
                echo "     - Valid JSON with keys: " . implode(', ', array_keys($content)) . PHP_EOL;
                
                // Check for specific fields that should trigger history display
                $important_fields = ['rating', 'targets', 'remarks'];
                foreach ($important_fields as $field) {
                    if (isset($content[$field])) {
                        echo "     - $field: " . (is_array($content[$field]) ? 'Array with ' . count($content[$field]) . ' items' : $content[$field]) . PHP_EOL;
                    }
                }
            } else {
                echo "     - Invalid JSON content" . PHP_EOL;
            }
        } else {
            echo "     - No content_json field" . PHP_EOL;
        }
    }
} else {
    echo "   ✗ No submissions found to analyze" . PHP_EOL;
}

echo PHP_EOL . "Test completed." . PHP_EOL;
?>
