<?php
/**
 * Debug initiatives functions
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/functions.php';
require_once 'app/lib/admins/index.php';
require_once 'app/lib/initiative_functions.php';

echo "<h1>Initiative Debug Test</h1>";

// Test 1: Database connection
echo "<h2>1. Database Connection</h2>";
if ($conn) {
    echo "✅ Database connected<br>";
    echo "Connected to: " . $conn->get_server_info() . "<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// Test 2: Direct database query
echo "<h2>2. Direct Database Query</h2>";
$direct_query = "SELECT * FROM initiatives ORDER BY initiative_name";
$result = $conn->query($direct_query);
if ($result) {
    $count = $result->num_rows;
    echo "✅ Direct query successful: {$count} initiatives found<br>";
    while ($row = $result->fetch_assoc()) {
        echo "- ID: {$row['initiative_id']}, Name: {$row['initiative_name']}<br>";
    }
} else {
    echo "❌ Direct query failed: " . $conn->error . "<br>";
}

// Test 3: get_all_initiatives function
echo "<h2>3. get_all_initiatives() Function</h2>";
try {
    $initiatives = get_all_initiatives();
    echo "✅ Function executed successfully<br>";
    echo "Returned: " . count($initiatives) . " initiatives<br>";
    foreach ($initiatives as $init) {
        echo "- ID: {$init['initiative_id']}, Name: {$init['initiative_name']}, Programs: {$init['program_count']}<br>";
    }
} catch (Exception $e) {
    echo "❌ Function failed: " . $e->getMessage() . "<br>";
}

// Test 4: Session and auth
echo "<h2>4. Session and Authentication</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "User Role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
echo "Is Admin: " . (is_admin() ? 'Yes' : 'No') . "<br>";

// Test 5: AJAX simulation
echo "<h2>5. AJAX Request Simulation</h2>";
$_GET['ajax_table'] = '1';
$_GET['search'] = '';
$_GET['is_active'] = '';

$filters = [];
$initiatives_ajax = get_all_initiatives($filters);
echo "AJAX simulation result: " . count($initiatives_ajax) . " initiatives<br>";

// Test 6: Check for PHP errors
echo "<h2>6. Error Check</h2>";
$errors = error_get_last();
if ($errors) {
    echo "Last error: " . print_r($errors, true) . "<br>";
} else {
    echo "✅ No PHP errors detected<br>";
}
?>
