<?php
/**
 * View Reports
 * 
 * Interface for agency users to view reports related to their programs and sector.
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
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/reports.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get available reporting periods
$reporting_periods = get_all_reporting_periods();

// Get selected period (if any)
$selected_period = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;

// Get reports for selected period
$reports = [];
if ($selected_period) {
    $reports = get_agency_reports($_SESSION['agency_id'], $selected_period);
}

// Set page variables for base layout
$pageTitle = 'View Reports';
$cssBundle = 'agency-reports';
$jsBundle = 'agency-reports';
$bodyClass = 'reports-page';
$contentFile = __DIR__ . '/partials/view_reports_content.php';
// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
?>