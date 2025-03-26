<?php
/**
 * Create Program
 * 
 * Allows agency users to create their own programs.
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
$pageTitle = 'Create New Program';

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract and sanitize form data
    $program_name = trim($_POST['program_name'] ?? '');
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $target = $_POST['target'] ?? '';
    
    // Basic validation
    if (empty($program_name)) {
        $messageType = 'danger';
        $message = 'Program name is required';
    } else if (!empty($start_date) && !empty($end_date) && strtotime($start_date) > strtotime($end_date)) {
        $messageType = 'danger';
        $message = 'End date cannot be before start date';
    } else {
        // Prepare data for function
        $program_data = [
            'program_name' => $program_name,
            'description' => $description,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'target' => $target
        ];
        
        // Create the program
        $result = agency_create_program($program_data);
        
        if (isset($result['success']) && $result['success']) {
            $messageType = 'success';
            $message = $result['message'] ?? 'Program created successfully';
            
            // Redirect to program details after brief delay
            header("Refresh: 2; URL=program_details.php?id=" . $result['program_id']);
        } else {
            $messageType = 'danger';
            $message = $result['error'] ?? 'An unknown error occurred';
        }
    }
}

// Get current period for setting targets
$current_period = get_current_reporting_period();

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/agency.css'
];

// Additional scripts - updated to use the unified JS file
$additionalScripts = [
    APP_URL . '/assets/js/agency/program_management.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">Create New Program</h1>
            <p class="text-muted">Add a new program to your agency portfolio</p>
        </div>
        <a href="view_programs.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Programs
        </a>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="createProgramForm">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="program_name" class="form-label">Program Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="program_name" name="program_name" required>
                    </div>
                    <div class="col-md-4">
                        <label for="sector_id" class="form-label">Sector</label>
                        <input type="text" class="form-control" value="<?php echo get_sector_name($_SESSION['sector_id']); ?>" readonly>
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
                
                <?php if ($current_period): ?>
                <div class="mb-3 border-top pt-3">
                    <label for="target" class="form-label">
                        Initial Target for Q<?php echo $current_period['quarter']; ?>-<?php echo $current_period['year']; ?>
                    </label>
                    <textarea class="form-control" id="target" name="target" rows="2" 
                              placeholder="Example: Plant 100 trees, Train 50 people, etc."></textarea>
                    <small class="form-text text-muted">
                        Specify what you aim to achieve with this program in the current reporting period
                    </small>
                </div>
                <?php endif; ?>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-outline-secondary">Reset</button>
                    <button type="submit" class="btn btn-primary">Create Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Basic form validation
    const form = document.getElementById('createProgramForm');
    
    form.addEventListener('submit', function(e) {
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
    });
});
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
