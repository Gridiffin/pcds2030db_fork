<?php
/**
 * Fix Data Types: Convert Empty Strings to Proper Numeric Values
 * 
 * This script fixes the Timber Export Value data to use proper numeric types
 * instead of empty strings for missing values.
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/lib/db_connect.php';

echo "=== Data Type Fix for Timber Export Value ===\n";

// Get the current data
$query = "SELECT * FROM sector_outcomes_data WHERE metric_id = 7 AND sector_id = 1 LIMIT 1";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    echo "❌ No data found!\n";
    exit;
}

$row = $result->fetch_assoc();
$data = json_decode($row['data_json'], true);

echo "📊 Current Data Analysis:\n";
echo "- Columns: " . count($data['columns']) . "\n";
echo "- Rows: " . count($data['data']) . "\n";

// Count empty strings vs proper values
$empty_strings = 0;
$total_cells = 0;

foreach ($data['data'] as $row_label => $row_data) {
    foreach ($row_data as $col => $value) {
        $total_cells++;
        if ($value === '' || $value === null) {
            $empty_strings++;
        }
    }
}

echo "- Total cells: $total_cells\n";
echo "- Empty string cells: $empty_strings\n";
echo "- Valid numeric cells: " . ($total_cells - $empty_strings) . "\n";

// Fix the data by converting empty strings to 0
$fixed_data = $data;
$fixes_applied = 0;

foreach ($fixed_data['data'] as $row_label => &$row_data) {
    foreach ($row_data as $col => &$value) {
        if ($value === '' || $value === null || !is_numeric($value)) {
            $value = 0;
            $fixes_applied++;
        } else {
            // Ensure numeric values are stored as numbers, not strings
            $value = (float)$value;
        }
    }
}

echo "\n🔧 Data Fixes:\n";
echo "- Fixes applied: $fixes_applied\n";
echo "- Empty strings converted to: 0\n";
echo "- All values ensured to be numeric\n";

// Update the database
$fixed_json = json_encode($fixed_data);

$update_query = "UPDATE sector_outcomes_data SET data_json = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("si", $fixed_json, $row['id']);

if ($update_stmt->execute()) {
    echo "\n✅ Data types fixed successfully!\n";
    echo "💾 Updated data size: " . strlen($fixed_json) . " bytes\n";
    
    // Verify the fix
    $verify_query = "SELECT data_json FROM sector_outcomes_data WHERE id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("i", $row['id']);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $verify_row = $verify_result->fetch_assoc();
    $verify_data = json_decode($verify_row['data_json'], true);
    
    // Check that all values are now properly numeric
    $all_numeric = true;
    foreach ($verify_data['data'] as $row_data) {
        foreach ($row_data as $value) {
            if (!is_numeric($value)) {
                $all_numeric = false;
                break 2;
            }
        }
    }
    
    echo "✅ Verification: " . ($all_numeric ? "All values are now numeric" : "Some non-numeric values remain") . "\n";
    
} else {
    echo "❌ Failed to update data: " . $conn->error . "\n";
}

echo "\n=== Data Type Fix Complete ===\n";
?>
