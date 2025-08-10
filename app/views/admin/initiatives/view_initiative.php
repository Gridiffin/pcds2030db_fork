<?php
/**
 * Admin View Initiative Page
 * 
 * Displays comprehensive information about a specific initiative including
 * associated programs, timeline, and management actions.
 * Follows the same pattern as admin program_details.php.
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
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_names_helper.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get initiative ID from URL
$initiative_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$initiative_id) {
    $_SESSION['message'] = 'Invalid initiative ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_initiatives.php');
    exit;
}

// Get initiative details
$initiative = get_initiative_by_id($initiative_id);

if (!$initiative) {
    $_SESSION['message'] = 'Initiative not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_initiatives.php');
    exit;
}

// Get column names using db_names helper
$initiative_id_col = get_column_name('initiatives', 'id');
$initiative_name_col = get_column_name('initiatives', 'name');
$initiative_description_col = get_column_name('initiatives', 'description');
$initiative_number_col = get_column_name('initiatives', 'number');
$initiative_status_col = get_column_name('initiatives', 'status');
$start_date_col = get_column_name('initiatives', 'start_date');
$end_date_col = get_column_name('initiatives', 'end_date');
$created_at_col = get_column_name('initiatives', 'created_at');
$updated_at_col = get_column_name('initiatives', 'updated_at');

// Program column names for displaying associated programs
$programNameCol = get_column_name('programs', 'name');
$programNumberCol = get_column_name('programs', 'number');
$agencyNameCol = get_column_name('agency', 'name');

// Get associated programs
$associated_programs = get_initiative_programs($initiative_id);

// Set page title and header configuration
$pageTitle = 'View Initiative';
$header_config = [
    'title' => 'Initiative Details',
    'subtitle' => htmlspecialchars($initiative[$initiative_name_col] ?? 'Unknown Initiative'),
    'breadcrumb' => [
        ['text' => 'Home', 'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php'],
        ['text' => 'Initiatives', 'url' => APP_URL . '/app/views/admin/initiatives/manage_initiatives.php'],
        ['text' => 'View', 'url' => null]
    ],
    'actions' => [
        [
            'url' => 'manage_initiatives.php',
            'text' => 'Back to Initiatives',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ],
        [
            'url' => 'edit.php?id=' . $initiative_id,
            'text' => 'Edit Initiative',
            'icon' => 'fas fa-edit',
            'class' => 'btn-primary'
        ]
    ]
];

// Layout variables
$cssBundle = 'admin-view-initiative';
$jsBundle = 'admin-view-initiative';
$contentFile = __DIR__ . '/partials/view_content.php';

// Include base layout
require_once dirname(__DIR__, 2) . '/layouts/base_admin.php';
?>