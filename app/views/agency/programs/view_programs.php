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
require_once PROJECT_ROOT_PATH . 'lib/agencies/program_agency_assignments.php';
require_once PROJECT_ROOT_PATH . 'lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'lib/initiative_functions.php';

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
    APP_URL . '/assets/js/utilities/table_sorting.js',
    APP_URL . '/assets/js/utilities/pagination.js',
    APP_URL . '/assets/js/agency/view_programs.js'
];

// Get active initiatives for filtering
$active_initiatives = get_initiatives_for_select(true);

// Initialize program arrays
$programs = [];
$programs_with_drafts = [];
$programs_with_submissions = [];
$programs_without_submissions = [];

// Get programs for the current agency user
$agency_id = $_SESSION['agency_id'] ?? null;

if ($agency_id !== null) {
    // Build query - both focal and regular users see programs their agency has access to
    // Focal users get enhanced permissions within their agency programs, but don't see other agencies' programs
    $query = "SELECT DISTINCT p.*, 
                     i.initiative_name,
                     i.initiative_number,
                     i.initiative_id,
                     latest_sub.is_draft,
                     latest_sub.period_id,
                     latest_sub.submission_id as latest_submission_id,
                     latest_sub.submitted_at,
                     rp.period_type,
                     rp.period_number,
                     rp.year as period_year,
                     COALESCE(latest_sub.submitted_at, p.created_at) as updated_at,
                     paa.role as user_role
              FROM programs p 
              LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
              LEFT JOIN program_agency_assignments paa ON p.program_id = paa.program_id AND paa.agency_id = ? AND paa.is_active = 1
              LEFT JOIN (
                  SELECT ps1.*
                  FROM program_submissions ps1
                  INNER JOIN (
                      SELECT program_id, MAX(submission_id) as max_submission_id
                      FROM program_submissions
                      WHERE is_deleted = 0
                      GROUP BY program_id
                  ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
              ) latest_sub ON p.program_id = latest_sub.program_id
              LEFT JOIN reporting_periods rp ON latest_sub.period_id = rp.period_id
              WHERE p.is_deleted = 0 AND paa.assignment_id IS NOT NULL
              ORDER BY p.program_name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
}

// Process programs and separate into appropriate arrays
foreach ($programs as $program) {
    // Check if program has any submissions
    if (isset($program['latest_submission_id']) && $program['latest_submission_id']) {
        // Program has submissions
        if (isset($program['is_draft']) && $program['is_draft']) {
            // Latest submission is a draft
            $programs_with_drafts[] = $program;
        } else {
            // Latest submission is finalized
            $programs_with_submissions[] = $program;
        }
    } else {
        // Program has no submissions (program template)
        $programs_without_submissions[] = $program;
    }
}

// Additional scripts - Make sure view_programs.js is loaded
// Note: rating_utils.js is already loaded in footer.php, so we don't need to include it again
$additionalScripts = [
    APP_URL . '/assets/js/agency/view_programs.js', // Ensure this script is included
    APP_URL . '/assets/js/utilities/table_sorting.js' // Add table sorting script
];

// Function to render rating badge
function renderRatingBadge($rating_map, $current_rating) {
    return '<span class="badge bg-' . $rating_map[$current_rating]['class'] . ' rating-badge" ' .
           'title="' . htmlspecialchars($rating_map[$current_rating]['label']) . '">' .
           '<i class="' . $rating_map[$current_rating]['icon'] . ' me-1"></i>' .
           htmlspecialchars($rating_map[$current_rating]['label']) .
           '</span>';
}

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Agency Programs',
    'subtitle' => 'View and manage your agency\'s programs',
    'variant' => 'green'
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<!-- Toast Notification for Program Creation/Deletion -->
<?php if (!empty($message)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('<?= ucfirst($messageType) ?>', <?= json_encode($message) ?>, '<?= $messageType ?>');
        });
    </script>
<?php endif; ?>

<div class="mb-3">
    <a href="<?php echo APP_URL; ?>/app/views/agency/programs/create_program.php" class="btn btn-primary">
        <i class="fas fa-plus-circle me-1"></i> Create New Program
    </a>
</div>

