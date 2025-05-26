<?php
// Minimal test version of manage outcomes page
echo "<!DOCTYPE html>";
echo "<html><head><title>Debug Test</title></head><body>";
echo "<h1>DEBUG: Page is loading</h1>";

// Test 1: Basic PHP execution
echo "<p>✓ PHP is executing</p>";

// Test 2: Include config
try {
    require_once '../../../config/config.php';
    echo "<p>✓ Config loaded</p>";
} catch (Exception $e) {
    echo "<p>✗ Config error: " . $e->getMessage() . "</p>";
}

// Test 3: Database connection
try {
    require_once ROOT_PATH . 'app/lib/db_connect.php';
    if (isset($conn) && $conn->ping()) {
        echo "<p>✓ Database connected</p>";
    } else {
        echo "<p>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p>✗ Database error: " . $e->getMessage() . "</p>";
}

// Test 4: Session
try {
    require_once ROOT_PATH . 'app/lib/session.php';
    echo "<p>✓ Session loaded</p>";
} catch (Exception $e) {
    echo "<p>✗ Session error: " . $e->getMessage() . "</p>";
}

// Test 5: Functions
try {
    require_once ROOT_PATH . 'app/lib/functions.php';
    echo "<p>✓ Functions loaded</p>";
} catch (Exception $e) {
    echo "<p>✗ Functions error: " . $e->getMessage() . "</p>";
}

// Test 6: Admin functions
try {
    require_once ROOT_PATH . 'app/lib/admins/index.php';
    echo "<p>✓ Admin functions loaded</p>";
} catch (Exception $e) {
    echo "<p>✗ Admin functions error: " . $e->getMessage() . "</p>";
}

// Test 7: Authentication
try {
    if (function_exists('is_admin')) {
        $is_admin = is_admin();
        echo "<p>" . ($is_admin ? "✓" : "✗") . " Admin check: " . ($is_admin ? "TRUE" : "FALSE") . "</p>";
    } else {
        echo "<p>✗ is_admin function not found</p>";
    }
} catch (Exception $e) {
    echo "<p>✗ Auth error: " . $e->getMessage() . "</p>";
}

// Test 8: Outcomes functions
try {
    if (function_exists('get_all_outcomes_data')) {
        $outcomes = get_all_outcomes_data();
        echo "<p>✓ Outcomes function works - returned: " . gettype($outcomes) . "</p>";
        if (is_array($outcomes)) {
            echo "<p>✓ Outcomes count: " . count($outcomes) . "</p>";
        }
    } else {
        echo "<p>✗ get_all_outcomes_data function not found</p>";
    }
} catch (Exception $e) {
    echo "<p>✗ Outcomes error: " . $e->getMessage() . "</p>";
}

echo "<h2>Next: Testing Layout Includes</h2>";

// Test 9: Header layout
try {
    echo "<p>Attempting to include header...</p>";
    ob_start();
    require_once '../../layouts/header.php';
    $header_content = ob_get_contents();
    ob_end_clean();
    echo "<p>✓ Header included successfully</p>";
    echo "<p>Header content length: " . strlen($header_content) . " characters</p>";
} catch (Exception $e) {
    echo "<p>✗ Header error: " . $e->getMessage() . "</p>";
}

echo "<p><strong>If you see this, basic PHP execution is working!</strong></p>";
echo "</body></html>";
?>
