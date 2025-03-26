<?php
/**
 * View Programs
 * 
 * Interface for agency users to view all their assigned programs.
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

// Additional styles and scripts
$additionalStyles = [
    APP_URL . '/assets/css/custom/agency.css'
];

$additionalScripts = [
    APP_URL . '/assets/js/agency/program_view.js'
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
        <a href="create_program.php" class="btn btn-success me-2">
            <i class="fas fa-plus-circle me-1"></i> Create New Program
        </a>
        <?php if ($current_period && $current_period['status'] === 'open'): ?>
            <a href="submit_program_data.php" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Submit Program Data
            </a>
        <?php endif; ?>
    </div>
</div>

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
                <p class="text-muted">You don't have any programs assigned to your agency yet. Contact the administrator for assistance.</p>
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
                                            <a href="submit_program_data.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-success" title="Submit Data">
                                                <i class="fas fa-edit"></i>
                                            </a>
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

<?php
// Include footer
require_once '../layouts/footer.php';
?>
