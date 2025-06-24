<?php
/**
 * Simple test to check initiatives data directly
 */

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';

echo "<h1>Simple Initiative Test</h1>";

// Test database connection
if ($conn) {
    echo "<p style='color: green;'>✓ Database connected</p>";
    
    // Direct query test
    $sql = "SELECT * FROM initiatives WHERE is_active = 1";
    $result = $conn->query($sql);
    
    if ($result) {
        $initiatives = [];
        while ($row = $result->fetch_assoc()) {
            $initiatives[] = $row;
        }
        
        echo "<h2>Active Initiatives:</h2>";
        echo "<p>Found " . count($initiatives) . " active initiatives</p>";
        
        if (!empty($initiatives)) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Name</th><th>Number</th><th>Description</th><th>Active</th></tr>";
            foreach ($initiatives as $init) {
                echo "<tr>";
                echo "<td>" . $init['initiative_id'] . "</td>";
                echo "<td>" . htmlspecialchars($init['initiative_name']) . "</td>";
                echo "<td>" . htmlspecialchars($init['initiative_number'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars(substr($init['initiative_description'] ?? '', 0, 50)) . "...</td>";
                echo "<td>" . ($init['is_active'] ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>Query failed: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}
?>
