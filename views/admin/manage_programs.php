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
                        <th>Status</th>
                        <th>Status Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($programs)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">No programs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($programs as $program): ?>
                            <tr class="<?php echo isset($program['is_draft']) && $program['is_draft'] ? 'draft-program' : ''; ?>" data-program-type="<?php echo $program['is_assigned'] ? 'assigned' : 'created'; ?>">
                                <td>
                                    <div class="fw-medium">
                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                        <?php if (isset($program['is_draft']) && $program['is_draft']): ?>
                                            <span class="draft-indicator" title="Draft"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted program-type-indicator">
                                        <i class="fas fa-<?php echo $program['is_assigned'] ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                        <?php echo $program['is_assigned'] ? 'Assigned Program' : 'Custom Program'; ?>
                                    </div>
                                    <?php if (!empty($program['description'])): ?>
                                        <div class="small text-muted"><?php echo substr(htmlspecialchars($program['description']), 0, 100); ?><?php echo strlen($program['description']) > 100 ? '...' : ''; ?></div>
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
                                        
                                        <?php if (isset($program['is_draft']) && $program['is_draft']): ?>
                                        <a href="update_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-secondary" title="Edit Program">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        
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

<!-- Program Type Tabs -->
<ul class="nav nav-tabs px-3 pt-2 border-0" id="programTypeTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="all-programs-tab" data-bs-toggle="tab" data-bs-target="#all-programs-content" type="button" role="tab" aria-controls="all-programs-content" aria-selected="true">
            All Programs <span class="badge bg-primary ms-1"><?php echo count($programs); ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="created-programs-tab" data-bs-toggle="tab" data-bs-target="#created-programs-content" type="button" role="tab" aria-controls="created-programs-content" aria-selected="false">
            Agency-Created <span class="badge bg-success ms-1"><?php echo count(array_filter($programs, function($p) { return !$p['is_assigned']; })); ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="assigned-programs-tab" data-bs-toggle="tab" data-bs-target="#assigned-programs-content" type="button" role="tab" aria-controls="assigned-programs-content" aria-selected="false">
            Assigned <span class="badge bg-info ms-1"><?php echo count(array_filter($programs, function($p) { return $p['is_assigned']; })); ?></span>
        </button>
    </li>
</ul>

<div class="tab-content" id="programTypeTabsContent">
    <div class="tab-pane fade show active" id="all-programs-content" role="tabpanel" aria-labelledby="all-programs-tab">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0" id="programsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Program Name</th>
                            <th>Status</th>
                            <th>Status Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($programs)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">No programs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($programs as $program): ?>
                                <tr class="<?php echo $program['is_assigned'] ? 'assigned-program' : 'created-program'; ?> <?php echo isset($program['is_draft']) && $program['is_draft'] ? 'draft-program' : ''; ?>">
                                    <td>
                                        <div class="fw-medium">
                                            <?php echo htmlspecialchars($program['program_name']); ?>
                                            <?php if (isset($program['is_draft']) && $program['is_draft']): ?>
                                                <span class="draft-indicator" title="Draft"></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small text-muted program-type-indicator">
                                            <i class="fas fa-<?php echo $program['is_assigned'] ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                            <?php echo $program['is_assigned'] ? 'Assigned Program' : 'Custom Program'; ?>
                                        </div>
                                        <?php if (!empty($program['description'])): ?>
                                            <div class="small text-muted"><?php echo substr(htmlspecialchars($program['description']), 0, 100); ?><?php echo strlen($program['description']) > 100 ? '...' : ''; ?></div>
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
                                            
                                            <?php if (isset($program['is_draft']) && $program['is_draft']): ?>
                                            <a href="update_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-secondary" title="Edit Program">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php endif; ?>
                                            
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
    
    <!-- Similar content for other tabs - created and assigned programs -->
    <!-- ...created-programs-content tab pane... -->
    <!-- ...assigned-programs-content tab pane... -->
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