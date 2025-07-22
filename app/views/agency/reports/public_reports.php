<?php
/**
 * Public Reports - Agency View
 * 
 * Interface for agency users to view and download public reports 
 * that have been made available by admin users.
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

// Get public reports
$public_reports = get_public_reports();

// Set page variables for base layout
$pageTitle = 'Public Reports';
$cssBundle = 'agency-reports'; // CSS bundle for reports module
$jsBundle = 'agency-reports';
$bodyClass = 'reports-page';
$contentFile = __DIR__ . '/partials/public_reports_content.php';
// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
?>