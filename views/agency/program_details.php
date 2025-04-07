<?php
/**
 * Program Details - Temporarily Unavailable
 * 
 * This page is currently under maintenance.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Get program ID from URL with validation
$program_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$program_id) {
    $_SESSION['message'] = 'No program specified or invalid program ID.';
    $_SESSION['message_type'] = 'warning';
    header('Location: view_programs.php');
    exit;
}

// Set page title
$pageTitle = 'Program Details - Under Maintenance';

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/agency.css'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Program Details</h1>
        <p class="text-muted mb-0">View program information</p>
    </div>
    <a href="view_programs.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Programs
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="fas fa-tools fa-4x text-muted"></i>
        </div>
        <h3>This Page is Under Maintenance</h3>
        <p class="text-muted">We're working on improving the program details page. Please check back soon.</p>
        <div class="mt-4">
            <a href="view_programs.php" class="btn btn-primary">
                <i class="fas fa-list me-1"></i> View All Programs
            </a>
            <a href="update_program.php?id=<?php echo $program_id; ?>" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-edit me-1"></i> Update This Program
            </a>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
