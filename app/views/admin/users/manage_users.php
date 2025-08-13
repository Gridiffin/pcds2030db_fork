<?php
/**
 * Manage Users Page
 * 
 * Admin interface for managing user accounts.
 * Using standard Bootstrap modals with fixes.
 */

// Define the project root path correctly by navigating up from the current file's directory.
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include the main config file which defines global constants like APP_URL.
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Include necessary libraries
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Manage Users';

// Process form submissions
$message = '';
$message_type = '';

// Check if there's a message in the session and use it
if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    
    // If show_toast_only is set, we'll only show the toast notification
    $show_toast_only = isset($_SESSION['show_toast_only']) && $_SESSION['show_toast_only'];
    
    // Clear the message from session after using it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    if (isset($_SESSION['show_toast_only'])) {
        unset($_SESSION['show_toast_only']);
    }
}

// Handle user actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Check if this is an AJAX request
        $is_ajax = isset($_POST['ajax_request']) && $_POST['ajax_request'] == '1';
        
        $result = [];
        
        switch ($_POST['action']) {
            case 'add_user':
                $result = add_user($_POST);
                if (isset($result['success'])) {
                    $message = 'User added successfully.';
                    $message_type = 'success';
                    
                    // Store in session for redirect
                    $_SESSION['message'] = $message;
                    $_SESSION['message_type'] = $message_type;
                    
                    // Redirect to clear the form and prevent resubmission
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $message = $result['error'] ?? 'Failed to add user.';
                    $message_type = 'danger';
                }
                break;

            case 'edit_user':
                $result = update_user($_POST);
                if (isset($result['success'])) {
                    $message = 'User updated successfully.';
                    $message_type = 'success';
                } else {
                    $message = $result['error'] ?? 'Failed to update user.';
                    $message_type = 'danger';
                }
                break;            case 'delete_user':
                $result = delete_user($_POST['user_id']);
                if (isset($result['success'])) {
                    $message = $result['message'] ?? 'User deleted successfully.';
                    $message_type = 'success';
                } else {
                    $message = $result['error'] ?? 'Failed to delete user.';
                    $message_type = 'danger';
                }
                break;
        }
        
        // If this was an AJAX request, return JSON response instead of setting message variables
        if ($is_ajax) {
            header('Content-Type: application/json');
            if ($message_type === 'success') {
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                echo json_encode(['error' => $message]);
            }
            exit;
        }
    }
}

// Get all users and separate them by role
$all_users = get_all_users();
$admin_users = array_filter($all_users, function($user) {
    return $user['role'] === 'admin';
});
$agency_users = array_filter($all_users, function($user) {
    return $user['role'] === 'agency' || $user['role'] === 'focal';
});

// Set up variables for base_admin layout
$pageTitle = 'Manage Users';
$cssBundle = 'admin-users';
$jsBundle = 'admin-users';

// Configure modern page header
$header_config = [
    'title' => 'User Management',
    'subtitle' => 'Create and manage user accounts for the system',
    'breadcrumb' => [
        [
            'text' => 'Home',
            'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php'
        ],
        [
            'text' => 'Users',
            'url' => null // Current page, no link
        ]
    ],
    'variant' => 'green',
    'actions' => [
        [
            'text' => 'Add New User',
            'url' => APP_URL . '/app/views/admin/users/add_user.php',
            'class' => 'btn-light',
            'icon' => 'fas fa-user-plus'
        ]
    ]
];

// Set content file that contains the main page content
$contentFile = __DIR__ . '/partials/manage_users_content.php';

require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';