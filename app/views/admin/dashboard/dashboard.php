<?php
/**
 * Admin Dashboard
 * 
 * Main interface for admin users.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';

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
            <?php require_once ROOT_PATH . 'app/lib/period_selector_dashboard.php'; ?>

        <!-- Quick Actions Section - Optimized for high-value admin actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0 text-white"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row justify-content-center text-center g-4">
                            <?php
                            // Get current period status for contextual actions
                            $periodOpen = isset($current_period) && isset($current_period['status']) && $current_period['status'] === 'open';
                            $periodId = $current_period['period_id'] ?? 0;
                            ?>                            <div class="col-lg-3 col-md-4 col-6">
                                <a href="<?php echo view_url('admin', 'programs/assign_programs.php'); ?>" class="btn btn-outline-success w-100 d-flex flex-column align-items-center justify-content-center quick-action-btn border-success">
                                    <i class="fas fa-tasks fa-2x"></i>
                                    <span class="mt-2">Assign Programs</span>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-4 col-6">
                                <a href="<?php echo view_url('admin', 'periods/reporting_periods.php'); ?>" class="btn <?php echo $periodOpen ? 'btn-outline-danger' : 'btn-outline-success'; ?> w-100 d-flex flex-column align-items-center justify-content-center quick-action-btn <?php echo $periodOpen ? 'border-danger' : 'border-success'; ?>">
                                    <i class="fas fa-calendar-alt fa-2x"></i>
                                    <span class="mt-2"><?php echo $periodOpen ? 'Manage Periods' : 'Manage Periods'; ?></span>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-4 col-6">
                                <a href="<?php echo view_url('admin', 'reports/generate_reports.php'); ?>" class="btn btn-outline-primary w-100 d-flex flex-column align-items-center justify-content-center quick-action-btn border-primary">
                                    <i class="fas fa-file-powerpoint fa-2x"></i>
                                    <span class="mt-2">Generate Reports</span>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-4 col-6">
                                <a href="<?php echo view_url('admin', 'users/add_user.php'); ?>" class="btn btn-outline-info w-100 d-flex flex-column align-items-center justify-content-center quick-action-btn border-info">
                                    <i class="fas fa-user-plus fa-2x"></i>
                                    <span class="mt-2">Add New User</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div data-period-content="stats_section">
            <div class="row">
                <!-- Agencies Reporting Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card primary">
                        <div class="card-body">
                            <div class="icon-container">
                                <i class="fas fa-users stat-icon"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-title">Agencies Reporting</div>
                                <div class="stat-value">
                                    <?php echo $submission_stats['agencies_reported'] ?? 0; ?>/<?php echo $submission_stats['total_agencies'] ?? 0; ?>
                                </div>
                                <div class="stat-subtitle">
                                    <i class="fas fa-check me-1"></i>
                                    <?php echo $submission_stats['agencies_reported'] ?? 0; ?> Agencies Reported
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Programs On Track Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card warning">
                        <div class="card-body">
                            <div class="icon-container">
                                <i class="fas fa-calendar-check stat-icon"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-title">Programs On Track</div>
                                <div class="stat-value">
                                    <?php echo $submission_stats['on_track_programs'] ?? 0; ?>
                                </div>
                                <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
                                <div class="stat-subtitle">
                                    <i class="fas fa-chart-line me-1"></i>
                                    <?php echo round(($submission_stats['on_track_programs'] / $submission_stats['total_programs']) * 100); ?>% of total
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Programs Delayed Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card danger">
                        <div class="card-body">
                            <div class="icon-container">
                                <i class="fas fa-exclamation-triangle stat-icon"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-title">Programs Delayed</div>
                                <div class="stat-value>
                                    <?php echo $submission_stats['delayed_programs'] ?? 0; ?>
                                </div>
                                <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
                                <div class="stat-subtitle">
                                    <i class="fas fa-chart-line me-1"></i>
                                    <?php echo round(($submission_stats['delayed_programs'] / $submission_stats['total_programs']) * 100); ?>% of total
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Completion Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card success">
                        <div class="card-body">
                            <div class="icon-container">
                                <i class="fas fa-clipboard-list stat-icon"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-title">Overall Completion</div>
                                <div class="stat-value">
                                    <?php echo $submission_stats['completion_percentage'] ?? 0; ?>%
                                </div>
                                <div class="stat-subtitle progress mt-2" style="height: 10px;">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: <?php echo $submission_stats['completion_percentage'] ?? 0; ?>%"
                                         aria-valuenow="<?php echo $submission_stats['completion_percentage'] ?? 0; ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Programs Overview Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">                    <div class="card-header">
                        <h5 class="card-title m-0">Programs Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row gx-4 gy-4">
                            <!-- Assigned Programs Section -->
                            <div class="col-md-6">
                                <div class="p-3 border rounded h-100 bg-light">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-success me-2" style="min-width: 90px;">Assigned</span>
                                        <span class="fw-bold">Latest Assigned Programs</span>
                                        <span class="badge bg-secondary ms-auto">Total: <?php echo $assigned_count; ?></span>
                                    </div>
                                    <?php if (empty($assigned_programs)): ?>
                                        <div class="alert alert-light">
                                            <i class="fas fa-info-circle me-2"></i>No assigned programs found.
                                        </div>
                                    <?php else: ?>                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover table-custom" style="table-layout: fixed; width: 100%; min-width: 500px;">
                                                <colgroup>
                                                    <col style="width: 45%">
                                                    <col style="width: 30%">
                                                    <col style="width: 25%">
                                                </colgroup>
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Program Name</th>
                                                        <th>Agency</th>
                                                        <th>Created Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($assigned_programs as $program): ?>
                                                        <tr>
                                                            <td class="text-truncate" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                                                <a href="<?php echo view_url('admin', 'programs/view_program.php', ['id' => $program['program_id']]); ?>" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                                                </a>
                                                            </td>
                                                            <td class="text-truncate" title="<?php echo htmlspecialchars($program['agency_name']); ?>">
                                                                <?php echo htmlspecialchars($program['agency_name']); ?>
                                                            </td>
                                                            <td>
                                                                <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div><div class="text-center mt-2">
                                            <a href="<?php echo view_url('admin', 'programs/programs.php', ['program_type' => 'assigned']); ?>" class="btn btn-sm btn-outline-success">
                                                View All Assigned Programs <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Agency Created Programs Section -->
                            <div class="col-md-6">
                                <div class="p-3 border rounded h-100 bg-light">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-info me-2" style="min-width: 90px;">Agency</span>
                                        <span class="fw-bold">Latest Agency-Created Programs</span>
                                        <span class="badge bg-secondary ms-auto">Total: <?php echo $agency_count; ?></span>
                                    </div>
                                    <?php if (empty($agency_programs)): ?>
                                        <div class="alert alert-light">
                                            <i class="fas fa-info-circle me-2"></i>No agency-created programs found.
                                        </div>
                                    <?php else: ?>                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover table-custom" style="table-layout: fixed; width: 100%; min-width: 500px;">
                                                <colgroup>
                                                    <col style="width: 45%">
                                                    <col style="width: 30%">
                                                    <col style="width: 25%">
                                                </colgroup>
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Program Name</th>
                                                        <th>Agency</th>
                                                        <th>Created Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($agency_programs as $program): ?>
                                                        <tr>
                                                            <td class="text-truncate" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                                                <a href="<?php echo view_url('admin', 'programs/view_program.php', ['id' => $program['program_id']]); ?>" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                                                </a>
                                                            </td>
                                                            <td class="text-truncate" title="<?php echo htmlspecialchars($program['agency_name']); ?>">
                                                                <?php echo htmlspecialchars($program['agency_name']); ?>
                                                            </td>
                                                            <td>
                                                                <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-center mt-2">
                                            <a href="<?php echo view_url('admin', 'programs/programs.php', ['program_type' => 'agency']); ?>" class="btn btn-sm btn-outline-info">
                                                View All Agency Programs <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>                    </div>
                </div>
            </div>
        </div>

        <!-- Outcomes Overview Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title m-0">Outcomes Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row gx-4 gy-4">                            <!-- Outcomes Statistics Cards -->
                            <div class="col-lg-3 col-md-6">
                                <div class="card bg-primary text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                        <h4><?php echo $outcomes_stats['total_outcomes']; ?></h4>
                                        <p class="mb-0">Total Outcomes</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-3 col-md-6">
                                <div class="card bg-success text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-check-square fa-3x mb-3"></i>
                                        <h4><?php echo $outcomes_stats['total_outcomes']; ?></h4>
                                        <p class="mb-0">Total Outcomes</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-3 col-md-6">
                                <div class="card bg-warning text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                                        <h4><?php echo $outcomes_stats['draft_outcomes']; ?></h4>
                                        <p class="mb-0">Drafts</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-3 col-md-6">
                                <div class="card bg-info text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-building fa-3x mb-3"></i>
                                        <h4><?php echo $outcomes_stats['sectors_with_outcomes']; ?></h4>
                                        <p class="mb-0">Sectors</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Outcomes Actions -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="p-3 border rounded h-100 bg-light">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-primary me-2" style="min-width: 90px;">Manage</span>
                                        <span class="fw-bold">Outcomes Management</span>
                                    </div>
                                    <p class="text-muted mb-3">View, edit, and manage all outcomes data across sectors</p>                                    <div class="d-flex gap-2">
                                        <a href="<?php echo view_url('admin', 'outcomes/manage_outcomes.php'); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-cogs me-1"></i> Manage Outcomes
                                        </a>
                                        <a href="<?php echo view_url('admin', 'outcomes/create_outcome_flexible.php'); ?>" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-plus-circle me-1"></i> Create New
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-3 border rounded h-100 bg-light">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-info me-2" style="min-width: 90px;">Activity</span>
                                        <span class="fw-bold">Recent Outcomes Activity</span>
                                    </div>
                                    <?php if (empty($outcomes_stats['recent_outcomes'])): ?>
                                        <div class="alert alert-light">
                                            <i class="fas fa-info-circle me-2"></i>No recent outcomes activity found.
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach (array_slice($outcomes_stats['recent_outcomes'], 0, 3) as $outcome): ?>
                                                <div class="list-group-item px-0 py-2 border-0">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($outcome['table_name']); ?></h6>
                                                            <small class="text-muted"><?php echo htmlspecialchars($outcome['sector_name'] ?? 'Unknown Sector'); ?></small>
                                                        </div>
                                                        <span class="badge bg-<?php echo $outcome['is_draft'] ? 'warning' : 'success'; ?>">
                                                            <?php echo $outcome['is_draft'] ? 'Draft' : 'Submitted'; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>                                        <div class="text-center mt-2">
                                            <a href="<?php echo view_url('admin', 'outcomes/outcome_history.php'); ?>" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-history me-1"></i> View All Activity <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title m-0">Recent Submissions</h5>
                </div>
                <div class="card-body" data-period-content="submissions_section">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Agency</th>
                                    <th>Program</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_submissions)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-3">No recent submissions for this period</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_submissions as $submission): ?>
                                        <tr>
                                            <td><?php echo $submission['agency_name']; ?></td>
                                            <td><?php echo $submission['program_name']; ?></td>
                                            <td><?php echo date('M j, g:i a', strtotime($submission['submission_date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>

