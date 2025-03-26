<?php
/**
 * Create Program
 * 
 * Interface for agency users to create new programs.
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

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_program'])) {
    $result = create_agency_program($_POST);
    
    if (isset($result['success'])) {
        // Store success message in session
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = 'success';
        
        // Redirect to program list or details page
        if (isset($result['program_id'])) {
            header('Location: program_details.php?id=' . $result['program_id']);
        } else {
            header('Location: view_programs.php');
        }
        exit;
    } else {
        $message = $result['error'] ?? 'An error occurred while creating the program.';
        $messageType = 'danger';
    }
}

// Set page title
$pageTitle = 'Create New Program';

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
        <h1 class="h2 mb-0">Create New Program</h1>
        <p class="text-muted">Create a new program for your agency</p>
    </div>
    <a href="view_programs.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Programs
    </a>
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

<!-- Program Creation Form -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">Program Information</h5>
    </div>
    <div class="card-body">
        <form method="post" id="createProgramForm" class="program-form">
            <!-- Basic Information -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Basic Information</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="program_name" class="form-label">Program Name *</label>
                        <input type="text" class="form-control" id="program_name" name="program_name" required>
                    </div>
                    <div class="col-md-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date">
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                </div>
            </div>
            
            <!-- Target Information -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Target Information</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="target" class="form-label">Target *</label>
                        <input type="text" class="form-control" id="target" name="target" required>
                        <div class="form-text">Define a measurable target for this program</div>
                    </div>
                    <div class="col-md-6">
                        <label for="target_date" class="form-label">Target Date *</label>
                        <input type="date" class="form-control" id="target_date" name="target_date" required>
                        <div class="form-text">When do you expect to reach this target?</div>
                    </div>
                </div>
            </div>
            
            <!-- Status Information -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Status Information</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Current Status *</label>
                        <input type="hidden" id="status" name="status" value="not-started">
                        <div class="status-pills">
                            <div class="status-pill on-track" data-status="on-track">
                                <i class="fas fa-check-circle me-2"></i> On Track
                            </div>
                            <div class="status-pill delayed" data-status="delayed">
                                <i class="fas fa-exclamation-triangle me-2"></i> Delayed
                            </div>
                            <div class="status-pill completed" data-status="completed">
                                <i class="fas fa-flag-checkered me-2"></i> Completed
                            </div>
                            <div class="status-pill not-started active" data-status="not-started">
                                <i class="fas fa-hourglass-start me-2"></i> Not Started
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="status_date" class="form-label">Status Date *</label>
                        <input type="date" class="form-control" id="status_date" name="status_date" required value="<?php echo date('Y-m-d'); ?>">
                        <div class="form-text">When was this status determined?</div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <a href="view_programs.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" name="submit_program" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Create Program
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
