<?php
/**
 * Admin Programs
 * 
 * Programs overview for admin users with separate sections for unsubmitted and submitted programs.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';
require_once ROOT_PATH . 'app/lib/admins/statistics.php';
require_once ROOT_PATH . 'app/lib/initiative_functions.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Programs Overview';

// Determine the period_id to use
$url_period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;

if ($url_period_id) {
    $current_period = get_reporting_period($url_period_id); // Fetch the period selected via URL
    // Ensure $current_period is not null and contains period_id, otherwise, it might indicate an invalid period_id in URL
    if ($current_period && isset($current_period['period_id'])) {
        $period_id = $current_period['period_id'];
    } else {
        // Fallback or error handling if URL period_id is invalid
        $current_period = get_current_reporting_period(); // Fallback to default
        $period_id = $current_period ? $current_period['period_id'] : null;
        // Optionally, redirect or show an error if an invalid period_id was in the URL
    }
} else {
    // Get current reporting period if no period_id in URL
    $current_period = get_current_reporting_period();
    $period_id = $current_period ? $current_period['period_id'] : null;
}

// This $viewing_period is used by the period_selector.php component to show the correct selection in the dropdown
$viewing_period = $current_period; // $current_period is now correctly set based on URL or default

// Check for program type filter from URL
$initial_program_type = isset($_GET['program_type']) ? $_GET['program_type'] : null;

// Get all programs with filters
$programs = get_admin_programs_list($period_id, []); // No filters, get all

// Separate programs into unsubmitted and submitted
$unsubmitted_programs = [];
$submitted_programs = [];

foreach ($programs as $program) {
    // Determine if this is an unsubmitted program (draft in backend)
    $is_unsubmitted = isset($program['is_draft']) && $program['is_draft'] == 1;
    
    if ($is_unsubmitted) {
        $unsubmitted_programs[] = $program;
    } else {
        $submitted_programs[] = $program;
    }
}

// Get all sectors for filter dropdown
$sectors = get_all_sectors();

// Get all agencies for filter dropdown
$agencies = [];
$agencies_query = "SELECT user_id, agency_name FROM users WHERE role = 'agency' ORDER BY agency_name";
$agencies_result = $conn->query($agencies_query);
while ($row = $agencies_result->fetch_assoc()) {
    $agencies[] = $row;
}

// Get active initiatives for filtering
$active_initiatives = get_initiatives_for_select(true);

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/period_selector.js',
    APP_URL . '/assets/js/admin/programs_admin.js'
];

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Programs Overview',
    'subtitle' => 'Monitor and manage all programs across sectors',
    'variant' => 'green',
    'actions' => [
        [
            'text' => 'Bulk Assign Initiatives',
            'url' => APP_URL . '/app/views/admin/programs/bulk_assign_initiatives.php',
            'class' => 'btn-secondary',
            'icon' => 'fas fa-link'
        ],
        [
            'text' => 'Assign Programs',
            'url' => APP_URL . '/app/views/admin/programs/assign_programs.php',
            'class' => 'btn-light',
            'icon' => 'fas fa-tasks'
        ]
    ]
];

// Include the modern page header
require_once '../../layouts/page_header.php';

// Check for session messages
$message = '';
$message_type = '';

if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    
    // Clear the message from session after using it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<!-- Programs Management Content -->
<main class="flex-fill">    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'danger' ? 'exclamation-circle' : 'info-circle'); ?> me-2"></i>
                <div><?php echo htmlspecialchars($message); ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        
        <script>
        // Show toast notification for immediate feedback
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($message_type === 'success'): ?>
                showToast('<?php echo addslashes($message); ?>', 'success', 6000);
            <?php elseif ($message_type === 'danger'): ?>
                showToast('<?php echo addslashes($message); ?>', 'danger', 8000);
            <?php else: ?>
                showToast('<?php echo addslashes($message); ?>', '<?php echo $message_type; ?>', 5000);
            <?php endif; ?>
        });
        </script>
    <?php endif; ?>

    <!-- Period Selector Component -->
    <?php require_once ROOT_PATH . 'app/lib/period_selector.php'; ?>

<!-- Unsubmitted Programs Card -->
<div class="card shadow-sm mb-4 w-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">
            <i class="fas fa-edit me-2"></i>Unsubmitted Programs
        </h5>
        <span class="badge bg-warning"><?php echo count($unsubmitted_programs); ?> Programs</span>
    </div>
    
    <!-- Unsubmitted Programs Filters -->
    <div class="card-body pb-0">
        <div class="row g-3">
            <div class="col-md-4 col-sm-12">
                <label for="unsubmittedProgramSearch" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="unsubmittedProgramSearch" placeholder="Search by program name">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="unsubmittedRatingFilter" class="form-label">Rating</label>
                <select class="form-select" id="unsubmittedRatingFilter">
                    <option value="">All Ratings</option>
                    <option value="target-achieved">Monthly Target Achieved</option>
                    <option value="on-track-yearly">On Track for Year</option>
                    <option value="severe-delay">Severe Delays</option>
                    <option value="not-started">Not Started</option>
                </select>
            </div>            <div class="col-md-3 col-sm-6">
                <label for="unsubmittedTypeFilter" class="form-label">Program Type</label>
                <select class="form-select" id="unsubmittedTypeFilter">
                    <option value="">All Types</option>
                    <option value="assigned">Assigned</option>
                    <option value="agency">Agency-Created</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="unsubmittedAgencyFilter" class="form-label">Agency</label>
                <select class="form-select" id="unsubmittedAgencyFilter">
                    <option value="">All Agencies</option>
                    <?php foreach ($agencies as $agency): ?>
                        <option value="<?php echo $agency['user_id']; ?>">
                            <?php echo htmlspecialchars($agency['agency_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="unsubmittedInitiativeFilter" class="form-label">Initiative</label>
                <select class="form-select" id="unsubmittedInitiativeFilter">
                    <option value="">All Initiatives</option>
                    <option value="no-initiative">Not Linked to Initiative</option>
                    <?php foreach ($active_initiatives as $initiative): ?>
                        <option value="<?php echo $initiative['initiative_id']; ?>">
                            <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>        </div>
        <div class="row mt-2">
            <div class="col-md-12 d-flex align-items-end justify-content-end">
                <button id="resetUnsubmittedFilters" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-undo me-1"></i> Reset Filters
                </button>
            </div>
        </div>
        <div id="unsubmittedFilterBadges" class="filter-badges mt-2"></div>
    </div>
    
    <div class="card-body pt-2 p-0">
        <div class="table-responsive">            <table class="table table-hover table-custom mb-0" id="unsubmittedProgramsTable">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-sort="name">Program Name <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="initiative">Initiative <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="agency">Agency <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="rating">Rating <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="date">Last Updated <i class="fas fa-sort ms-1"></i></th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>                    <?php if (empty($unsubmitted_programs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">No unsubmitted programs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($unsubmitted_programs as $program): 
                            // Determine program type (assigned or agency-created)
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
                        ?>                            <tr data-program-type="<?php echo $is_assigned ? 'assigned' : 'agency'; ?>"
                                data-sector-id="<?php echo $program['sector_id']; ?>"
                                data-agency-id="<?php echo $program['owner_agency_id']; ?>"
                                data-initiative-id="<?php echo $program['initiative_id'] ?? ''; ?>"
                                data-rating="<?php echo $current_rating; ?>">
                                <td class="text-truncate" style="max-width: 300px;">
                                    <div class="fw-medium">
                                        <a href="view_program.php?id=<?php echo $program['program_id']; ?>&period_id=<?php echo $period_id; ?>">
                                            <span class="program-name" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                                <?php if (!empty($program['program_number'])): ?>
                                                    <span class="badge bg-info me-2" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($program['program_name']); ?>
                                            </span>
                                        </a>                                        <span class="badge bg-light text-dark ms-1">Unsubmitted</span>
                                    </div>                                    <div class="small text-muted program-type-indicator">
                                        <i class="fas fa-<?php echo $is_assigned ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                        <?php echo $is_assigned ? 'Assigned' : 'Agency-Created'; ?>
                                    </div>
                                </td>
                                <td class="text-truncate" style="max-width: 250px;">
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
                                <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $rating_map[$current_rating]['class']; ?>">
                                        <?php echo $rating_map[$current_rating]['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($program['updated_at']) && $program['updated_at'] !== '0000-00-00 00:00:00') {
                                        echo date('M j, Y', strtotime($program['updated_at']));
                                    } elseif (!empty($program['submission_date']) && $program['submission_date'] !== '0000-00-00 00:00:00') {
                                        echo date('M j, Y', strtotime($program['submission_date']));
                                    } else {
                                        echo 'Not set';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Program actions">
                                        <a href="view_program.php?id=<?php echo $program['program_id']; ?>&period_id=<?php echo $period_id; ?>" class="btn btn-outline-primary" title="View Program Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-secondary" title="Edit Program">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" 
                                           class="btn btn-outline-danger" 
                                           title="Delete Program"
                                           onclick="confirmDeleteProgram(<?php echo $program['program_id']; ?>, <?php echo $period_id ?? 'null'; ?>); return false;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                    <div class="mt-1 d-grid">
                                        <?php if (isset($program['submission_id']) && !empty($program['submission_id'])): ?>
                                            <a href="resubmit.php?program_id=<?php echo $program['program_id']; ?>&period_id=<?php echo $period_id; ?>" 
                                               class="btn btn-outline-success btn-sm w-100" 
                                               title="Submit Program for this Period"
                                               onclick="return confirm('Are you sure you want to submit this program for the period? This will mark it as officially submitted.');">
                                                <i class="fas fa-check-circle"></i> Submit
                                            </a>
                                        <?php else: ?>
                                            <small class="text-muted">No submissions</small>
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

<!-- Submitted Programs Card -->
<div class="card shadow-sm mb-4 w-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">
            <i class="fas fa-check-circle me-2"></i>Submitted Programs
            <span class="badge bg-info ms-2" title="These programs have been officially submitted">
                <i class="fas fa-lock me-1"></i> Finalized
            </span>
        </h5>
        <span class="badge bg-success"><?php echo count($submitted_programs); ?> Programs</span>
    </div>
    
    <!-- Submitted Programs Filters -->
    <div class="card-body pb-0">
        <div class="row g-3">
            <div class="col-md-4 col-sm-12">
                <label for="submittedProgramSearch" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="submittedProgramSearch" placeholder="Search by program name">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="submittedRatingFilter" class="form-label">Rating</label>
                <select class="form-select" id="submittedRatingFilter">
                    <option value="">All Ratings</option>
                    <option value="target-achieved">Monthly Target Achieved</option>
                    <option value="on-track-yearly">On Track for Year</option>
                    <option value="on-track">On Track</option>
                    <option value="delayed">Delayed</option>
                    <option value="severe-delay">Severe Delays</option>
                    <option value="completed">Completed</option>
                    <option value="not-started">Not Started</option>
                </select>
            </div>            <div class="col-md-3 col-sm-6">
                <label for="submittedTypeFilter" class="form-label">Program Type</label>
                <select class="form-select" id="submittedTypeFilter">
                    <option value="">All Types</option>
                    <option value="assigned">Assigned</option>
                    <option value="agency">Agency-Created</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="submittedAgencyFilter" class="form-label">Agency</label>
                <select class="form-select" id="submittedAgencyFilter">
                    <option value="">All Agencies</option>
                    <?php foreach ($agencies as $agency): ?>
                        <option value="<?php echo $agency['user_id']; ?>">
                            <?php echo htmlspecialchars($agency['agency_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="submittedInitiativeFilter" class="form-label">Initiative</label>
                <select class="form-select" id="submittedInitiativeFilter">
                    <option value="">All Initiatives</option>
                    <option value="no-initiative">Not Linked to Initiative</option>
                    <?php foreach ($active_initiatives as $initiative): ?>
                        <option value="<?php echo $initiative['initiative_id']; ?>">
                            <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12 d-flex align-items-end justify-content-end">
                <button id="resetSubmittedFilters" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-undo me-1"></i> Reset Filters
                </button>
            </div>
        </div>
        <div id="submittedFilterBadges" class="filter-badges mt-2"></div>
    </div>
    
    <div class="card-body pt-2 p-0">
        <div class="table-responsive">            <table class="table table-hover table-custom mb-0" id="submittedProgramsTable">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-sort="name">Program Name <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="initiative">Initiative <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="agency">Agency <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="rating">Rating <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="date">Last Updated <i class="fas fa-sort ms-1"></i></th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead><tbody>                    <?php if (empty($submitted_programs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">No submitted programs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($submitted_programs as $program): 
                            // Determine program type (assigned or agency-created)
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
                        ?>                            <tr data-program-type="<?php echo $is_assigned ? 'assigned' : 'agency'; ?>"
                                data-sector-id="<?php echo $program['sector_id']; ?>"
                                data-agency-id="<?php echo $program['owner_agency_id']; ?>"
                                data-initiative-id="<?php echo $program['initiative_id'] ?? ''; ?>"
                                data-rating="<?php echo $current_rating; ?>">
                                <td class="text-truncate" style="max-width: 300px;">
                                    <div class="fw-medium">
                                        <a href="view_program.php?id=<?php echo $program['program_id']; ?>&period_id=<?php echo $period_id; ?>">
                                            <span class="program-name" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                                <?php if (!empty($program['program_number'])): ?>
                                                    <span class="badge bg-info me-2" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($program['program_name']); ?>
                                            </span>
                                        </a>
                                    </div>
                                    <div class="small text-muted program-type-indicator">
                                        <i class="fas fa-<?php echo $is_assigned ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                        <?php echo $is_assigned ? 'Assigned' : 'Agency-Created'; ?>
                                    </div>
                                </td>                                <td class="text-truncate" style="max-width: 250px;">                                    <?php if (!empty($program['initiative_name'])): ?>
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
                                <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $rating_map[$current_rating]['class']; ?>">
                                        <?php echo $rating_map[$current_rating]['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($program['updated_at']) && $program['updated_at'] !== '0000-00-00 00:00:00') {
                                        echo date('M j, Y', strtotime($program['updated_at']));
                                    } elseif (!empty($program['submission_date']) && $program['submission_date'] !== '0000-00-00 00:00:00') {
                                        echo date('M j, Y', strtotime($program['submission_date']));
                                    } else {
                                        echo 'Not set';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Program actions">
                                        <a href="view_program.php?id=<?php echo $program['program_id']; ?>&period_id=<?php echo $period_id; ?>" class="btn btn-outline-primary" title="View Program Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_program.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-secondary" title="Edit Program">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" 
                                           class="btn btn-outline-danger" 
                                           title="Delete Program"
                                           onclick="confirmDeleteProgram(<?php echo $program['program_id']; ?>, <?php echo $period_id ?? 'null'; ?>); return false;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                    <div class="mt-1 d-grid">
                                        <?php if (isset($program['submission_id']) && !empty($program['submission_id'])): ?>
                                            <a href="unsubmit.php?program_id=<?php echo $program['program_id']; ?>&period_id=<?php echo $period_id; ?>" 
                                               class="btn btn-outline-warning btn-sm w-100" 
                                               title="Unsubmit Program for this Period"
                                               onclick="return confirm('Are you sure you want to unsubmit this program for the period? This will revert its status and allow the agency to edit it again.');">
                                                <i class="fas fa-undo"></i> Unsubmit
                                            </a>
                                        <?php else: ?>
                                            <small class="text-muted">No submissions</small>
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

<!-- Store program data for JavaScript filtering -->
<script>
    // Make program data available to JavaScript for client-side filtering
    const unsubmittedPrograms = <?php echo json_encode($unsubmitted_programs, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const submittedPrograms = <?php echo json_encode($submitted_programs, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const periodId = <?php echo json_encode($period_id); ?>;
    const initialProgramType = <?php echo json_encode($initial_program_type); ?>;
</script>
</main>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
