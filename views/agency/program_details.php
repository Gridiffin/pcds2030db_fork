<?php
/**
 * Program Details
 * 
 * Interface for agency users to view program details and submission history.
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

// Get program ID from URL
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$program_id) {
    $_SESSION['message'] = 'No program specified.';
    $_SESSION['message_type'] = 'warning';
    header('Location: view_programs.php');
    exit;
}

// Get program details
$program = get_program_details($program_id);

if (!$program || isset($program['error'])) {
    $_SESSION['message'] = $program['error'] ?? 'Program not found or access denied.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get message from session if available
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'info';

// Clear message from session
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Organize submissions by period for display
$submissions_by_period = [];
foreach ($program['submissions'] as $submission) {
    $period_name = "Q{$submission['quarter']}-{$submission['year']}";
    $submissions_by_period[$period_name] = $submission;
}

// Get current reporting period
$current_period = get_current_reporting_period();

// Set page title
$pageTitle = 'Program Details';

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/agency.css'
];

// Additional scripts
$additionalScripts = [
    'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js',
    APP_URL . '/assets/js/utilities/status_utils.js',
    APP_URL . '/assets/js/agency/program_details.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Program Details</h1>
        <p class="text-muted">View and manage program information</p>
    </div>
    <div>
        <?php if ($current_period && $current_period['status'] === 'open'): ?>
            <a href="update_program.php?id=<?php echo $program_id; ?>" class="btn btn-primary me-2">
                <i class="fas fa-edit me-1"></i> Update Program
            </a>
        <?php endif; ?>
        <a href="view_programs.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Programs
        </a>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <div><?php echo $message; ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Program Information Cards -->
<div class="row">
    <!-- Program Main Info -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0"><?php echo htmlspecialchars($program['program_name']); ?></h5>
                <span class="badge bg-<?php echo $program['is_assigned'] ? 'primary' : 'success'; ?>">
                    <?php echo $program['is_assigned'] ? 'Assigned Program' : 'Agency Created'; ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="text-muted mb-3">Description</h6>
                        <p><?php echo !empty($program['description']) ? htmlspecialchars($program['description']) : 'No description available.'; ?></p>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-item">
                                <small class="text-muted d-block">Sector</small>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-layer-group me-2 text-primary"></i>
                                    <span><?php echo htmlspecialchars($program['sector_name']); ?></span>
                                </div>
                            </div>
                            
                            <?php if ($program['start_date']): ?>
                            <div class="info-item">
                                <small class="text-muted d-block">Start Date</small>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                    <span><?php echo date('M j, Y', strtotime($program['start_date'])); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($program['end_date']): ?>
                            <div class="info-item">
                                <small class="text-muted d-block">End Date</small>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-check me-2 text-primary"></i>
                                    <span><?php echo date('M j, Y', strtotime($program['end_date'])); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <small class="text-muted d-block">Created On</small>
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
    
    <!-- Current Status -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title m-0">Current Status</h5>
            </div>
            <div class="card-body text-center">
                <?php 
                $latest_status = 'not-started';
                $latest_target = 'Not set';
                $latest_achievement = 'Not reported';
                $latest_target_date = null;
                $latest_status_date = null;
                
                if (!empty($program['submissions'])) {
                    $latest = $program['submissions'][0]; // First item is latest due to ordering
                    $latest_status = $latest['status'];
                    $latest_target = $latest['target'];
                    $latest_achievement = !empty($latest['achievement']) ? $latest['achievement'] : 'Not reported';
                    $latest_target_date = $latest['target_date'] ?? null;
                    $latest_status_date = $latest['status_date'] ?? null;
                }
                ?>
                
                <div class="status-badge mb-4">
                    <div class="status-pill large <?php echo $latest_status; ?> active">
                        <i class="fas fa-<?php 
                            switch($latest_status) {
                                case 'on-track': echo 'check-circle'; break;
                                case 'delayed': echo 'exclamation-triangle'; break;
                                case 'completed': echo 'flag-checkered'; break;
                                default: echo 'hourglass-start';
                            }
                        ?> me-2"></i>
                        <?php echo ucwords(str_replace('-', ' ', $latest_status)); ?>
                    </div>
                </div>
                
                <div class="target-info mb-4">
                    <h6 class="text-muted">TARGET</h6>
                    <div class="h4"><?php echo htmlspecialchars($latest_target); ?></div>
                    <?php if ($latest_target_date): ?>
                        <div class="small text-muted">
                            Target Date: <?php echo date('M j, Y', strtotime($latest_target_date)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="achievement-info">
                    <h6 class="text-muted">ACHIEVEMENT</h6>
                    <div class="h4"><?php echo htmlspecialchars($latest_achievement); ?></div>
                    <?php if ($latest_status_date): ?>
                        <div class="small text-muted">
                            Last Updated: <?php echo date('M j, Y', strtotime($latest_status_date)); ?>
                        </div>
                    <?php endif; ?>
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
                        <th>Target Date</th>
                        <th>Achievement</th>
                        <th>Status</th>
                        <th>Status Date</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions_by_period as $period => $submission): ?>
                        <tr>
                            <td>
                                <strong><?php echo $period; ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($submission['target']); ?></td>
                            <td><?php echo $submission['target_date'] ? date('M j, Y', strtotime($submission['target_date'])) : 'Not set'; ?></td>
                            <td><?php echo !empty($submission['achievement']) ? htmlspecialchars($submission['achievement']) : '-'; ?></td>
                            <td>
                                <?php echo get_status_badge($submission['status']); ?>
                            </td>
                            <td><?php echo $submission['status_date'] ? date('M j, Y', strtotime($submission['status_date'])) : 'Not set'; ?></td>
                            <td><?php echo !empty($submission['remarks']) ? htmlspecialchars($submission['remarks']) : '-'; ?></td>
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
    // Prepare data for charts
    const submissionData = <?php echo json_encode(array_map(function($sub) {
        return [
            'period' => "Q{$sub['quarter']}-{$sub['year']}",
            'target' => $sub['target'],
            'achievement' => $sub['achievement'] ?? null,
            'status' => $sub['status']
        ];
    }, $program['submissions'] ?? [])); ?>;
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
