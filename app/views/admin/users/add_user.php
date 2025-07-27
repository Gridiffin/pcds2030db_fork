<?php
/**
 * Add User Page
 * 
 * Admin interface for adding new user accounts.
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
$pageTitle = 'Add New User';

// Process form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the form submission
    $result = add_user($_POST);
    
    if (isset($result['success'])) {
        $_SESSION['message'] = 'User added successfully.';
        $_SESSION['message_type'] = 'success';
        $_SESSION['show_toast_only'] = true; // Add this flag to indicate we want only a toast notification
        header('Location: manage_users.php');
        exit;
    } else {
        $message = $result['error'] ?? 'Failed to add user.';
        $message_type = 'danger';
    }
}

$config = include __DIR__ . '/../../../config/db_names.php';
if (!$config || !isset($config['tables']['agency'])) {
    die('Config not loaded or missing agency table definition.');
}
$agencyTable = $config['tables']['agency'];
$agencyIdCol = $config['columns']['agency']['id'];
$agencyNameCol = $config['columns']['agency']['name'];
$agencies = get_all_agencies($conn);

// Set up variables for base layout
$cssBundle = 'admin-users'; // Use modular admin-users CSS bundle (~80kB vs 352kB)
$jsBundle = 'admin-users';
$additionalStyles = [
    // Add admin-specific CSS files that may not be in the main bundle
    APP_URL . '/assets/css/admin/admin-common.css',
    APP_URL . '/assets/css/admin/users.css',
    APP_URL . '/assets/css/custom/admin.css'
];
$additionalScripts = [
    APP_URL . '/assets/js/admin/user_form.js'
];

// Configure modern page header
$header_config = [
    'title' => 'Add New User',
    'subtitle' => 'Create a new user account',
    'variant' => 'white',
    'actions' => [
        [
            'text' => 'Back to Users',
            'url' => APP_URL . '/app/views/admin/users/manage_users.php',
            'class' => 'btn-outline-primary',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Set content file that contains the main page content
$contentFile = __DIR__ . '/partials/add_user_content.php';

require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';