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

// Combine both program types into a single array for display
$all_programs = [];
foreach ($programs['assigned'] as $program) {
    $program['program_type'] = 'assigned';
    $all_programs[] = $program;
}
foreach ($programs['created'] as $program) {
    $program['program_type'] = 'created';
    $all_programs[] = $program;
}

// Sort programs by updated_at or created_at (most recent first)
usort($all_programs, function($a, $b) {
    $a_date = !empty($a['updated_at']) ? $a['updated_at'] : $a['created_at'];
    $b_date = !empty($b['updated_at']) ? $b['updated_at'] : $b['created_at'];
    return strtotime($b_date) - strtotime($a_date);
});

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
                    <div class="h4 mb-0"><?php echo count($all_programs); ?></div>
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
                    <div class="h4 mb-0"><?php echo count($programs['assigned']); ?></div>
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
                    <div class="h4 mb-0"><?php echo count($programs['created']); ?></div>
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
                    foreach ($all_programs as $program) {
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
                    <option value="created">Agency-Created Programs</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- All Programs -->
<div class="card shadow-sm mb-4 program-section" id="allPrograms">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">All Programs</h5>
        <span class="badge bg-primary"><?php echo count($all_programs); ?> Programs</span>
    </div>
    <div class="card-body">
        <?php if (empty($all_programs)): ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-project-diagram fa-3x text-muted"></i>
                </div>
                <h5>No programs found</h5>
                <p class="text-muted">Create your first program or wait for your administrator to assign programs to you.</p>
                <a href="create_program.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Create New Program
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-custom" id="programsTable">
                    <thead>
                        <tr>
                            <th>Program Name</th>
                            <th>Type</th>
                            <th>Target</th>
                            <!-- Removed Target Date column -->
                            <th>Status</th>
                            <th>Status Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_programs as $program): ?>
                            <tr data-program-type="<?php echo $program['program_type']; ?>">
                                <td>
                                    <div class="fw-medium"><?php echo $program['program_name']; ?></div>
                                    <?php if (!empty($program['description'])): ?>
                                        <div class="small text-muted"><?php echo substr($program['description'], 0, 100); ?><?php echo strlen($program['description']) > 100 ? '...' : ''; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($program['program_type'] === 'assigned'): ?>
                                        <span class="badge bg-primary">Assigned</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Agency-Created</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $program['current_target'] ?? 'Not set'; ?></td>
                                <!-- Removed Target Date column -->
                                <td>
                                    <?php echo get_status_badge($program['status'] ?? 'not-started'); ?>
                                </td>
                                <td><?php echo isset($program['status_date']) && $program['status_date'] ? date('M j, Y', strtotime($program['status_date'])) : 'Not set'; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="program_details.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-primary" title="View Details (Under Maintenance)">
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
