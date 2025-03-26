<?php
/**
 * View Programs
 * 
 * Interface for agency users to view, create, and submit data for their programs.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'My Programs';

// Get current reporting period
$current_period = get_current_reporting_period();

// Get agency's programs
$programs = get_agency_programs();

// Handle program creation form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_program'])) {
    // Process program creation
    $result = agency_create_program($_POST);
    
    if (isset($result['success']) && $result['success']) {
        $messageType = 'success';
        $message = $result['message'] ?? 'Program created successfully.';
        
        // Reload page to show new program
        header("Refresh: 2; URL=view_programs.php");
    } else {
        $messageType = 'danger';
        $message = $result['error'] ?? 'An unknown error occurred';
    }
}

// Handle program data submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_program_data'])) {
    $result = submit_program_data($_POST);
    
    if (isset($result['success'])) {
        $messageType = 'success';
        $message = $result['message'] ?? 'Program data submitted successfully.';
    } else {
        $messageType = 'danger';
        $message = $result['error'] ?? 'Failed to submit program data.';
    }
}

// Additional styles and scripts
$additionalStyles = [
    APP_URL . '/assets/css/custom/agency.css'
];

$additionalScripts = [
    APP_URL . '/assets/js/agency/view_programs.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">My Programs</h1>
        <p class="text-muted">View and manage all programs assigned to your agency</p>
    </div>
    
    <div>
        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#createProgramModal">
            <i class="fas fa-plus-circle me-1"></i> Create New Program
        </button>
        <?php if ($current_period && $current_period['status'] === 'open' && !empty($programs)): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#selectProgramModal">
                <i class="fas fa-edit me-1"></i> Submit Program Data
            </button>
        <?php endif; ?>
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

<!-- Programs List -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Your Assigned Programs</h5>
        <span class="badge bg-primary"><?php echo count($programs); ?> Programs</span>
    </div>
    <div class="card-body">
        <?php if (empty($programs)): ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-project-diagram fa-3x text-muted"></i>
                </div>
                <h5>No programs found</h5>
                <p class="text-muted">
                    You don't have any programs yet. Click "Create New Program" to add your first program.
                </p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-custom">
                    <thead>
                        <tr>
                            <th>Program Name</th>
                            <th>Description</th>
                            <th>Timeline</th>
                            <th>Current Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs as $program): ?>
                            <tr>
                                <td>
                                    <a href="program_details.php?id=<?php echo $program['program_id']; ?>" class="fw-medium text-decoration-none text-primary">
                                        <?php echo $program['program_name']; ?>
                                    </a>
                                </td>
                                <td>
                                    <?php 
                                        $description = $program['description'] ?? '';
                                        echo (strlen($description) > 100) ? substr($description, 0, 100) . '...' : $description; 
                                    ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i> 
                                        <?php echo date('M Y', strtotime($program['start_date'])); ?> - 
                                        <?php echo date('M Y', strtotime($program['end_date'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php 
                                        $status_class = '';
                                        $status_text = $program['status'] ?? 'not-started';
                                        switch($status_text) {
                                            case 'on-track': $status_class = 'success'; break;
                                            case 'delayed': $status_class = 'warning'; break;
                                            case 'completed': $status_class = 'info'; break;
                                            default: $status_class = 'secondary'; $status_text = 'not-started';
                                        }
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo ucwords(str_replace('-', ' ', $status_text)); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="program_details.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($current_period && $current_period['status'] === 'open'): ?>
                                            <button type="button" class="btn btn-outline-success submit-data-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#submitDataModal"
                                                    data-program-id="<?php echo $program['program_id']; ?>"
                                                    data-program-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                                    data-status="<?php echo $status_text; ?>"
                                                    data-target="<?php echo htmlspecialchars($program['current_target'] ?? ''); ?>"
                                                    title="Submit Data">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Program Status Meaning Card -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="card-title m-0">Program Status Definitions</h5>
    </div>
    <div class="card-body pb-2">
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="d-flex align-items-center">
                    <span class="status-dot bg-success me-2"></span>
                    <div>
                        <strong>On Track</strong>
                        <p class="small text-muted mb-0">Program is progressing according to plan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="d-flex align-items-center">
                    <span class="status-dot bg-warning me-2"></span>
                    <div>
                        <strong>Delayed</strong>
                        <p class="small text-muted mb-0">Program is behind schedule</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="d-flex align-items-center">
                    <span class="status-dot bg-info me-2"></span>
                    <div>
                        <strong>Completed</strong>
                        <p class="small text-muted mb-0">Program has been successfully completed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="d-flex align-items-center">
                    <span class="status-dot bg-secondary me-2"></span>
                    <div>
                        <strong>Not Started</strong>
                        <p class="small text-muted mb-0">Program has not yet begun</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Remove the Bootstrap CREATE PROGRAM MODAL -->
<!-- Instead, add a container for the dynamic form -->
<div id="formContainer"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the program management
    initProgramManagement();
    
    // Handle status pill selection
    const statusPills = document.querySelectorAll('.status-pill');
    if (statusPills.length) {
        statusPills.forEach(pill => {
            pill.addEventListener('click', function() {
                statusPills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('submission_status').value = this.dataset.status;
            });
        });
    }
});

/**
 * Initialize program management
 */
