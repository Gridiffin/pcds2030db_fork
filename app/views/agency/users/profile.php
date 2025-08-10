<?php
/**
 * User Profile Page
 * Allows users to edit their username, password, and email
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
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';

// Verify user is logged in (all logged in users can access their profile)
if (!is_logged_in()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set up variables for base.php layout
$pageTitle = 'User Profile';
$cssBundle = 'agency-users-profile'; // Specific CSS bundle for profile page
$jsBundle = 'agency-users-profile';   // Specific JS bundle for profile page
$contentFile = __DIR__ . '/partials/profile_content.php';
$bodyClass = 'profile-page';

// Additional CSS files (if needed for specific components not in bundle)
$additionalStyles = [];

// Additional JavaScript files (if needed)
$additionalScripts = [];

// Configure page header
$header_config = [
    'title' => 'User Profile',
    'subtitle' => 'Manage your account information and preferences',
    'breadcrumb' => [
        [
            'text' => 'Home',
            'url' => ($_SESSION['role'] === 'admin') 
                    ? APP_URL . '/app/views/admin/dashboard/dashboard.php'
                    : APP_URL . '/app/views/agency/dashboard/dashboard.php'
        ],
        [
            'text' => 'Profile',
            'url' => null // Current page, no link
        ]
    ],
    'variant' => 'blue', // Use blue variant for profile pages
    'actions' => [
        [
            'url' => ($_SESSION['role'] === 'admin') 
                    ? APP_URL . '/app/views/admin/dashboard/dashboard.php'
                    : APP_URL . '/app/views/agency/dashboard/dashboard.php',
            'text' => 'Back to Home',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-light'
        ]
    ]
];

// Include the base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
