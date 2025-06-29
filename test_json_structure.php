<?php
/**
 * Test script to examine JSON data structure
 */

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/admins/outcomes.php';

$metric_id = 7;
$outcome_details = get_outcome_data_for_display($metric_id);

echo "Raw data_json:\n";
echo $outcome_details['data_json'] . "\n\n";

echo "Parsed data structure:\n";
$parsed_data = $outcome_details['parsed_data'];
print_r($parsed_data);

echo "\nLooking for columns in different locations:\n";
echo "parsed_data['columns']: " . (isset($parsed_data['columns']) ? print_r($parsed_data['columns'], true) : 'not found') . "\n";
echo "parsed_data['column_names']: " . (isset($parsed_data['column_names']) ? print_r($parsed_data['column_names'], true) : 'not found') . "\n";
echo "parsed_data['metrics']: " . (isset($parsed_data['metrics']) ? print_r($parsed_data['metrics'], true) : 'not found') . "\n";

// Check if data is structured differently
if (isset($parsed_data['January'])) {
    echo "\nFound month-based data structure\n";
    echo "January data: " . print_r($parsed_data['January'], true) . "\n";
}
?>
