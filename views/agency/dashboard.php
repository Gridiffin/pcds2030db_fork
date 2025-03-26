<?php
/**
 * Agency Dashboard
 * 
 * Main interface for agency users.
 * Shows overview of their programs and reporting requirements.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Agency Dashboard';

// Get agency's programs
$programs = get_agency_programs();

// Get current reporting period
$current_period = get_current_reporting_period();

// Add period_id handling for historical views
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);
$viewing_period = $period_id ? get_reporting_period($period_id) : $current_period;

// Get agency's submission status for the selected period
$submission_status = get_agency_submission_status($_SESSION['user_id'], $period_id);

// Get agency's sector metrics
$metrics = get_agency_sector_metrics($_SESSION['sector_id'], $period_id); // Update this function to accept period_id

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/agency.css'
];

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/charts/chart.min.js',
    APP_URL . '/assets/js/agency/dashboard_charts.js',
    APP_URL . '/assets/js/agency/dashboard.js',
    APP_URL . '/assets/js/period_selector.js' // Add period selector script
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">Agency Dashboard</h1>
            <p class="text-muted">Welcome, <?php echo $_SESSION['agency_name']; ?></p>
        </div>
        <div class="d-flex align-items-center">
            <button class="btn btn-sm btn-outline-primary ms-2" id="refreshPage">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Period Selector Component -->
    <?php require_once '../../includes/period_selector.php'; ?>

    <!-- Quick Actions Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center text-center g-3">
                        <?php if ($current_period && $current_period['status'] === 'open'): ?>
                            <div class="col-lg-3 col-md-4 col-6">
                                <a href="view_programs.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                    <i class="fas fa-edit fa-lg mb-2"></i>
                                    <span>Manage Programs</span>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6">
                                <a href="submit_metrics.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                    <i class="fas fa-chart-line fa-lg mb-2"></i>
                                    <span>Submit Sector Metrics</span>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="col-lg-3 col-md-4 col-6">
                            <a href="view_programs.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-project-diagram fa-lg mb-2"></i>
                                <span>View Programs</span>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-4 col-6">
                            <a href="view_programs.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" 
                               data-target-modal="createProgramModal">
                                <i class="fas fa-plus-circle fa-lg mb-2"></i>
                                <span>Create New Program</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submission Status & Stats -->
    <?php if ($current_period): ?>
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title m-0">Current Submission Status</h5>
                </div>
                <div class="card-body pb-0">
                    <?php if ($submission_status): ?>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="card submission-card">
                                    <div class="card-body text-center">
                                        <div class="display-4 mb-2"><?php echo $submission_status['programs_submitted']; ?>/<?php echo $submission_status['total_programs']; ?></div>
                                        <div class="progress mb-3" style="height: 10px;">
                                            <?php $program_percent = $submission_status['total_programs'] > 0 ? ($submission_status['programs_submitted'] / $submission_status['total_programs']) * 100 : 0; ?>
                                            <div class="progress-bar <?php echo $program_percent == 100 ? 'bg-success' : 'bg-primary'; ?>" role="progressbar" style="width: <?php echo $program_percent; ?>%" aria-valuenow="<?php echo $program_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <h6 class="mb-0">Programs Submitted</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="card submission-card">
                                    <div class="card-body text-center">
                                        <div class="display-4 mb-2"><?php echo $submission_status['metrics_submitted']; ?>/<?php echo $submission_status['total_metrics']; ?></div>
                                        <div class="progress mb-3" style="height: 10px;">
                                            <?php $metrics_percent = $submission_status['total_metrics'] > 0 ? ($submission_status['metrics_submitted'] / $submission_status['total_metrics']) * 100 : 0; ?>
                                            <div class="progress-bar <?php echo $metrics_percent == 100 ? 'bg-success' : 'bg-primary'; ?>" role="progressbar" style="width: <?php echo $metrics_percent; ?>%" aria-valuenow="<?php echo $metrics_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <h6 class="mb-0">Metrics Submitted</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center pb-3">
                            <?php if ($program_percent == 100 && $metrics_percent == 100): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>All submissions complete for Q<?php echo $current_period['quarter']; ?>-<?php echo $current_period['year']; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Please complete all submissions before <?php echo date('F j, Y', strtotime($current_period['end_date'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-calendar-alt fa-3x text-muted"></i>
                            </div>
                            <h5>No submission data</h5>
                            <p class="text-muted">Start submitting your program data for the current period.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title m-0">Program Status Overview</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($programs)): ?>
                        <canvas id="programStatusChart" height="200"></canvas>
                        <div class="d-flex justify-content-center mt-3">
                            <div class="d-flex align-items-center me-3">
                                <span class="status-dot bg-success me-2"></span>
                                <span class="small">On Track</span>
                            </div>
                            <div class="d-flex align-items-center me-3">
                                <span class="status-dot bg-warning me-2"></span>
                                <span class="small">Delayed</span>
                            </div>
                            <div class="d-flex align-items-center me-3">
                                <span class="status-dot bg-info me-2"></span>
                                <span class="small">Completed</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="status-dot bg-secondary me-2"></span>
                                <span class="small">Not Started</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-project-diagram fa-3x text-muted"></i>
                            </div>
                            <h5>No programs found</h5>
                            <p class="text-muted">Contact the administrator to assign programs to your agency.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Your Programs Table -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">Your Programs</h5>
                    <a href="view_programs.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($programs)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                                <thead>
                                    <tr>
                                        <th>Program Name</th>
                                        <th>Targets</th>
                                        <th>Status</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $count = 0;
                                    foreach ($programs as $program): 
                                        if ($count++ >= 5) break; // Show only 5 programs
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="program_details.php?id=<?php echo $program['program_id']; ?>">
                                                    <?php echo $program['program_name']; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if(isset($program['current_target']) && $program['current_target']): ?>
                                                    <span class="text-primary"><?php echo $program['current_target']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted"><em>No target set</em></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status_class = '';
                                                    switch($program['status'] ?? 'not-started') {
                                                        case 'on-track': $status_class = 'success'; break;
                                                        case 'delayed': $status_class = 'warning'; break;
                                                        case 'completed': $status_class = 'info'; break;
                                                        default: $status_class = 'secondary';
                                                    }
                                                ?>
                                                <span class="badge bg-<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($program['status'] ?? 'Not Started'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo isset($program['updated_at']) ? date('M j, Y', strtotime($program['updated_at'])) : 'N/A'; ?></td>
                                            <td>
                                                <?php if ($current_period && $current_period['status'] === 'open'): ?>
                                                    <a href="submit_program_data.php?id=<?php echo $program['program_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i> Update
                                                    </a>
                                                <?php else: ?>
                                                    <a href="program_details.php?id=<?php echo $program['program_id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($programs) > 5): ?>
                            <div class="text-center mt-3">
                                <a href="view_programs.php" class="btn btn-outline-primary btn-sm">View All Programs</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-project-diagram fa-3x text-muted"></i>
                            </div>
                            <h5>No programs found</h5>
                            <p class="text-muted">Contact the administrator to assign programs to your agency.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sector Information -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title m-0">Sector Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h4 class="h6 text-uppercase text-muted mb-2">Your Sector</h4>
                        <h3 class="h5 mb-0"><?php echo get_sector_name($_SESSION['sector_id']); ?></h3>
                    </div>
                    
                    <div class="mb-3">
                        <h4 class="h6 text-uppercase text-muted mb-2">Required Metrics</h4>
                        <?php if (!empty($metrics)): ?>
                            <ul class="list-group list-group-flush">
                                <?php 
                                $count = 0;
                                foreach ($metrics as $metric): 
                                    if ($count++ >= 5) break; // Show only 5 metrics
                                ?>
                                    <li class="list-group-item d-flex align-items-center p-2">
                                        <span class="metric-icon me-2">
                                            <i class="fas fa-chart-line"></i>
                                        </span>
                                        <span class="flex-grow-1"><?php echo $metric['metric_name']; ?></span>
                                        <?php if(isset($metric['is_submitted']) && $metric['is_submitted']): ?>
                                            <span class="badge bg-success">Submitted</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (count($metrics) > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="submit_metrics.php" class="btn btn-outline-primary btn-sm w-100">View All Metrics</a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted">No metrics defined for your sector yet.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4">
                        <h4 class="h6 text-uppercase text-muted mb-3">Need Help?</h4>
                        <div class="d-grid gap-2">
                            <a href="<?php echo APP_URL; ?>/help.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-question-circle me-1"></i> View Help Documentation
                            </a>
                            <a href="<?php echo APP_URL; ?>/contact.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-envelope me-1"></i> Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pass chart data to JavaScript -->
<script>
    // Prepare program status data
    const programStatusData = {
        labels: ['On Track', 'Delayed', 'Completed', 'Not Started'],
        data: [
            <?php echo $submission_status['program_status']['on-track'] ?? 0; ?>,
            <?php echo $submission_status['program_status']['delayed'] ?? 0; ?>,
            <?php echo $submission_status['program_status']['completed'] ?? 0; ?>,
            <?php echo $submission_status['program_status']['not-started'] ?? 0; ?>
        ],
        colors: ['#28a745', '#ffc107', '#17a2b8', '#6c757d']
    };

    // Add this to your existing script
    document.addEventListener('DOMContentLoaded', function() {
        // Handle buttons that should open modals on the programs page
        const modalButtons = document.querySelectorAll('[data-target-modal]');
        modalButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetUrl = this.getAttribute('href');
                const targetModal = this.getAttribute('data-target-modal');
                
                // Redirect to programs page with modal param
                window.location.href = targetUrl + '?modal=' + targetModal;
            });
        });
    });
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
