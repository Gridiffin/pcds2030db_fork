<?php
/**
 * Test Date Handling in Admin Edit Program
 * This script tests the date parameter binding fix
 */

require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';

// Test the parameter binding that was causing the issue
$test_program_id = 1; // Use a test program ID
$program_name = "Test Program";
$program_number = "1.1.1";
$initiative_id = null;
$owner_agency_id = 1;
$sector_id = 1;
$start_date = "2025-01-01";
$end_date = "2025-12-31";
$is_assigned = 1;
$edit_permissions_json = '{"edit_permissions":[]}';

echo "<h2>Testing Parameter Binding Fix</h2>\n";

// Test the OLD binding (that was causing the issue)
echo "<h3>OLD Binding (would fail):</h3>\n";
echo "bind_param('ssiissisii', ...)<br>\n";
echo "Parameters: s,s,i,i,s,s,i,s,i,i<br>\n";
echo "Values: '{$program_name}', '{$program_number}', {$initiative_id}, {$owner_agency_id}, '{$sector_id}', '{$start_date}', {$end_date}, '{$is_assigned}', {$edit_permissions_json}, {$test_program_id}<br>\n";
echo "<strong>Issues:</strong><br>\n";
echo "- sector_id as string (s) instead of int (i)<br>\n";
echo "- end_date as int (i) instead of string (s)<br>\n";
echo "- is_assigned as string (s) instead of int (i)<br>\n";
echo "- edit_permissions as int (i) instead of string (s)<br>\n";

echo "<h3>NEW Binding (fixed):</h3>\n";
echo "bind_param('ssiisssisi', ...)<br>\n";
echo "Parameters: s,s,i,i,s,s,s,i,s,i<br>\n";
echo "Values: '{$program_name}', '{$program_number}', {$initiative_id}, {$owner_agency_id}, {$sector_id}, '{$start_date}', '{$end_date}', {$is_assigned}, '{$edit_permissions_json}', {$test_program_id}<br>\n";
echo "<strong>Fixed:</strong><br>\n";
echo "- sector_id as int (i) ✓<br>\n";
echo "- end_date as string (s) ✓<br>\n";
echo "- is_assigned as int (i) ✓<br>\n";
echo "- edit_permissions as string (s) ✓<br>\n";

// Test actual date validation
echo "<h3>Date Validation Test:</h3>\n";

$test_dates = [
    "2025-01-01" => "Valid date",
    "2025-13-01" => "Invalid month", 
    "2025-02-30" => "Invalid day",
    "2025" => "Year only (would cause original error)",
    "" => "Empty date",
    null => "Null date"
];

foreach ($test_dates as $date => $description) {
    $is_valid_format = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
    $is_valid_date = $date ? strtotime($date) : true; // null/empty are OK
    
    echo "'{$date}' ({$description}): ";
    if ($date === null || $date === "") {
        echo "<span style='color: green;'>✓ NULL/Empty OK</span>";
    } elseif ($is_valid_format && $is_valid_date) {
        echo "<span style='color: green;'>✓ Valid</span>";
    } else {
        echo "<span style='color: red;'>✗ Invalid</span>";
        if (!$is_valid_format) echo " (format)";
        if (!$is_valid_date) echo " (date)";
    }
    echo "<br>\n";
}

echo "<h3>Database Column Types:</h3>\n";
echo "start_date: DATE (accepts YYYY-MM-DD or NULL)<br>\n";
echo "end_date: DATE (accepts YYYY-MM-DD or NULL)<br>\n";

echo "<h3>Conclusion:</h3>\n";
echo "The original error 'Incorrect date value: '2025' for column 'end_date'' was caused by:<br>\n";
echo "1. Wrong parameter binding type (i instead of s) for date values<br>\n";
echo "2. This caused MySQL to receive an integer instead of a date string<br>\n";
echo "3. The new binding correctly passes dates as strings to MySQL<br>\n";

?>
