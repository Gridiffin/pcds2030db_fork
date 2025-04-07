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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set is_draft flag based on which button was clicked
    $is_draft = isset($_POST['save_draft']);
    
    // Prepare data for submission
    $program_data = [
        'program_name' => $_POST['program_name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'target' => $_POST['target'] ?? '',
        'status' => $_POST['status'] ?? 'not-started',
        'status_date' => date('Y-m-d')
    ];
    
    // Submit as draft or final based on button clicked
    if ($is_draft) {
        // Less validation for drafts
        $result = create_agency_program_draft($program_data);
    } else {
        // Full validation for final submission
        $result = create_agency_program($program_data);
    }
    
    if (isset($result['success'])) {
        // Set success message
        $_SESSION['message'] = $is_draft ? 'Program saved as draft successfully.' : 'Program created successfully.';
        $_SESSION['message_type'] = 'success';
        
        // Redirect to the program list
        header('Location: view_programs.php');
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
                        <div class="form-text character-counter">0/500 characters</div>
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
                    <div class="col-md-12">
                        <label for="target" class="form-label">Target *</label>
                        <input type="text" class="form-control" id="target" name="target" required>
                        <div class="form-text">Define a measurable target for this program.</div>
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
                            <option value="target-achieved">Monthly Target Achieved</option>
                            <option value="on-track-yearly">On Track for Year</option>
                            <option value="severe-delay">Severe Delays</option>
                            <option value="not-started" selected>Not Started</option>
                        </select>
                        <div class="form-text">Current status category of the program</div>
                    </div>
                    <div class="col-md-6">
                        <label for="status_date" class="form-label">Status Date *</label>
                        <input type="date" class="form-control" id="status_date" name="status_date" required value="<?php echo date('Y-m-d'); ?>">
                        <div class="form-text">When was this status determined?</div>
                    </div>
                    <div class="col-md-12">
                        <label for="status_text" class="form-label">Status Description</label>
                        <textarea class="form-control" id="status_text" name="status_text" rows="2"></textarea>
                        <div class="form-text">Describe the current status of this program in detail</div>
                    </div>
                </div>
            </div>
            
            <!-- Optional: Hidden fields for JSON structure -->
            <input type="hidden" name="content_structure" value="json">
            
            <div class="d-flex justify-content-end mt-4">
                <a href="view_programs.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" name="save_draft" class="btn btn-secondary me-2">
                    <i class="fas fa-save me-1"></i> Save as Draft
                </button>
                <button type="submit" name="submit_program" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i> Submit Final
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
