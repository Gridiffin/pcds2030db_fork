<?php
/**
 * Update Program
 * 
 * Interface for agency users to update program details and submission data.
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

// Get current reporting period
$current_period = get_current_reporting_period();
if (!$current_period || $current_period['status'] !== 'open') {
    $_SESSION['message'] = 'No active reporting period available.';
    $_SESSION['message_type'] = 'warning';
    header('Location: program_details.php?id=' . $program_id);
    exit;
}

// Get current submission if exists
$current_submission = $program['current_submission'] ?? null;

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_program'])) {
    // Add program_id to data
    $_POST['program_id'] = $program_id;
    
    $result = update_agency_program($_POST);
    
    if (isset($result['success'])) {
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = 'success';
        
        header('Location: program_details.php?id=' . $program_id);
        exit;
    } else {
        $message = $result['error'] ?? 'An error occurred while updating the program.';
        $messageType = 'danger';
    }
}

// Set page title
$pageTitle = 'Update Program';

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/agency.css'
];

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/status_utils.js',
    APP_URL . '/assets/js/agency/program_form.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Update Program</h1>
        <p class="text-muted">Update details and status for: <?php echo htmlspecialchars($program['program_name']); ?></p>
    </div>
    <div>
        <a href="program_details.php?id=<?php echo $program_id; ?>" class="btn btn-outline-secondary me-2">
            <i class="fas fa-eye me-1"></i> View Details
        </a>
        <a href="view_programs.php" class="btn btn-outline-secondary">
            <i class="fas fa-list me-1"></i> All Programs
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

<!-- Current Reporting Period Badge -->
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-center">
        <i class="fas fa-calendar-alt me-2"></i>
        <div>
            <strong>Current Reporting Period:</strong> Q<?php echo $current_period['quarter']; ?>-<?php echo $current_period['year']; ?> 
            (<?php echo date('M j, Y', strtotime($current_period['start_date'])); ?> - 
            <?php echo date('M j, Y', strtotime($current_period['end_date'])); ?>)
        </div>
    </div>
</div>

<!-- Program Update Form -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Program Details</h5>
        <span class="badge bg-<?php echo $program['is_assigned'] ? 'primary' : 'success'; ?>">
            <?php echo $program['is_assigned'] ? 'Assigned Program' : 'Agency Created'; ?>
        </span>
    </div>
    <div class="card-body">
        <form method="post" class="program-form">
            <input type="hidden" name="program_id" value="<?php echo $program['program_id']; ?>">
            
            <!-- Basic Information -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Basic Information</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="program_name" class="form-label">Program Name *</label>
                        <input type="text" class="form-control" id="program_name" name="program_name" 
                               value="<?php echo htmlspecialchars($program['program_name']); ?>" 
                               <?php echo $program['is_assigned'] ? 'readonly' : 'required'; ?>>
                        <?php if ($program['is_assigned']): ?>
                            <div class="form-text">This program was assigned by an administrator and its name cannot be changed.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($program['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?php echo $program['start_date'] ? date('Y-m-d', strtotime($program['start_date'])) : ''; ?>"
                               <?php echo $program['is_assigned'] ? 'readonly' : ''; ?>>
                        <?php if ($program['is_assigned']): ?>
                            <div class="form-text">Start date was set by an administrator.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?php echo $program['end_date'] ? date('Y-m-d', strtotime($program['end_date'])) : ''; ?>"
                               <?php echo $program['is_assigned'] ? 'readonly' : ''; ?>>
                        <?php if ($program['is_assigned']): ?>
                            <div class="form-text">End date was set by an administrator.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Target Information -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Target Information</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="target" class="form-label">Target *</label>
                        <input type="text" class="form-control" id="target" name="target" required
                               value="<?php echo htmlspecialchars($current_submission['current_target'] ?? ''); ?>">
                        <div class="form-text">Define a measurable target for this program. The program timeline is already set by the start/end dates.</div>
                    </div>
                </div>
            </div>
            
            <!-- Status Information -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Status Information</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label">Current Status *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="on-track" <?php echo ($current_submission['status'] ?? '') == 'on-track' ? 'selected' : ''; ?>>On Track</option>
                            <option value="delayed" <?php echo ($current_submission['status'] ?? '') == 'delayed' ? 'selected' : ''; ?>>Delayed</option>
                            <option value="completed" <?php echo ($current_submission['status'] ?? '') == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="not-started" <?php echo ($current_submission['status'] ?? '') == 'not-started' ? 'selected' : ''; ?>>Not Started</option>
                        </select>
                        <div class="form-text">Current status category of the program</div>
                    </div>
                    <div class="col-md-6">
                        <label for="status_date" class="form-label">Status Date *</label>
                        <input type="date" class="form-control" id="status_date" name="status_date" required
                               value="<?php echo isset($current_submission['status_date']) ? date('Y-m-d', strtotime($current_submission['status_date'])) : date('Y-m-d'); ?>">
                        <div class="form-text">When was this status determined?</div>
                    </div>
                    <div class="col-md-12">
                        <label for="status_text" class="form-label">Status Description</label>
                        <textarea class="form-control" id="status_text" name="status_text" rows="2"><?php echo htmlspecialchars($current_submission['status_text'] ?? ''); ?></textarea>
                        <div class="form-text">Describe the current status of this program in detail</div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden field for JSON structure -->
            <input type="hidden" name="content_structure" value="json">
            
            <div class="d-flex justify-content-end mt-4">
                <a href="program_details.php?id=<?php echo $program_id; ?>" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" name="update_program" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Update Program
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
