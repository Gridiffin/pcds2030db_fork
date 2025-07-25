<?php
/**
 * Audit Log
 * 
 * Admin page to view audit logs.
 */

// Define the project root path correctly
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include the main config file which defines global constants like APP_URL
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Include necessary libraries
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admin_functions.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Audit Log';

// Set up variables for base layout
$cssBundle = 'main';
$jsBundle = 'admin-common';
$additionalStyles = [
    'https://fonts.googleapis.com/icon?family=Material+Icons',
    APP_URL . '/assets/css/custom/audit_log.css'
];
$additionalScripts = [];

// Configure modern page header
$header_config = [
    'title' => 'Audit Log',
    'subtitle' => 'View system activity and database changes',
    'variant' => 'white',
    'actions' => []
];

// Set content file that contains the main page content
$contentFile = __DIR__ . '/partials/audit_log_content.php';
include PROJECT_ROOT_PATH . '/app/views/layouts/base.php';