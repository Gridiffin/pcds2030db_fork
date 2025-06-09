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
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/programs.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is an auto-save request
    if (isset($_POST['auto_save'])) {
        // Handle auto-save via AJAX
        header('Content-Type: application/json');
        
        // Handle targets array data - collect all targets into single strings
        $targets_combined = '';
        $status_descriptions_combined = '';
        
        if (isset($_POST['targets']) && is_array($_POST['targets'])) {
            $target_parts = [];
            $status_parts = [];
            
            foreach ($_POST['targets'] as $target_data) {
                if (isset($target_data['target']) && !empty(trim($target_data['target']))) {
                    $target_parts[] = trim($target_data['target']);
                }
                if (isset($target_data['status_description']) && !empty(trim($target_data['status_description']))) {
                    $status_parts[] = trim($target_data['status_description']);
                }
            }
            
            $targets_combined = implode('; ', $target_parts);
            $status_descriptions_combined = implode('; ', $status_parts);
        }
        
        // Fall back to simple fields if no array data
        if (empty($targets_combined) && isset($_POST['target'])) {
            $targets_combined = $_POST['target'];
        }
        if (empty($status_descriptions_combined) && isset($_POST['status_description'])) {
            $status_descriptions_combined = $_POST['status_description'];
        }
        
        $program_data = [
            'program_id' => $_POST['program_id'] ?? 0,
            'program_name' => $_POST['program_name'] ?? '',
            'brief_description' => $_POST['brief_description'] ?? '',
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'target' => $targets_combined,
            'status_description' => $status_descriptions_combined
        ];
        
        $result = auto_save_program_draft($program_data);
        echo json_encode($result);
        exit;
    }
      // Handle full form submission
    // Handle targets array data - collect all targets into single strings
    $targets_combined = '';
    $status_descriptions_combined = '';
    
    if (isset($_POST['targets']) && is_array($_POST['targets'])) {
        $target_parts = [];
        $status_parts = [];
        
        foreach ($_POST['targets'] as $target_data) {
            if (isset($target_data['target']) && !empty(trim($target_data['target']))) {
                $target_parts[] = trim($target_data['target']);
            }
            if (isset($target_data['status_description']) && !empty(trim($target_data['status_description']))) {
                $status_parts[] = trim($target_data['status_description']);
            }
        }
        
        $targets_combined = implode('; ', $target_parts);
        $status_descriptions_combined = implode('; ', $status_parts);
    }
    
    // Fall back to simple fields if no array data
    if (empty($targets_combined) && isset($_POST['target'])) {
        $targets_combined = $_POST['target'];
    }
    if (empty($status_descriptions_combined) && isset($_POST['status_description'])) {
        $status_descriptions_combined = $_POST['status_description'];
    }
      $program_data = [
        'program_name' => $_POST['program_name'] ?? '',
        'brief_description' => $_POST['brief_description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'target' => $targets_combined,
        'status_description' => $status_descriptions_combined
    ];
    
    // Check if this is an update to existing program or new creation
    $program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
    
    if ($program_id > 0) {
        // Update existing program draft
        $result = update_wizard_program_draft($program_id, $program_data);
    } else {
        // Create new comprehensive program draft using wizard function
        $result = create_wizard_program_draft($program_data);
    }
    
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
require_once '../../layouts/header.php';

// Include agency navigation
require_once '../../layouts/agency_nav.php';

// Set up header variables
$title = "Create New Program";
$subtitle = "Create a new program draft with basic information";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = [
    [
        'url' => 'view_programs.php',
        'text' => 'Back to Programs',
        'icon' => 'fas fa-arrow-left',
        'class' => 'btn-outline-secondary'
    ]
];

// Include the dashboard header component
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
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
<?php endif; ?>            <!-- Program Creation Wizard -->
            <div class="card shadow-sm mb-4 w-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Create New Program
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Wizard Progress Indicator -->
                    <div class="wizard-progress mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="step-indicator active" id="step-1-indicator">
                                <div class="step-number">1</div>
                                <div class="step-label">Basic Info</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step-indicator" id="step-2-indicator">
                                <div class="step-number">2</div>
                                <div class="step-label">Targets</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step-indicator" id="step-3-indicator">
                                <div class="step-number">3</div>
                                <div class="step-label">Review</div>
                            </div>
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-primary" id="wizard-progress-bar" style="width: 25%"></div>
                        </div>
                    </div>                    <!-- Wizard Form -->
                    <form id="createProgramWizard" method="post">
                        <!-- Hidden field to track program_id for auto-save -->
                        <input type="hidden" id="program_id" name="program_id" value="0">
                        <!-- Step 1: Basic Information -->
                        <div class="wizard-step active" id="step-1">
                            <div class="step-content">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Basic Program Information
                                </h6>
                                
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

                                <!-- Add missing start_date and end_date input elements -->
                                <div class="form-group mb-4">
                                    <label for="start_date" class="form-label">
                                        Start Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="start_date" 
                                           name="start_date" 
                                           required>
                                </div>
                                <div class="form-group mb-4">
                                    <label for="end_date" class="form-label">
                                        End Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="end_date" 
                                           name="end_date" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Targets -->
                        <div class="wizard-step" id="step-2">
                            <div class="step-content">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-bullseye me-2"></i>
                                    Targets
                                </h6>
                                
                                <!-- Dynamic Targets and Status Descriptions -->
                                <div id="targets-container">
                                    <!-- Initial target group will be added by JavaScript -->
                                </div>

                                <button type="button" id="add-target-button" class="btn btn-secondary">Add Another Target</button>
                            </div>
                        </div>

                        <!-- Step 3: Review & Save -->
                        <div class="wizard-step" id="step-3">
                            <div class="step-content">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-eye me-2"></i>
                                    Review Program Information
                                </h6>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Please review your program information below. You can go back to previous steps to make changes.
                                </div>                                <!-- Review Summary -->
                                <div class="review-summary">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="review-section mb-3">
                                                <h6 class="text-muted mb-2">Program Name</h6>
                                                <p class="mb-0" id="review-program-name">-</p>
                                            </div>
                                            <div class="review-section mb-3">
                                                <h6 class="text-muted mb-2">Timeline</h6>
                                                <p class="mb-0" id="review-timeline">-</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="review-section mb-3">
                                                <h6 class="text-muted mb-2">Brief Description</h6>
                                                <p class="mb-0" id="review-description">-</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Targets Section -->
                                    <div class="review-section mt-4">
                                        <h6 class="text-muted mb-2">Targets</h6>
                                        <div id="review-targets">
                                            <table class="review-target-table">
                                                <thead>
                                                    <tr>
                                                        <th>Target</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- rows injected by JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Save Note -->
                                <div class="mt-4 p-3 bg-light rounded">
                                    <small class="text-muted">
                                        <i class="fas fa-save me-1"></i>
                                        <strong>Note:</strong> This will save your program as a draft. You can edit and add more details anytime before submitting for review.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Wizard Navigation -->
                        <div class="wizard-navigation mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="display: none;">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Previous
                                </button>
                                <div class="ms-auto">
                                    <button type="button" class="btn btn-primary" id="nextBtn">
                                        Next
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                    <button type="submit" class="btn btn-success" id="saveDraftBtn" style="display: none;">
                                        <i class="fas fa-save me-2"></i>
                                        Save Draft
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Auto-save Status -->
                        <div class="auto-save-status mt-2 text-center" id="autoSaveStatus" style="display: none;">
                            <small class="text-muted">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Changes saved automatically
                            </small>
                        </div>
                    </form>
                </div>
            </div>

<!-- Wizard CSS -->
<style>
.wizard-progress {
    margin-bottom: 2rem;
}

.step-indicator {
    text-align: center;
    position: relative;
    flex: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step-indicator.active .step-number {
    background-color: #0d6efd;
    color: white;
}

.step-indicator.completed .step-number {
    background-color: #198754;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
}

.step-indicator.active .step-label {
    color: #0d6efd;
    font-weight: 600;
}

.step-indicator.completed .step-label {
    color: #198754;
    font-weight: 600;
}

.step-line {
    height: 2px;
    background-color: #e9ecef;
    flex: 1;
    margin: 0 15px;
    margin-top: 20px;
    transition: all 0.3s ease;
}

.step-line.completed {
    background-color: #198754;
}

.wizard-step {
    display: none;
    animation: fadeIn 0.3s ease-in-out;
}

.wizard-step.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

.review-section {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1rem;
}

.review-section:last-child {
    border-bottom: none;
}

.auto-save-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
}

.targets-list .target-item {
    border-left: 3px solid #0d6efd;
    transition: all 0.2s ease;
}

.targets-list .target-item:hover {
    background-color: #f8f9fa !important;
    border-left-color: #198754;
}

.target-text {
    font-size: 0.95rem;
    line-height: 1.4;
}

.status-text small {
    font-style: italic;
}
</style>

<!-- Wizard JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wizard state
    let currentStep = 1;
    const totalSteps = 3;
    let formData = {};
    
    // Elements
    const wizard = document.getElementById('createProgramWizard');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    const progressBar = document.getElementById('wizard-progress-bar');
    const autoSaveStatus = document.getElementById('autoSaveStatus');
    
    // Form inputs for auto-save
    const formInputs = wizard.querySelectorAll('input, select, textarea');
    
    // Initialize wizard
    initializeWizard();
    
    function initializeWizard() {
        showStep(currentStep);
        updateProgressBar();
        setupAutoSave();
        setupValidation();
    }
    
    function showStep(step) {
        // Hide all steps
        document.querySelectorAll('.wizard-step').forEach(stepEl => {
            stepEl.classList.remove('active');
        });
        
        // Show current step
        document.getElementById(`step-${step}`).classList.add('active');
        
        // Update step indicators
        updateStepIndicators(step);
        
        // Update navigation buttons
        updateNavigationButtons(step);
        
        // Update progress bar
        updateProgressBar();
        
        // Update review if on step 3
        if (step === 3) {
            updateReviewSummary();
        }
    }
    
    function updateStepIndicators(step) {
        for (let i = 1; i <= totalSteps; i++) {
            const indicator = document.getElementById(`step-${i}-indicator`);
            const stepLine = indicator.nextElementSibling;
            
            indicator.classList.remove('active', 'completed');
            
            if (i < step) {
                indicator.classList.add('completed');
                if (stepLine && stepLine.classList.contains('step-line')) {
                    stepLine.classList.add('completed');
                }
            } else if (i === step) {
                indicator.classList.add('active');
            } else {
                if (stepLine && stepLine.classList.contains('step-line')) {
                    stepLine.classList.remove('completed');
                }
            }
        }
    }
    
    function updateNavigationButtons(step) {
        prevBtn.style.display = step > 1 ? 'inline-block' : 'none';
        nextBtn.style.display = step < totalSteps ? 'inline-block' : 'none';
        saveDraftBtn.style.display = step === totalSteps ? 'inline-block' : 'none';
    }
    
    function updateProgressBar() {
        const progressPercentage = (currentStep / totalSteps) * 100;
        progressBar.style.width = progressPercentage + '%';
    }
    function updateReviewSummary() {
        const data = collectFormData();

        // Program Name
        document.getElementById('review-program-name').textContent = data.program_name || '-';

        // Timeline
        let timeline = '-';
        if (data.start_date && data.end_date) {
            timeline = `${formatDate(data.start_date)} - ${formatDate(data.end_date)}`;
        } else if (data.start_date) {
            timeline = `Starts: ${formatDate(data.start_date)}`;
        } else if (data.end_date) {
            timeline = `Ends: ${formatDate(data.end_date)}`;
        }
        document.getElementById('review-timeline').textContent = timeline;

        // Brief Description
        document.getElementById('review-description').textContent =
            data.brief_description || 'No description provided';
        // Targets
        const tbody = [];
        if (data.targets && data.targets.length) {
            data.targets.forEach(t => {
                const tgt = escapeHtml(t.target || '');
                const st  = escapeHtml(t.status_description || '');
                if (tgt || st) {
                    tbody.push(`
                        <tr>
                        <td>${tgt || '-'}</td>
                        <td>${st  || '-'}</td>
                        </tr>
                    `);
                }
            });
        }
        const tableBody = tbody.length
            ? tbody.join('')
            : `<tr><td colspan="2" class="text-muted">No targets added yet</td></tr>`;

        document
        .querySelector('#review-targets tbody')
        .innerHTML = tableBody;
    }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }
        
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        function collectFormData() {
            const data = {};
            
            // Collect basic form inputs
            formInputs.forEach(input => {
                if (input.name && input.name !== 'targets' && !input.name.startsWith('targets[')) {
                    data[input.name] = input.value;
                }
            });
            
            // Collect targets data specifically
            const targetGroups = document.querySelectorAll('.target-group');
            if (targetGroups.length > 0) {
                data.targets = [];
                targetGroups.forEach(group => {
                    const targetInput = group.querySelector('input[name*="[target]"]');
                    const statusInput = group.querySelector('input[name*="[status_description]"]');
                    
                    if (targetInput || statusInput) {
                        data.targets.push({
                            target: targetInput ? targetInput.value : '',
                            status_description: statusInput ? statusInput.value : ''
                        });
                    }
                });
            }
            
            return data;
        }
        
    function validateStep(step) {
        const stepElement = document.getElementById(`step-${step}`);
        const requiredFields = stepElement.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                showFieldError(field, `${field.previousElementSibling.textContent.replace('*', '').trim()} is required`);
                isValid = false;
            } else {
                clearFieldError(field);
            }
        });
        
        // Step 1 specific validation
        if (step === 1) {
            const programName = document.getElementById('program_name');
            if (programName.value.trim() && programName.value.trim().length < 3) {
                showFieldError(programName, 'Program name must be at least 3 characters long');
                isValid = false;
            }
        }
        
        // Step 2 specific validation
        if (step === 2) {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            
            if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
                showFieldError(endDate, 'End date cannot be before start date');
                isValid = false;
            }
        }
        
        return isValid;
    }
      function setupAutoSave() {
        let autoSaveTimeout;
        
        // Setup auto-save for existing form inputs
        function addAutoSaveToInputs() {
            const currentInputs = wizard.querySelectorAll('input, select, textarea');
            currentInputs.forEach(input => {
                // Remove existing listeners to avoid duplicates
                input.removeEventListener('input', handleInputChange);
                input.addEventListener('input', handleInputChange);
            });
        }
        
        function handleInputChange() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                autoSaveFormData();
            }, 2000); // Auto-save after 2 seconds of inactivity
        }
        
        // Initial setup
        addAutoSaveToInputs();
        
        // Re-setup auto-save when new targets are added
        const addTargetButton = document.getElementById('add-target-button');
        if (addTargetButton) {
            const originalClick = addTargetButton.onclick;
            addTargetButton.onclick = function() {
                if (originalClick) originalClick.call(this);
                // Re-setup auto-save for new inputs after a short delay
                setTimeout(addAutoSaveToInputs, 100);
            };
        }
    }
      function autoSaveFormData() {
        const data = collectFormData();
        
        // Only auto-save if program name is provided
        if (!data.program_name || data.program_name.trim().length < 3) {
            return;
        }
        
        // Show saving indicator
        showAutoSaveStatus('Saving...', 'warning');
        
        // Prepare data for auto-save
        const formData = new FormData();
        
        // Add basic form fields
        Object.keys(data).forEach(key => {
            if (key !== 'targets' && data[key]) {
                formData.append(key, data[key]);
            }
        });
        
        // Add targets array data
        if (data.targets && data.targets.length > 0) {
            data.targets.forEach((target, index) => {
                if (target.target) {
                    formData.append(`targets[${index}][target]`, target.target);
                }
                if (target.status_description) {
                    formData.append(`targets[${index}][status_description]`, target.status_description);
                }
            });
        }
        
        formData.append('auto_save', '1');
        
        // Send AJAX request
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Store program_id for subsequent auto-saves
                if (result.program_id) {
                    document.getElementById('program_id').value = result.program_id;
                }
                showAutoSaveStatus('Saved', 'success');
            } else {
                showAutoSaveStatus('Save failed', 'error');
                console.error('Auto-save failed:', result.error);
            }
        })
        .catch(error => {
            console.error('Auto-save error:', error);
            showAutoSaveStatus('Save failed', 'error');
        });
    }
    
    function showAutoSaveStatus(message, type) {
        const iconClass = type === 'success' ? 'fa-check-circle text-success' : 
                         type === 'warning' ? 'fa-clock text-warning' : 
                         'fa-exclamation-circle text-danger';
        
        autoSaveStatus.innerHTML = `
            <small class="text-muted">
                <i class="fas ${iconClass} me-1"></i>
                ${message}
            </small>
        `;
        autoSaveStatus.style.display = 'block';
        
        // Hide after 3 seconds for success/error messages
        if (type !== 'warning') {
            setTimeout(() => {
                autoSaveStatus.style.display = 'none';
            }, 3000);
        }
    }
    
    function setupValidation() {
        // Real-time validation for program name
        const programNameInput = document.getElementById('program_name');
        programNameInput.addEventListener('blur', function() {
            const name = this.value.trim();
            if (name === '') {
                showFieldError(this, 'Program name is required');
            } else if (name.length < 3) {
                showFieldError(this, 'Program name must be at least 3 characters long');
            } else {
                clearFieldError(this);
            }
        });
        
        // Date validation
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        // Ensure the elements exist before accessing them
        if (startDateInput && endDateInput) {
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
            
            startDateInput.addEventListener('change', validateDates);
            endDateInput.addEventListener('change', validateDates);
        } else {
            console.error('Start date or end date input element is missing in the DOM.');
        }
    }
    
    // Navigation event listeners
    // Ensure DOM is fully loaded and nextBtn exists
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        });
    } else {
        console.error('nextBtn does not exist in the DOM');
    }
    
    prevBtn.addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });
    
    // Form submission
    wizard.addEventListener('submit', function(e) {
        if (!validateStep(currentStep)) {
            e.preventDefault();
            return false;
        }
        
        // Disable save button to prevent double submission
        saveDraftBtn.disabled = true;
        saveDraftBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving Draft...';
    });
    
    // Step indicator click navigation
    for (let i = 1; i <= totalSteps; i++) {
        document.getElementById(`step-${i}-indicator`).addEventListener('click', function() {
            if (i <= currentStep || validateStep(currentStep)) {
                currentStep = i;
                showStep(currentStep);
            }
        });
    }
      // JavaScript to dynamically add and remove targets with numbering
    function updateTargetNumbers() {
        const targetGroups = document.querySelectorAll('.target-group');
        targetGroups.forEach((group, index) => {
            const label = group.querySelector('.target-label');
            label.textContent = `Target ${index + 1}`;
            
            // Update input names to maintain proper indexing
            const targetInput = group.querySelector('input[name*="[target]"]');
            const statusInput = group.querySelector('input[name*="[status_description]"]');
            
            if (targetInput) {
                targetInput.name = `targets[${index}][target]`;
            }
            if (statusInput) {
                statusInput.name = `targets[${index}][status_description]`;
            }
        });
    }

    document.getElementById('add-target-button').addEventListener('click', function() {
        const container = document.getElementById('targets-container');
        const index = container.querySelectorAll('.target-group').length;

        const targetGroup = document.createElement('div');
        targetGroup.className = 'form-group target-group mb-3';

        targetGroup.innerHTML = `
            <label class="target-label fw-bold">Target ${index + 1}</label>
            <input type="text" name="targets[${index}][target]" class="form-control mb-2" placeholder="Enter target" required>

            <label class="fw-bold">Status Description</label>
            <input type="text" name="targets[${index}][status_description]" class="form-control mb-2" placeholder="Enter status description" required>

            <button type="button" class="btn btn-danger btn-sm remove-target-button">Remove Target</button>
        `;

        container.appendChild(targetGroup);
        updateTargetNumbers();

        // Add event listener for the remove button
        targetGroup.querySelector('.remove-target-button').addEventListener('click', function() {
            targetGroup.remove();
            updateTargetNumbers();
            // Trigger auto-save after removal
            setTimeout(() => {
                autoSaveFormData();
            }, 500);
        });
        
        // Setup auto-save for new inputs
        const newInputs = targetGroup.querySelectorAll('input');
        newInputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(window.autoSaveTimeout);
                window.autoSaveTimeout = setTimeout(() => {
                    autoSaveFormData();
                }, 2000);
            });
        });
    });
      // Initialize the first target group
    const container = document.getElementById('targets-container');
    container.innerHTML = '';

    const targetGroup = document.createElement('div');
    targetGroup.className = 'form-group target-group mb-3';

    targetGroup.innerHTML = `
        <label class="target-label fw-bold">Target 1</label>
        <input type="text" name="targets[0][target]" class="form-control mb-2" placeholder="Enter target" required>

        <label class="fw-bold">Status Description <small class="text-muted">(e.g., "In progress")</small></label>
        <input type="text" name="targets[0][status_description]" class="form-control mb-2" placeholder="Enter status description" required>
    `;

    container.appendChild(targetGroup);
    
    // Setup auto-save for initial target inputs
    const initialInputs = targetGroup.querySelectorAll('input');
    initialInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(window.autoSaveTimeout);
            window.autoSaveTimeout = setTimeout(() => {
                autoSaveFormData();
            }, 2000);
        });
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
