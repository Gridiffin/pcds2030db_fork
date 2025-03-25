<?php
/**
 * Admin Dashboard
 * 
 * Main interface for admin users (Ministry staff).
 * Shows overview of all sectors, agencies, and reporting status.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin_functions.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Admin Dashboard';

// Get dashboard statistics
$stats = get_admin_dashboard_stats();

// Get current reporting period
$current_period = get_current_reporting_period();

// Get agency submission status
$agency_submissions = get_agency_submission_status($current_period['period_id'] ?? null);

// Get recent programs
$recent_programs = get_recent_programs(5);

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/admin.css'
];

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/charts/chart.min.js',
    APP_URL . '/assets/js/charts/admin_dashboard_charts.js'
];

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';
?>

<!-- Main content wrapper modification -->
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">Ministry Dashboard</h1>
            <p class="text-muted mb-0">Overview of all agency reporting activities</p>
        </div>
        <div class="d-flex align-items-center">
            <?php if ($current_period): ?>
                <div class="me-2 text-end">
                    <small class="d-block text-muted mb-1">Current Reporting Period</small>
                    <span class="badge bg-success">Q<?php echo $current_period['quarter']; ?>-<?php echo $current_period['year']; ?></span>
                    <span class="badge <?php echo $current_period['status'] === 'open' ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo ucfirst($current_period['status']); ?>
                    </span>
                </div>
            <?php else: ?>
                <div class="me-2 text-end">
                    <small class="d-block text-muted mb-1">Reporting Period</small>
                    <span class="badge bg-warning">No active period</span>
                </div>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-primary ms-2" id="refreshPage">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Quick Actions Section (Improved) -->
    <div class="row mb-4 quick-actions-container">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center text-center g-3">
                        <div class="col-lg-2 col-md-4 col-6">
                            <a href="add_program.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-plus-circle fa-lg mb-2"></i>
                                <span>New Program</span>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <a href="reporting_periods.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-calendar-alt fa-lg mb-2"></i>
                                <span>Manage Periods</span>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <a href="generate_report.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-file-powerpoint fa-lg mb-2"></i>
                                <span>Generate Report</span>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <a href="manage_users.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-users-cog fa-lg mb-2"></i>
                                <span>Manage Users</span>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <a href="export_data.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-file-excel fa-lg mb-2"></i>
                                <span>Export Data</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview (Improved) -->
    <div class="row mb-4 g-3">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs text-uppercase text-muted mb-1">Agencies</div>
                            <div class="d-flex align-items-end">
                                <h3 class="mb-0 me-2"><?php echo $stats['total_agencies'] ?? 0; ?></h3>
                                <small class="text-success"><i class="fas fa-check"></i> Active</small>
                            </div>
                        </div>
                        <div class="stat-icon-container">
                            <i class="fas fa-building stat-icon"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="agencies.php" class="btn btn-sm btn-outline-primary w-100">Manage</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs text-muted text-uppercase mb-1">Total Programs</div>
                            <div class="h3 mb-0 text-dark"><?php echo $stats['total_programs'] ?? 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-project-diagram stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card secondary h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs text-muted text-uppercase mb-1">Submissions Complete</div>
                            <div class="h3 mb-0 text-dark"><?php echo $stats['submissions_complete'] ?? 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs text-muted text-uppercase mb-1">Pending Submissions</div>
                            <div class="h3 mb-0 text-dark"><?php echo $stats['submissions_pending'] ?? 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row">
        <!-- Submission Status -->
        <div class="col-lg-8 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">Agency Submission Status</h5>
                        <?php if (!empty($agency_submissions)): ?>
                            <button class="btn btn-sm btn-outline-light" id="refreshSubmissions">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($current_period): ?>
                        <?php if (!empty($agency_submissions)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th>Agency</th>
                                            <th>Sector</th>
                                            <th>Programs</th>
                                            <th>Metrics</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($agency_submissions as $agency): ?>
                                            <tr>
                                                <td><?php echo $agency['agency_name']; ?></td>
                                                <td><?php echo $agency['sector_name']; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 15px;">
                                                        <?php $programPercent = ($agency['programs_submitted'] / $agency['total_programs']) * 100; ?>
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                             style="width: <?php echo $programPercent; ?>%;" 
                                                             aria-valuenow="<?php echo $programPercent; ?>" 
                                                             aria-valuemin="0" aria-valuemax="100">
                                                             <?php echo $agency['programs_submitted']; ?>/<?php echo $agency['total_programs']; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 15px;">
                                                        <?php $metricPercent = ($agency['metrics_submitted'] / $agency['total_metrics']) * 100; ?>
                                                        <div class="progress-bar bg-info" role="progressbar" 
                                                             style="width: <?php echo $metricPercent; ?>%;" 
                                                             aria-valuenow="<?php echo $metricPercent; ?>" 
                                                             aria-valuemin="0" aria-valuemax="100">
                                                             <?php echo $agency['metrics_submitted']; ?>/<?php echo $agency['total_metrics']; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($programPercent == 100 && $metricPercent == 100): ?>
                                                        <span class="badge bg-success">Complete</span>
                                                    <?php elseif ($programPercent > 0 || $metricPercent > 0): ?>
                                                        <span class="badge bg-warning">In Progress</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Not Started</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">No agencies have submitted data yet.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No active reporting period. 
                            <a href="reporting_periods.php">Create a new reporting period</a> to start collecting data.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Program Status Chart -->
        <div class="col-lg-4 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-header">
                    <h5 class="card-title m-0">Program Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="programStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Programs -->
        <div class="col-lg-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">Recent Programs</h5>
                    <a href="programs.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_programs)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th>Program Name</th>
                                        <th>Sector</th>
                                        <th>Agency</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_programs as $program): ?>
                                        <tr>
                                            <td>
                                                <a href="program_details.php?id=<?php echo $program['program_id']; ?>">
                                                    <?php echo $program['program_name']; ?>
                                                </a>
                                            </td>
                                            <td><?php echo $program['sector_name']; ?></td>
                                            <td><?php echo $program['agency_name']; ?></td>
                                            <td>
                                                <?php 
                                                    $status_class = '';
                                                    switch($program['status']) {
                                                        case 'on-track': $status_class = 'success'; break;
                                                        case 'delayed': $status_class = 'warning'; break;
                                                        case 'completed': $status_class = 'info'; break;
                                                        default: $status_class = 'secondary';
                                                    }
                                                ?>
                                                <span class="badge bg-<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($program['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No programs have been created yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sector Metrics -->
        <div class="col-lg-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">Sector Overview</h5>
                    <a href="sectors.php" class="btn btn-sm btn-outline-primary">Manage Sectors</a>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <canvas id="sectorProgramsChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pass data to charts -->
<script>
    const programStatusData = <?php echo json_encode($stats['program_status'] ?? []); ?>;
    const sectorProgramsData = <?php echo json_encode($stats['sector_programs'] ?? []); ?>;
    
    // Add a variable to track if there's an active reporting period
    const hasActivePeriod = <?php echo $current_period ? 'true' : 'false'; ?>;
</script>

<!-- Add additional script for dashboard functionality -->
<script src="<?php echo APP_URL; ?>/assets/js/admin/dashboard.js"></script>

<?php
// Include footer - important to close container properly
require_once '../layouts/footer.php';
?>
