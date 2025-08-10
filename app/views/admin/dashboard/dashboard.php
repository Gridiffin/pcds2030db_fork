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

// Prepare individual outcomes for graphs and KPIs
$all_outcomes = get_all_outcomes();
$admin_chart_outcomes = array_values(array_filter($all_outcomes, function($o) {
    $type = strtolower($o['type'] ?? '');
    return in_array($type, ['chart', 'graph']);
}));
$admin_kpi_outcomes = array_values(array_filter($all_outcomes, function($o) {
    $type = strtolower($o['type'] ?? '');
    return $type === 'kpi';
}));

// Get recent programs for display - simple query
$recent_programs_query = "SELECT 
    p.program_id, p.program_name, p.created_at,
    a.agency_name,
    u.fullname as creator_name
FROM programs p
LEFT JOIN agency a ON p.agency_id = a.agency_id  
LEFT JOIN users u ON p.created_by = u.user_id
WHERE p.is_deleted = 0
ORDER BY p.created_at DESC 
LIMIT 5";

$recent_programs_result = $conn->query($recent_programs_query);
$recent_programs = [];
if ($recent_programs_result && $recent_programs_result->num_rows > 0) {
    while ($row = $recent_programs_result->fetch_assoc()) {
        $recent_programs[] = $row;
    }
}

// Count total programs
$total_programs_count = count($recent_programs);

// Set up variables for base_admin layout
$pageTitle = 'Admin Dashboard';
$cssBundle = 'admin-dashboard';
$jsBundle = 'admin-dashboard';
$contentFile = __DIR__ . '/partials/dashboard_content.php';
$metaDescription = 'PCDS 2030 Admin Dashboard - Modern system overview and management tools';

// Pass hasActivePeriod to JavaScript for inline scripts
$hasActivePeriod = isset($current_period) && !empty($current_period);
$inlineScripts = "
    var hasActivePeriod = " . ($hasActivePeriod ? 'true' : 'false') . ";
    var currentPeriodId = " . ($period_id ? $period_id : 'null') . ";
    var APP_URL = '" . APP_URL . "';
";

// Configure page header
$header_config = [
    'title' => 'Admin Dashboard',
    'subtitle' => 'System overview and management',
    'breadcrumb' => [
        [
            'text' => 'Home',
            'url' => null // Current page, no link
        ]
    ],
    'variant' => 'green',
    'actions' => []
];

// Include the admin base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';

