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

// Check for edit permissions if this is an assigned program
$edit_permissions = [];
$default_values = [];

if ($program['is_assigned'] && isset($program['edit_permissions'])) {
    // Convert from JSON if stored as string
    if (is_string($program['edit_permissions'])) {
        $settings = json_decode($program['edit_permissions'], true);
        
        // Check if we have the new format with both permissions and default values
        if (isset($settings['edit_permissions'])) {
            $edit_permissions = $settings['edit_permissions'];
            $default_values = $settings['default_values'] ?? [];
        } else {
            // Legacy format - just permissions array
            $edit_permissions = $settings ?? [];
        }
    } else {
        $edit_permissions = $program['edit_permissions'] ?? [];
    }
}

// Function to check if a field is editable
function is_editable($field) {
    global $program, $edit_permissions;
    
    // If not assigned by admin, all fields are editable except program name
    if (!isset($program['is_assigned']) || !$program['is_assigned']) {
        return $field !== 'program_name';
    }
    
    // For assigned programs, check permissions array
    return in_array($field, $edit_permissions);
}

// Function to get default value if field is not editable
function get_field_value($field, $current_value = '') {
    global $default_values;
    
    // If this field has a default value and is not editable, use the default value
    if (isset($default_values[$field]) && !is_editable($field)) {
        return $default_values[$field];
    }
    
    // Otherwise return current value if available
    return $current_value;
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

// Check if program has been submitted as final and is not a draft
$is_final_submitted = isset($current_submission['is_draft']) && $current_submission['is_draft'] == 0;

// If program has been submitted as final, redirect with message
if ($is_final_submitted) {
    $_SESSION['message'] = 'This program has been submitted as final and cannot be edited. Please contact an administrator if changes are needed.';
    $_SESSION['message_type'] = 'warning';
    header('Location: view_programs.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check which button was clicked
    $is_draft = isset($_POST['save_draft']);
    $finalize_draft = isset($_POST['finalize_draft']);
    
    // Prepare program data
    $program_id = intval($_POST['program_id'] ?? 0);
    $program_data = [
        'program_id' => $program_id,
        'period_id' => $current_period['period_id'], // Add the current period ID which is required
        'program_name' => $_POST['program_name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'target' => $_POST['target'] ?? '',
        'status' => $_POST['status'] ?? 'not-started',
        'status_date' => $_POST['status_date'] ?? date('Y-m-d'),
        'status_text' => $_POST['status_text'] ?? '',
        'achievement' => $_POST['achievement'] ?? '',
        'remarks' => $_POST['remarks'] ?? ''
    ];
    
    if ($finalize_draft && isset($_POST['submission_id'])) {
        // Option 1: Finalize an existing draft
        $result = finalize_draft_submission($_POST['submission_id']);
    } else {
        // Option 2: Submit as draft or final based on button clicked
        $result = submit_program_data($program_data, $is_draft);
    }
    
    if (isset($result['success'])) {
        // Set success message
        if ($finalize_draft) {
            $_SESSION['message'] = 'Draft finalized successfully.';
        } else if ($is_draft) {
            $_SESSION['message'] = 'Program saved as draft successfully.';
        } else {
            $_SESSION['message'] = 'Program updated successfully.';
        }
        $_SESSION['message_type'] = 'success';
        
        // Redirect to programs page
        header('Location: view_programs.php');
        exit;
    } else {
        $message = $result['error'] ?? 'An error occurred while updating the program.';
        $messageType = 'danger';
    }
}

// Check if the program has a draft submission for the current period
$is_draft = false;
$submission_id = null;

// Check for current submission
if (isset($program['current_submission'])) {
    $current_submission = $program['current_submission'];
    $is_draft = isset($current_submission['is_draft']) && $current_submission['is_draft'] == 1;
    if ($is_draft) {
        $submission_id = $current_submission['submission_id'];
    }
}

// Set page title
$pageTitle = 'Update Program';

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

<!-- Include any draft notification banner if this is a draft -->
<?php if ($is_draft): ?>
<div class="draft-banner mb-4">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Draft Mode:</strong> This program submission is currently saved as a draft. You can continue editing or submit the final version.
</div>
<?php endif; ?>

<!-- Program Update Form -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Program Details</h5>
        <span class="badge bg-<?php echo $program['is_assigned'] ? 'primary' : 'success'; ?>">
            <?php echo $program['is_assigned'] ? 'Assigned Program' : 'Agency Created'; ?>
        </span>
    </div>
    <div class="card-body">
        <form method="post" id="updateProgramForm" class="program-form">
            <input type="hidden" name="program_id" value="<?php echo $program['program_id']; ?>">
            <?php if ($is_draft && $submission_id): ?>
            <input type="hidden" name="submission_id" value="<?php echo $submission_id; ?>">
            <?php endif; ?>
            
            <!-- Basic Information -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Basic Information</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="program_name" class="form-label">Program Name *</label>
                        <input type="text" class="form-control" id="program_name" name="program_name" 
                               value="<?php echo htmlspecialchars($program['program_name']); ?>" 
                               readonly>
                        <div class="form-text">Program name cannot be changed once created.</div>
                    </div>
                    <div class="col-md-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                 <?php echo (!is_editable('description')) ? 'readonly' : ''; ?>><?php echo htmlspecialchars(get_field_value('description', $program['description'] ?? '')); ?></textarea>
                        <?php if ($program['is_assigned'] && !is_editable('description')): ?>
                            <div class="form-text">Description was set by an administrator and cannot be changed.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?php echo get_field_value('timeline', $program['start_date'] ? date('Y-m-d', strtotime($program['start_date'])) : ''); ?>"
                               <?php echo (!is_editable('timeline')) ? 'readonly' : ''; ?>>
                        <?php if ($program['is_assigned'] && !is_editable('timeline')): ?>
                            <div class="form-text">Start date was set by an administrator and cannot be changed.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?php echo get_field_value('timeline', $program['end_date'] ? date('Y-m-d', strtotime($program['end_date'])) : ''); ?>"
                               <?php echo (!is_editable('timeline')) ? 'readonly' : ''; ?>>
                        <?php if ($program['is_assigned'] && !is_editable('timeline')): ?>
                            <div class="form-text">End date was set by an administrator and cannot be changed.</div>
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
                               value="<?php 
                               // Get target from content_json if available
                               if (isset($current_submission['content_json']) && is_string($current_submission['content_json'])) {
                                   $content = json_decode($current_submission['content_json'], true);
                                   echo htmlspecialchars(get_field_value('target', $content['target'] ?? ''));
                               } else {
                                   echo htmlspecialchars(get_field_value('target', $current_submission['target'] ?? ''));
                               }
                               ?>"
                               <?php echo (!is_editable('target')) ? 'readonly' : ''; ?>>
                        <?php if ($program['is_assigned'] && !is_editable('target')): ?>
                            <div class="form-text">Target was set by an administrator and cannot be changed.</div>
                        <?php else: ?>
                            <div class="form-text">Define a measurable target for this program. The program timeline is already set by the start/end dates.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Status Information -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Status Information</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label">Current Status *</label>
                        <select class="form-select" id="status" name="status" required
                                <?php echo (!is_editable('status')) ? 'disabled' : ''; ?>>
                            <option value="target-achieved" <?php echo get_field_value('status', $current_submission['status'] ?? '') == 'target-achieved' ? 'selected' : ''; ?>>Monthly Target Achieved</option>
                            <option value="on-track-yearly" <?php echo get_field_value('status', $current_submission['status'] ?? '') == 'on-track-yearly' ? 'selected' : ''; ?>>On Track for Year</option>
                            <option value="severe-delay" <?php echo get_field_value('status', $current_submission['status'] ?? '') == 'severe-delay' ? 'selected' : ''; ?>>Severe Delays</option>
                            <option value="not-started" <?php echo get_field_value('status', $current_submission['status'] ?? '') == 'not-started' ? 'selected' : ''; ?>>Not Started</option>
                        </select>
                        <?php if ($program['is_assigned'] && !is_editable('status')): ?>
                            <div class="form-text">Status was set by an administrator and cannot be changed.</div>
                        <?php else: ?>
                            <div class="form-text">Current status category of the program</div>
                        <?php endif; ?>
                        
                        <?php if (!is_editable('status')): ?>
                            <!-- Hidden field to preserve value when disabled -->
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars(get_field_value('status', $current_submission['status'] ?? 'not-started')); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="status_date" class="form-label">Status Date *</label>
                        <input type="date" class="form-control" id="status_date" name="status_date" required
                               value="<?php 
                               // Get status_date from content_json if available
                               if (isset($current_submission['content_json']) && is_string($current_submission['content_json'])) {
                                   $content = json_decode($current_submission['content_json'], true);
                                   echo isset($content['status_date']) && $content['status_date'] ? date('Y-m-d', strtotime($content['status_date'])) : date('Y-m-d');
                               } else {
                                   echo isset($current_submission['status_date']) ? date('Y-m-d', strtotime($current_submission['status_date'])) : date('Y-m-d');
                               }
                               ?>">
                        <div class="form-text">When was this status determined?</div>
                    </div>
                    <div class="col-md-12">
                        <label for="status_text" class="form-label">Status Description / Achievements </label>
                        <textarea class="form-control" id="status_text" name="status_text" rows="2"><?php 
                        // Get status_text from content_json if available
                        if (isset($current_submission['content_json']) && is_string($current_submission['content_json'])) {
                            $content = json_decode($current_submission['content_json'], true);
                            echo htmlspecialchars($content['status_text'] ?? '');
                        } else {
                            echo htmlspecialchars($current_submission['status_text'] ?? '');
                        }
                        ?></textarea>
                        <div class="form-text">Describe the current status of this program in detail</div>
                    </div>
                    <div class="col-md-12">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter any additional information or remarks"><?php 
                        // Get remarks from content_json if available
                        if (isset($current_submission['content_json']) && is_string($current_submission['content_json'])) {
                            $content = json_decode($current_submission['content_json'], true);
                            echo htmlspecialchars($content['remarks'] ?? '');
                        } else {
                            echo htmlspecialchars($current_submission['remarks'] ?? '');
                        }
                        ?></textarea>
                        <div class="form-text">Additional notes or comments about this program's implementation.</div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden field for JSON structure -->
            <input type="hidden" name="content_structure" value="json">
            
            <div class="d-flex justify-content-end mt-4">
                <a href="program_details.php?id=<?php echo $program_id; ?>" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <?php if ($is_draft): ?>
                <!-- For drafts: show option to update draft or finalize -->
                <button type="submit" name="save_draft" class="btn btn-secondary me-2">
                    <i class="fas fa-save me-1"></i> Update Draft
                </button>
                <button type="submit" name="finalize_draft" class="btn btn-success">
                    <i class="fas fa-check-circle me-1"></i> Submit Final
                </button>
                <?php else: ?>
                <!-- For regular updates: Show save as draft or submit final -->
                <button type="submit" name="save_draft" class="btn btn-secondary me-2">
                    <i class="fas fa-save me-1"></i> Save as Draft
                </button>
                <button type="submit" name="submit_program" class="btn btn-primary" 
                    onclick="return confirm('Are you sure you want to submit this program as final? You will not be able to make further edits.')">
                    <i class="fas fa-paper-plane me-1"></i> Submit Final
                </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
