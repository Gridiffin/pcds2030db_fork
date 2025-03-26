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
$period_id = $current_period['period_id'] ?? null;

// Get agency programs
$programs_by_type = get_agency_programs_by_type();
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
    APP_URL . '/assets/js/agency/dashboard.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Agency Dashboard</h1>
        <p class="text-muted"><?php echo $_SESSION['agency_name']; ?> - <?php echo $agency_sector; ?> Sector</p>
    </div>
    <button id="refreshPage" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-sync-alt me-1"></i> Refresh Data
    </button>
</div>

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
<div class="row">
    <!-- Programs Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Programs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $submission_status['total_programs'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2 text-xs">
                    <span class="text-success">
                        <i class="fas fa-check me-1"></i>
                        <?php echo $submission_status['programs_submitted'] ?? 0; ?> Programs Submitted
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- On Track Programs Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            On Track Programs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $program_status_data['on-track']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
                <?php if ($submission_status['total_programs'] > 0): ?>
                    <div class="mt-2 text-xs">
                        <span class="text-success">
                            <?php $percent = round(($program_status_data['on-track'] / $submission_status['total_programs']) * 100); ?>
                            <i class="fas fa-chart-line me-1"></i>
                            <?php echo $percent; ?>% of total
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delayed Programs Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Delayed Programs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $program_status_data['delayed']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
                <?php if ($submission_status['total_programs'] > 0): ?>
                    <div class="mt-2 text-xs">
                        <span class="text-warning">
                            <?php $percent = round(($program_status_data['delayed'] / $submission_status['total_programs']) * 100); ?>
                            <i class="fas fa-chart-line me-1"></i>
                            <?php echo $percent; ?>% of total
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Completed Programs Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Completed Programs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $program_status_data['completed']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-trophy fa-2x text-gray-300"></i>
                    </div>
                </div>
                <?php if ($submission_status['total_programs'] > 0): ?>
                    <div class="mt-2 text-xs">
                        <span class="text-info">
                            <?php $percent = round(($program_status_data['completed'] / $submission_status['total_programs']) * 100); ?>
                            <i class="fas fa-chart-line me-1"></i>
                            <?php echo $percent; ?>% of total
                        </span>
                    </div>
                <?php endif; ?>
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
                <h6 class="m-0 font-weight-bold text-primary">Recent Program Updates</h6>
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
</div>

<!-- Pass data to chart -->
<script>
    const programStatusChartData = {
        data: [
            <?php echo $program_status_data['on-track']; ?>,
            <?php echo $program_status_data['delayed']; ?>,
            <?php echo $program_status_data['completed']; ?>,
            <?php echo $program_status_data['not-started']; ?>
        ],
        colors: ['#28a745', '#ffc107', '#17a2b8', '#6c757d']
    };
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
