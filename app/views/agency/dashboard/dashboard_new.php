<?php
/**
 * Agency Dashboard - Refactored
 * 
 * Modern Bento Grid layout for agency users showing program stats and submission rating.
 * Now uses base.php layout pattern with modular structure.
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
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/outcomes.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/controllers/DashboardController.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set up variables for base.php layout
$pageTitle = 'Agency Dashboard';
$cssBundle = 'dashboard';
$jsBundle = 'dashboard';
$contentFile = __DIR__ . '/dashboard_content.php';

// Configure page header
$header_config = [
    'title' => 'Agency Dashboard',
    'subtitle' => 'Program tracking and reporting',
    'variant' => 'green',
    'actions' => [
        [
            'url' => '#',
            'id' => 'refreshDashboard',
            'text' => 'Refresh Data',
            'icon' => 'fas fa-sync-alt',
            'class' => 'btn-light'
        ]
    ]
];

// Get current reporting period
$current_period = get_current_reporting_period();

// Use only current period (removing old period switching logic)
$viewing_period = $current_period;
$period_id = $current_period['period_id'] ?? null;

// Initialize dashboard controller for initial rendering
$dashboardController = new DashboardController($conn);
$dashboardData = $dashboardController->getDashboardData(
    $_SESSION['agency_id'] ?? null, 
    $period_id,
    false  // Default to excluding assigned programs for initial load
);

// Extract initial data for page rendering
$stats = $dashboardData['stats'];
$chartData = $dashboardData['chart_data'];
$recentUpdates = $dashboardData['recent_updates'];

// Get outcomes statistics for the agency
$outcomes_stats = get_agency_outcomes_statistics(null, $period_id);

// Include the base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
