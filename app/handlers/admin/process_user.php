<?php
/**
 * Process User Actions
 * 
 * Handles user CRUD operations for admin
 */

// Include necessary files
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admin_functions.php';

// Verify user is admin
if (!is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Check if action is provided in POST or GET
$action = '';
if (isset($_POST['action'])) {
    $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
    $action = $_GET['action'];
}

if (empty($action)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Action is required']);
    exit;
}

$result = [];

switch ($action) {
    case 'add_user':
        $result = add_user($_POST);
        break;
        
    case 'edit_user':
        $result = update_user($_POST);
        break;
        
    case 'delete_user':
        // Handle both GET and POST methods
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : (isset($_GET['user_id']) ? $_GET['user_id'] : null);
        if (!$user_id) {
            $result = ['error' => 'User ID is required'];
        } else {
            $result = delete_user($user_id);
        }
        break;
        
    case 'toggle_active':
        // New action for toggling user active status
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : null;
        
        if (!$user_id || $is_active === null) {
            $result = ['error' => 'User ID and status are required'];
        } else {
            $update_data = [
                'user_id' => $user_id,
                'is_active' => $is_active
            ];
            // Use the update_user function to update just the is_active field
            $result = update_user($update_data);
        }
        break;
        
    default:
        $result = ['error' => 'Invalid action'];
}

// Check if this is a regular form submission or AJAX
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if ($is_ajax) {
    // Return JSON response for AJAX requests
    header('Content-Type: application/json');
    echo json_encode($result);
} else {
    // For regular form submissions, set a session message and redirect
    if (isset($result['success'])) {
        $_SESSION['message'] = $result['message'] ?? 'Operation completed successfully';
        $_SESSION['message_type'] = 'success';
        $_SESSION['show_toast_only'] = true; // Only show toast, not alert
    } else {
        $_SESSION['message'] = $result['error'] ?? 'An error occurred';
        $_SESSION['message_type'] = 'danger';
        $_SESSION['show_toast_only'] = true;
    }
    
    // Redirect back to the manage users page
    header('Location: ' . APP_URL . '/app/views/admin/manage_users.php');
}
exit;

