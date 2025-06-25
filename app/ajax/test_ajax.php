<?php
/**
 * Simple test endpoint to check if AJAX is working
 */

header('Content-Type: application/json');
error_log("test_ajax.php: Request received");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Only POST allowed']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'AJAX is working!',
    'timestamp' => date('Y-m-d H:i:s'),
    'post_data' => $_POST
]);
?>
