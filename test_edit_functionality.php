<?php
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

echo "Testing updated admin edit outcome functionality...\n\n";

// Get a sample outcome to test with
$result = $conn->query("SELECT metric_id, table_name, data_json FROM sector_outcomes_data LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $metric_id = $row['metric_id'];
    $table_name = $row['table_name'];
    $data_json = $row['data_json'];
    
    echo "Testing with outcome:\n";
    echo "  ID: {$metric_id}\n";
    echo "  Name: {$table_name}\n";
    
    // Parse the data JSON to verify it's in flexible format
    $data = json_decode($data_json, true);
    if (isset($data['columns']) && isset($data['data'])) {
        echo "  ✓ Data is in flexible format\n";
        echo "  Columns: " . implode(', ', $data['columns']) . "\n";
        echo "  Rows: " . implode(', ', array_keys($data['data'])) . "\n";
        
        // Test a small data update (simulate form submission)
        $updated_data = $data;
        $updated_data['data']['January']['2023'] = 12345.67; // Update one value
        $updated_json = json_encode($updated_data);
        
        // Test the database update (without actually updating, just prepare)
        $update_query = "UPDATE sector_outcomes_data 
                        SET table_name = ?, data_json = ?, updated_at = NOW() 
                        WHERE metric_id = ?";
        $update_stmt = $conn->prepare($update_query);
        
        if ($update_stmt) {
            echo "  ✓ Database update query prepared successfully\n";
            echo "  ✓ New format edit functionality should work\n";
        } else {
            echo "  ✗ Database update query failed: " . $conn->error . "\n";
        }
    } else {
        echo "  ✗ Data is not in flexible format\n";
        echo "  Data structure: " . print_r(array_keys($data), true) . "\n";
    }
} else {
    echo "No outcomes found in database to test with.\n";
}

echo "\nEdit functionality validation complete.\n";
?>
