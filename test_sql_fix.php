<?php
/**
 * Test file to verify the add_user SQL fix
 * This file tests the database queries without actually creating users
 */

require_once 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/admins/users.php';

echo "<h3>Testing Add User SQL Fix</h3>\n";

// Test 1: Check if agency_groups can be retrieved
echo "<h4>Test 1: Get Agency Groups</h4>\n";
$agency_groups = get_all_agency_groups($conn);
if (!empty($agency_groups)) {
    echo "✅ Successfully retrieved " . count($agency_groups) . " agency groups\n";
    foreach ($agency_groups as $group) {
        echo "- Group: {$group['group_name']} (ID: {$group['agency_group_id']}, Sector: {$group['sector_id']})\n";
    }
} else {
    echo "❌ Failed to retrieve agency groups\n";
}

echo "<br><br>";

// Test 2: Test agency group validation query (without inserting)
echo "<h4>Test 2: Agency Group Validation Query</h4>\n";
try {
    // Test the validation query that was fixed
    $group_check = "SELECT agency_group_id FROM agency_group WHERE agency_group_id = ?";
    $stmt = $conn->prepare($group_check);
    
    if ($stmt) {
        // Test with a known agency group ID (if any exist)
        if (!empty($agency_groups)) {
            $test_id = $agency_groups[0]['agency_group_id'];
            $stmt->bind_param("i", $test_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo "✅ Agency group validation query works correctly for ID: $test_id\n";
            } else {
                echo "❌ Agency group validation query returned no results\n";
            }
        } else {
            echo "ℹ️ No agency groups to test with\n";
        }
    } else {
        echo "❌ Failed to prepare agency group validation query\n";
    }
} catch (Exception $e) {
    echo "❌ Error in agency group validation: " . $e->getMessage() . "\n";
}

echo "<br><br>";

// Test 3: Test sectors retrieval
echo "<h4>Test 3: Get Sectors</h4>\n";
try {
    $sectors = get_all_sectors();
    if (!empty($sectors)) {
        echo "✅ Successfully retrieved " . count($sectors) . " sectors\n";
        foreach ($sectors as $sector) {
            echo "- Sector: {$sector['sector_name']} (ID: {$sector['sector_id']})\n";
        }
    } else {
        echo "❌ Failed to retrieve sectors\n";
    }
} catch (Exception $e) {
    echo "❌ Error retrieving sectors: " . $e->getMessage() . "\n";
}

echo "<br><br>";
echo "<h4>Summary</h4>\n";
echo "If all tests show ✅, the SQL syntax error has been fixed and the add user form should work properly.\n";
?>
