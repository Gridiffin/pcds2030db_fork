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
require_once PROJECT_ROOT_PATH . 'lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'lib/numbering_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get active initiatives for dropdown
$active_initiatives = get_initiatives_for_select(true);

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
    
    // Create new program using simplified function
    $result = create_simple_program($program_data);
    
    if (isset($result['success']) && $result['success']) {
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

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Create New Program',
    'subtitle' => 'Create a new program as an empty vessel. Add submissions for specific periods later.',
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
                                                   value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Optional: Set an end date if the program has a specific timeline
                                            </div>
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
                                                Create program (empty vessel)
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

<style>
#period_id option[data-status="open"] {
    font-weight: bold;
    color: #28a745;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('period_id');
    const initiativeSelect = document.getElementById('initiative_id');
    const programNumberInput = document.getElementById('program_number');
    
    // Highlight open periods
    Array.from(periodSelect.options).forEach(option => {
        if (option.dataset.status === 'open') {
            option.classList.add('text-success', 'fw-bold');
        }
    });
    
    // Select an open period by default if none selected
    if (!periodSelect.value) {
        const openOption = Array.from(periodSelect.options).find(
            option => option.dataset.status === 'open'
        );
        if (openOption) {
            openOption.selected = true;
        }
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
            finalNumberPreview.textContent = 'Will be generated automatically';
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
});
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
