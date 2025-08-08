<?php
/**
 * Controller for the Admin Dashboard.
 *
 * This script handles the business logic for the admin dashboard,
 * preparing all the data needed by the view.
 */

// Since this file is included from a view, the path to config is already set up.
// No need for separate require_once statements for config, session, etc.

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

$hasActivePeriod = isset($current_period) && !empty($current_period);

// Additional scripts needed by the view
// IMPORTANT: Admin dashboard now uses a Vite bundle (jsBundle = 'admin-dashboard').
// Do not load raw JS files here to avoid duplicate execution and ESM import errors.
// Leave additionalScripts empty unless you need an external, non-bundled script.
$additionalScripts = [];

// Configuration for the page header
$header_config = [
    'title' => 'Admin Dashboard',
    'subtitle' => 'System overview and management',
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