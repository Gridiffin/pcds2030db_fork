<?php
/**
 * Admin Reporting Periods Management
 * 
 * Allows administrators to manage reporting periods.
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
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Page configuration
$pageTitle = 'Reporting Periods Management';
$currentPage = 'periods';

// Set up variables for base layout
$cssBundle = 'admin-periods'; // Use admin-periods CSS bundle for proper styling
$jsBundle = null; // No JavaScript bundle - use direct scripts

// Configure modern page header
$header_config = [
    'title' => 'Reporting Periods',
    'subtitle' => 'Manage quarterly reporting periods for data collection',
    'variant' => 'green',
    'actions' => [
        [
            'url' => '#',
            'text' => 'Add Period',
            'icon' => 'fas fa-plus-circle',
            'class' => 'btn-primary',
            'id' => 'addPeriodBtn'
        ]
    ]
];

// Set content file that contains the main page content
$contentFile = __DIR__ . '/partials/reporting_periods_content.php';

require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';
?>