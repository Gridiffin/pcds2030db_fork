<?php
/**
 * Migration Script: Convert Timber Export Value to Flexible Format
 * 
 * This script converts the old monthly array format to the new flexible format
 * for the Timber Export Value outcome (metric_id = 7)
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/lib/db_connect.php';

echo "=== Timber Export Value Data Migration ===\n";

// Get the current data for Timber Export Value
$query = "SELECT * FROM sector_outcomes_data WHERE metric_id = 7 AND sector_id = 1 LIMIT 1";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    echo "❌ No Timber Export Value data found!\n";
    exit;
}

$row = $result->fetch_assoc();
$current_data = json_decode($row['data_json'], true);
$current_row_config = json_decode($row['row_config'], true);
$current_column_config = json_decode($row['column_config'], true);

echo "📊 Current Data Structure:\n";
echo "- Table Name: " . $row['table_name'] . "\n";
echo "- Structure Type: " . $row['table_structure_type'] . "\n";
echo "- Is Draft: " . ($row['is_draft'] ? 'Yes' : 'No') . "\n";
echo "- Data Format: " . (is_array($current_data) ? 'Old Monthly Format' : 'Unknown') . "\n";

// Extract column labels from column_config
$columns = [];
if ($current_column_config && isset($current_column_config['columns'])) {
    foreach ($current_column_config['columns'] as $col) {
        $columns[] = $col['label'];
    }
}

echo "- Columns: " . implode(', ', $columns) . "\n";

// Convert old format to new format
$new_data = [
    'columns' => $columns,
    'data' => []
];

// Convert each month's data
foreach ($current_data as $month => $values) {
    $new_data['data'][$month] = [];
    
    // Map each value to its corresponding column
    for ($i = 0; $i < count($columns) && $i < count($values); $i++) {
        $column_name = $columns[$i];
        $value = $values[$i];
        
        // Convert null values to empty strings for consistency
        $new_data['data'][$month][$column_name] = ($value === null) ? '' : $value;
    }
}

echo "\n🔄 Converted Data Structure:\n";
echo "- Format: New Flexible Format\n";
echo "- Columns: " . count($new_data['columns']) . "\n";
echo "- Rows: " . count($new_data['data']) . "\n";

// Display sample of converted data
echo "\n📋 Sample Converted Data:\n";
$sample_months = array_slice(array_keys($new_data['data']), 0, 3);
foreach ($sample_months as $month) {
    echo "- $month: ";
    $month_data = [];
    foreach ($new_data['data'][$month] as $col => $val) {
        $month_data[] = "$col=" . (is_numeric($val) ? number_format($val, 2) : $val);
    }
    echo implode(', ', $month_data) . "\n";
}

// Prepare update query
$new_data_json = json_encode($new_data);

echo "\n🚀 Ready to migrate data...\n";
echo "❓ Do you want to proceed with the migration? (y/N): ";

// For this script, we'll automatically proceed with the migration
// In a real scenario, you might want user confirmation

$confirm = 'y'; // Auto-confirm for this migration

if (strtolower($confirm) === 'y') {
    // Create backup first
    $backup_query = "CREATE TABLE IF NOT EXISTS sector_outcomes_data_backup_" . date('Y_m_d_H_i_s') . " AS SELECT * FROM sector_outcomes_data WHERE id = ?";
    $backup_stmt = $conn->prepare($backup_query);
    $backup_stmt->bind_param("i", $row['id']);
    
    if ($backup_stmt->execute()) {
        echo "✅ Backup created successfully\n";
        
        // Update the data
        $update_query = "UPDATE sector_outcomes_data SET data_json = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $new_data_json, $row['id']);
        
        if ($update_stmt->execute()) {
            echo "✅ Data migration completed successfully!\n";
            echo "📝 Updated record ID: " . $row['id'] . "\n";
            echo "💾 New data size: " . strlen($new_data_json) . " bytes\n";
            
            // Verify the migration
            $verify_query = "SELECT data_json FROM sector_outcomes_data WHERE id = ?";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param("i", $row['id']);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            $verify_row = $verify_result->fetch_assoc();
            $verify_data = json_decode($verify_row['data_json'], true);
            
            if (isset($verify_data['columns']) && isset($verify_data['data'])) {
                echo "✅ Migration verification successful!\n";
                echo "- Columns: " . count($verify_data['columns']) . "\n";
                echo "- Data rows: " . count($verify_data['data']) . "\n";
            } else {
                echo "❌ Migration verification failed!\n";
            }
            
        } else {
            echo "❌ Migration failed: " . $conn->error . "\n";
        }
    } else {
        echo "❌ Backup creation failed: " . $conn->error . "\n";
    }
} else {
    echo "❌ Migration cancelled by user\n";
}

echo "\n=== Migration Complete ===\n";
?>