function initProgramManagement() {
    // Create program button
    const createProgramButton = document.querySelector('button[data-bs-target="#createProgramModal"]');
    if (createProgramButton) {
        // Remove the bootstrap data attribute
        createProgramButton.removeAttribute('data-bs-target');
        // Add our custom click handler
        createProgramButton.addEventListener('click', function(e) {
            e.preventDefault();
            showCreateProgramForm();
        });
    }
    
    // Handle selection from program select modal
    // ...existing code...
    
    // Handle direct submission button clicks
    // ...existing code...
}

/**
 * Show the create program form using custom modal
 */
function showCreateProgramForm() {
    const formContainer = document.getElementById('formContainer');
    
    const formHtml = `
        <div class="form-overlay">
            <div class="form-wrapper">
                <div class="form-header">
                    <h3>Create New Program</h3>
                    <button type="button" class="close-form">&times;</button>
                </div>
                <form method="POST" action="${window.location.href}" class="p-3" id="createProgramForm">
                    <input type="hidden" name="create_program" value="1">
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="program_name" class="form-label">Program Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="program_name" name="program_name" required>
                        </div>
                        <div class="col-md-4">
                            <label for="sector_id" class="form-label">Sector</label>
                            <input type="text" class="form-control" value="${get_sector_name()}" readonly>
                            <small class="form-text text-muted">Programs can only be created in your sector</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Program Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                    
                    ${getCurrentPeriodHtml()}
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary close-form">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Create Program
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    formContainer.innerHTML = formHtml;
    
    // Set up event listeners for closing
    formContainer.querySelectorAll('.close-form').forEach(button => {
        button.addEventListener('click', hideForm);
    });
    
    // Close on overlay click
    const overlay = formContainer.querySelector('.form-overlay');
    overlay.addEventListener('click', function(e) {
        if (e.target === this) hideForm();
    });
    
    // Set minimum date for start date to today
    const startDateField = document.getElementById('start_date');
    if (startDateField) {
        const today = new Date().toISOString().split('T')[0];
        startDateField.setAttribute('min', today);
    }
    
    // Prevent scrolling on the body
    document.body.style.overflow = 'hidden';
    
    // Add validation to the form
    const form = document.getElementById('createProgramForm');
    if (form) {
        form.addEventListener('submit', validateCreateForm);
    }
}

/**
 * Hide the form
 */
function hideForm() {
    const formContainer = document.getElementById('formContainer');
    formContainer.innerHTML = '';
    document.body.style.overflow = '';
}

/**
 * Validate the create program form
 */
function validateCreateForm(e) {
    const programName = document.getElementById('program_name').value.trim();
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (programName === '') {
        e.preventDefault();
        alert('Program name is required');
        return;
    }
    
    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        e.preventDefault();
        alert('End date cannot be before start date');
        return;
    }
}

/**
 * Get sector name
 */
function get_sector_name() {
    // Get sector name from the badge in the header
    const sectorBadge = document.querySelector('.agency-badge');
    return sectorBadge ? sectorBadge.textContent.trim() : '';
}

/**
 * Get current period HTML
 */
function getCurrentPeriodHtml() {
    // Check if there's a current period label available
    const periodLabel = document.querySelector('.period-badge .badge');
    if (!periodLabel) return '';
    
    // Extract quarter and year from period label
    const periodText = periodLabel.textContent.trim();
    const match = periodText.match(/Q(\d+)-(\d+)/);
    
    if (!match) return '';
    
    const quarter = match[1];
    const year = match[2];
    
    return `
    <div class="mb-3 border-top pt-3">
        <label for="target" class="form-label">
            Initial Target for Q${quarter}-${year}
        </label>
        <textarea class="form-control" id="target" name="target" rows="2" 
                  placeholder="Example: Plant 100 trees, Train 50 people, etc."></textarea>
        <small class="form-text text-muted">
            Specify what you aim to achieve with this program in the current reporting period
        </small>
    </div>
    `;
}
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
