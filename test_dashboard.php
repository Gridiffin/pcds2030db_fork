<?php
// Turn on error reporting to see what's breaking
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing dashboard loading...<br>";

try {
    echo "1. Loading config...<br>";
    require_once __DIR__ . '/app/config/config.php';
    echo "✅ Config loaded<br>";

    echo "2. Setting up session (simulating logged in user)...<br>";
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set session like a logged in user
    $_SESSION['user_id'] = 12;
    $_SESSION['role'] = 'focal';
    $_SESSION['agency_id'] = 5;
    echo "✅ Session set<br>";

    echo "3. Testing dashboard file exists...<br>";
    $dashboard_file = __DIR__ . '/app/views/agency/dashboard/dashboard.php';
    if (file_exists($dashboard_file)) {
        echo "✅ Dashboard file exists<br>";
        
        echo "4. Attempting to include dashboard...<br>";
        ob_start(); // Capture any output
        include $dashboard_file;
        $output = ob_get_contents();
        ob_end_clean();
        
        if (empty($output)) {
            echo "❌ Dashboard produced no output (blank page)<br>";
        } else {
            echo "✅ Dashboard produced output (" . strlen($output) . " characters)<br>";
            echo "First 500 characters:<br>";
            echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "</pre>";
        }
        
    } else {
        echo "❌ Dashboard file does not exist at: $dashboard_file<br>";
    }

} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "❌ PHP ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}

echo "<br><a href='" . APP_URL . "/app/views/agency/dashboard/dashboard.php'>Try direct dashboard link</a>";
?>