<?php
/**
 * Edit Program - Simplified
 * 
 * Simple interface for agency users to edit program basic information only.
 * Submissions are managed separately.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/programs.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/program_agency_assignments.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/program_user_assignments.php';
require_once PROJECT_ROOT_PATH . 'lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'lib/numbering_helpers.php';
require_once PROJECT_ROOT_PATH . 'lib/rating_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
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

// Check if user can edit this program using new permission system
if (!can_edit_program($program_id)) {
    $_SESSION['message'] = 'You do not have permission to edit this program.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// For legacy compatibility
$is_owner = is_program_owner($program_id);

// Get active initiatives for dropdown
$active_initiatives = get_initiatives_for_select(true);

// Get users in assigned agencies for user assignment
$assignable_users = get_assignable_users_for_program($program_id);

// Get current user assignments for this program
$current_user_assignments = get_program_assigned_users($program_id);

// Check if program has editor restrictions
$restrict_editors = program_has_editor_restrictions($program_id);

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_data = [
        'program_id' => $program_id,
        'program_name' => $_POST['program_name'] ?? '',
        'program_number' => $_POST['program_number'] ?? '',
        'brief_description' => $_POST['brief_description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'initiative_id' => !empty($_POST['initiative_id']) ? intval($_POST['initiative_id']) : null,
        'rating' => $_POST['rating'] ?? 'not_started'
    ];
    
    // Update program using simplified function
    $result = update_simple_program($program_data);
    
    if (isset($result['success']) && $result['success']) {
        // Handle user assignment settings
        $new_restrict_editors = isset($_POST['restrict_editors']) ? 1 : 0;
        $assigned_editor_users = isset($_POST['assigned_editors']) ? $_POST['assigned_editors'] : [];
        
        // Update editor restrictions
        set_program_editor_restrictions($program_id, $new_restrict_editors);
        
        if ($new_restrict_editors) {
            // Remove all current user assignments
            foreach ($current_user_assignments as $assignment) {
                remove_user_from_program($program_id, $assignment['user_id']);
            }
            
            // Add new assignments
            if (!empty($assigned_editor_users)) {
                foreach ($assigned_editor_users as $user_id) {
                    assign_user_to_program($program_id, intval($user_id), 'editor', 'Updated during program edit');
                }
            }
        } else {
            // Remove all user assignments when restrictions are disabled
            foreach ($current_user_assignments as $assignment) {
                remove_user_from_program($program_id, $assignment['user_id']);
            }
        }
        
        // Set success message and redirect
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = 'success';
        
        // Redirect to program list
        header('Location: view_programs.php');
        exit;
    } else {
        $message = $result['error'] ?? 'An error occurred while updating the program.';
        $messageType = 'danger';
    }
}

// Set page title
$pageTitle = 'Edit Program';

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$program_display_name = '';
if (!empty($program['program_number'])) {
    $program_display_name = '<span class="badge bg-info me-2" title="Program Number">' . htmlspecialchars($program['program_number']) . '</span>';
}
$program_display_name .= htmlspecialchars($program['program_name']);

$header_config = [
    'title' => 'Edit Program',
    'subtitle' => $program_display_name,
    'subtitle_html' => true,
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'view_programs.php',
            'text' => 'Back to Program',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Error/Success Messages -->
            <?php if (!empty($message)): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showToast('<?= ucfirst($messageType) ?>', <?= json_encode($message) ?>, '<?= $messageType ?>');
                    });
                </script>
            <?php endif; ?>

            <!-- Simple Program Editing Form -->
            <div class="card shadow-sm mb-4 w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Program Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" id="editProgramForm">
                        <div class="row">
                            <div class="col-md-8">
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
                                           value="<?php echo htmlspecialchars($_POST['program_name'] ?? $program['program_name']); ?>">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This will be the main identifier for your program
                                    </div>
                                </div>

                                <!-- Initiative Selection -->
                                <div class="mb-4">
                                    <label for="initiative_id" class="form-label">
                                        Link to Initiative
                                        <span class="badge bg-secondary ms-1">Optional</span>
                                    </label>
                                    <select class="form-select" id="initiative_id" name="initiative_id">
                                        <option value="">Select an initiative (optional)</option>
                                        <?php foreach ($active_initiatives as $initiative): ?>
                                            <option value="<?php echo $initiative['initiative_id']; ?>"
                                                    <?php echo (isset($_POST['initiative_id']) ? $_POST['initiative_id'] : $program['initiative_id']) == $initiative['initiative_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                                                <?php if ($initiative['initiative_number']): ?>
                                                    (<?php echo htmlspecialchars($initiative['initiative_number']); ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Link this program to a strategic initiative for better organization and reporting
                                    </div>
                                </div>

                                <!-- Program Number -->
                                <div class="mb-4">
                                    <label for="program_number" class="form-label">
                                        Program Number
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="program_number" 
                                           name="program_number" 
                                           placeholder="Select initiative first"
                                           disabled
                                           pattern="[\w.]+"
                                           title="Program number can contain letters, numbers, and dots"
                                           value="<?php echo htmlspecialchars($_POST['program_number'] ?? $program['program_number'] ?? ''); ?>">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <span id="number-help-text">Select an initiative to enable program numbering</span>
                                    </div>
                                    <div id="final-number-display" class="mt-1" style="display: none;">
                                        <small class="text-muted">Final number will be: <span id="final-number-preview"></span></small>
                                    </div>
                                    <div id="number-validation" class="mt-2" style="display: none;">
                                        <small id="validation-message"></small>
                                    </div>
                                </div>

                                <!-- Brief Description -->
                                <div class="mb-4">
                                    <label for="brief_description" class="form-label">Brief Description</label>
                                    <textarea class="form-control" 
                                              id="brief_description" 
                                              name="brief_description"
                                              rows="3"
                                              placeholder="Provide a short summary of the program"><?php echo htmlspecialchars($_POST['brief_description'] ?? $program['program_description'] ?? ''); ?></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        A brief overview to help identify this program
                                    </div>
                                </div>

                                <!-- Program Rating - Only visible to focal users -->
                                <?php if (is_focal_user()): ?>
                                <div class="mb-4">
                                    <label for="rating" class="form-label">
                                        Program Rating <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="rating" name="rating" required>
                                        <option value="">Select a rating</option>
                                        <?php 
                                        $current_rating = $_POST['rating'] ?? $program['rating'] ?? RATING_NOT_STARTED;
                                        $rating_options = get_rating_options();
                                        foreach ($rating_options as $value => $label): 
                                        ?>
                                            <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $current_rating == $value ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-chart-line me-1"></i>
                                        Summarized rating of this program
                                    </div>
                                </div>
                                <?php else: ?>
                                <!-- Hidden rating field for non-focal users -->
                                <input type="hidden" name="rating" value="<?php echo htmlspecialchars($program['rating'] ?? RATING_NOT_STARTED); ?>">
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4">
                                <!-- Timeline Section -->
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            Timeline
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Start Date -->
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">
                                                Start Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="start_date" 
                                                   name="start_date"
                                                   value="<?php echo htmlspecialchars($_POST['start_date'] ?? $program['start_date'] ?? ''); ?>">
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Optional: Set a start date if the program has a specific timeline
                                            </div>
                                        </div>

                                        <!-- End Date -->
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">
                                                End Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="end_date" 
                                                   name="end_date"
                                                   value="<?php echo htmlspecialchars($_POST['end_date'] ?? $program['end_date'] ?? ''); ?>">
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Optional: Set an end date if the program has a specific timeline
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- User Permissions Section -->
                                <div class="card shadow-sm mt-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-users me-2"></i>
                                            User Permissions
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Current Status -->
                                        <div class="alert alert-<?php echo $restrict_editors ? 'warning' : 'success'; ?> mb-3">
                                            <i class="fas fa-<?php echo $restrict_editors ? 'lock' : 'unlock'; ?> me-2"></i>
                                            <strong>Current Status:</strong> 
                                            <?php if ($restrict_editors): ?>
                                                Editing restricted to specific users
                                            <?php else: ?>
                                                All agency users can edit
                                            <?php endif; ?>
                                        </div>

                                        <!-- Restrict Editors Toggle -->
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="restrict_editors" name="restrict_editors"
                                                       <?php echo $restrict_editors ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="restrict_editors">
                                                    <strong>Restrict editing to specific users</strong>
                                                </label>
                                            </div>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                When disabled, all agency users can edit. When enabled, only selected users can edit.
                                            </div>
                                        </div>

                                        <!-- User Selection (shown when restrictions are enabled) -->
                                        <div id="userSelectionSection" style="display: <?php echo $restrict_editors ? 'block' : 'none'; ?>;">
                                            <label class="form-label">
                                                <i class="fas fa-user-edit me-1"></i>
                                                Select users who can edit this program:
                                            </label>
                                            
                                            <?php if (!empty($assignable_users)): ?>
                                                <div class="user-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 0.75rem;">
                                                    <?php 
                                                    // Create array of currently assigned editor user IDs for easy checking
                                                    $assigned_editor_ids = array_column(array_filter($current_user_assignments, function($assignment) {
                                                        return $assignment['role'] === 'editor';
                                                    }), 'user_id');
                                                    ?>
                                                    
                                                    <?php foreach ($assignable_users as $user): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="assigned_editors[]" 
                                                                   value="<?php echo $user['user_id']; ?>"
                                                                   id="user_<?php echo $user['user_id']; ?>"
                                                                   <?php echo in_array($user['user_id'], $assigned_editor_ids) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="user_<?php echo $user['user_id']; ?>">
                                                                <strong><?php echo htmlspecialchars($user['fullname'] ?: $user['username']); ?></strong>
                                                                <br><small class="text-muted"><?php echo htmlspecialchars($user['username']); ?></small>
                                                                <?php if ($user['current_role']): ?>
                                                                    <span class="badge bg-info ms-2"><?php echo ucfirst($user['current_role']); ?></span>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                
                                                <!-- Select All / None buttons -->
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="selectAllUsers()">
                                                        <i class="fas fa-check-double me-1"></i>Select All
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectNoUsers()">
                                                        <i class="fas fa-times me-1"></i>Select None
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    No assignable users found.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info Card -->
                                <div class="card shadow-sm mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2"></i>
                                            What You Can Edit
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Program name and description
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-link text-primary me-2"></i>
                                                Initiative linkage
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-hashtag text-info me-2"></i>
                                                Program number
                                            </li>
                                            <li>
                                                <i class="fas fa-calendar text-warning me-2"></i>
                                                Timeline dates
                                            </li>
                                        </ul>
                                        <hr>
                                        <div class="alert alert-info mb-0">
                                            <small>
                                                <i class="fas fa-info-circle me-1"></i>
                                                <strong>Note:</strong> Submissions are managed separately. Use the "Add Submission" button on the program details page to add or edit submissions.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="program_details.php?id=<?php echo $program_id; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Update Program
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const initiativeSelect = document.getElementById('initiative_id');
    const programNumberInput = document.getElementById('program_number');
    
    // Initialize program number field based on current initiative
    const currentInitiative = initiativeSelect.value;
    if (currentInitiative) {
        programNumberInput.disabled = false;
        programNumberInput.placeholder = 'Enter program number';
        document.getElementById('number-help-text').textContent = 'Enter a program number or leave blank for auto-generation';
        
        // Show final number preview
        document.getElementById('final-number-display').style.display = 'block';
        const currentNumber = programNumberInput.value;
        document.getElementById('final-number-preview').textContent = currentNumber || 'Will be generated automatically';
    }

    // Handle initiative selection for program numbering
    initiativeSelect.addEventListener('change', function() {
        const selectedInitiative = this.value;
        const helpText = document.getElementById('number-help-text');
        const finalNumberDisplay = document.getElementById('final-number-display');
        const finalNumberPreview = document.getElementById('final-number-preview');
        
        if (selectedInitiative) {
            programNumberInput.disabled = false;
            programNumberInput.placeholder = 'Enter program number';
            helpText.textContent = 'Enter a program number or leave blank for auto-generation';
            
            // Show final number preview
            finalNumberDisplay.style.display = 'block';
            const currentNumber = programNumberInput.value;
            finalNumberPreview.textContent = currentNumber || 'Will be generated automatically';
        } else {
            programNumberInput.disabled = true;
            programNumberInput.placeholder = 'Select initiative first';
            helpText.textContent = 'Select an initiative to enable program numbering';
            finalNumberDisplay.style.display = 'none';
        }
    });

    // Handle program number validation
    programNumberInput.addEventListener('input', function() {
        const number = this.value.trim();
        const validationDiv = document.getElementById('number-validation');
        const validationMessage = document.getElementById('validation-message');
        const finalNumberPreview = document.getElementById('final-number-preview');
        
        if (number) {
            // Basic validation
            if (/^[a-zA-Z0-9.]+$/.test(number)) {
                validationDiv.style.display = 'block';
                validationMessage.className = 'text-success';
                validationMessage.textContent = 'Valid program number format';
                finalNumberPreview.textContent = number;
            } else {
                validationDiv.style.display = 'block';
                validationMessage.className = 'text-danger';
                validationMessage.textContent = 'Invalid format. Use only letters, numbers, and dots.';
            }
        } else {
            validationDiv.style.display = 'none';
            finalNumberPreview.textContent = 'Will be generated automatically';
        }
    });

// Handle restrict editors toggle
document.getElementById('restrict_editors').addEventListener('change', function() {
    const userSection = document.getElementById('userSelectionSection');
    if (this.checked) {
        userSection.style.display = 'block';
    } else {
        userSection.style.display = 'none';
        // Uncheck all user checkboxes when disabling restrictions
        const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
        userCheckboxes.forEach(checkbox => checkbox.checked = false);
    }
});

// Select all users function
function selectAllUsers() {
    const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
    userCheckboxes.forEach(checkbox => checkbox.checked = true);
}

// Select no users function
function selectNoUsers() {
    const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
    userCheckboxes.forEach(checkbox => checkbox.checked = false);
}

// Initialize user selection section on page load
document.addEventListener('DOMContentLoaded', function() {
    const restrictCheckbox = document.getElementById('restrict_editors');
    const userSection = document.getElementById('userSelectionSection');
    
    // Show/hide user section based on initial checkbox state
    if (restrictCheckbox.checked) {
        userSection.style.display = 'block';
    } else {
        userSection.style.display = 'none';
    }
});
});
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?> 