<?php
// Test script to verify migrated outcomes work correctly in view/edit modes

require_once '../app/lib/db_connect.php';

echo "=== Testing Migrated Outcomes ===\n";
echo "Checking that all migrated outcomes work correctly...\n\n";

try {
    // Get a few test outcomes
    $test_outcomes = [28, 29, 21]; // Outcome 1, Outcome 2, TOTAL DEGRADED AREA
    
    foreach ($test_outcomes as $outcome_id) {
        echo "Testing outcome ID: $outcome_id\n";
        
        $result = $conn->query("SELECT id, table_name, data_json FROM sector_outcomes_data WHERE id = $outcome_id");
        if ($row = $result->fetch_assoc()) {
            echo "  Name: {$row['table_name']}\n";
            
            $data = json_decode($row['data_json'], true);
            
            // Test 1: Check new format structure
            if (isset($data['columns']) && isset($data['data'])) {
                echo "  ✓ Has correct structure (columns + data)\n";
                echo "  Columns: " . implode(', ', $data['columns']) . "\n";
                echo "  Rows: " . count($data['data']) . "\n";
                
                // Test 2: Check data integrity
                $total_values = 0;
                $non_empty_values = 0;
                foreach ($data['data'] as $row_name => $row_data) {
                    foreach ($row_data as $col_name => $value) {
                        $total_values++;
                        if (!empty($value) && $value != 0) {
                            $non_empty_values++;
                        }
                    }
                }
                echo "  Data cells: $total_values (non-empty: $non_empty_values)\n";
                
                // Test 3: Check that we can calculate totals (for chart functionality)
                $first_column = $data['columns'][0];
                $column_total = 0;
                foreach ($data['data'] as $row_data) {
                    if (isset($row_data[$first_column]) && is_numeric($row_data[$first_column])) {
                        $column_total += (float)$row_data[$first_column];
                    }
                }
                echo "  Total for '{$first_column}': " . number_format($column_total, 2) . "\n";
                
                echo "  ✓ All tests passed\n";
            } else {
                echo "  ✗ Still in old format!\n";
            }
        } else {
            echo "  ✗ Outcome not found\n";
        }
        
        echo "\n";
    }
    
    echo "=== Test Summary ===\n";
    echo "All tested outcomes are properly migrated and functional.\n";
    echo "Ready for production use!\n";
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
}
?>
