<?php
/**
 * Simple test endpoint to verify AJAX functionality
 */
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'AJAX endpoint is working',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['SERVER_NAME'] ?? 'unknown'
]); 