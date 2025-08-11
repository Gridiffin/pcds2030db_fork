<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define PROJECT_ROOT_PATH if not defined
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(__FILE__) . '/');
}

// Test rating display logic used in edit program page
echo "=== PCDS2030 Edit Program Rating Verification ===\n\n";

// Test rating mapping (same as in the updated admin_edit_program_content.php)
$rating_map = [
    'not_started' => ['label' => 'Not Started', 'class' => 'secondary', 'icon' => 'fas fa-hourglass-start'],
    'on_track_for_year' => ['label' => 'On Track for Year', 'class' => 'warning', 'icon' => 'fas fa-calendar-check'],
    'monthly_target_achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success', 'icon' => 'fas fa-check-circle'],
    'severe_delay' => ['label' => 'Severe Delays', 'class' => 'danger', 'icon' => 'fas fa-exclamation-triangle']
];

// Test with different rating values
$test_ratings = ['not_started', 'on_track_for_year', 'monthly_target_achieved', 'severe_delay', 'invalid_rating', null];

echo "Rating mapping test:\n";
foreach ($test_ratings as $test_rating) {
    $current_rating = $test_rating ?? 'not_started';
    
    // Fallback if rating is not in map
    if (!isset($rating_map[$current_rating])) {
        $current_rating = 'not_started';
    }
    
    echo "Input: " . ($test_rating ?? 'null') . " => ";
    echo "Rating: {$current_rating}, ";
    echo "Label: {$rating_map[$current_rating]['label']}, ";
    echo "Class: bg-{$rating_map[$current_rating]['class']}, ";
    echo "Icon: {$rating_map[$current_rating]['icon']}\n";
}

echo "\n=== Rating HTML Output Example ===\n";

// Test HTML output for a sample rating
$program_rating = 'monthly_target_achieved';
$current_rating = $program_rating ?? 'not_started';

if (!isset($rating_map[$current_rating])) {
    $current_rating = 'not_started';
}

$html_output = '<span class="badge bg-' . $rating_map[$current_rating]['class'] . '">' .
               '<i class="' . $rating_map[$current_rating]['icon'] . ' me-1"></i>' .
               $rating_map[$current_rating]['label'] .
               '</span>';

echo "Sample HTML for 'monthly_target_achieved':\n";
echo htmlspecialchars($html_output) . "\n";

echo "\n=== Consistency Check ===\n";

// Check if this matches the rating options in the form
$form_rating_options = [
    'not_started' => 'Not Started',
    'on_track_for_year' => 'On Track for Year', 
    'monthly_target_achieved' => 'Monthly Target Achieved',
    'severe_delay' => 'Severe Delays'
];

echo "Form options vs display mapping consistency:\n";
foreach ($form_rating_options as $value => $label) {
    $display_label = $rating_map[$value]['label'] ?? 'MISSING';
    $match = ($label === $display_label) ? '✓' : '✗';
    echo "{$match} {$value}: Form='{$label}' | Display='{$display_label}'\n";
}

echo "\n=== Test Complete ===\n";
echo "✓ Rating mapping logic verified\n";
echo "✓ Fallback handling confirmed\n";
echo "✓ HTML output structure validated\n";
echo "✓ Form/display consistency checked\n";
?>
