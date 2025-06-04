<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/agencies/programs.php';

// Test specific program that we know has data
$program_id = 168;

echo "<h3>Program History Debug for Program ID: $program_id</h3>";

$program_history = get_program_edit_history($program_id);

echo "<h4>Raw Program History Data:</h4>";
echo "<pre>";
print_r($program_history);
echo "</pre>";

echo "<h4>History Display Condition Check:</h4>";
echo "<p>isset(\$program_history['submissions']): " . (isset($program_history['submissions']) ? 'TRUE' : 'FALSE') . "</p>";

if (isset($program_history['submissions'])) {
    echo "<p>count(\$program_history['submissions']): " . count($program_history['submissions']) . "</p>";
    echo "<p>count > 1: " . (count($program_history['submissions']) > 1 ? 'TRUE' : 'FALSE') . "</p>";
    
    echo "<h4>Submissions Details:</h4>";
    foreach ($program_history['submissions'] as $i => $submission) {
        echo "<p><strong>Submission " . ($i + 1) . ":</strong></p>";
        echo "<ul>";
        echo "<li>ID: " . ($submission['submission_id'] ?? 'N/A') . "</li>";
        echo "<li>Period: " . ($submission['period_display'] ?? 'N/A') . "</li>";
        echo "<li>Is Draft: " . (isset($submission['is_draft']) ? ($submission['is_draft'] ? 'Yes' : 'No') : 'N/A') . "</li>";
        echo "<li>Has Content: " . (isset($submission['content_json']) ? 'Yes' : 'No') . "</li>";
        echo "</ul>";
    }
} else {
    echo "<p>❌ No submissions found in program_history</p>";
}

// Test field history specifically
if (isset($program_history['submissions']) && count($program_history['submissions']) > 1) {
    echo "<h4>Field History Test (program_name):</h4>";
    $name_history = get_field_edit_history($program_history['submissions'], 'program_name');
    echo "<p>Name history count: " . (is_array($name_history) ? count($name_history) : 'Not an array') . "</p>";
    if (!empty($name_history)) {
        echo "<p>✅ Field history should be displayed</p>";
        echo "<pre>";
        print_r($name_history);
        echo "</pre>";
    } else {
        echo "<p>❌ Field history is empty</p>";
    }
}
?>
