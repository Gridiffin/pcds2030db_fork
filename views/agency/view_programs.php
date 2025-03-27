<?php
/**
 * View Programs
 * 
 * Interface for agency users to view and manage their programs.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';
require_once '../../includes/status_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Get message from session if available
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'info';

// Clear message from session
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Set page title
$pageTitle = 'Manage Programs';

// Get programs for agency
$programs = get_agency_programs_by_type();

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/agency.css'
];

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/status_utils.js',
    APP_URL . '/assets/js/agency/view_programs.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Set dashboard header variables and include component
$title = "Manage Programs";
$subtitle = "View, update, and create programs for your agency";
$actions = [
    [
        'url' => 'create_program.php',
        'class' => 'btn-primary',
        'icon' => 'fas fa-plus-circle',
        'text' => 'Create New Program'
    ]
];
require_once '../../includes/dashboard_header.php';
?>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <div><?php echo $message; ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Filters Section -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title">Program Filters</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="programSearch" placeholder="Search programs...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="on-track">On Track</option>
                    <option value="delayed">Delayed</option>
                    <option value="completed">Completed</option>
                    <option value="not-started">Not Started</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="programTypeFilter">
                    <option value="">All Programs</option>
                    <option value="assigned">Assigned Programs</option>
                    <option value="created">My Created Programs</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Assigned Programs -->
<div class="card shadow-sm mb-4 program-section" id="assignedPrograms">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Assigned Programs</h5>
        <span class="badge bg-primary"><?php echo count($programs['assigned']); ?> Programs</span>
    </div>
    <div class="card-body">
        <?php if (empty($programs['assigned'])): ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-project-diagram fa-3x text-muted"></i>
                </div>
                <h5>No assigned programs found</h5>
                <p class="text-muted">Programs assigned by administrators will appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-custom">
                    <thead>
                        <tr>
                            <th>Program Name</th>
                            <th>Target</th>
                            <th>Target Date</th>
                            <th>Status</th>
                            <th>Status Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs['assigned'] as $program): ?>
                            <tr>
                                <td>
                                    <div class="fw-medium"><?php echo $program['program_name']; ?></div>
                                    <?php if (!empty($program['description'])): ?>
                                        <div class="small text-muted"><?php echo substr($program['description'], 0, 100); ?><?php echo strlen($program['description']) > 100 ? '...' : ''; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $program['current_target'] ?? 'Not set'; ?></td>
                                <td><?php echo $program['target_date'] ? date('M j, Y', strtotime($program['target_date'])) : 'Not set'; ?></td>
                                <td>
                                    <?php echo get_status_badge($program['status'] ?? 'not-started'); ?>
                                </td>
                                <td><?php echo $program['status_date'] ? date('M j, Y', strtotime($program['status_date'])) : 'Not set'; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="program_details.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="update_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-secondary" title="Update Status">
                                            <i class="fas fa-edit"></i>
                                        </a>
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

<!-- Agency-Created Programs -->
<div class="card shadow-sm mb-4 program-section" id="createdPrograms">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Agency-Created Programs</h5>
        <span class="badge bg-success"><?php echo count($programs['created']); ?> Programs</span>
    </div>
    <div class="card-body">
        <?php if (empty($programs['created'])): ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-folder-plus fa-3x text-muted"></i>
                </div>
                <h5>No agency-created programs found</h5>
                <p class="text-muted">Create your own programs using the "Create New Program" button at the top of the page.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-custom">
                    <thead>
                        <tr>
                            <th>Program Name</th>
                            <th>Target</th>
                            <th>Target Date</th>
                            <th>Status</th>
                            <th>Status Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs['created'] as $program): ?>
                            <tr>
                                <td>
                                    <div class="fw-medium"><?php echo $program['program_name']; ?></div>
                                    <?php if (!empty($program['description'])): ?>
                                        <div class="small text-muted"><?php echo substr($program['description'], 0, 100); ?><?php echo strlen($program['description']) > 100 ? '...' : ''; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $program['current_target'] ?? 'Not set'; ?></td>
                                <td><?php echo $program['target_date'] ? date('M j, Y', strtotime($program['target_date'])) : 'Not set'; ?></td>
                                <td>
                                    <?php echo get_status_badge($program['status'] ?? 'not-started'); ?>
                                </td>
                                <td><?php echo $program['status_date'] ? date('M j, Y', strtotime($program['status_date'])) : 'Not set'; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="program_details.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="update_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-secondary" title="Update Program">
                                            <i class="fas fa-edit"></i>
                                        </a>
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

<!-- Status Definitions Card -->
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
                        <p class="small text-muted mb-0">Program has been completed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="d-flex align-items-center">
                    <span class="status-dot bg-secondary me-2"></span>
                    <div>
                        <strong>Not Started</strong>
                        <p class="small text-muted mb-0">Program has not begun yet</p>
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
