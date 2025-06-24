<?php
// Test what initiatives are being returned
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/initiative_functions.php';

// Test database connection
echo "Testing database connection...\n";
$conn = get_db_connection();
if ($conn) {
    echo "✓ Database connected successfully\n";
} else {
    echo "✗ Database connection failed\n";
    exit;
}

// Test get_all_initiatives function
echo "\nTesting get_all_initiatives() function...\n";
$initiatives = get_all_initiatives();

echo "Number of initiatives returned: " . count($initiatives) . "\n";

if (!empty($initiatives)) {
    echo "\nFirst initiative details:\n";
    foreach ($initiatives[0] as $key => $value) {
        echo "  $key: " . var_export($value, true) . "\n";
    }
    
    echo "\nAll initiative names:\n";
    foreach ($initiatives as $initiative) {
        echo "  - " . $initiative['initiative_name'] . " (ID: " . $initiative['initiative_id'] . ", Active: " . $initiative['is_active'] . ")\n";
    }
} else {
    echo "No initiatives found\n";
}

// Test with filters
echo "\nTesting with empty filters...\n";
$filtered_initiatives = get_all_initiatives([]);
echo "Count with empty filters: " . count($filtered_initiatives) . "\n";

echo "\nTesting with null is_active filter...\n";
$filtered_initiatives = get_all_initiatives(['is_active' => null]);
echo "Count with null is_active: " . count($filtered_initiatives) . "\n";

echo "\nDone.\n";
?>
