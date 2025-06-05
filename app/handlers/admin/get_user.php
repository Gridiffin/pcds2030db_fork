<?php
/**
 * Get User API Endpoint
 * 
 * Returns user data for the edit user form
 */

// Include necessary files
require_once '../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admin_functions.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is admin
if (!is_admin()) {
    // Log unauthorized user data access attempt
    log_audit_action(
        'user_data_access_denied',
        'Unauthorized attempt to access user data',
        'failure'
    );
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
    // Log user not found
    log_audit_action(
        'user_data_access_failed',
        "User not found for ID: {$user_id}",
        'failure'
    );
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();

// Log successful user data access
log_audit_action(
    'user_data_access',
    "Successfully accessed user data for ID: {$user_id} ({$user['username']})",
    'success'
);

// Return user data as JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'user' => $user
]);
