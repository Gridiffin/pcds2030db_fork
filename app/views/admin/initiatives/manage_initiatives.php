<?php
/**
 * Agency Initiatives View
 * 
 * Read-only view of initiatives that have programs assigned to the current agency.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/initiatives.php';
require_once PROJECT_ROOT_PATH . 'lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'lib/db_names_helper.php';

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

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build filters array
$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}
if ($status_filter !== '') {
    $filters['is_active'] = $status_filter === 'active' ? 1 : 0;
}

// Get initiatives for admin (all initiatives)
$initiatives = get_all_initiatives($filters);

// Get column names using db_names helper
$initiative_id_col = get_column_name('initiatives', 'id');
$initiative_name_col = get_column_name('initiatives', 'name');
$initiative_number_col = get_column_name('initiatives', 'number');
$initiative_description_col = get_column_name('initiatives', 'description');
$start_date_col = get_column_name('initiatives', 'start_date');
$end_date_col = get_column_name('initiatives', 'end_date');
$is_active_col = get_column_name('initiatives', 'is_active');

// Set up variables for base layout
$pageTitle = 'Initiatives';
$cssBundle = 'admin-manage-initiatives';
$jsBundle = 'admin-manage-initiatives';
$contentFile = __DIR__ . '/partials/content.php';
$header_config = [
    'title' => 'Initiatives',
    'subtitle' => 'View initiatives where your agency has assigned programs',
    'variant' => 'blue',
    'actions' => []
];
require_once dirname(__DIR__, 2) . '/layouts/base.php';
