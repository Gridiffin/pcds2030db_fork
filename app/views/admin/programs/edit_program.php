<?php
/**
 * Enhanced Admin Edit Program Page
 * 
 * Allows admin users to edit program basic information with cross-agency access.
 * Refactored to use base_admin.php layout following agency side patterns.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/admin_edit_program_data.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$program_id) {
    $_SESSION['message'] = 'Invalid program ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Get all edit program data using the admin data helper
$edit_data = get_admin_edit_program_data($program_id);

if (!$edit_data) {
    $_SESSION['message'] = 'Program not found or access denied.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Extract data for easier access in views
$program = $edit_data['program'];
$agency_info = $edit_data['agency_info'];
$initiatives = $edit_data['initiatives'];
$agencies = $edit_data['agencies'];
$sectors = $edit_data['sectors'];

// Admin can edit all programs
$can_edit = true;
$can_view = true;

// Set up base layout variables
$pageTitle = 'Edit Program - ' . $program['program_name'];
$cssBundle = 'admin-edit-program'; // Vite bundle for admin edit program page
$jsBundle = 'admin-edit-program';

// Configure modern page header
$header_config = [
    'title' => 'Edit Program',
    'subtitle' => $program['program_name'] . ' - ' . $agency_info['agency_name'],
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'program_details.php?id=' . $program_id,
            'text' => 'Back to Program Details',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Add additional actions
$header_config['actions'][] = [
    'url' => 'programs.php',
    'text' => 'All Programs',
    'icon' => 'fas fa-list',
    'class' => 'btn-info'
];

// Set content file for base layout
$contentFile = __DIR__ . '/partials/admin_edit_program_content.php';

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';