<?php
/**
 * Admin Dashboard
 * 
 * Main interface for admin users, powered by a controller.
 */

// Define the project root path correctly by navigating up from the current file's directory.
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include the main config file which defines global constants like APP_URL.
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Include necessary libraries
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/outcomes.php';

// Include the controller to fetch data
require_once PROJECT_ROOT_PATH . 'app/controllers/AdminDashboardController.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Admin Dashboard';

// Get current reporting period
$current_period = get_current_reporting_period();

// Add period_id handling for historical views
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);
$viewing_period = $period_id ? get_reporting_period($period_id) : $current_period;

// Get data for the dashboard
$submission_stats = get_period_submission_stats($period_id);
$recent_submissions = get_recent_submissions($period_id, 5);

// Get outcomes statistics for the dashboard
$outcomes_stats = get_outcomes_statistics($period_id);

// Get both assigned and agency-created programs for display
$assigned_programs = get_admin_programs_list($period_id, [
    'is_assigned' => true,
    'limit' => 5,
    'sort_by' => 'p.created_at',
    'sort_order' => 'DESC'
]);
$agency_programs = get_admin_programs_list($period_id, [
    'is_assigned' => false,
    'limit' => 5,
    'sort_by' => 'p.created_at',
    'sort_order' => 'DESC'
]);

// Count assigned and agency-created programs
$assigned_count = count($assigned_programs);
$agency_count = count($agency_programs);

// Set page title
$pageTitle = 'Admin Dashboard';

// Set up variables for base layout
$cssBundle = 'main'; // Use main CSS bundle which includes all necessary styles
$jsBundle = 'admin-dashboard';
$additionalStyles = [
    // Add admin-specific CSS files that may not be in the main bundle
    APP_URL . '/assets/css/admin/admin-common.css',
    APP_URL . '/assets/css/admin/dashboard.css',
    APP_URL . '/assets/css/custom/admin.css'
];
$additionalScripts = [
    asset_url('js/admin', 'dashboard_charts.js'),
    asset_url('js/admin', 'dashboard.js'),
    asset_url('js', 'period_selector.js')
];

// Configure modern page header
$header_config = [
    'title' => 'Admin Dashboard',
    'subtitle' => 'System overview and management',
    'breadcrumb' => [
        [
            'text' => 'Dashboard',
            'url' => null // Current page, no link
        ]
    ],
    'variant' => 'green',
    'actions' => [
        [
            'text' => 'Refresh Data',
            'url' => '#',
            'id' => 'refreshPage',
            'class' => 'btn-light',
            'icon' => 'fas fa-sync-alt'
        ]
    ]
];

// Pass hasActivePeriod to JavaScript
$hasActivePeriod = isset($current_period) && !empty($current_period);

// Set content file that contains the main page content
$contentFile = __DIR__ . '/partials/dashboard_content.php';

include PROJECT_ROOT_PATH . '/app/views/layouts/base.php';

