<?php
/**
 * Create Program - Simplified
 * 
 * Simple interface for agency users to create programs with basic information only.
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
require_once PROJECT_ROOT_PATH . 'lib/agencies/program_user_assignments.php';
require_once PROJECT_ROOT_PATH . 'lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'lib/numbering_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get active initiatives for dropdown
$active_initiatives = get_initiatives_for_select(true);

// Get users in current agency for assignment
$agency_id = $_SESSION['agency_id'] ?? null;
$agency_users = [];
if ($agency_id) {
    $stmt = $conn->prepare("
        SELECT user_id, username, fullname 
        FROM users 
        WHERE agency_id = ? AND role = 'agency' AND is_active = 1
        ORDER BY fullname, username
    ");
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $agency_users[] = $row;
    }
}

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    $program_data = [
        'program_name' => $_POST['program_name'] ?? '',
        'program_number' => $_POST['program_number'] ?? '',
        'brief_description' => $_POST['brief_description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'initiative_id' => !empty($_POST['initiative_id']) ? intval($_POST['initiative_id']) : null
    ];
    
    // Handle user assignment settings
    $restrict_editors = isset($_POST['restrict_editors']) ? 1 : 0;
    $assigned_editor_users = isset($_POST['assigned_editors']) ? $_POST['assigned_editors'] : [];
    
    // Create new program using simplified function
    $result = create_simple_program($program_data);
    
    if (isset($result['success']) && $result['success'] && isset($result['program_id'])) {
        $program_id = $result['program_id'];
        
        // Set editor restrictions
        if ($restrict_editors) {
            set_program_editor_restrictions($program_id, true);
            
            // Assign selected users as editors
            if (!empty($assigned_editor_users)) {
                foreach ($assigned_editor_users as $user_id) {
                    assign_user_to_program($program_id, intval($user_id), 'editor', 'Assigned during program creation');
                }
            }
        }
        
        // Set success message and redirect
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = 'success';
        
        // Redirect to programs list
        header('Location: view_programs.php');
        exit;
    } else {
        $message = $result['error'] ?? 'An error occurred while creating the program.';
        $messageType = 'danger';
    }
}

// Set page title
$pageTitle = 'Create New Program';

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/agency/create_program.js'
];

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Create New Program',
    'subtitle' => 'Create a new program template. Add progress reports for specific periods when ready.',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'view_programs.php',
            'text' => 'Back to Programs',
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

            <!-- Initiative data for JavaScript -->
            <script>
                window.initiativeData = <?php echo json_encode($active_initiatives); ?>;
            </script>

            <!-- Simple Program Creation Form -->
            <div class="card shadow-sm mb-4 w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Create New Program
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" id="createProgramForm">
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
                                           value="<?php echo htmlspecialchars($_POST['program_name'] ?? ''); ?>">
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
                                                    <?php echo (isset($_POST['initiative_id']) && $_POST['initiative_id'] == $initiative['initiative_id']) ? 'selected' : ''; ?>>
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
                                           value="<?php echo htmlspecialchars($_POST['program_number'] ?? ''); ?>">
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
                                              placeholder="Provide a short summary of the program"><?php echo htmlspecialchars($_POST['brief_description'] ?? ''); ?></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        A brief overview to help identify this program
                                    </div>
                                </div>
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
                                                   value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Please enter a full date in <strong>YYYY-MM-DD</strong> format. Partial dates (year or year-month) are not accepted.
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
                                                   value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Please enter a full date in <strong>YYYY-MM-DD</strong> format. Partial dates (year or year-month) are not accepted.
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
                                        <!-- Restrict Editors Toggle -->
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="restrict_editors" name="restrict_editors"
                                                       <?php echo (isset($_POST['restrict_editors']) && $_POST['restrict_editors']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="restrict_editors">
                                                    <strong>Restrict editing to specific users</strong>
                                                </label>
                                            </div>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                By default, all agency users can edit. Enable this to limit editing to selected users only.
                                            </div>
                                        </div>

                                        <!-- User Selection (shown when restrictions are enabled) -->
                                        <div id="userSelectionSection" style="display: none;">
                                            <label class="form-label">
                                                <i class="fas fa-user-edit me-1"></i>
                                                Select users who can edit this program:
                                            </label>
                                            
                                            <?php if (!empty($agency_users)): ?>
                                                <div class="user-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 0.75rem;">
                                                    <?php foreach ($agency_users as $user): ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="assigned_editors[]" 
                                                                   value="<?php echo $user['user_id']; ?>"
                                                                   id="user_<?php echo $user['user_id']; ?>"
                                                                   <?php echo (isset($_POST['assigned_editors']) && in_array($user['user_id'], $_POST['assigned_editors'])) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="user_<?php echo $user['user_id']; ?>">
                                                                <strong><?php echo htmlspecialchars($user['fullname'] ?: $user['username']); ?></strong>
                                                                <br><small class="text-muted"><?php echo htmlspecialchars($user['username']); ?></small>
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
                                                    No other users found in your agency.
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
                                            How It Works
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Create program template
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-calendar-plus text-primary me-2"></i>
                                                Add submissions for specific periods
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-bullseye text-info me-2"></i>
                                                Add targets to submissions
                                            </li>
                                            <li>
                                                <i class="fas fa-paperclip text-warning me-2"></i>
                                                Upload attachments to submissions
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="view_programs.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Create Program
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

// Initialize on page load
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
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
