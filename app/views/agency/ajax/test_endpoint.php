<?php
/**
 * Simple test endpoint to debug JSON parsing issues
 */

// Start output buffering to catch any unwanted output
ob_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Set JSON header first
    header('Content-Type: application/json');
    
    // Simple test response
    $response = [
        'success' => true,
        'message' => 'Test endpoint working',
        'test_data' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'post_data' => $_POST
        ]
    ];
    
    // Clean any output buffer
    ob_clean();
    
    // Send JSON response
    echo json_encode($response);
    
} catch (Exception $e) {
    // Clean any output buffer
    ob_clean();
    
    // Set error status
    http_response_code(500);
    
    // Send error JSON
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush();
?>
