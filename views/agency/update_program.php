<?php
/**
 * Update Program
 * 
 * Interface for agency users to update program information.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agencies/index.php';
require_once '../../includes/status_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$program_id) {
    $_SESSION['message'] = 'Invalid program ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get program details
$program = get_program_details($program_id);

if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get current reporting period for submissions
$current_period = get_current_reporting_period();

// If no current period, redirect with error
if (!$current_period) {
    $_SESSION['message'] = 'No active reporting period found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Helper function to check if a field is editable for assigned programs
function is_editable($field) {
    global $program;
    
    // If not an assigned program, all fields are editable
    if (!isset($program['is_assigned']) || !$program['is_assigned']) {
        return true;
    }
    
    // Otherwise, check edit permissions
    if (!isset($program['edit_permissions'])) {
        return true; // Default to editable if no specific permissions
    }
    
    $permissions = json_decode($program['edit_permissions'], true);
    
    // Check if field is in the editable permissions array
    return isset($permissions['edit_permissions']) && 
           is_array($permissions['edit_permissions']) && 
           in_array($field, $permissions['edit_permissions']);
}

// Helper function to get field value from POST, default, or content
function get_field_value($field, $default = '') {
    if (isset($_POST[$field])) {
        return $_POST[$field];
    }
    
    return $default;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine submission type
    $is_draft = isset($_POST['save_draft']);
    $finalize_draft = isset($_POST['finalize_draft']);
    
    if ($finalize_draft) {
        $submission_id = $_POST['submission_id'] ?? 0;
        $result = finalize_draft_submission($submission_id);
    } else {
        // Prepare program data for update
        $program_data = [
            'program_id' => $program_id,
            'program_name' => $_POST['program_name'] ?? $program['program_name'],
            'description' => $_POST['description'] ?? $program['description'],
            'start_date' => $_POST['start_date'] ?? $program['start_date'],
            'end_date' => $_POST['end_date'] ?? $program['end_date'],
            'period_id' => $_POST['period_id'] ?? $current_period['period_id'],
            'rating' => $_POST['rating'] ?? 'not-started',
            'remarks' => $_POST['remarks'] ?? '',
        ];
        
        // Process targets data from form
        $targets = [];
        if (isset($_POST['target_text']) && is_array($_POST['target_text'])) {
            foreach ($_POST['target_text'] as $index => $text) {
                if (!empty($text)) {
                    $targets[] = [
                        'text' => $text,
                        'status_description' => $_POST['target_status_description'][$index] ?? ''
                    ];
                }
            }
        }
        
        // Add targets to program data
        $program_data['targets'] = $targets;
        
        // Submit the program data
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
    $submission_id = $current_submission['submission_id'] ?? null;
    
    // Process content_json if available
    if (isset($current_submission['content_json']) && is_string($current_submission['content_json'])) {
        $content = json_decode($current_submission['content_json'], true);
        
        // If we have the new structure with targets array, use it
        if (isset($content['targets']) && is_array($content['targets'])) {
            $targets = $content['targets'];
            $rating = $content['rating'] ?? 'not-started';
            $remarks = $content['remarks'] ?? '';
        } else {
            // Legacy data - create a single target from old structure
            $targets = [
                [
                    'target_text' => $content['target'] ?? $current_submission['target'] ?? '',
                    'status_description' => $content['status_text'] ?? $current_submission['status_text'] ?? ''
                ]
            ];
            $rating = $current_submission['status'] ?? 'not-started';
            $remarks = $content['remarks'] ?? '';
        }
    } else {
        // Old structure without content_json
        $targets = [
            [
                'target_text' => $current_submission['target'] ?? '',
                'status_description' => $current_submission['status_text'] ?? ''
            ]
        ];
        $rating = $current_submission['status'] ?? 'not-started';
        $remarks = $current_submission['remarks'] ?? '';
    }
} else {
    // No current submission, initialize empty targets
    $targets = [['target_text' => '', 'status_description' => '']];
    $rating = 'not-started';
    $remarks = '';
}

// Set page title
$pageTitle = 'Update Program';

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/agency/program_management.js',
    APP_URL . '/assets/js/utilities/status_utils.js'
];

// Include header (which contains the DOCTYPE declaration)
require_once '../layouts/header.php';

// Set up header variables
$title = "Update Program";
$subtitle = htmlspecialchars($program['program_name']) . " - " . 
            htmlspecialchars($current_period['name'] ?? '') . 
            " (" . date('M j, Y', strtotime($current_period['start_date'])) . " - " . 
            date('M j, Y', strtotime($current_period['end_date'])) . ")";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = [
    [
        'url' => 'view_programs.php',
        'text' => 'Back to Programs',
        'icon' => 'fa-arrow-left',
        'class' => 'btn-outline-secondary'
    ]
];

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Include the dashboard header component with the light style
require_once '../../includes/dashboard_header.php';

// Include any draft notification banner if this is a draft
if ($is_draft): ?>
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
    <form id="updateProgramForm" method="post">
        <div class="card-body">
            <input type="hidden" name="period_id" value="<?php echo $current_period['period_id']; ?>">
            <?php if ($submission_id): ?>
            <input type="hidden" name="submission_id" value="<?php echo $submission_id; ?>">
            <?php endif; ?>
            
            <!-- Basic Information -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Basic Information</h6>
                <div class="mb-3">
                    <label for="program_name" class="form-label">Program Name *</label>
                    <input type="text" class="form-control" id="program_name" name="program_name" required
                            value="<?php echo htmlspecialchars($program['program_name']); ?>"
                            <?php echo (!is_editable('program_name')) ? 'readonly' : ''; ?>>
                    <?php if ($program['is_assigned'] && !is_editable('program_name')): ?>
                        <div class="form-text">Program name was set by an administrator and cannot be changed.</div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Program Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"
                                <?php echo (!is_editable('description')) ? 'readonly' : ''; ?>><?php echo htmlspecialchars($program['description']); ?></textarea>
                    <?php if ($program['is_assigned'] && !is_editable('description')): ?>
                        <div class="form-text">Description was set by an administrator and cannot be changed.</div>
                    <?php endif; ?>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                value="<?php echo get_field_value('start_date', $program['start_date'] ? date('Y-m-d', strtotime($program['start_date'])) : ''); ?>"
                                <?php echo (!is_editable('timeline')) ? 'readonly' : ''; ?>>
                        <?php if ($program['is_assigned'] && !is_editable('timeline')): ?>
                            <div class="form-text">Start date was set by an administrator and cannot be changed.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                value="<?php echo get_field_value('end_date', $program['end_date'] ? date('Y-m-d', strtotime($program['end_date'])) : ''); ?>"
                                <?php echo (!is_editable('timeline')) ? 'readonly' : ''; ?>>
                        <?php if ($program['is_assigned'] && !is_editable('timeline')): ?>
                            <div class="form-text">End date was set by an administrator and cannot be changed.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Program Rating -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Program Rating</h6>
                <p class="text-muted mb-3">
                    How would you rate the overall progress of this program?
                </p>
                
                <input type="hidden" id="rating" name="rating" value="<?php echo $rating; ?>">
                
                <div class="rating-pills">
                    <div class="rating-pill target-achieved <?php echo ($rating == 'target-achieved') ? 'active' : ''; ?> <?php echo (!is_editable('rating')) ? 'disabled' : ''; ?>" data-rating="target-achieved">
                        <i class="fas fa-check-circle me-2"></i> Monthly Target Achieved
                    </div>
                    <div class="rating-pill on-track-yearly <?php echo ($rating == 'on-track-yearly') ? 'active' : ''; ?> <?php echo (!is_editable('rating')) ? 'disabled' : ''; ?>" data-rating="on-track-yearly">
                        <i class="fas fa-calendar-check me-2"></i> On Track for Year
                    </div>
                    <div class="rating-pill severe-delay <?php echo ($rating == 'severe-delay') ? 'active' : ''; ?> <?php echo (!is_editable('rating')) ? 'disabled' : ''; ?>" data-rating="severe-delay">
                        <i class="fas fa-exclamation-triangle me-2"></i> Severe Delays
                    </div>
                    <div class="rating-pill not-started <?php echo ($rating == 'not-started' || !$rating) ? 'active' : ''; ?> <?php echo (!is_editable('rating')) ? 'disabled' : ''; ?>" data-rating="not-started">
                        <i class="fas fa-clock me-2"></i> Not Started
                    </div>
                </div>
                
                <?php if ($program['is_assigned'] && !is_editable('rating')): ?>
                    <div class="form-text">Rating was set by an administrator and cannot be changed.</div>
                <?php endif; ?>
            </div>
            
            <!-- Targets Section -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Program Targets</h6>
                <p class="text-muted mb-3">
                    Define one or more targets for this program, each with its own status description.
                </p>
                
                <div id="targets-container">
                    <?php 
                    $canEditTargets = is_editable('targets');
                    
                    foreach ($targets as $index => $target): 
                        $target_text = $target['target_text'] ?? '';
                        $status_description = $target['status_description'] ?? '';
                        $canDelete = $index > 0; // Only allow deleting additional targets
                    ?>
                    <div class="target-entry">
                        <?php if ($canDelete && $canEditTargets): ?>
                        <button type="button" class="btn-close remove-target" aria-label="Remove target"></button>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Target <?php echo $index + 1; ?> *</label>
                            <input type="text" class="form-control target-input" name="target_text[]" 
                                    value="<?php echo htmlspecialchars($target_text); ?>" 
                                    placeholder="Define a measurable target (e.g., 'Plant 100 trees')"
                                    <?php echo ($canEditTargets) ? '' : 'readonly'; ?>>
                            <?php if (!$canEditTargets && $index === 0): ?>
                            <div class="form-text">Targets were set by an administrator and cannot be changed.</div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Status Description</label>
                            <textarea class="form-control status-description" name="target_status_description[]" rows="2" 
                                        placeholder="Describe the current status or progress toward this target"
                                        <?php echo (is_editable('status_text')) ? '' : 'readonly'; ?>><?php echo htmlspecialchars($status_description); ?></textarea>
                            <?php if (!is_editable('status_text') && $index === 0): ?>
                            <div class="form-text">Status descriptions were set by an administrator and cannot be changed.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($canEditTargets): ?>
                <button type="button" id="add-target-btn" class="btn btn-outline-secondary add-target-btn">
                    <i class="fas fa-plus-circle me-1"></i> Add Another Target
                </button>
                <?php endif; ?>
            </div>
            
            <!-- Remarks -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Additional Remarks</h6>
                <div class="mb-3">
                    <label for="remarks" class="form-label">Remarks (Optional)</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                placeholder="Enter any additional notes or context about this program"
                                <?php echo (is_editable('remarks')) ? '' : 'readonly'; ?>><?php echo htmlspecialchars($remarks); ?></textarea>
                    <?php if (!is_editable('remarks')): ?>
                    <div class="form-text">Remarks were set by an administrator and cannot be changed.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <div>
                <?php if ($is_draft): ?>
                    <button type="submit" name="save_draft" class="btn btn-secondary me-2">
                        <i class="fas fa-save me-1"></i> Save Draft
                    </button>
                    <button type="submit" name="finalize_draft" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i> Finalize Submission
                    </button>
                <?php else: ?>
                    <button type="submit" name="save_draft" class="btn btn-secondary me-2">
                        <i class="fas fa-save me-1"></i> Save as Draft
                    </button>
                    <button type="submit" name="submit_program" class="btn btn-primary">
                        <i class="fas fa-check-circle me-1"></i> Update Program
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Rating pills selection
        const ratingPills = document.querySelectorAll('.rating-pill:not(.disabled)');
        const ratingInput = document.getElementById('rating');
        
        ratingPills.forEach(pill => {
            pill.addEventListener('click', function() {
                // Remove active class from all pills
                ratingPills.forEach(p => p.classList.remove('active'));
                
                // Add active class to clicked pill
                this.classList.add('active');
                
                // Update hidden input
                ratingInput.value = this.getAttribute('data-rating');
            });
        });
        
        // Add target functionality
        const addTargetBtn = document.getElementById('add-target-btn');
        if (addTargetBtn) {
            const targetsContainer = document.getElementById('targets-container');
            
            // Keep track of the highest target number used
            let highestTargetNumber = document.querySelectorAll('.target-entry').length;
            
            // Function to update target numbers sequentially
            function updateTargetNumbers() {
                const targetEntries = document.querySelectorAll('.target-entry');
                targetEntries.forEach((entry, index) => {
                    const label = entry.querySelector('.form-label');
                    if (label) {
                        label.textContent = `Target ${index + 1} *`;
                    }
                });
            }
            
            addTargetBtn.addEventListener('click', function() {
                // Increment the highest target number
                highestTargetNumber++;
                
                const targetEntry = document.createElement('div');
                targetEntry.className = 'target-entry';
                
                const html = `
                    <button type="button" class="btn-close remove-target" aria-label="Remove target"></button>
                    <div class="mb-3">
                        <label class="form-label">Target ${highestTargetNumber} *</label>
                        <input type="text" class="form-control target-input" name="target_text[]" 
                               placeholder="Define a measurable target (e.g., 'Plant 100 trees')">
                        <div class="form-text">Define a specific, measurable target for this program.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Status Description</label>
                        <textarea class="form-control status-description" name="target_status_description[]" rows="2" 
                                  placeholder="Describe the current status or progress toward this target"></textarea>
                        <div class="form-text">Describe the current status or achievement toward this target.</div>
                    </div>
                `;
                
                targetEntry.innerHTML = html;
                targetsContainer.appendChild(targetEntry);
                
                // Attach remove event listener to the new target
                const removeBtn = targetEntry.querySelector('.remove-target');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        targetEntry.remove();
                        // Update target numbers after removing
                        updateTargetNumbers();
                    });
                }
            });
        }
        
        // Initialize existing remove buttons
        document.querySelectorAll('.remove-target').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.target-entry').remove();
                // Update target numbers after removing
                updateTargetNumbers();
            });
        });
        
        // Form validation
        document.getElementById('updateProgramForm').addEventListener('submit', function(e) {
            const programName = document.getElementById('program_name').value;
            const targetInputs = document.querySelectorAll('.target-input');
            let hasFilledTarget = false;
            
            // Validate program name
            if (!programName.trim()) {
                alert('Please enter a program name.');
                e.preventDefault();
                return false;
            }
            
            // For finalize/submit actions, validate at least one target
            if (e.submitter && (e.submitter.name === 'submit_program' || e.submitter.name === 'finalize_draft')) {
                targetInputs.forEach(input => {
                    if (input.value.trim()) {
                        hasFilledTarget = true;
                    }
                });
                
                if (!hasFilledTarget) {
                    alert('Please add at least one target for this program.');
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    });
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
