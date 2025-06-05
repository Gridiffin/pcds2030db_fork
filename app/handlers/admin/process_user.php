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
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is admin
if (!is_admin()) {
    // Log unauthorized user management attempt
    log_audit_action(
        'user_management_denied',
        'Unauthorized attempt to access user management functions',
        'failure'
    );
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
        // Log user creation attempt
        if (isset($result['success'])) {
            $username = $_POST['username'] ?? 'Unknown';
            log_audit_action(
                'user_create',
                "Successfully created user: {$username}",
                'success',
                $_SESSION['user_id'] ?? null
            ); // Added user_id for better traceability
        } else {
            $username = $_POST['username'] ?? 'Unknown';
            $error = $result['error'] ?? 'Unknown error';
            log_audit_action(
                'user_create_failed',
                "Failed to create user: {$username}. Error: {$error}",
                'failure'
            );
        }
        break;
        
    case 'edit_user':
        $result = update_user($_POST);
        // Log user update attempt
        if (isset($result['success'])) {
            $user_id = $_POST['user_id'] ?? 'Unknown';
            $username = $_POST['username'] ?? 'Unknown';
            log_audit_action(
                'user_update',
                "Successfully updated user ID {$user_id} ({$username})",
                'success',
                $_SESSION['user_id'] ?? null
            ); // Added user_id for better traceability
        } else {
            $user_id = $_POST['user_id'] ?? 'Unknown';
            $username = $_POST['username'] ?? 'Unknown';
            $error = $result['error'] ?? 'Unknown error';
            log_audit_action(
                'user_update_failed',
                "Failed to update user ID {$user_id} ({$username}). Error: {$error}",
                'failure'
            );
        }
        break;
          case 'delete_user':
        // Handle both GET and POST methods
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : (isset($_GET['user_id']) ? $_GET['user_id'] : null);
        if (!$user_id) {
            $result = ['error' => 'User ID is required'];
            log_audit_action(
                'user_delete_failed',
                'Failed to delete user: User ID is required',
                'failure'
            );
        } else {
            $result = delete_user($user_id);
            // Log user deletion attempt
            if (isset($result['success'])) {
                log_audit_action(
                    'user_delete',
                    "Successfully deleted user ID: {$user_id}",
                    'success',
                    $_SESSION['user_id'] ?? null
                ); // Added user_id for better traceability
            } else {
                $error = $result['error'] ?? 'Unknown error';
                log_audit_action(
                    'user_delete_failed',
                    "Failed to delete user ID {$user_id}. Error: {$error}",
                    'failure'
                );
            }
        }
        break;
          case 'toggle_active':
        // New action for toggling user active status
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : null;
        
        if (!$user_id || $is_active === null) {
            $result = ['error' => 'User ID and status are required'];
            log_audit_action(
                'user_status_toggle_failed',
                'Failed to toggle user status: User ID and status are required',
                'failure'
            );
        } else {
            $update_data = [
                'user_id' => $user_id,
                'is_active' => $is_active
            ];
            // Use the update_user function to update just the is_active field
            $result = update_user($update_data);
            
            // Log user status toggle attempt
            if (isset($result['success'])) {
                $status = $is_active ? 'activated' : 'deactivated';
                log_audit_action(
                    'user_status_toggle',
                    "Successfully {$status} user ID: {$user_id}",
                    'success',
                    $_SESSION['user_id'] ?? null
                ); // Added user_id for better traceability
            } else {
                $error = $result['error'] ?? 'Unknown error';
                $status = $is_active ? 'activate' : 'deactivate';
                log_audit_action(
                    'user_status_toggle_failed',
                    "Failed to {$status} user ID {$user_id}. Error: {$error}",
                    'failure'
                );
            }
        }
        break;
          default:
        $result = ['error' => 'Invalid action'];
        log_audit_action(
            'user_management_invalid_action',
            "Invalid user management action attempted: {$action}",
            'failure'
        );
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
    header('Location: ' . APP_URL . '/app/views/admin/users/manage_users.php');
}
exit;

