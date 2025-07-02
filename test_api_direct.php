<?php
/**
 * Direct API test to catch PHP errors
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Direct API Test</h2>";
echo "<p>Testing app/api/simple_gantt_data.php directly...</p>";

// Set GET parameter
$_GET['initiative_id'] = 1;

try {
    // Capture output
    ob_start();
    
    // Include the API file
    include 'app/api/simple_gantt_data.php';
    
    $output = ob_get_clean();
    
    echo "<h3>API Output:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Try to decode as JSON
    $data = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<h3>Parsed JSON:</h3>";
        echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
    } else {
        echo "<p style='color: red;'>Output is not valid JSON: " . json_last_error_msg() . "</p>";
    }
    
} catch (Throwable $e) {
    echo "<p style='color: red;'>PHP Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}
?>
