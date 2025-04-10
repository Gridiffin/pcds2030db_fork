<?php
/**
 * View Programs
 * 
 * Interface for agency users to view their programs.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';
require_once '../../includes/status_helpers.php'; // Make sure this file is included

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
$pageTitle = 'View Programs';

// Get agency programs
$agency_id = $_SESSION['user_id'];

// Define the function if it doesn't exist
if (!function_exists('get_agency_programs')) {
    function get_agency_programs($agency_id) {
        global $conn;
        
        $query = "SELECT p.*, 
                  (SELECT ps.status FROM program_submissions ps 
                   WHERE ps.program_id = p.program_id 
                   ORDER BY ps.submission_date DESC LIMIT 1) as status,
                  (SELECT ps.is_draft FROM program_submissions ps 
                   WHERE ps.program_id = p.program_id 
                   ORDER BY ps.submission_date DESC LIMIT 1) as is_draft,
                  (SELECT ps.submission_date FROM program_submissions ps 
                   WHERE ps.program_id = p.program_id 
                   ORDER BY ps.submission_date DESC LIMIT 1) as updated_at
                  FROM programs p 
                  WHERE p.owner_agency_id = ?
                  ORDER BY p.program_name";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $agency_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $programs = [];
        while ($row = $result->fetch_assoc()) {
            $programs[] = $row;
        }
        
        return $programs;
    }
}

// Get programs data
$programs = get_agency_programs($agency_id);

// Additional scripts - Make sure view_programs.js is loaded
$additionalScripts = [
    APP_URL . '/assets/js/utilities/status_utils.js',
    APP_URL . '/assets/js/agency/view_programs.js' // Ensure this script is included
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Set up header variables
$title = "Agency Programs";
$subtitle = "View and manage your agency's programs";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = [
    [
        'url' => APP_URL . '/views/agency/create_program.php', // Fix: use absolute URL with APP_URL
        'text' => 'Create New Program',
        'icon' => 'fas fa-plus-circle',
        'class' => 'btn-primary'
    ]
];

// Include the dashboard header component with the light style
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

<!-- Filter Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title m-0">
            <i class="fas fa-filter me-2 text-white"></i>Filter Programs
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-5 col-sm-12">
                <label for="programSearch" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="programSearch" placeholder="Search by program name">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="statusFilter" class="form-label">Status</label>
                <select class="form-select" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="target-achieved">Monthly Target Achieved</option>
                    <option value="on-track-yearly">On Track for Year</option>
                    <option value="severe-delay">Severe Delays</option>
                    <option value="not-started">Not Started</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="programTypeFilter" class="form-label">Program Type</label>
                <select class="form-select" id="programTypeFilter">
                    <option value="">All Types</option>
                    <option value="assigned">Assigned Programs</option>
                    <option value="created">Custom Programs</option>
                </select>
            </div>
            <div class="col-md-1 col-sm-12 d-flex align-items-end">
                <button id="resetFilters" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filter indicator will be inserted here by JavaScript -->

<!-- All Programs Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">All Programs</h5>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0" id="programsTable">
                <thead class="table-light">
                    <tr>
                        <th>Program Name</th>
                        <th>Status</th>
                        <th>Last Updated</th>
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
                                </td>
                                <td>
                                    <?php 
                                    // Ensure we're using the new status values by converting any legacy status
                                    $current_status = isset($program['status']) ? convert_legacy_status($program['status']) : 'not-started';
                                    
                                    // Debug the status to see what's coming from the database
                                    // echo "<!-- Status: " . $current_status . " -->";
                                    
                                    // Map database status values to display labels and classes
                                    $status_map = [
                                        'on-track' => ['label' => 'On Track', 'class' => 'warning'],
                                        'on-track-yearly' => ['label' => 'On Track for Year', 'class' => 'warning'],
                                        'target-achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success'],
                                        'delayed' => ['label' => 'Delayed', 'class' => 'danger'],
                                        'severe-delay' => ['label' => 'Severe Delays', 'class' => 'danger'],
                                        'completed' => ['label' => 'Completed', 'class' => 'primary'],
                                        'not-started' => ['label' => 'Not Started', 'class' => 'secondary']
                                    ];
                                    
                                    // Set default if status is not in our map
                                    if (!isset($status_map[$current_status])) {
                                        $current_status = 'not-started';
                                    }
                                    
                                    // Get the label and class from our map
                                    $status_label = $status_map[$current_status]['label'];
                                    $status_class = $status_map[$current_status]['class'];
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo $status_label; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($program['updated_at']) && $program['updated_at']) {
                                        echo date('M j, Y', strtotime($program['updated_at']));
                                    } elseif (isset($program['created_at']) && $program['created_at']) {
                                        echo date('M j, Y', strtotime($program['created_at']));
                                    } else {
                                        echo 'Not set';
                                    }
                                    ?>
                                </td>
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
                                        
                                        <?php if (!$program['is_assigned']): ?>
                                        <button type="button" class="btn btn-outline-danger delete-program-btn" 
                                            data-id="<?php echo $program['program_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                            title="Delete Program">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
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

<!-- Pagination component -->
<div class="pagination-container mt-3 d-flex justify-content-between align-items-center">
    <div>
        <span id="showing-entries">Showing 1-<?php echo min(count($programs), 10); ?> of <?php echo count($programs); ?> entries</span>
    </div>
    <nav aria-label="Program pagination">
        <ul class="pagination pagination-sm" id="programPagination">
            <!-- Pagination will be populated by JavaScript -->
        </ul>
    </nav>
</div>

<!-- Add program data for JavaScript pagination -->
<script>
    // Make program data available to JavaScript for client-side pagination
    const allPrograms = <?php echo json_encode($programs); ?>;
    
    // Set pagination options
    const paginationOptions = {
        itemsPerPage: 10,
        currentPage: 1
    };
</script>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
