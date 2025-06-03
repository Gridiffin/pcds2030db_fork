<?php
// Test script for the fixed functions
require_once 'app/config/config.php';
require_once 'app/lib/agencies/programs.php';

echo "<h2>Testing get_program_edit_history() function</h2>\n";

// Test with a program that has submissions
$program_id = 155;
echo "<h3>Testing with program_id: $program_id</h3>\n";

try {
    $history = get_program_edit_history($program_id);
    echo "<pre>";
    var_dump($history);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Testing get_field_edit_history() function</h3>\n";

// Test the field history function with some sample data
$submissions = [
    [
        'submission_date' => '2025-06-01 16:53:57',
        'target' => 'Sample target 1',
        'brief_description' => 'First description'
    ],
    [
        'submission_date' => '2025-06-02 10:23:24',  
        'target' => 'Sample target 2',
        'brief_description' => 'Updated description'
    ]
];

try {
    $field_history = get_field_edit_history($submissions, 'target');
    echo "<h4>History for 'target' field:</h4>";
    echo "<pre>";
    var_dump($field_history);
    echo "</pre>";
    
    $desc_history = get_field_edit_history($submissions, 'brief_description');
    echo "<h4>History for 'brief_description' field:</h4>";
    echo "<pre>";
    var_dump($desc_history);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<p style='color: green;'>Test completed successfully!</p>";
?>