<!-- Programs with Draft Submissions Card -->
<div class="card shadow-sm mb-4 w-100 draft-programs-card">
    <div class="card-header d-flex justify-content-between align-items-center bg-light border-start border-warning border-4">
        <h5 class="card-title view-programs-card-title m-0 d-flex align-items-center">
            <i class="fas fa-edit text-warning me-2"></i>
            Programs with Draft Submissions
            <span class="badge bg-warning text-dark ms-2" title="These programs have draft submissions that can be edited">
                <i class="fas fa-pencil-alt me-1"></i> Draft Submissions
            </span>
            <span class="badge bg-secondary ms-2" id="draft-count"><?php echo count($programs_with_drafts); ?></span>
        </h5>
    </div>
    
    <!-- Draft Programs Filters -->    <div class="card-body pb-0">
        <div class="row g-3">
            <div class="col-md-4 col-sm-12">
                <label for="draftProgramSearch" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="draftProgramSearch" placeholder="Search by program name or number">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="draftRatingFilter" class="form-label">Rating</label>
                <select class="form-select" id="draftRatingFilter">
                    <option value="">All Ratings</option>
                    <option value="target-achieved">Monthly Target Achieved</option>
                    <option value="on-track-yearly">On Track for Year</option>
                    <option value="severe-delay">Severe Delays</option>
                    <option value="not-started">Not Started</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="draftTypeFilter" class="form-label">Program Type</label>
                <select class="form-select" id="draftTypeFilter">
                    <option value="">All Types</option>
                    <option value="assigned">Assigned</option>
                    <option value="created">Agency-Created Programs</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="draftInitiativeFilter" class="form-label">Initiative</label>
                <select class="form-select" id="draftInitiativeFilter">
                    <option value="">All Initiatives</option>
                    <option value="no-initiative">Not Linked to Initiative</option>
                    <?php foreach ($active_initiatives as $initiative): ?>
                        <option value="<?php echo $initiative['initiative_id']; ?>">
                            <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                        </option>
                    <?php endforeach; ?>
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
        <div class="table-responsive">            <table class="table table-hover table-custom mb-0" id="draftProgramsTable">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-sort="name">
                            <i class="fas fa-project-diagram me-1"></i>Program Information 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="sortable initiative-display" data-sort="initiative">
                            <i class="fas fa-lightbulb me-1"></i>Initiative 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="sortable" data-sort="rating">
                            <i class="fas fa-chart-line me-1"></i>Progress Rating 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="sortable" data-sort="date">
                            <i class="fas fa-clock me-1"></i>Last Updated 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="text-end">
                            <i class="fas fa-cog me-1"></i>Actions
                        </th>
                    </tr>
                </thead>
                <tbody>                    <?php if (empty($programs_with_drafts)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">No programs with draft submissions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php                        foreach ($programs_with_drafts as $program): 
                            // Determine program type (assigned or custom)
                            $is_assigned = isset($program['is_assigned']) && $program['is_assigned'] ? true : false;
                              // Include rating helpers for status mapping
                            require_once PROJECT_ROOT_PATH . 'lib/rating_helpers.php';
                            
                            // Use rating directly from database (no conversion needed)
                            $current_rating = isset($program['rating']) ? $program['rating'] : 'not_started';
                            
                            // Map database rating values to display labels, classes, and icons
                            $rating_map = [
                                'not_started' => [
                                    'label' => 'Not Started', 
                                    'class' => 'secondary',
                                    'icon' => 'fas fa-hourglass-start'
                                ],
                                'on_track_for_year' => [
                                    'label' => 'On Track for Year', 
                                    'class' => 'warning',
                                    'icon' => 'fas fa-calendar-check'
                                ],
                                'monthly_target_achieved' => [
                                    'label' => 'Monthly Target Achieved', 
                                    'class' => 'success',
                                    'icon' => 'fas fa-check-circle'
                                ],
                                'severe_delay' => [
                                    'label' => 'Severe Delays', 
                                    'class' => 'danger',
                                    'icon' => 'fas fa-exclamation-triangle'
                                ]
                            ];
                            
                            // Set default if rating is not in our map
                            if (!isset($rating_map[$current_rating])) {
                                $current_rating = 'not_started';
                            }
                            
                            // Check if this is a draft
                            $is_draft = isset($program['is_draft']) && $program['is_draft'] ? true : false;
                        ?>                            <tr class="<?php echo $is_draft ? 'draft-program' : ''; ?>"                                data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>">                                <!-- Draft programs initiative column -->
                                <td class="text-truncate program-name-col">
                                    <div class="fw-medium">
                                        <span class="program-name" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                            <?php if (!empty($program['program_number'])): ?>
                                                <span class="badge bg-info me-2" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($program['program_name']); ?>
                                        </span>
                                        <?php if ($is_draft): ?>
                                            <span class="draft-indicator" title="Draft"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted program-type-indicator">
                                        <i class="fas fa-<?php echo $is_assigned ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                        <?php echo $is_assigned ? 'Assigned' : 'Agency-Created'; ?>
                                    </div>
                                </td>
                                <td class="text-truncate initiative-col" 
                                    data-initiative="<?php echo !empty($program['initiative_name']) ? htmlspecialchars($program['initiative_name']) : 'zzz_no_initiative'; ?>"
                                    data-initiative-id="<?php echo $program['initiative_id'] ?? '0'; ?>">
                                    <?php if (!empty($program['initiative_name'])): ?>
                                        <span class="badge bg-primary initiative-badge" title="Initiative">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            <span class="initiative-badge-card" title="<?php 
                                                echo !empty($program['initiative_number']) ? 
                                                    htmlspecialchars($program['initiative_number'] . ' - ' . $program['initiative_name']) : 
                                                    htmlspecialchars($program['initiative_name']); 
                                            ?>">
                                                <?php 
                                                echo !empty($program['initiative_number']) ? 
                                                    htmlspecialchars($program['initiative_number'] . ' - ' . $program['initiative_name']) : 
                                                    htmlspecialchars($program['initiative_name']); 
                                                ?>
                                            </span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            <i class="fas fa-minus me-1"></i>Not Linked
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td data-rating="<?php echo $current_rating; ?>" data-rating-order="<?php 
                                    $rating_order = [
                                        'monthly_target_achieved' => 1,
                                        'on_track_for_year' => 2,
                                        'severe_delay' => 3,
                                        'not_started' => 4
                                    ];
                                    echo $rating_order[$current_rating] ?? 999;
                                ?>">
                                    <span class="badge bg-<?php echo $rating_map[$current_rating]['class']; ?> rating-badge" 
                                          title="<?php echo $rating_map[$current_rating]['label']; ?>">
                                        <i class="<?php echo $rating_map[$current_rating]['icon']; ?> me-1"></i>
                                        <?php echo $rating_map[$current_rating]['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $date_iso = '';
                                    if (isset($program['updated_at']) && $program['updated_at']) {
                                        $date_iso = date('Y-m-d', strtotime($program['updated_at']));
                                        $date_display = date('M j, Y g:i A', strtotime($program['updated_at']));
                                    } elseif (isset($program['created_at']) && $program['created_at']) {
                                        $date_iso = date('Y-m-d', strtotime($program['created_at']));
                                        $date_display = date('M j, Y g:i A', strtotime($program['created_at']));
                                    } else {
                                        $date_display = 'Not set';
                                    }
                                    ?>
                                    <span <?php if ($date_iso) echo 'data-date="' . $date_iso . '"'; ?>><?php echo $date_display; ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm d-flex flex-nowrap" role="group" aria-label="Program actions">
                                        <a href="program_details.php?id=<?php echo $program['program_id']; ?>" 
                                           class="btn btn-outline-secondary flex-fill" 
                                           title="View detailed program information including submissions, targets, and progress"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php 
                                        // Check if user can delete this program (only creators and focal users)
                                        $can_delete = is_focal_user() || is_program_creator($program['program_id']);
                                        if ($can_delete): ?>
                                            <button type="button" class="btn btn-outline-danger flex-fill delete-program-btn" 
                                                    data-id="<?php echo $program['program_id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($program['program_name']); ?>" 
                                                    title="Permanently delete this program and all its data"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php 
                                        // Check if user can edit this program (same logic as program templates)
                                        $can_edit = can_edit_program($program['program_id']);
                                        $is_creator = isset($program['created_by']) && $program['created_by'] == $_SESSION['user_id'];
                                        ?>
                                        
                                        <?php if ($can_edit): ?>
                                        <button type="button" class="btn btn-outline-secondary flex-fill more-actions-btn" 
                                                data-program-id="<?php echo $program['program_id']; ?>"
                                                data-program-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                                data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>"
                                                title="Edit submission and program details"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top">
                                            <i class="fas fa-ellipsis-v"></i>
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

<!-- Programs with Finalized Submissions Card -->
<div class="card shadow-sm mb-4 w-100 finalized-programs-card">
    <div class="card-header d-flex justify-content-between align-items-center bg-light border-start border-success border-4">
        <h5 class="card-title view-programs-card-title m-0 d-flex align-items-center">
            <i class="fas fa-check-circle text-success me-2"></i>
            Programs with Finalized Submissions
            <span class="badge bg-success ms-2" title="These programs have finalized submissions">
                <i class="fas fa-check me-1"></i> Finalized
            </span>
            <span class="badge bg-secondary ms-2" id="finalized-count"><?php echo count($programs_with_submissions); ?></span>
        </h5>
    </div>
    
    <!-- Finalized Programs Filters -->    <div class="card-body pb-0">
        <div class="row g-3">
            <div class="col-md-4 col-sm-12">
                <label for="finalizedProgramSearch" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="finalizedProgramSearch" placeholder="Search by program name or number">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="finalizedRatingFilter" class="form-label">Rating</label>                <select class="form-select" id="finalizedRatingFilter">
                    <option value="">All Ratings</option>
                    <option value="target-achieved">Monthly Target Achieved</option>
                    <option value="on-track-yearly">On Track for Year</option>
                    <option value="severe-delay">Severe Delays</option>
                    <option value="not-started">Not Started</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="finalizedTypeFilter" class="form-label">Program Type</label>
                <select class="form-select" id="finalizedTypeFilter">
                    <option value="">All Types</option>
                    <option value="assigned">Assigned</option>
                    <option value="created">Agency-Created Programs</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="finalizedInitiativeFilter" class="form-label">Initiative</label>
                <select class="form-select" id="finalizedInitiativeFilter">
                    <option value="">All Initiatives</option>
                    <option value="no-initiative">Not Linked to Initiative</option>
                    <?php foreach ($active_initiatives as $initiative): ?>
                        <option value="<?php echo $initiative['initiative_id']; ?>">
                            <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                        </option>
                    <?php endforeach; ?>
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
        <div class="table-responsive">            <table class="table table-hover table-custom mb-0" id="finalizedProgramsTable">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-sort="name">
                            <i class="fas fa-project-diagram me-1"></i>Program Information 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="sortable initiative-display" data-sort="initiative">
                            <i class="fas fa-lightbulb me-1"></i>Initiative 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="sortable" data-sort="rating">
                            <i class="fas fa-chart-line me-1"></i>Progress Rating 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="sortable" data-sort="date">
                            <i class="fas fa-clock me-1"></i>Last Updated 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="text-end">
                            <i class="fas fa-cog me-1"></i>Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($programs_with_submissions)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">No programs with finalized submissions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php                        foreach ($programs_with_submissions as $program): 
                            // Determine program type (assigned or custom)
                            $is_assigned = isset($program['is_assigned']) && $program['is_assigned'] ? true : false;
                            
                            // Convert rating for display
                            // Use rating directly from database (no conversion needed)
                            $current_rating = isset($program['rating']) ? $program['rating'] : 'not_started';
                            
                            // Map database rating values to display labels, classes, and icons
                            $rating_map = [
                                'not_started' => [
                                    'label' => 'Not Started', 
                                    'class' => 'secondary',
                                    'icon' => 'fas fa-hourglass-start'
                                ],
                                'on_track_for_year' => [
                                    'label' => 'On Track for Year', 
                                    'class' => 'warning',
                                    'icon' => 'fas fa-calendar-check'
                                ],
                                'monthly_target_achieved' => [
                                    'label' => 'Monthly Target Achieved', 
                                    'class' => 'success',
                                    'icon' => 'fas fa-check-circle'
                                ],
                                'severe_delay' => [
                                    'label' => 'Severe Delays', 
                                    'class' => 'danger',
                                    'icon' => 'fas fa-exclamation-triangle'
                                ]
                            ];
                            
                            // Set default if rating is not in our map
                            if (!isset($rating_map[$current_rating])) {
                                $current_rating = 'not_started';
                            }                        ?>                            <tr data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>">
                                <!-- Finalized programs initiative column -->
                                <td class="text-truncate program-name-col">
                                    <div class="fw-medium">
                                        <span class="program-name" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                            <?php if (!empty($program['program_number'])): ?>
                                                <span class="badge bg-info me-2" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($program['program_name']); ?>
                                        </span>
                                    </div>
                                    <div class="small text-muted program-type-indicator">
                                        <i class="fas fa-<?php echo $is_assigned ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                        <?php echo $is_assigned ? 'Assigned' : 'Agency-Created'; ?>
                                    </div>
                                </td>
                                <td class="text-truncate initiative-col" 
                                    data-initiative="<?php echo !empty($program['initiative_name']) ? htmlspecialchars($program['initiative_name']) : 'zzz_no_initiative'; ?>"
                                    data-initiative-id="<?php echo $program['initiative_id'] ?? '0'; ?>">
                                    <?php if (!empty($program['initiative_name'])): ?>
                                        <span class="badge bg-primary initiative-badge" title="Initiative">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            <span class="initiative-badge-card" title="<?php 
                                                echo !empty($program['initiative_number']) ? 
                                                    htmlspecialchars($program['initiative_number'] . ' - ' . $program['initiative_name']) : 
                                                    htmlspecialchars($program['initiative_name']); 
                                            ?>">
                                                <?php 
                                                echo !empty($program['initiative_number']) ? 
                                                    htmlspecialchars($program['initiative_number'] . ' - ' . $program['initiative_name']) : 
                                                    htmlspecialchars($program['initiative_name']); 
                                                ?>
                                            </span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            <i class="fas fa-minus me-1"></i>Not Linked
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td data-rating="<?php echo $current_rating; ?>" data-rating-order="<?php 
                                    $rating_order = [
                                        'monthly_target_achieved' => 1,
                                        'on_track_for_year' => 2,
                                        'severe_delay' => 3,
                                        'not_started' => 4
                                    ];
                                    echo $rating_order[$current_rating] ?? 999;
                                ?>">
                                    <span class="badge bg-<?php echo $rating_map[$current_rating]['class']; ?> rating-badge" 
                                          title="<?php echo $rating_map[$current_rating]['label']; ?>">
                                        <i class="<?php echo $rating_map[$current_rating]['icon']; ?> me-1"></i>
                                        <?php echo $rating_map[$current_rating]['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $date_iso = '';
                                    if (isset($program['updated_at']) && $program['updated_at']) {
                                        $date_iso = date('Y-m-d', strtotime($program['updated_at']));
                                        $date_display = date('M j, Y g:i A', strtotime($program['updated_at']));
                                    } elseif (isset($program['created_at']) && $program['created_at']) {
                                        $date_iso = date('Y-m-d', strtotime($program['created_at']));
                                        $date_display = date('M j, Y g:i A', strtotime($program['created_at']));
                                    } else {
                                        $date_display = 'Not set';
                                    }
                                    ?>
                                    <span <?php if ($date_iso) echo 'data-date="' . $date_iso . '"'; ?>><?php echo $date_display; ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm d-flex flex-nowrap" role="group" aria-label="Program actions">
                                        <a href="program_details.php?id=<?php echo $program['program_id']; ?>" 
                                           class="btn btn-outline-secondary flex-fill" 
                                           title="View detailed program information including submissions, targets, and progress"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php 
                                        // Check if user can delete this program (only creators and focal users)
                                        $can_delete = is_focal_user() || is_program_creator($program['program_id']);
                                        if ($can_delete): ?>
                                            <button type="button" class="btn btn-outline-danger flex-fill delete-program-btn" 
                                                    data-id="<?php echo $program['program_id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($program['program_name']); ?>" 
                                                    title="Permanently delete this program and all its data"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php 
                                        // Check if user can edit this program (same logic as program templates)
                                        $can_edit = can_edit_program($program['program_id']);
                                        $is_creator = isset($program['created_by']) && $program['created_by'] == $_SESSION['user_id'];
                                        ?>
                                        
                                        <?php if ($can_edit): ?>
                                        <button type="button" class="btn btn-outline-secondary flex-fill more-actions-btn" 
                                                data-program-id="<?php echo $program['program_id']; ?>"
                                                data-program-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                                data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>"
                                                title="Edit submission and program details"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top">
                                            <i class="fas fa-ellipsis-v"></i>
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

<!-- Programs Without Submissions Card -->
<div class="card shadow-sm mb-4 w-100 empty-programs-card">
    <div class="card-header d-flex justify-content-between align-items-center bg-light border-start border-info border-4">
                    <h5 class="card-title view-programs-card-title m-0 d-flex align-items-center text-white">
                <i class="fas fa-folder-open text-white me-2" style="color: #fff !important;"></i>
                Program Templates
                <span class="badge bg-info ms-2" title="These programs are templates waiting for progress reports">
                    <i class="fas fa-file-alt me-1 text-white"></i> Ready for Reports
                </span>
                <span class="badge bg-secondary ms-2" id="empty-count"><?php echo count($programs_without_submissions); ?></span>
            </h5>
    </div>
    
    <div class="card-body pb-0">
        <div class="row g-3">
            <div class="col-md-4 col-sm-12">
                <label for="emptyProgramSearch" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="emptyProgramSearch" placeholder="Search by program name or number">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="emptyTypeFilter" class="form-label">Program Type</label>
                <select class="form-select" id="emptyTypeFilter">
                    <option value="">All Types</option>
                    <option value="assigned">Assigned</option>
                    <option value="created">Agency-Created Programs</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="emptyInitiativeFilter" class="form-label">Initiative</label>
                <select class="form-select" id="emptyInitiativeFilter">
                    <option value="">All Initiatives</option>
                    <option value="no-initiative">Not Linked to Initiative</option>
                    <?php foreach ($active_initiatives as $initiative): ?>
                        <option value="<?php echo $initiative['initiative_id']; ?>">
                            <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 col-sm-12 d-flex align-items-end">
                <button id="resetEmptyFilters" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
        <div id="emptyFilterBadges" class="filter-badges mt-2"></div>
    </div>
    
    <div class="card-body pt-2 p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0" id="emptyProgramsTable">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-sort="name">
                            <i class="fas fa-project-diagram me-1"></i>Program Information 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="sortable initiative-display" data-sort="initiative">
                            <i class="fas fa-lightbulb me-1"></i>Initiative 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="sortable" data-sort="date">
                            <i class="fas fa-clock me-1"></i>Created Date 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="text-end">
                            <i class="fas fa-cog me-1"></i>Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($programs_without_submissions)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">No program templates found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($programs_without_submissions as $program): 
                            // Determine program type (assigned or custom)
                            $is_assigned = isset($program['is_assigned']) && $program['is_assigned'] ? true : false;
                        ?>
                            <tr data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>">
                                <td class="text-truncate program-name-col">
                                    <div class="fw-medium">
                                        <span class="program-name" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                            <?php if (!empty($program['program_number'])): ?>
                                                <span class="badge bg-info me-2" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($program['program_name']); ?>
                                        </span>
                                        <span class="badge bg-info ms-2" title="Program template - ready for progress reports">
                                            <i class="fas fa-file-alt me-1"></i> Template
                                        </span>
                                    </div>
                                    <div class="small text-muted program-type-indicator">
                                        <i class="fas fa-<?php echo $is_assigned ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                        <?php echo $is_assigned ? 'Assigned' : 'Agency-Created'; ?>
                                    </div>
                                </td>
                                <td class="text-truncate initiative-col" 
                                    data-initiative="<?php echo !empty($program['initiative_name']) ? htmlspecialchars($program['initiative_name']) : 'zzz_no_initiative'; ?>"
                                    data-initiative-id="<?php echo $program['initiative_id'] ?? '0'; ?>">
                                    <?php if (!empty($program['initiative_name'])): ?>
                                        <span class="badge bg-primary initiative-badge" title="Initiative">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            <span class="initiative-badge-card" title="<?php 
                                                echo !empty($program['initiative_number']) ? 
                                                    htmlspecialchars($program['initiative_number'] . ' - ' . $program['initiative_name']) : 
                                                    htmlspecialchars($program['initiative_name']); 
                                            ?>">
                                                <?php 
                                                echo !empty($program['initiative_number']) ? 
                                                    htmlspecialchars($program['initiative_number'] . ' - ' . $program['initiative_name']) : 
                                                    htmlspecialchars($program['initiative_name']); 
                                                ?>
                                            </span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            <i class="fas fa-minus me-1"></i>Not Linked
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $date_iso = '';
                                    if (isset($program['created_at']) && $program['created_at']) {
                                        $date_iso = date('Y-m-d', strtotime($program['created_at']));
                                        $date_display = date('M j, Y g:i A', strtotime($program['created_at']));
                                    } else {
                                        $date_display = 'Not set';
                                    }
                                    ?>
                                    <span <?php if ($date_iso) echo 'data-date="' . $date_iso . '"'; ?>><?php echo $date_display; ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm d-flex flex-nowrap" role="group" aria-label="Program actions">
                                        <a href="program_details.php?id=<?php echo $program['program_id']; ?>" 
                                           class="btn btn-outline-secondary flex-fill" 
                                           title="View detailed program information"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php 
                                        // Check if user can edit this program (editors can add submissions)
                                        $can_edit = can_edit_program($program['program_id']);
                                        $is_creator = isset($program['created_by']) && $program['created_by'] == $_SESSION['user_id'];
                                        ?>
                                        
                                        <?php if ($can_edit): ?>
                                        <a href="add_submission.php?program_id=<?php echo $program['program_id']; ?>" 
                                           class="btn btn-outline-success flex-fill" 
                                           title="Add First Submission - Start progress reporting for this program"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($is_creator): ?>
                                        <a href="edit_program.php?id=<?php echo $program['program_id']; ?>" 
                                           class="btn btn-outline-primary flex-fill" 
                                           title="Edit program details and settings"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php 
                                        // Check if user can delete this program (only creators and focal users)
                                        $can_delete = is_focal_user() || is_program_creator($program['program_id']);
                                        if ($can_delete): ?>
                                            <button type="button" class="btn btn-outline-danger flex-fill delete-program-btn" 
                                                    data-id="<?php echo $program['program_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                                    title="Permanently delete this program and all its data"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
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

<!-- Add program data for JavaScript -->
<script>
    // Make program data available to JavaScript for filtering and pagination
    const allPrograms = <?php echo json_encode($programs); ?>;
    
    // Update counters when page loads
    document.addEventListener('DOMContentLoaded', function() {
        updateProgramCounters();
        
        // Add loading states for tables
        initializeTableLoadingStates();
        
        // Add enhanced filtering
        initializeEnhancedFiltering();
    });
    
    function updateProgramCounters() {
        const draftCount = document.querySelectorAll('#draftProgramsTable tbody tr:not(.d-none)').length;
        const finalizedCount = document.querySelectorAll('#finalizedProgramsTable tbody tr:not(.d-none)').length;
        const emptyCount = document.querySelectorAll('#emptyProgramsTable tbody tr:not(.d-none)').length;
        
        document.getElementById('draft-count').textContent = draftCount;
        document.getElementById('finalized-count').textContent = finalizedCount;
        document.getElementById('empty-count').textContent = emptyCount;
    }
    
    function initializeTableLoadingStates() {
        const tables = document.querySelectorAll('.table-responsive');
        tables.forEach(table => {
            table.classList.add('table-loading');
            setTimeout(() => {
                table.classList.remove('table-loading');
            }, 500);
        });
    }
    
    function initializeEnhancedFiltering() {
        // Add real-time counter updates when filters change
        const filterInputs = document.querySelectorAll('input[id*="Search"], select[id*="Filter"]');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                setTimeout(updateProgramCounters, 100);
            });
            input.addEventListener('input', function() {
                setTimeout(updateProgramCounters, 100);
            });
        });
        
        // Add filter clear functionality
        const resetButtons = document.querySelectorAll('[id*="resetFilters"]');
        resetButtons.forEach(button => {
            button.addEventListener('click', function() {
                setTimeout(updateProgramCounters, 100);
            });
        });
    }
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

<script>
window.currentUserRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
</script>




