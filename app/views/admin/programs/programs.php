<?php
/**
 * Admin View: Programs
 *
 * This file is the main view for the admin programs page.
 * It ensures the controller has run to provide data, then includes the necessary partials.
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

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Ensure the controller has run and prepared the data. If not, load it.
if (!isset($programs_with_drafts)) {
    require_once PROJECT_ROOT_PATH . 'app/controllers/AdminProgramsController.php';
}

// Set up variables for base layout
$pageTitle = $pageTitle ?? 'Admin Programs';
$cssBundle = 'main'; // Use main CSS bundle which includes all necessary styles
$jsBundle = 'admin-programs';
$additionalStyles = [
    // Add admin-specific CSS files that may not be in the main bundle
    APP_URL . '/assets/css/admin/admin-common.css',
    APP_URL . '/assets/css/admin/programs.css',
    APP_URL . '/assets/css/custom/admin.css'
];
$additionalScripts = [
    // Additional scripts specific to admin programs can be added here if needed
];

// Configure modern page header
$header_config = [
    'title' => $pageTitle,
    'subtitle' => 'View and manage programs across all agencies',
    'variant' => 'green',
    'actions' => [
        [
            'text' => 'Bulk Assign Initiatives',
            'url' => 'bulk_assign_initiatives.php',
            'class' => 'btn-light',
            'icon' => 'fas fa-link'
        ]
    ]
];

// Set content file that contains the main page content
$contentFile = __DIR__ . '/partials/programs_content.php';

include PROJECT_ROOT_PATH . '/app/views/layouts/base.php';




