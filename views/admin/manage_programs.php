<?php
/**
 * Manage Programs
 * 
 * Interface for admin users to manage all programs.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin_functions.php';

// Verify user is an admin
if (!is_admin()) {
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

// Get all programs
$programs = get_all_programs();

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/admin.css'
];

// Additional scripts - Ensure manage_programs.js is loaded
$additionalScripts = [
    APP_URL . '/assets/js/utilities/status_utils.js',
    APP_URL . '/assets/js/admin/manage_programs.js'  // Ensure this script is included
];

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Set dashboard header variables and include component
$title = "Manage Programs";
$subtitle = "View, update, and create programs for all agencies";
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

<!-- Program Statistics -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-light h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon-container bg-primary">
                    <i class="fas fa-project-diagram text-white"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-0">Total Programs</h6>
                    <div class="h4 mb-0"><?php echo count($programs); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-light h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon-container bg-primary">
                    <i class="fas fa-tasks text-white"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-0">Assigned Programs</h6>
                    <div class="h4 mb-0"><?php echo count(array_filter($programs, function($program) { return $program['is_assigned'] == 1; })); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-light h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon-container bg-success">
                    <i class="fas fa-folder-plus text-white"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-0">Agency-Created</h6>
                    <div class="h4 mb-0"><?php echo count(array_filter($programs, function($program) { return $program['is_assigned'] == 0; })); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-light h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon-container bg-info">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-0">On Track</h6>
                    <?php 
                    $on_track_count = 0;
                    foreach ($programs as $program) {
                        if (($program['status'] ?? '') === 'on-track') {
                            $on_track_count++;
                        }
                    }
                    ?>
                    <div class="h4 mb-0"><?php echo $on_track_count; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title">Program Filters</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="programSearch" class="form-label">Search Programs</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="programSearch" placeholder="Search by program name...">
                </div>
                <small class="form-text text-muted">Search by program name or description</small>
            </div>
            <div class="col-md-3">
                <label for="statusFilter" class="form-label">Status Filter</label>
                <select class="form-select" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="on-track">On Track</option>
                    <option value="delayed">Delayed</option>
                    <option value="completed">Completed</option>
                    <option value="not-started">Not Started</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="programTypeFilter" class="form-label">Program Type</label>
                <select class="form-select" id="programTypeFilter">
                    <option value="">All Programs</option>
                    <option value="assigned">Assigned Programs</option>
                    <option value="created">Agency-Created Programs</option>
                </select>
            </div>
        </div>
        
        <!-- Add a separate div for the reset button with clear styling -->
        <div class="filter-actions mt-3 text-end">
            <button type="button" id="resetFilters" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-sync-alt me-1"></i> Reset Filters
            </button>
        </div>
    </div>
</div>

<!-- Filter indicator will be inserted here by JavaScript -->

<!-- All Programs Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">All Programs</h5>
        <button type="button" class="btn btn-sm btn-primary" id="createProgramBtn">
            <i class="fas fa-plus-circle me-1"></i> Create Program
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0" id="programsTable">
                <thead class="table-light">
                    <tr>
                        <th>Program Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Status Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($programs)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">No programs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($programs as $program): ?>
                            <tr data-program-type="<?php echo $program['is_assigned'] ? 'assigned' : 'created'; ?>">
                                <td>
                                    <div class="fw-medium">
                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                        <?php if (isset($program['is_draft']) && $program['is_draft']): ?>
                                            <span class="badge bg-warning ms-2">Draft</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($program['description'])): ?>
                                        <div class="small text-muted"><?php echo substr(htmlspecialchars($program['description']), 0, 100); ?><?php echo strlen($program['description']) > 100 ? '...' : ''; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($program['is_assigned']): ?>
                                        <span class="badge bg-primary">Assigned</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Agency-Created</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo get_status_badge($program['status'] ?? 'not-started'); ?>
                                </td>
                                <td><?php echo isset($program['status_date']) && $program['status_date'] ? date('M j, Y', strtotime($program['status_date'])) : 'Not set'; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm float-end">
                                        <a href="program_details.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="update_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-secondary" title="Edit Program">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger delete-program-btn" 
                                            data-id="<?php echo $program['program_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                            title="Delete Program">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the program: <strong id="program-name-display"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="delete_program.php" method="post" id="delete-program-form">
                    <input type="hidden" name="program_id" id="program-id-input">
                    <button type="submit" class="btn btn-danger">Delete Program</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>