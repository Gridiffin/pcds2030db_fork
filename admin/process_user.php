<?php
/**
 * Process User Actions
 * 
 * Handles user CRUD operations for admin
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

// Check if action is provided
if (!isset($_POST['action'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Action is required']);
    exit;
}

$action = $_POST['action'];
$result = [];

switch ($action) {
    case 'add_user':
        $result = add_user($_POST);
        break;
        
    case 'edit_user':
        $result = update_user($_POST);
        break;
        
    case 'delete_user':
        $result = delete_user($_POST['user_id']);
        break;
        
    default:
        $result = ['error' => 'Invalid action'];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($result);
exit;
