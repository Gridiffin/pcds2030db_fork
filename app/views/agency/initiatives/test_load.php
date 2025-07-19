<?php
/**
 * Simple test to verify initiatives.php loads without fatal errors
 */

// Define test variables
$_SESSION = [
    'role' => 'agency',
    'agency_id' => 1,
    'username' => 'test_user'
];

// Define superglobals for testing
$_SERVER = [
    'HTTP_HOST' => 'localhost',
    'SCRIPT_NAME' => '/pcds2030_dashboard_fork/app/views/agency/initiatives/initiatives.php',
    'HTTPS' => 'off'
];

// Define function stubs to prevent undefined function errors
if (!function_exists('is_agency')) {
    function is_agency() { return true; }
}
if (!function_exists('get_agency_initiatives')) {
    function get_agency_initiatives($agency_id, $search = '', $filter = '', $page = 1, $per_page = 10) {
        return [
            'data' => [],
            'total' => 0,
            'pages' => 1,
            'current_page' => 1
        ];
    }
}

// Test the file inclusion
echo "Testing initiatives.php load...\n";

try {
    // Capture output to prevent HTML rendering
    ob_start();
    
    // Include the file
    require_once __DIR__ . '/initiatives.php';
    
    // Clean the buffer
    ob_end_clean();
    
    echo "✅ SUCCESS: initiatives.php loaded without fatal errors!\n";
    
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
?>
