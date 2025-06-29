<?php
/**
 * Test script to simulate admin view_outcome.php execution
 */

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing admin view_outcome.php simulation...\n";

// Simulate the includes and setup from view_outcome.php
require_once 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/admins/users.php';
require_once ROOT_PATH . 'app/lib/status_helpers.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';

// Simulate getting a metric_id (use the first one from our test)
$metric_id = 7;

echo "Testing with metric_id: $metric_id\n";

// Test the same logic as view_outcome.php
$outcome_details = get_outcome_data_for_display($metric_id);

if (!$outcome_details) {
    echo "ERROR: get_outcome_data_for_display returned null\n";
    exit;
}

echo "SUCCESS: Outcome details retrieved\n";

$table_name = $outcome_details['table_name'];
$sector_id = $outcome_details['sector_id'];
$sector_name = $outcome_details['sector_name'] ?? 'Unknown Sector';
$period_id = $outcome_details['period_id'];
$year = $outcome_details['year'] ?? 'N/A';
$quarter = $outcome_details['quarter'] ?? 'N/A';
$reporting_period_name = "Q{$quarter} {$year}";
$status = $outcome_details['status'] ?? 'submitted';
$overall_rating = $outcome_details['overall_rating'] ?? null;

echo "Table name: $table_name\n";
echo "Sector: $sector_name\n";
echo "Period: $reporting_period_name\n";
echo "Status: $status\n";
echo "Overall rating: " . ($overall_rating ?? 'null') . "\n";

// Test table structure type check
$table_structure_type = $outcome_details['table_structure_type'] ?? 'classic';
echo "Table structure type: $table_structure_type\n";

if ($table_structure_type === 'flexible') {
    echo "Would redirect to flexible view\n";
} else {
    echo "Using classic view\n";
}

// Test date parsing
try {
    $created_at = new DateTime($outcome_details['created_at']);
    $updated_at = new DateTime($outcome_details['updated_at']);
    echo "Created at: " . $created_at->format('F j, Y, g:i A') . "\n";
    echo "Updated at: " . $updated_at->format('F j, Y, g:i A') . "\n";
} catch (Exception $e) {
    echo "ERROR parsing dates: " . $e->getMessage() . "\n";
}

// Test JSON data parsing
$outcome_metrics_data = $outcome_details['parsed_data'] ?? [];
if (empty($outcome_metrics_data) && !empty($outcome_details['data_json'])) {
    $outcome_metrics_data = json_decode($outcome_details['data_json'], true) ?? [];
}

echo "Parsed data empty: " . (empty($outcome_metrics_data) ? 'Yes' : 'No') . "\n";

if (!empty($outcome_metrics_data)) {
    echo "Parsed data keys: " . implode(', ', array_keys($outcome_metrics_data)) . "\n";
    
    // Check for columns
    $metric_names = $outcome_metrics_data['columns'] ?? [];
    echo "Metric names count: " . count($metric_names) . "\n";
    if (!empty($metric_names)) {
        echo "Metric names: " . implode(', ', $metric_names) . "\n";
    }
    
    // Check for data
    $data = $outcome_metrics_data['data'] ?? [];
    echo "Data sections count: " . count($data) . "\n";
    if (!empty($data)) {
        echo "Data keys: " . implode(', ', array_keys($data)) . "\n";
    }
}

// Test status and rating functions
echo "Testing status display: " . get_status_display_name($status) . "\n";
if ($overall_rating !== null) {
    echo "Testing rating badge: " . get_rating_badge($overall_rating) . "\n";
}

echo "\nTest completed successfully!\n";
?>
