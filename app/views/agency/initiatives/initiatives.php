<?php
/**
 * Agency Initiatives View - Refactored
 * 
 * Read-only view of initiatives that have programs assigned to the current agency.
 * Uses modular structure with base.php layout and partials.
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
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/initiatives.php';
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_names_helper.php';

// Verify user is an agency
if (!is_agency()) {
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

// Get column names using db_names helper
$initiative_id_col = get_column_name('initiatives', 'id');
$initiative_name_col = get_column_name('initiatives', 'name');
$initiative_number_col = get_column_name('initiatives', 'number');
$initiative_description_col = get_column_name('initiatives', 'description');
$start_date_col = get_column_name('initiatives', 'start_date');
$end_date_col = get_column_name('initiatives', 'end_date');
$is_active_col = get_column_name('initiatives', 'is_active');

// Get initiatives for current agency
$agency_id = $_SESSION['agency_id'] ?? null;
$initiatives = get_agency_initiatives($agency_id, $filters);

// Configure page for base.php layout
$pageTitle = 'Initiatives';
$cssBundle = 'initiatives';
$jsBundle = 'initiatives';

// Configure the modern page header
$header_config = [
    'title' => 'Initiatives',
    'subtitle' => 'View initiatives where your agency has assigned programs',
    'variant' => 'blue',
    'actions' => []
];

// Set content file for base layout to include
$contentFile = __DIR__ . '/partials/initiatives_content.php';

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
