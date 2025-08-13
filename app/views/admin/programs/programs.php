<?php
/**
 * Admin View Programs - Overhauled Version
 * 
 * Interface for admin users to view all finalized programs across agencies.
 * Modular structure with base.php layout following agency side patterns.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/AdminProgramsModel.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get message from session if available
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'info';

// Clear message from session
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Initialize the model for data access (proper separation of concerns)
$adminProgramsModel = new AdminProgramsModel($conn);

// Fetch data using the model instead of direct SQL in view
$programs = $adminProgramsModel->getFinalizedPrograms();
$programs_with_submissions = $programs; // Both arrays are the same in this context
$agencies = $adminProgramsModel->getAllAgencies();
$active_initiatives = $adminProgramsModel->getActiveInitiatives();


// Set up base layout variables
$pageTitle = 'Admin Programs Overview';
$cssBundle = 'admin-view-programs'; // Vite bundle for admin view programs page
$jsBundle = 'admin-view-programs';
$additionalScripts = [
    'js/admin/programs/admin-finalized-programs.js'
];

// Configure modern page header
$header_config = [
    'title' => 'Programs Overview',
    'subtitle' => 'View and manage finalized programs across all agencies',
    'breadcrumb' => [
        [
            'text' => 'Home',
            'url' => APP_URL . '/index.php?page=admin_dashboard'
        ],
        [
            'text' => 'Programs',
            'url' => null // Current page, no link
        ]
    ],
    'variant' => 'green'
];

// Set content file for base layout
$contentFile = __DIR__ . '/partials/programs_content.php';

// Include base layout - it will render header, nav, content, and footer
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';