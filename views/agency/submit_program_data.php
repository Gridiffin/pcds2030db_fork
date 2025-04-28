<?php
/**
 * Submit Program Data
 * 
 * Interface for agency users to submit data for their programs.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agencies/index.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Submit Program Data';

// Get current reporting period
$current_period = get_current_reporting_period();
if (!$current_period || $current_period['status'] !== 'open') {
    $error_message = 'No active reporting period is currently open.';
    $show_form = false;
} else {
    $show_form = true;
}

// Process form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle draft submission
    if (isset($_POST['save_draft'])) {
        $result = submit_program_data($_POST, true);
        if (isset($result['success'])) {
            $message = $result['message'] ?? 'Program data saved as draft.';
            $message_type = 'success';
        } else {
            $message = $result['error'] ?? 'Failed to save draft.';
            $message_type = 'danger';
        }
    }
    // Handle final submission
    else if (isset($_POST['submit_program'])) {
        $result = submit_program_data($_POST, false);
        if (isset($result['success'])) {
            $message = $result['message'] ?? 'Program data submitted successfully.';
            $message_type = 'success';
        } else {
            $message = $result['error'] ?? 'Failed to submit program data.';
            $message_type = 'danger';
        }
    }
    // Handle finalizing a draft
    else if (isset($_POST['finalize_draft'])) {
        $result = finalize_draft_submission($_POST['submission_id']);
        if (isset($result['success'])) {
            $message = $result['message'] ?? 'Draft finalized successfully.';
            $message_type = 'success';
        } else {
            $message = $result['error'] ?? 'Failed to finalize draft.';
            $message_type = 'danger';
        }
    }
}

// Handle quick updates from the view_programs page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_update']) && isset($_POST['program_id'])) {
    $result = submit_program_data($_POST);
    
    if (isset($result['success'])) {
        $message = $result['message'] ?? 'Program data submitted successfully.';
        $message_type = 'success';
        
        // Redirect back to programs page after brief delay
        header("Refresh: 1; URL=view_programs.php");
    } else {
        $message = $result['error'] ?? 'Failed to submit program data.';
        $message_type = 'danger';
    }
}

// Check if a specific program is requested
$selected_program = null;
$program_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Get agency's programs
$programs = get_agency_programs_by_type();

// If a program ID is provided, get its data
if ($program_id) {
    foreach ($programs as $program) {
        if ($program['program_id'] == $program_id) {
            $selected_program = $program;
            break;
        }
    }
    
    // Get more detailed information including past submissions
    if ($selected_program) {
        $selected_program = get_program_details($program_id);
    }
}


$additionalScripts = [
    APP_URL . '/assets/js/agency/program_management.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Submit Program Data</h1>
        <p class="text-muted">Update your program's targets and achievements</p>
    </div>
    
    <?php if ($current_period): ?>
        <div class="period-badge">
            <span class="badge bg-success">
                <i class="fas fa-calendar-alt me-1"></i>
                Q<?php echo $current_period['quarter']; ?>-<?php echo $current_period['year']; ?>
            </span>
            <span class="badge bg-success">
                <i class="fas fa-clock me-1"></i>
                Ends: <?php echo date('M j, Y', strtotime($current_period['end_date'])); ?>
            </span>
        </div>
    <?php else: ?>
        <div class="period-badge">
            <span class="badge bg-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                No Active Reporting Period
            </span>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <div><?php echo $message; ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<?php if (!$show_form): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo $error_message; ?> Please try again when a reporting period is active.
    </div>
<?php else: ?>
    <!-- Program Selection -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Select Program</h5>
        </div>
        <div class="card-body">
            <?php if (empty($programs)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You don't have any programs assigned to your agency yet.
                </div>
            <?php else: ?>
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="program-select" class="form-label">Choose a program to update:</label>
                        <select class="form-select" id="program-select" name="id" onchange="this.form.submit()">
                            <option value="">-- Select Program --</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?php echo $program['program_id']; ?>" <?php echo $program_id == $program['program_id'] ? 'selected' : ''; ?>>
                                    <?php echo $program['program_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i> Select
                        </button>
                        <a href="view_programs.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-list me-1"></i> View All Programs
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Program Submission Form -->
    <?php if ($selected_program): ?>
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Update Program: <?php echo $selected_program['program_name']; ?></h5>
            </div>
            <div class="card-body">
                <div class="program-info mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-muted mb-2">Program Description:</h6>
                            <p><?php echo $selected_program['description'] ?? 'No description available.'; ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Timeline:</h6>
                            <p>
                                <strong>Start:</strong> <?php echo date('M j, Y', strtotime($selected_program['start_date'])); ?><br>
                                <strong>End:</strong> <?php echo date('M j, Y', strtotime($selected_program['end_date'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <form method="post" class="program-form" id="programSubmissionForm">
                    <input type="hidden" name="program_id" value="<?php echo $selected_program['program_id']; ?>">
                    <input type="hidden" name="period_id" value="<?php echo $current_period['period_id']; ?>">
                    
                    <?php
                    // Check if there's an existing submission for the current period
                    $current_submission = null;
                    if (!empty($selected_program['submissions'])) {
                        foreach ($selected_program['submissions'] as $submission) {
                            if ($submission['period_id'] == $current_period['period_id']) {
                                $current_submission = $submission;
                                break;
                            }
                        }
                    }
                    ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="target" class="form-label">Target <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="target" name="target" rows="2" required><?php echo $current_submission['target'] ?? ''; ?></textarea>
                                <small class="form-text text-muted">Specify what you aim to achieve (e.g., "Plant 100 trees", "Train 50 staff members")</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="achievement" class="form-label">Achievement <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="achievement" name="achievement" rows="2" required><?php echo $current_submission['achievement'] ?? ''; ?></textarea>
                                <small class="form-text text-muted">Describe what has been achieved so far (e.g., "Planted 50 trees", "Trained 30 staff members")</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Program Status</label>
                        <input type="hidden" id="status" name="status" value="<?php echo $current_submission['status'] ?? 'not-started'; ?>">
                        
                        <div class="status-pills">
                            <div class="status-pill target-achieved <?php echo ($current_submission['status'] ?? '') == 'target-achieved' ? 'active' : ''; ?>" data-status="target-achieved">
                                <i class="fas fa-check-circle me-2"></i> Monthly Target Achieved
                            </div>
                            <div class="status-pill on-track-yearly <?php echo ($current_submission['status'] ?? '') == 'on-track-yearly' ? 'active' : ''; ?>" data-status="on-track-yearly">
                                <i class="fas fa-calendar-check me-2"></i> On Track for Year
                            </div>
                            <div class="status-pill severe-delay <?php echo ($current_submission['status'] ?? '') == 'severe-delay' ? 'active' : ''; ?>" data-status="severe-delay">
                                <i class="fas fa-exclamation-triangle me-2"></i> Severe Delays
                            </div>
                            <div class="status-pill not-started <?php echo ($current_submission['status'] ?? '') == 'not-started' ? 'active' : ''; ?>" data-status="not-started">
                                <i class="fas fa-hourglass-start me-2"></i> Not Started
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter any additional information or remarks"><?php echo $current_submission['remarks'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="view_programs.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        
                        <?php if (isset($current_submission) && $current_submission['is_draft'] == 1): ?>
                            <!-- For drafts, show Submit Final button -->
                            <form method="post" class="d-inline me-2">
                                <input type="hidden" name="submission_id" value="<?php echo $current_submission['submission_id']; ?>">
                                <button type="submit" name="finalize_draft" class="btn btn-success">
                                    <i class="fas fa-check-circle me-1"></i> Submit Final
                                </button>
                            </form>
                            <button type="submit" name="save_draft" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Draft
                            </button>
                        <?php else: ?>
                            <!-- For new submissions or updates to finalized submissions -->
                            <button type="submit" name="save_draft" class="btn btn-secondary me-2">
                                <i class="fas fa-save me-1"></i> Save as Draft
                            </button>
                            <button type="submit" name="submit_program" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Submit Final
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
                
                <!-- Previous Submissions -->
                <?php if (!empty($selected_program['submissions'])): ?>
                    <hr class="my-4">
                    <h5>Previous Submissions</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Target</th>
                                    <th>Achievement</th>
                                    <th>Status</th>
                                    <th>Submission Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($selected_program['submissions'] as $submission): ?>
                                    <?php if ($submission['period_id'] != $current_period['period_id']): ?>
                                        <tr>
                                            <td>Q<?php echo $submission['quarter']; ?>-<?php echo $submission['year']; ?></td>
                                            <td><?php echo $submission['target']; ?></td>
                                            <td><?php echo $submission['achievement']; ?></td>
                                            <td>
                                                <?php
                                                    $status_class = '';
                                                    switch($submission['status']) {
                                                        case 'on-track': $status_class = 'success'; break;
                                                        case 'delayed': $status_class = 'warning'; break;
                                                        case 'completed': $status_class = 'info'; break;
                                                        default: $status_class = 'secondary';
                                                    }
                                                ?>
                                                <span class="badge bg-<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($submission['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($submission['created_at'])); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
