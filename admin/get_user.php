<?php
/**
 * Get User API Endpoint
 * 
 * Returns user data for the edit user form
 */

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/admin_functions.php';

// Verify user is admin
if (!is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

$user_id = intval($_GET['id']);

// Get user data
$query = "SELECT u.*, s.sector_name 
          FROM users u 
          LEFT JOIN sectors s ON u.sector_id = s.sector_id 
          WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();

// Return user data as JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'user' => $user
]);
