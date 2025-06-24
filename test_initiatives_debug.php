<?php
// Test initiatives functionality step by step
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/initiative_functions.php';

echo "=== TESTING INITIATIVES FUNCTIONALITY ===\n\n";

// 1. Test database connection
echo "1. Testing database connection...\n";
$conn = get_db_connection();
if ($conn) {
    echo "   ✓ Database connected successfully\n\n";
} else {
    echo "   ✗ Database connection failed\n";
    exit;
}

// 2. Test direct SQL query
echo "2. Testing direct SQL query...\n";
$sql = "SELECT * FROM initiatives ORDER BY initiative_name ASC";
$result = $conn->query($sql);
if ($result) {
    echo "   ✓ Direct SQL query successful\n";
    echo "   ✓ Found " . $result->num_rows . " initiatives\n\n";
    
    if ($result->num_rows > 0) {
        echo "   Initiative names:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   - " . $row['initiative_name'] . " (ID: " . $row['initiative_id'] . ", Active: " . $row['is_active'] . ")\n";
        }
        echo "\n";
    }
} else {
    echo "   ✗ Direct SQL query failed: " . $conn->error . "\n\n";
}

// 3. Test get_all_initiatives function
echo "3. Testing get_all_initiatives() function...\n";
$initiatives = get_all_initiatives();
echo "   ✓ Function returned " . count($initiatives) . " initiatives\n";

if (!empty($initiatives)) {
    echo "   ✓ Sample initiative data:\n";
    $first = $initiatives[0];
    foreach ($first as $key => $value) {
        echo "     $key: " . var_export($value, true) . "\n";
    }
    echo "\n";
} else {
    echo "   ✗ No initiatives returned from function\n\n";
}

// 4. Test with empty filters
echo "4. Testing with empty filters...\n";
$filtered = get_all_initiatives([]);
echo "   ✓ Empty filters returned " . count($filtered) . " initiatives\n\n";

// 5. Test with different filter values
echo "5. Testing various filter scenarios...\n";

$test_filters = [
    ['is_active' => 1],
    ['is_active' => 0],
    ['search' => ''],
    ['search' => 'test'],
];

foreach ($test_filters as $i => $filter) {
    $result = get_all_initiatives($filter);
    echo "   Filter " . ($i + 1) . " (" . json_encode($filter) . "): " . count($result) . " initiatives\n";
}

echo "\n=== TESTING COMPLETE ===\n";
?>
