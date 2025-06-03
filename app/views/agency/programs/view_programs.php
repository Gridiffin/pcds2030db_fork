<?php
/**
 * View Programs
 * 
 * Interface for agency users to view their programs.
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
require_once PROJECT_ROOT_PATH . 'lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'lib/rating_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
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

// Add additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/agency/view_programs.js',
    APP_URL . '/assets/js/utilities/table_sorting.js'
];

// Get agency programs
$agency_id = $_SESSION['user_id'];

// Define the function if it doesn't exist
if (!function_exists('get_agency_programs')) {    function get_agency_programs($agency_id) {
        global $conn;
        
        // Fixed query to properly get the latest submission for each program
        // Uses a subquery to find the latest submission_id for each program, then joins back to get the full data
        // Extract rating from JSON content
        $query = "SELECT p.*, 
                         COALESCE(latest_sub.is_draft, 1) as is_draft,
                         latest_sub.period_id,
                         COALESCE(latest_sub.submission_date, p.created_at) as updated_at,
                         latest_sub.submission_id as latest_submission_id,
                         COALESCE(JSON_UNQUOTE(JSON_EXTRACT(latest_sub.content_json, '$.rating')), 'not-started') as rating
                  FROM programs p 
                  LEFT JOIN (
                      SELECT ps1.*
                      FROM program_submissions ps1
                      INNER JOIN (
                          SELECT program_id, MAX(submission_id) as max_submission_id
                          FROM program_submissions
                          GROUP BY program_id
                      ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
                  ) latest_sub ON p.program_id = latest_sub.program_id
                  WHERE p.owner_agency_id = ?
                  ORDER BY p.program_name";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $agency_id);
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

// Separate programs into drafts and finalized submissions
$draft_programs = [];
$finalized_programs = [];

// Get current reporting period once for all programs
$current_period = get_current_reporting_period();

// Process programs and separate into appropriate arrays
foreach ($programs as $program) {
    // Determine if this is a draft submission
    $is_draft = isset($program['is_draft']) && $program['is_draft'] ? true : false;
    
    // Determine if program is finalized for current period
    $is_finalized = false;
    if ($current_period && 
        isset($program['period_id']) && 
        $current_period['period_id'] == $program['period_id'] && 
        isset($program['is_draft']) && 
        $program['is_draft'] == 0) {
        $is_finalized = true;
    }
    
    if ($is_draft || !$is_finalized) {
        $draft_programs[] = $program;
    } else {
        $finalized_programs[] = $program;
    }
}

// Additional scripts - Make sure view_programs.js is loaded
// Note: rating_utils.js is already loaded in footer.php, so we don't need to include it again
$additionalScripts = [
    APP_URL . '/assets/js/agency/view_programs.js', // Ensure this script is included
    APP_URL . '/assets/js/utilities/table_sorting.js' // Add table sorting script
];

// Include header
require_once '../../layouts/header.php';

// Include agency navigation
require_once '../../layouts/agency_nav.php';

// Set up header variables
$title = "Agency Programs";
$subtitle = "View and manage your agency's programs";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = [
    [
        'url' => APP_URL . '/app/views/agency/programs/create_program.php', // Fix: use absolute URL with APP_URL
        'text' => 'Create New Program',
        'icon' => 'fas fa-plus-circle',
        'class' => 'btn-primary'
    ]
];

// Include the dashboard header component with the light style
require_once PROJECT_ROOT_PATH . 'lib/dashboard_header.php';
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

<!-- Draft Programs Card -->
<div class="card shadow-sm mb-4 w-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Draft Programs</h5>
    </div>
    
    <!-- Draft Programs Filters -->
    <div class="card-body pb-0">
        <div class="row g-3">
            <div class="col-md-5 col-sm-12">
                <label for="draftProgramSearch" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="draftProgramSearch" placeholder="Search by program name">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="draftRatingFilter" class="form-label">Rating</label>
                <select class="form-select" id="draftRatingFilter">
                    <option value="">All Ratings</option>
                    <option value="target-achieved">Monthly Target Achieved</option>
                    <option value="on-track-yearly">On Track for Year</option>
                    <option value="severe-delay">Severe Delays</option>
                    <option value="not-started">Not Started</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="draftTypeFilter" class="form-label">Program Type</label>
                <select class="form-select" id="draftTypeFilter">
                    <option value="">All Types</option>
                    <option value="assigned">Assigned</option>
                    <option value="created">Agency-Created Programs</option>
                </select>
            </div>
            <div class="col-md-1 col-sm-12 d-flex align-items-end">
                <button id="resetDraftFilters" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
        <div id="draftFilterBadges" class="filter-badges mt-2"></div>
    </div>
    
    <div class="card-body pt-2 p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0" id="draftProgramsTable">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-sort="name">Program Name <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="rating">Rating <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="date">Last Updated <i class="fas fa-sort ms-1"></i></th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($draft_programs)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">No draft programs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php                        foreach ($draft_programs as $program): 
                            // Determine program type (assigned or custom)
                            $is_assigned = isset($program['is_assigned']) && $program['is_assigned'] ? true : false;
                              // Convert rating for display
                            $current_rating = isset($program['rating']) ? convert_legacy_rating($program['rating']) : 'not-started';
                            
                            // Map database rating values to display labels and classes
                            $rating_map = [
                                'on-track' => ['label' => 'On Track', 'class' => 'warning'],
                                'on-track-yearly' => ['label' => 'On Track for Year', 'class' => 'warning'],
                                'target-achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success'],
                                'delayed' => ['label' => 'Delayed', 'class' => 'danger'],
                                'severe-delay' => ['label' => 'Severe Delays', 'class' => 'danger'],
                                'completed' => ['label' => 'Completed', 'class' => 'primary'],
                                'not-started' => ['label' => 'Not Started', 'class' => 'secondary']
                            ];
                            
                            // Set default if rating is not in our map
                            if (!isset($rating_map[$current_rating])) {
                                $current_rating = 'not-started';
                            }
                            
                            // Check if this is a draft
                            $is_draft = isset($program['is_draft']) && $program['is_draft'] ? true : false;
                        ?>
                            <tr class="<?php echo $is_draft ? 'draft-program' : ''; ?>" 
                                data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>">
                                <td>
                                    <div class="fw-medium">
                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                        <?php if ($is_draft): ?>
                                            <span class="draft-indicator" title="Draft"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted program-type-indicator">
                                        <i class="fas fa-<?php echo $is_assigned ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                        <?php echo $is_assigned ? 'Assigned' : 'Agency-Created'; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $rating_map[$current_rating]['class']; ?>">
                                        <?php echo $rating_map[$current_rating]['label']; ?>
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
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Program actions">
                                        <a href="update_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-secondary" title="Edit Program">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger delete-program-btn" 
                                                data-id="<?php echo $program['program_id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($program['program_name']); ?>" 
                                                title="Delete Program">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm submit-program" data-program-id="<?php echo $program['program_id']; ?>" title="Submit Program">
                                            <i class="fas fa-check"></i>
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

<!-- Finalized Programs Card -->
<div class="card shadow-sm mb-4 w-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">
            Finalized Programs 
            <span class="badge bg-info ms-2" title="These programs have finalized submissions for the current period">
                <i class="fas fa-lock me-1"></i> No longer editable
            </span>
        </h5>
    </div>
    
    <!-- Finalized Programs Filters -->
    <div class="card-body pb-0">
        <div class="row g-3">
            <div class="col-md-5 col-sm-12">
                <label for="finalizedProgramSearch" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="finalizedProgramSearch" placeholder="Search by program name">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="finalizedRatingFilter" class="form-label">Rating</label>
                <select class="form-select" id="finalizedRatingFilter">
                    <option value="">All Ratings</option>
                    <option value="target-achieved">Monthly Target Achieved</option>
                    <option value="on-track-yearly">On Track for Year</option>
                    <option value="severe-delay">Severe Delays</option>
                    <option value="not-started">Not Started</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="finalizedTypeFilter" class="form-label">Program Type</label>
                <select class="form-select" id="finalizedTypeFilter">
                    <option value="">All Types</option>
                    <option value="assigned">Assigned</option>
                    <option value="created">Agency-Created Programs</option>
                </select>
            </div>
            <div class="col-md-1 col-sm-12 d-flex align-items-end">
                <button id="resetFinalizedFilters" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
        <div id="finalizedFilterBadges" class="filter-badges mt-2"></div>
    </div>
    
    <div class="card-body pt-2 p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0" id="finalizedProgramsTable">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-sort="name">Program Name <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="rating">Rating <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="date">Last Updated <i class="fas fa-sort ms-1"></i></th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($finalized_programs)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">No finalized programs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php                        foreach ($finalized_programs as $program): 
                            // Determine program type (assigned or custom)
                            $is_assigned = isset($program['is_assigned']) && $program['is_assigned'] ? true : false;
                            
                            // Convert rating for display
                            $current_rating = isset($program['rating']) ? convert_legacy_rating($program['rating']) : 'not-started';
                            
                            // Map database rating values to display labels and classes
                            $rating_map = [
                                'on-track' => ['label' => 'On Track', 'class' => 'warning'],
                                'on-track-yearly' => ['label' => 'On Track for Year', 'class' => 'warning'],
                                'target-achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success'],
                                'delayed' => ['label' => 'Delayed', 'class' => 'danger'],
                                'severe-delay' => ['label' => 'Severe Delays', 'class' => 'danger'],
                                'completed' => ['label' => 'Completed', 'class' => 'primary'],
                                'not-started' => ['label' => 'Not Started', 'class' => 'secondary']
                            ];
                            
                            // Set default if rating is not in our map
                            if (!isset($rating_map[$current_rating])) {
                                $current_rating = 'not-started';
                            }
                        ?>
                            <tr data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>">
                                <td>
                                    <div class="fw-medium">
                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                    </div>
                                    <div class="small text-muted program-type-indicator">
                                        <i class="fas fa-<?php echo $is_assigned ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                        <?php echo $is_assigned ? 'Assigned' : 'Agency-Created'; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $rating_map[$current_rating]['class']; ?>">
                                        <?php echo $rating_map[$current_rating]['label']; ?>
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
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Program actions">
                                        <a href="program_details.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-primary" title="View Program Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Delete button only shows for custom programs (not assigned ones) -->
                                        <?php if (!$is_assigned): ?>
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
                <form action="<?php echo view_url('agency/programs', 'delete_program.php'); ?>" method="post" id="delete-program-form">
                    <input type="hidden" name="program_id" id="program-id-input">
                    <button type="submit" class="btn btn-danger">Delete Program</button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
// Include footer
require_once '../../layouts/footer.php';
?>



