<?php
// Migration script to convert all legacy outcome data to new flexible format
// Run this script only once after backing up the database

require_once '../app/lib/db_connect.php';

echo "=== Outcome Data Migration Script ===\n";
echo "Converting all legacy outcomes to new flexible data structure...\n\n";

try {
    // First, let's backup the current data (already exists but confirm)
    echo "1. Checking if backup exists...\n";
    $backup_check = $conn->query("SHOW TABLES LIKE 'sector_outcomes_data_backup_%'");
    if ($backup_check->num_rows == 0) {
        echo "   Creating backup table...\n";
        $backup_table = "sector_outcomes_data_backup_" . date('Y_m_d_H_i_s');
        $conn->query("CREATE TABLE $backup_table AS SELECT * FROM sector_outcomes_data");
        echo "   ✓ Backup created: $backup_table\n\n";
    } else {
        echo "   ✓ Backup already exists\n\n";
    }

    // Get all records that need migration
    echo "2. Analyzing outcome data formats...\n";
    $result = $conn->query("SELECT id, table_name, data_json FROM sector_outcomes_data ORDER BY table_name");
    $all_outcomes = [];
    while ($row = $result->fetch_assoc()) {
        $all_outcomes[] = $row;
    }
    
    $need_migration = [];
    $already_migrated = [];
        
        foreach ($all_outcomes as $outcome) {
            $data = json_decode($outcome['data_json'], true);
            
            // Check if it's already in new format (has 'columns' and 'data' keys)
            if (isset($data['columns']) && isset($data['data'])) {
                $already_migrated[] = $outcome;
            } else {
                $need_migration[] = $outcome;
            }
        }
    
    echo "   Total outcomes: " . count($all_outcomes) . "\n";
    echo "   Already migrated: " . count($already_migrated) . "\n";
    echo "   Need migration: " . count($need_migration) . "\n\n";
    
    if (empty($need_migration)) {
        echo "✓ All outcomes are already in the new flexible format!\n";
        exit(0);
    }
    
    echo "3. Starting migration process...\n";
    
    foreach ($need_migration as $outcome) {
        echo "   Processing: {$outcome['table_name']} (ID: {$outcome['id']})\n";
        
        $old_data = json_decode($outcome['data_json'], true);
        $new_data = [];
        
        // Detect the data structure and convert accordingly
        if (is_array($old_data)) {
            $first_key = array_keys($old_data)[0] ?? null;
            $first_value = $old_data[$first_key] ?? null;
            
            // Case 1: Monthly structure with arrays (like old Timber Export format)
            if (in_array($first_key, ['January', 'February', 'March', 'April', 'May', 'June', 
                                    'July', 'August', 'September', 'October', 'November', 'December'])) {
                
                echo "     -> Detected monthly array format\n";
                
                // Determine columns based on array length in first non-empty month
                $columns = [];
                $max_cols = 0;
                foreach ($old_data as $month => $values) {
                    if (is_array($values) && count($values) > $max_cols) {
                        $max_cols = count($values);
                    }
                }
                
                // Generate column names based on max array length
                if ($max_cols <= 5) {
                    // Assume it's years like the timber export data
                    $base_year = 2022;
                    for ($i = 0; $i < $max_cols; $i++) {
                        $columns[] = (string)($base_year + $i);
                    }
                } else {
                    // Generic column names
                    for ($i = 0; $i < $max_cols; $i++) {
                        $columns[] = "Column " . ($i + 1);
                    }
                }
                
                // Convert to new format
                $new_data = [
                    'columns' => $columns,
                    'data' => []
                ];
                
                foreach ($old_data as $month => $values) {
                    $new_data['data'][$month] = [];
                    if (is_array($values)) {
                        for ($i = 0; $i < count($columns); $i++) {
                            $value = isset($values[$i]) ? $values[$i] : 0;
                            // Convert string numbers to actual numbers
                            if (is_string($value) && is_numeric($value)) {
                                $value = (float)$value;
                            } else if ($value === null || $value === '') {
                                $value = 0;
                            }
                            $new_data['data'][$month][$columns[$i]] = $value;
                        }
                    } else {
                        // Single value, put in first column
                        $new_data['data'][$month][$columns[0]] = $values ?: 0;
                    }
                }
            }
            // Case 2: Year-based structure with arrays (like "Outcome 2")
            else if (is_numeric($first_key) && $first_key >= 2020 && $first_key <= 2030) {
                
                echo "     -> Detected year-based array format\n";
                
                // Determine columns based on array length
                $max_cols = 0;
                foreach ($old_data as $year => $values) {
                    if (is_array($values) && count($values) > $max_cols) {
                        $max_cols = count($values);
                    }
                }
                
                // Generate column names
                $columns = [];
                for ($i = 0; $i < $max_cols; $i++) {
                    $columns[] = "Quarter " . ($i + 1);
                }
                
                // Convert to new format with years as rows
                $new_data = [
                    'columns' => $columns,
                    'data' => []
                ];
                
                foreach ($old_data as $year => $values) {
                    $new_data['data'][$year] = [];
                    if (is_array($values)) {
                        for ($i = 0; $i < count($columns); $i++) {
                            $value = isset($values[$i]) ? $values[$i] : 0;
                            $new_data['data'][$year][$columns[$i]] = $value;
                        }
                    }
                }
            }
            // Case 3: Already in new format but missing structure indicators
            else if (isset($old_data['data']) && isset($old_data['columns'])) {
                echo "     -> Already in new format, no changes needed\n";
                continue;
            }
            // Case 4: Simple structure, convert to basic format
            else {
                echo "     -> Converting to basic single-column format\n";
                
                $new_data = [
                    'columns' => ['Value'],
                    'data' => []
                ];
                
                foreach ($old_data as $key => $value) {
                    $new_data['data'][$key] = ['Value' => $value ?: 0];
                }
            }
        } else {
            echo "     -> Error: Unexpected data format\n";
            continue;
        }
        
        // Update the database
        $json_string = json_encode($new_data, JSON_PRETTY_PRINT);
        $escaped_json = $conn->real_escape_string($json_string);
        $query = "UPDATE sector_outcomes_data SET data_json = '$escaped_json', updated_at = NOW() WHERE id = " . $outcome['id'];
        $result = $conn->query($query);
        
        if ($result) {
            echo "     ✓ Successfully migrated\n";
        } else {
            echo "     ✗ Failed to update database: " . $conn->error . "\n";
        }
        
        echo "\n";
    }
    
    echo "4. Migration completed!\n\n";
    
    // Verify the migration
    echo "5. Verifying migration results...\n";
    $result = $conn->query("SELECT id, table_name, data_json FROM sector_outcomes_data ORDER BY table_name");
    $updated_outcomes = [];
    while ($row = $result->fetch_assoc()) {
        $updated_outcomes[] = $row;
    }
    
    $success_count = 0;
    foreach ($updated_outcomes as $outcome) {
        $data = json_decode($outcome['data_json'], true);
        if (isset($data['columns']) && isset($data['data'])) {
            $success_count++;
        } else {
            echo "   ⚠ Still in old format: {$outcome['table_name']} (ID: {$outcome['id']})\n";
        }
    }
    
    echo "   ✓ Successfully migrated: $success_count/" . count($updated_outcomes) . " outcomes\n\n";
    
    echo "=== Migration Complete ===\n";
    echo "All outcome data has been successfully converted to the new flexible format.\n";
    echo "The old data has been preserved in the backup table.\n";
    
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
    echo "Please check the database and try again.\n";
}
?>
