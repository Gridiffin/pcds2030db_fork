<?php
// Simple session test
session_start();

header('Content-Type: application/json');

echo json_encode([
    'session_data' => [
        'user_id' => $_SESSION['user_id'] ?? 'not set',
        'role' => $_SESSION['role'] ?? 'not set',
        'username' => $_SESSION['username'] ?? 'not set'
    ],
    'session_id' => session_id(),
    'session_status' => session_status()
]);
?>
