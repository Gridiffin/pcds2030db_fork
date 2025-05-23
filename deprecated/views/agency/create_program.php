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
require_once '../../includes/agencies/index.php';
require_once '../../includes/rating_helpers.php';

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
    
    // Prepare data for submission
    $program_data = [
        'program_name' => $_POST['program_name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'rating' => $_POST['rating'] ?? 'not-started',
        'remarks' => $_POST['remarks'] ?? '',
        'targets' => $targets
    ];
    
    // Submit program data based on draft status
    if ($is_draft) {
        // For new programs as drafts, use the draft-specific function
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
$pageTitle = 'Create Program';

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/agency/program_management.js',
    APP_URL . '/assets/js/utilities/rating_utils.js',
    APP_URL . '/assets/js/utilities/program-history.js'
];

// Additional styles
$additionalStyles = '
<link rel="stylesheet" href="' . APP_URL . '/assets/css/components/program-history.css">
';

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Set up header variables
$title = "Create New Program";
$subtitle = "Add a new program to track";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = [];

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <form id="createProgramForm" method="post">
        <div class="card-header">
            <h5 class="card-title m-0">Program Information</h5>
        </div>
        <div class="card-body">
            <!-- Basic Information -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Basic Information</h6>
                <div class="mb-3">
                    <label for="program_name" class="form-label">Program Name *</label>
                    <input type="text" class="form-control" id="program_name" name="program_name" required
                           value="<?php echo htmlspecialchars($_POST['program_name'] ?? ''); ?>">
                    <div class="form-text">The name of the program as it will appear in reports and dashboards.</div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Program Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    <div class="form-text">Optional description of the program's purpose and goals.</div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                        <div class="form-text">When does/did the program start?</div>
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                        <div class="form-text">When is the program expected to end?</div>
                    </div>
                </div>
            </div>
            
            <!-- Program Rating -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Program Rating</h6>
                <p class="text-muted mb-3">
                    How would you rate the overall progress of this program?
                </p>
                
                <input type="hidden" id="rating" name="rating" value="<?php echo $_POST['rating'] ?? 'not-started'; ?>">
                
                <div class="rating-pills">
                    <div class="rating-pill target-achieved <?php echo (isset($_POST['rating']) && $_POST['rating'] == 'target-achieved') ? 'active' : ''; ?>" data-rating="target-achieved">
                        <i class="fas fa-check-circle me-2"></i> Monthly Target Achieved
                    </div>
                    <div class="rating-pill on-track-yearly <?php echo (isset($_POST['rating']) && $_POST['rating'] == 'on-track-yearly') ? 'active' : ''; ?>" data-rating="on-track-yearly">
                        <i class="fas fa-calendar-check me-2"></i> On Track for Year
                    </div>
                    <div class="rating-pill severe-delay <?php echo (isset($_POST['rating']) && $_POST['rating'] == 'severe-delay') ? 'active' : ''; ?>" data-rating="severe-delay">
                        <i class="fas fa-exclamation-triangle me-2"></i> Severe Delays
                    </div>
                    <div class="rating-pill not-started <?php echo (!isset($_POST['rating']) || $_POST['rating'] == 'not-started') ? 'active' : ''; ?>" data-rating="not-started">
                        <i class="fas fa-clock me-2"></i> Not Started
                    </div>
                </div>
            </div>
            
            <!-- Targets Section -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Program Targets</h6>
                <p class="text-muted mb-3">
                    Define one or more targets for this program, each with its own status description.
                </p>
                
                <div id="targets-container">
                    <div class="target-entry">
                        <div class="mb-3">
                            <label class="form-label">Target 1 *</label>
                            <input type="text" class="form-control target-input" name="target_text[]" 
                                   placeholder="Define a measurable target (e.g., 'Plant 100 trees')"
                                   value="<?php echo htmlspecialchars($_POST['target_text'][0] ?? ''); ?>">
                            <div class="form-text">Define a specific, measurable target for this program.</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Status Description</label>
                            <textarea class="form-control status-description" name="target_status_description[]" rows="2" 
                                      placeholder="Describe the current status or progress toward this target"><?php echo htmlspecialchars($_POST['target_status_description'][0] ?? ''); ?></textarea>
                            <div class="form-text">Describe the current status or achievement toward this target.</div>
                        </div>
                    </div>
                    
                    <?php
                    // Restore additional targets from previous form submission if any
                    if (isset($_POST['target_text']) && is_array($_POST['target_text']) && count($_POST['target_text']) > 1) {
                        for ($i = 1; $i < count($_POST['target_text']); $i++) {
                            if (!empty($_POST['target_text'][$i])) {
                                echo '<div class="target-entry">';
                                echo '<button type="button" class="btn-close remove-target" aria-label="Remove target"></button>';
                                echo '<div class="mb-3">';
                                echo '<label class="form-label">Target ' . ($i + 1) . ' *</label>';
                                echo '<input type="text" class="form-control target-input" name="target_text[]" ';
                                echo 'value="' . htmlspecialchars($_POST['target_text'][$i]) . '" ';
                                echo 'placeholder="Define a measurable target (e.g., \'Plant 100 trees\')">';
                                echo '<div class="form-text">Define a specific, measurable target for this program.</div>';
                                echo '</div>';
                                echo '<div class="mb-2">';
                                echo '<label class="form-label">Status Description</label>';
                                echo '<textarea class="form-control status-description" name="target_status_description[]" rows="2" ';
                                echo 'placeholder="Describe the current status or progress toward this target">' . htmlspecialchars($_POST['target_status_description'][$i] ?? '') . '</textarea>';
                                echo '<div class="form-text">Describe the current status or achievement toward this target.</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                </div>
                
                <button type="button" id="add-target-btn" class="btn btn-outline-secondary add-target-btn">
                    <i class="fas fa-plus-circle me-1"></i> Add Another Target
                </button>
            </div>
            
            <!-- Remarks -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Additional Remarks</h6>
                <div class="mb-3">
                    <label for="remarks" class="form-label">Remarks (Optional)</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3"
                              placeholder="Enter any additional notes or context about this program"><?php echo htmlspecialchars($_POST['remarks'] ?? ''); ?></textarea>
                    <div class="form-text">Any additional information that doesn't fit elsewhere.</div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="view_programs.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <div>
                <button type="submit" name="save_draft" class="btn btn-secondary me-2" id="saveDraftBtn">
                    <i class="fas fa-save me-1"></i> Save as Draft
                </button>
                <button type="submit" name="submit_program" class="btn btn-primary" id="createProgramBtn">
                    <i class="fas fa-check-circle me-1"></i> Create Program
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Rating pills selection
        const ratingPills = document.querySelectorAll('.rating-pill');
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
        
        // Initialize existing remove buttons
        document.querySelectorAll('.remove-target').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.target-entry').remove();
                // Update target numbers after removing
                updateTargetNumbers();
            });
        });
        
        // Form validation
        document.getElementById('createProgramForm').addEventListener('submit', function(e) {
            const programName = document.getElementById('program_name').value;
            const targetInputs = document.querySelectorAll('.target-input');
            let hasFilledTarget = false;
            
            // Validate program name
            if (!programName.trim()) {
                alert('Please enter a program name.');
                e.preventDefault();
                return false;
            }
            
            // For non-draft submissions, validate at least one target
            if (!e.submitter || !e.submitter.name || e.submitter.name !== 'save_draft') {
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
