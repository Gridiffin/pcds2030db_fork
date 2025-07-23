<?php
/**
 * Admin Dashboard
 * 
 * Main interface for admin users, powered by a controller.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';

// Include the controller to fetch data
require_once ROOT_PATH . 'app/controllers/AdminDashboardController.php';

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

// Additional scripts
$additionalScripts = [
    asset_url('js/admin', 'dashboard_charts.js'),
    asset_url('js/admin', 'dashboard.js'),
    asset_url('js', 'period_selector.js')
];

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
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

// Include the modern page header
require_once '../../layouts/page_header.php';

// Pass hasActivePeriod to JavaScript
$hasActivePeriod = isset($current_period) && !empty($current_period);
?>

<script>
    const hasActivePeriod = <?php echo $hasActivePeriod ? 'true' : 'false'; ?>;
</script>

<!-- Dashboard Content -->
<main class="flex-fill">
    <section class="section">
        <div class="container-fluid">
            <!-- Period Selector Component -->

            <!-- Quick Actions Section -->
            <?php require_once 'partials/_quick_actions.php'; ?>

            <!-- Stats Overview -->
            <div data-period-content="stats_section">
                <?php require_once 'partials/_stats_overview.php'; ?>
            </div>

            <!-- Programs Overview Section -->
            <?php require_once 'partials/_programs_overview.php'; ?>

            <!-- Outcomes Overview Section -->
            <?php require_once 'partials/_outcomes_overview.php'; ?>

            <!-- Recent Submissions -->
            <div class="row">
                <?php require_once 'partials/_recent_submissions.php'; ?>
            </div>
        </div>
    </section>
</main>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>

