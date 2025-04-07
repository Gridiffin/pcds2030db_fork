<?php
/**
 * Agency Dashboard
 * 
 * Main dashboard for agency users showing program stats and submission status.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';
require_once '../../includes/status_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Agency Dashboard';

// Get current reporting period
$current_period = get_current_reporting_period();

// Add period_id handling for historical views
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);
$viewing_period = $period_id ? get_reporting_period($period_id) : $current_period;

// Get agency programs
$programs_by_type = get_agency_programs_by_type($period_id);
$programs = array_merge($programs_by_type['assigned'], $programs_by_type['created']);

// Get agency submission status
$submission_status = get_agency_submission_status($_SESSION['user_id'], $period_id);

// Get agency sector name
$agency_sector = get_sector_name($_SESSION['sector_id']);

// Prepare program status data for chart
$program_status_data = [
    'on-track' => $submission_status['program_status']['on-track'] ?? 0,
    'delayed' => $submission_status['program_status']['delayed'] ?? 0,
    'completed' => $submission_status['program_status']['completed'] ?? 0,
    'not-started' => $submission_status['program_status']['not-started'] ?? 0
];

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/agency.css'
];

// Additional scripts
$additionalScripts = [
    'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js',
    APP_URL . '/assets/js/utilities/status_utils.js',
    APP_URL . '/assets/js/agency/dashboard.js',
    APP_URL . '/assets/js/period_selector.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<!-- Page Header -->
<div class="page-header pb-10">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-title">
                    <h3>Agency Dashboard</h3>
                    <p class="text-subtitle text-white">
                        <?php echo $_SESSION['agency_name']; ?> - <?php echo $agency_sector; ?> Sector
                    </p>
                </div>
            </div>
            <div class="col-auto">
                <button id="refreshPage" class="btn btn-light">
                    <i class="fas fa-sync-alt me-1"></i> Refresh Data
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Content -->
<section class="section">
    <div class="container-fluid">
        <!-- Period Selector Component -->
        <?php require_once '../../includes/period_selector.php'; ?>

        <!-- Current Reporting Period Alert -->
        <?php if ($current_period): ?>
            <div class="alert alert-<?php echo $current_period['status'] === 'open' ? 'info' : 'secondary'; ?> mb-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <i class="fas fa-<?php echo $current_period['status'] === 'open' ? 'calendar-check' : 'calendar-minus'; ?> fa-2x"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1">
                            <?php echo $current_period['status'] === 'open' ? 'Active Reporting Period' : 'Next Reporting Period'; ?>
                        </h5>
                        <p class="mb-0">
                            Q<?php echo $current_period['quarter']; ?>-<?php echo $current_period['year']; ?>
                            (<?php echo date('M j, Y', strtotime($current_period['start_date'])); ?> - 
                            <?php echo date('M j, Y', strtotime($current_period['end_date'])); ?>)
                            
                            <?php if ($current_period['status'] === 'open'): ?>
                                <span class="ms-2 badge bg-success">Open for Submissions</span>
                            <?php else: ?>
                                <span class="ms-2 badge bg-secondary">Closed</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No active reporting period found. Please contact the administrator.
            </div>
        <?php endif; ?>

        <!-- Dashboard Summary Cards -->
        <div data-period-content="stats_section">
            <div class="row">
                <!-- Programs Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card primary">
                        <div class="card-body">
                            <i class="fas fa-clipboard-list stat-icon"></i>
                            <div class="stat-title">Total Programs</div>
                            <div class="stat-value"><?php echo $submission_status['total_programs'] ?? 0; ?></div>
                            <div class="stat-subtitle">
                                <i class="fas fa-check me-1"></i>
                                <?php echo $submission_status['programs_submitted'] ?? 0; ?> Programs Submitted
                            </div>
                        </div>
                    </div>
                </div>

                <!-- On Track Programs Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card success">
                        <div class="card-body">
                            <i class="fas fa-check-circle stat-icon"></i>
                            <div class="stat-title">On Track Programs</div>
                            <div class="stat-value"><?php echo $program_status_data['on-track']; ?></div>
                            <?php if ($submission_status['total_programs'] > 0): ?>
                            <div class="stat-subtitle">
                                <i class="fas fa-chart-line me-1"></i>
                                <?php echo round(($program_status_data['on-track'] / $submission_status['total_programs']) * 100); ?>% of total
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Delayed Programs Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card warning">
                        <div class="card-body">
                            <i class="fas fa-exclamation-triangle stat-icon"></i>
                            <div class="stat-title">Delayed Programs</div>
                            <div class="stat-value"><?php echo $program_status_data['delayed']; ?></div>
                            <?php if ($submission_status['total_programs'] > 0): ?>
                            <div class="stat-subtitle">
                                <i class="fas fa-chart-line me-1"></i>
                                <?php echo round(($program_status_data['delayed'] / $submission_status['total_programs']) * 100); ?>% of total
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Completed Programs Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card info">
                        <div class="card-body">
                            <i class="fas fa-trophy stat-icon"></i>
                            <div class="stat-title">Completed Programs</div>
                            <div class="stat-value"><?php echo $program_status_data['completed']; ?></div>
                            <?php if ($submission_status['total_programs'] > 0): ?>
                            <div class="stat-subtitle">
                                <i class="fas fa-chart-line me-1"></i>
                                <?php echo round(($program_status_data['completed'] / $submission_status['total_programs']) * 100); ?>% of total
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="row">
            <!-- Program Status Chart -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Program Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:250px; width:100%">
                            <canvas id="programStatusChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            <span class="me-2">
                                <i class="fas fa-circle text-success"></i> On Track
                            </span>
                            <span class="me-2">
                                <i class="fas fa-circle text-warning"></i> Delayed
                            </span>
                            <span class="me-2">
                                <i class="fas fa-circle text-info"></i> Completed
                            </span>
                            <span>
                                <i class="fas fa-circle text-secondary"></i> Not Started
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Program Updates -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-white">Recent Program Updates</h6>
                        <a href="view_programs.php" class="btn btn-sm btn-primary">
                            View All Programs
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($programs)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p>No programs found for your agency.</p>
                                <a href="view_programs.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus-circle me-1"></i> Create Program
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Program</th>
                                            <th>Target</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // Sort programs by updated_at
                                        usort($programs, function($a, $b) {
                                            return strtotime($b['updated_at'] ?? $b['created_at']) - strtotime($a['updated_at'] ?? $a['created_at']);
                                        });
                                        
                                        // Display up to 5 most recent programs
                                        $recent_programs = array_slice($programs, 0, 5);
                                        
                                        foreach ($recent_programs as $program): 
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-medium"><?php echo $program['program_name']; ?></div>
                                                    <div class="small text-muted">
                                                        <?php if ($program['updated_at']): ?>
                                                            Updated: <?php echo date('M j, Y', strtotime($program['updated_at'])); ?>
                                                        <?php else: ?>
                                                            Created: <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td><?php echo $program['current_target'] ?? 'Not set'; ?></td>
                                                <td>
                                                    <?php echo get_status_badge($program['status'] ?? 'not-started'); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="program_details.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-primary" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="update_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-secondary" title="Update Status">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if (count($programs) > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="view_programs.php" class="btn btn-outline-primary btn-sm">
                                        View All <?php echo count($programs); ?> Programs <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Programs Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0"><i class="fas fa-tasks me-2"></i>Recent Programs</h5>
                    <a href="view_programs.php" class="btn btn-sm btn-outline-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_programs)): ?>
                        <div class="text-center py-5 px-4">
                            <div class="mb-3">
                                <i class="fas fa-project-diagram fa-3x text-muted"></i>
                            </div>
                            <h5>No programs found</h5>
                            <p class="text-muted">You don't have any programs assigned or created yet.</p>
                            <a href="create_program.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-1"></i> Create New Program
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive w-100">
                            <table class="table table-hover table-custom mb-0" style="width: 100%;">
                                <!-- ...existing code... -->
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Other Sectors Programs Card -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0"><i class="fas fa-sitemap me-2"></i>Other Sectors Programs</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($other_sectors_programs)): ?>
                        <div class="text-center py-5 px-4">
                            <div class="mb-3">
                                <i class="fas fa-sitemap fa-3x text-muted"></i>
                            </div>
                            <h5>No programs from other sectors</h5>
                            <p class="text-muted">There are no programs from other sectors available for viewing.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive w-100">
                            <table class="table table-hover table-custom mb-0" style="width: 100%;">
                                <!-- ...existing code... -->
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pass data to chart -->
<script>
    // Initialize with current data
    const programStatusChartData = {
        data: [
            <?php echo $program_status_data['on-track']; ?>,
            <?php echo $program_status_data['delayed']; ?>,
            <?php echo $program_status_data['completed']; ?>,
            <?php echo $program_status_data['not-started']; ?>
        ],
        colors: ['#28a745', '#ffc107', '#17a2b8', '#6c757d']
    };
    
    // Function to update chart after AJAX load
    function initProgramStatusChart() {
        const ctx = document.getElementById('programStatusChart');
        
        // Destroy existing chart if it exists
        if (Chart.getChart(ctx)) {
            Chart.getChart(ctx).destroy();
        }
        
        // Create new chart
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['On Track', 'Delayed', 'Completed', 'Not Started'],
                datasets: [{
                    data: programStatusChartData.data,
                    backgroundColor: programStatusChartData.colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%'
            }
        });
    }
    
    // Initialize chart on page load
    document.addEventListener('DOMContentLoaded', initProgramStatusChart);
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
