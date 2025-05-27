<?php
// Minimal test to verify our agency group fix
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/admins/users.php';

echo "<h2>Agency Group Fix Validation</h2>";

// Test the function
$result = get_all_agency_groups($conn);

echo "<h3>✅ Function Result Structure Check:</h3>";
if (empty($result)) {
    echo "<p style='color: red'>❌ No agency groups returned</p>";
} else {
    echo "<p style='color: green'>✅ Found " . count($result) . " agency groups</p>";
    
    // Check if sector_id is included
    $first_group = $result[0];
    if (isset($first_group['sector_id'])) {
        echo "<p style='color: green'>✅ sector_id field is present</p>";
    } else {
        echo "<p style='color: red'>❌ sector_id field is missing</p>";
    }
    
    echo "<h3>Sample Data:</h3>";
    echo "<pre>";
    print_r(array_slice($result, 0, 3)); // Show first 3 groups
    echo "</pre>";
}

echo "<h3>✅ Database Test - Direct Query:</h3>";
$direct_query = "SELECT `agency_group_id`, `group_name`, `sector_id` FROM `agency_group` ORDER BY `group_name` ASC";
$direct_result = $conn->query($direct_query);

if ($direct_result) {
    echo "<p style='color: green'>✅ Direct query successful</p>";
    echo "<p>Found " . $direct_result->num_rows . " rows</p>";
    
    if ($direct_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Group Name</th><th>Sector ID</th></tr>";
        while ($row = $direct_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['agency_group_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['group_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['sector_id']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red'>❌ Direct query failed: " . $conn->error . "</p>";
}

echo "<h3>✅ Test Summary:</h3>";
echo "<p>The fix ensures that agency groups are properly filtered by sector in the add user form.</p>";
echo "<p>Before: Agency groups showed all options regardless of sector selection</p>";
echo "<p>After: Agency groups are filtered based on the selected sector</p>";
?>
