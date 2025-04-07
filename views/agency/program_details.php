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

// Get program ID from URL with validation
$program_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$program_id) {
    $_SESSION['message'] = 'No program specified or invalid program ID.';
    $_SESSION['message_type'] = 'warning';
    header('Location: view_programs.php');
    exit;
}

// Use try-catch for error handling with database operations
try {
    // Get program details with optimized query
    $program = get_program_details($program_id);

    if (!$program || isset($program['error'])) {
        throw new Exception($program['error'] ?? 'Program not found or access denied.');
    }

    // Organize submissions by period for display (newest first)
    $submissions_by_period = [];
    if (!empty($program['submissions'])) {
        foreach ($program['submissions'] as $submission) {
            $period_name = "Q{$submission['quarter']}-{$submission['year']}";
            $submissions_by_period[$period_name] = $submission;
        }
    }

    // Get latest submission for current status display
    $latest = !empty($program['submissions']) ? $program['submissions'][0] : null;
    $latest_status = $latest ? $latest['status'] : 'not-started';
    $latest_target = $latest ? $latest['current_target'] : 'Not set';
    $latest_achievement = $latest && !empty($latest['achievement']) ? $latest['achievement'] : 'Not reported';
    $latest_status_date = $latest ? ($latest['status_date'] ?? null) : null;
    $latest_status_text = $latest ? ($latest['status_text'] ?? '') : '';

    // Get current reporting period
    $current_period = get_current_reporting_period();
} catch (Exception $e) {
    $_SESSION['message'] = 'Error: ' . $e->getMessage();
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

// Set page title
$pageTitle = htmlspecialchars($program['program_name']) . ' | Program Details';

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
        <h1 class="h2 mb-0"><?php echo htmlspecialchars($program['program_name']); ?></h1>
        <p class="text-muted mb-0">
            <span class="badge bg-<?php echo $program['is_assigned'] ? 'primary' : 'success'; ?> me-2">
                <?php echo $program['is_assigned'] ? 'Assigned Program' : 'Agency Created'; ?>
            </span>
            <span class="text-muted">
                <i class="fas fa-layer-group me-1"></i> <?php echo htmlspecialchars($program['sector_name']); ?>
            </span>
        </p>
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
<div class="row program-details">
    <!-- Program Main Info -->
    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title m-0"><i class="fas fa-info-circle me-2"></i>Program Information</h5>
            </div>
            <div class="card-body">
                <!-- Timeline Section -->
                <div class="info-group">
                    <h6>Timeline</h6>
                    <div class="d-flex align-items-center">
                        <div class="timeline-point">
                            <div class="small text-muted">Start Date</div>
                            <div class="timeline-date">
                                <?php echo $program['start_date'] ? date('M j, Y', strtotime($program['start_date'])) : '<span class="text-muted">Not specified</span>'; ?>
                            </div>
                        </div>
                        <div class="timeline-divider">
                            <div class="progress w-100">
                                <div class="progress-bar bg-<?php echo $latest_status === 'completed' ? 'primary' : 'success'; ?>" style="width: <?php echo $progress; ?>%;"></div>
                            </div>
                        </div>
                        <div class="timeline-point">
                            <div class="small text-muted">End Date</div>
                            <div class="timeline-date">
                                <?php echo $program['end_date'] ? date('M j, Y', strtotime($program['end_date'])) : '<span class="text-muted">Not specified</span>'; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="info-group">
                    <h6>Description</h6>
                    <p><?php echo $program['description'] ? nl2br(htmlspecialchars($program['description'])) : '<em class="text-muted">No description available.</em>'; ?></p>
                </div>

                <!-- Metadata Section -->
                <div class="info-group">
                    <h6>Metadata</h6>
                    <p><strong>Created On:</strong> <?php echo date('M j, Y', strtotime($program['created_at'])); ?></p>
                    <?php if (!empty($program['updated_at']) && $program['updated_at'] !== $program['created_at']): ?>
                        <p><strong>Last Updated:</strong> <?php echo date('M j, Y', strtotime($program['updated_at'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Status -->
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <h5 class="card-title m-0"><i class="fas fa-tasks me-2"></i>Current Status</h5>
            </div>
            <div class="card-body">
                <!-- Status Badge -->
                <div class="status-badge-lg">
                    <div class="status-pill large <?php echo $latest_status; ?> active">
                        <i class="fas fa-<?php echo $latest_status === 'completed' ? 'flag-checkered' : 'hourglass-start'; ?> me-2"></i>
                        <?php echo ucwords(str_replace('-', ' ', $latest_status)); ?>
                    </div>
                </div>

                <!-- Metrics Section -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="metric-card">
                            <h6>Target</h6>
                            <div class="h5"><?php echo htmlspecialchars($latest_target); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="metric-card">
                            <h6>Achievement</h6>
                            <div class="h5"><?php echo htmlspecialchars($latest_achievement); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Status Details -->
                <?php if (!empty($latest_status_text)): ?>
                    <div class="info-group mt-4">
                        <h6>Status Details</h6>
                        <p><?php echo nl2br(htmlspecialchars($latest_status_text)); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Submission History -->
<?php if (!empty($program['submissions'])): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title m-0"><i class="fas fa-history me-2"></i>Submission History</h5>
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
                            <td><?php echo htmlspecialchars($submission['current_target'] ?? $submission['target'] ?? 'Not set'); ?></td>
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
        <h5 class="card-title m-0"><i class="fas fa-chart-line me-2"></i>Progress Visualization</h5>
    </div>
    <div class="card-body">
        <div class="chart-container" style="position: relative; height:300px; width:100%">
            <canvas id="progressChart"></canvas>
        </div>
        <div id="chartNoData" class="text-center py-4 d-none">
            <i class="fas fa-chart-area fa-3x text-muted mb-3"></i>
            <p class="text-muted">Not enough data to display chart</p>
        </div>
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
            'target' => $sub['current_target'] ?? $sub['target'] ?? null,
            'achievement' => $sub['achievement'] ?? null,
            'status' => $sub['status']
        ];
    }, $program['submissions'] ?? [])); ?>;
    
    const programName = "<?php echo addslashes(htmlspecialchars($program['program_name'])); ?>";
    const programDates = {
        startDate: "<?php echo $program['start_date'] ? date('Y-m-d', strtotime($program['start_date'])) : ''; ?>",
        endDate: "<?php echo $program['end_date'] ? date('Y-m-d', strtotime($program['end_date'])) : ''; ?>"
    };
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
