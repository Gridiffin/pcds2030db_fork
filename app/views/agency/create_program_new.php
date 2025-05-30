<?php
/**
 * Create Program - Simplified Draft Only
 * 
 * Simple interface for agency users to create program drafts with basic information.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/programs.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare data for submission
    $program_data = [
        'program_name' => $_POST['program_name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? ''
    ];
    
    // Create program draft using new simplified function
    $result = create_simple_program_draft($program_data);
    
    if (isset($result['success']) && $result['success']) {
        // Set success message and redirect
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = 'success';
        
        // Redirect to programs list
        header('Location: view_programs.php');
        exit;
    } else {
        $message = $result['error'] ?? 'An error occurred while saving the program draft.';
        $messageType = 'danger';
    }
}

// Set page title
$pageTitle = 'Create New Program';

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Create New Program</h4>
                    <p class="text-muted mb-0">Create a new program draft with basic information</p>
                </div>
                <a href="view_programs.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Programs
                </a>
            </div>

            <!-- Error/Success Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Create Program Form -->
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-plus-circle me-2 text-primary"></i>
                                Program Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="createProgramForm" method="post">
                                <!-- Program Name -->
                                <div class="mb-4">
                                    <label for="program_name" class="form-label">
                                        Program Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="program_name" 
                                           name="program_name" 
                                           required
                                           placeholder="Enter the program name"
                                           value="<?php echo htmlspecialchars($_POST['program_name'] ?? ''); ?>">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This will be the main identifier for your program
                                    </div>
                                </div>

                                <!-- Program Description -->
                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="4"
                                              placeholder="Briefly describe the program's purpose and objectives (optional)"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Optional: Provide a brief overview of what this program aims to achieve
                                    </div>
                                </div>

                                <!-- Timeline -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-calendar me-2"></i>
                                        Program Timeline (Optional)
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="start_date" 
                                                   name="start_date"
                                                   value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                                            <div class="form-text">When did/will the program start?</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="end_date" 
                                                   name="end_date"
                                                   value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                                            <div class="form-text">When is the program expected to end?</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg" id="saveDraftBtn">
                                        <i class="fas fa-save me-2"></i>
                                        Save Draft
                                    </button>
                                </div>

                                <!-- Info Note -->
                                <div class="mt-3 p-3 bg-light rounded">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>Note:</strong> This will save your program as a draft. You can add more details and submit it for review later from the programs list.
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Simple JavaScript for client-side validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createProgramForm');
    const programNameInput = document.getElementById('program_name');
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    // Client-side validation for program name
    function validateProgramName() {
        const name = programNameInput.value.trim();
        
        if (name === '') {
            showFieldError(programNameInput, 'Program name is required');
            return false;
        } else if (name.length < 3) {
            showFieldError(programNameInput, 'Program name must be at least 3 characters long');
            return false;
        } else {
            clearFieldError(programNameInput);
            return true;
        }
    }

    // Date validation
    function validateDates() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            showFieldError(endDateInput, 'End date cannot be before start date');
            return false;
        } else {
            clearFieldError(endDateInput);
            return true;
        }
    }

    // Real-time validation
    programNameInput.addEventListener('blur', validateProgramName);
    programNameInput.addEventListener('input', function() {
        if (this.value.trim() !== '') {
            clearFieldError(this);
        }
    });

    endDateInput.addEventListener('change', validateDates);
    startDateInput.addEventListener('change', validateDates);

    // Form submission
    form.addEventListener('submit', function(e) {
        const isNameValid = validateProgramName();
        const areDatesValid = validateDates();
        
        if (!isNameValid || !areDatesValid) {
            e.preventDefault();
            return false;
        }

        // Disable button to prevent double submission
        saveDraftBtn.disabled = true;
        saveDraftBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving Draft...';
    });

    // Utility functions for showing/clearing field errors
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        // Remove existing error message
        const existingError = field.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
        
        // Add new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
});
</script>

<?php require_once '../layouts/footer.php'; ?>
