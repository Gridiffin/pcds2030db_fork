<?php
/**
 * Simple test to verify agency group data
 */

try {
    // Include necessary files
    require_once 'app/config/config.php';
    require_once 'app/lib/db_connect.php';
    require_once 'app/lib/admins/users.php';

    echo "<h2>Testing Agency Group Fix</h2>";
    
    // Test database connection
    if ($conn) {
        echo "<p style='color: green'>✓ Database connection successful</p>";
    } else {
        echo "<p style='color: red'>✗ Database connection failed</p>";
        exit;
    }
    
    // Test get_all_agency_groups function
    echo "<h3>Agency Groups Data:</h3>";
    $agency_groups = get_all_agency_groups($conn);
    
    if ($agency_groups) {
        echo "<pre>";
        print_r($agency_groups);
        echo "</pre>";
    } else {
        echo "<p style='color: red'>No agency groups found</p>";
    }
    
    // Test sectors data
    echo "<h3>Sectors Data:</h3>";
    $sectors_query = "SELECT sector_id, sector_name FROM sectors ORDER BY sector_name";
    $sectors_result = $conn->query($sectors_query);
    
    if ($sectors_result) {
        echo "<pre>";
        while ($row = $sectors_result->fetch_assoc()) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "<p style='color: red'>No sectors found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
}
?>
