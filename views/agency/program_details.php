<?php
/**
 * Program Details Page
 * 
 * Displays detailed information about a specific program.
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

// Check if program ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: view_programs.php');
    exit;
}

$program_id = intval($_GET['id']);

// Get program details
$program = get_program_details($program_id);
if (!$program) {
    header('Location: view_programs.php');
    exit;
}

// Set page title
$pageTitle = 'Program Details: ' . $program['program_name'];

// Get current reporting period
$current_period = get_current_reporting_period();

// Organize submissions by reporting period
$submissions_by_period = [];
if (!empty($program['submissions'])) {
    foreach ($program['submissions'] as $submission) {
        $period_key = $submission['year'] . '-Q' . $submission['quarter'];
        $submissions_by_period[$period_key] = $submission;
    }
    
    // Sort periods chronologically
    krsort($submissions_by_period);
}

// Additional styles and scripts
$additionalStyles = [
    APP_URL . '/assets/css/custom/agency.css'
];

$additionalScripts = [
    APP_URL . '/assets/js/charts/chart.min.js',
    APP_URL . '/assets/js/agency/program_details.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0"><?php echo $program['program_name']; ?></h1>
        <p class="text-muted">Program Details</p>
    </div>
    <div>
        <?php if ($current_period && $current_period['status'] === 'open'): ?>
            <a href="submit_program_data.php?id=<?php echo $program_id; ?>" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Submit Data
            </a>
        <?php endif; ?>
        <a href="view_programs.php" class="btn btn-outline-secondary ms-2">
            <i class="fas fa-arrow-left me-1"></i> Back to Programs
        </a>
    </div>
</div>

<!-- Program Overview Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Program Overview</h5>
        <span class="badge bg-<?php 
            $latest_status = $program['submissions'][0]['status'] ?? 'not-started';
            switch($latest_status) {
                case 'on-track': echo 'success'; break;
                case 'delayed': echo 'warning'; break;
                case 'completed': echo 'info'; break;
                default: echo 'secondary';
            }
        ?>">
            <?php echo ucwords(str_replace('-', ' ', $latest_status)); ?>
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h6 class="text-muted mb-3">Description</h6>
                <p><?php echo $program['description'] ?? 'No description available.'; ?></p>
                
                <?php if ($program['objectives']): ?>
                    <h6 class="text-muted mb-3 mt-4">Objectives</h6>
                    <p><?php echo $program['objectives']; ?></p>
                <?php endif; ?>
                
                <?php if ($program['outcomes']): ?>
                    <h6 class="text-muted mb-3 mt-4">Expected Outcomes</h6>
                    <p><?php echo $program['outcomes']; ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted">Program Details</h6>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">Timeline</small>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar me-2 text-primary"></i>
                                <span>
                                    <?php echo date('M j, Y', strtotime($program['start_date'])); ?> - 
                                    <?php echo date('M j, Y', strtotime($program['end_date'])); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">Sector</small>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-layer-group me-2 text-primary"></i>
                                <span><?php echo $program['sector_name']; ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($program['budget'])): ?>
                        <div class="mb-3">
                            <small class="text-muted d-block">Budget</small>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-dollar-sign me-2 text-primary"></i>
                                <span><?php echo number_format($program['budget'], 2); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <small class="text-muted d-block">Created</small>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock me-2 text-primary"></i>
                                <span><?php echo date('M j, Y', strtotime($program['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submission History -->
<?php if (!empty($program['submissions'])): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">Submission History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-custom">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Target</th>
                        <th>Achievement</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions_by_period as $period => $submission): ?>
                        <tr>
                            <td>
                                <strong><?php echo $period; ?></strong><br>
                                <small class="text-muted"><?php echo date('M j, Y', strtotime($submission['created_at'])); ?></small>
                            </td>
                            <td><?php echo $submission['target']; ?></td>
                            <td><?php echo $submission['achievement']; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    switch($submission['status']) {
                                        case 'on-track': echo 'success'; break;
                                        case 'delayed': echo 'warning'; break;
                                        case 'completed': echo 'info'; break;
                                        default: echo 'secondary';
                                    }
                                ?>">
                                    <?php echo ucwords(str_replace('-', ' ', $submission['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo $submission['remarks'] ?: '-'; ?></td>
                            <td><?php echo date('M j, Y', strtotime($submission['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Progress Visualization -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">Progress Visualization</h5>
    </div>
    <div class="card-body">
        <canvas id="progressChart" height="300"></canvas>
    </div>
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    No submission data available for this program yet. Start submitting data during an active reporting period.
</div>
<?php endif; ?>

<!-- Pass data to JavaScript for charts -->
<script>
    // Convert PHP data to JSON for charts
    const programData = <?php echo json_encode([
        'name' => $program['program_name'],
        'submissions' => array_values($submissions_by_period)
    ]); ?>;
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
