<?php
/**
 * System Settings (Refactored)
 * Admin page for configuring system-wide settings.
 */

// Define the project root path correctly
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include the main config file which defines global constants like APP_URL
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Include necessary libraries and controllers
require_once PROJECT_ROOT_PATH . 'app/controllers/AdminSettingsController.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Instantiate controller and handle POST
$controller = new AdminSettingsController();
$controller->handlePost();

// Set page title
$pageTitle = 'System Settings';

// Set up variables for base layout
$cssBundle = 'main';
$jsBundle = 'admin-common';
$additionalStyles = [
    APP_URL . '/assets/css/admin/admin-common.css'
];
$additionalScripts = [
    APP_URL . '/assets/js/admin/system_settings.js'
];

// Configure the modern page header
$header_config = [
    'title' => 'System Settings',
    'subtitle' => 'Configure system-wide settings',
    'variant' => 'green',
    'actions' => []
];

// Set content file that contains the main page content
$contentFile = __DIR__ . '/partials/system_settings_content.php';
include PROJECT_ROOT_PATH . '/app/views/layouts/base.php';