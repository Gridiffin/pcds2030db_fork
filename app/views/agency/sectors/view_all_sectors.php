<?php
/**
 * View All Sectors Programs
 * 
 * Page for agency users to view programs from all sectors (read-only).
 * Currently focused only on Forestry sector based on system configuration.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = MULTI_SECTOR_ENABLED ? 'View All Sectors' : 'Forestry Sector Programs';

// Get current reporting period
$current_period = get_current_reporting_period();

// Determine which tab is active
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'programs';

// Remove server-side filters and load all programs for client-side filtering
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);
$all_programs = get_all_sectors_programs($period_id, []);

// Filter out draft programs for agency view
if (!empty($all_programs) && !isset($all_programs['error'])) {
    $all_programs = array_filter($all_programs, function($program) {
        return !isset($program['is_draft']) || $program['is_draft'] == 0;
    });
    $all_programs = array_values($all_programs);
}

// Get current agency's sector
$current_sector_id = $_SESSION['sector_id'];

// Load sector outcomes data for the current sector
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/outcomes.php';
$sector_outcomes_data = get_agency_sector_outcomes($current_sector_id);

// Get all sectors from the database
if (MULTI_SECTOR_ENABLED) {
    $sectors = get_all_sectors();
} else {
    // Only get the Forestry sector
    $sectors = array_filter(get_all_sectors(), function($sector) {
        return $sector['sector_id'] == FORESTRY_SECTOR_ID;
    });
}

// Metrics functionality removed: get_agency_metrics_data and all related logic are unused and deleted for clarity.

// Get all agencies for filter dropdown
if (MULTI_SECTOR_ENABLED) {
    $agencies = [];
    $agencies_query = "SELECT user_id, agency_name FROM users WHERE role = 'agency' ORDER BY agency_name";
    $agencies_result = $conn->query($agencies_query);
    while ($row = $agencies_result->fetch_assoc()) {
        $agencies[] = $row;
    }
} else {
    // Only get agencies from the Forestry sector
    $agencies = [];
    $agencies_query = "SELECT user_id, agency_name FROM users WHERE role = 'agency' AND sector_id = ? ORDER BY agency_name";
    $stmt = $conn->prepare($agencies_query);
    $stmt->bind_param("i", $forestry_sector_id);
    $forestry_sector_id = FORESTRY_SECTOR_ID;
    $stmt->execute();
    $agencies_result = $stmt->get_result();
    while ($row = $agencies_result->fetch_assoc()) {
        $agencies[] = $row;
    }
}

$additionalScripts = [
    APP_URL . '/assets/js/utilities/rating_utils.js',
    APP_URL . '/assets/js/period_selector.js',
    APP_URL . '/assets/js/agency/all_sectors.js'
];

// Include header
require_once '../../layouts/header.php';

// Include agency navigation
require_once '../../layouts/agency_nav.php';

// Set up the page header variables
$title = MULTI_SECTOR_ENABLED ? "Cross-Sector Programs" : "Forestry Sector Programs";
$subtitle = MULTI_SECTOR_ENABLED ? 
    "View and track programs across all sectors" : 
    "View and track forestry sector programs";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = []; // No actions needed for this view

// Include the dashboard header component
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
?>

<div class="container-fluid px-4">
    <!-- Period Selector Component -->
    <?php require_once PROJECT_ROOT_PATH . 'app/lib/period_selector.php'; ?>

    <?php if (!MULTI_SECTOR_ENABLED): ?>
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Note:</strong> The dashboard is currently focused on the Forestry sector only.
    </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <!-- Metrics tab removed: Only Programs tab is shown -->
    <ul class="nav nav-tabs mb-4" id="viewTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $active_tab === 'programs' ? 'active' : ''; ?>" href="?tab=programs<?php echo $period_id ? '&period_id=' . $period_id : ''; ?><?php echo isset($_GET['sector_id']) ? '&sector_id=' . $_GET['sector_id'] : ''; ?><?php echo isset($_GET['agency_id']) ? '&agency_id=' . $_GET['agency_id'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" id="programs-tab">
                <i class="fas fa-project-diagram me-1"></i> Programs
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $active_tab === 'outcomes' ? 'active' : ''; ?>" href="?tab=outcomes<?php echo $period_id ? '&period_id=' . $period_id : ''; ?><?php echo isset($_GET['sector_id']) ? '&sector_id=' . $_GET['sector_id'] : ''; ?><?php echo isset($_GET['agency_id']) ? '&agency_id=' . $_GET['agency_id'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" id="outcomes-tab">
                <i class="fas fa-chart-bar me-1"></i> Outcomes
            </a>
        </li>
    </ul>

<!-- Filter Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title m-0">
            <i class="fas fa-filter me-2"></i>
            <?php echo $active_tab === 'outcomes' ? 'Filter Outcomes' : 'Filter Programs'; ?>
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3 filter-control-wrapper">
                <label for="searchFilter" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchFilter" placeholder="<?php echo $active_tab === 'outcomes' ? 'Outcome table name or description' : 'Program name or description'; ?>" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
            </div>
            
            <div class="col-md-2 filter-control-wrapper">
                <label for="ratingFilter" class="form-label">Rating</label>
                <select class="form-select" id="ratingFilter">
                    <option value="">All Ratings</option>
                    <option value="target-achieved">Target Achieved</option>
                    <option value="on-track-yearly">On Track</option>
                    <option value="severe-delay">Delayed</option>
                    <option value="not-started">Not Started</option>
                </select>
            </div>
            
            <div class="col-md-3 filter-control-wrapper">
                <label for="agencyFilter" class="form-label">Agency</label>
                <select class="form-select" id="agencyFilter">
                    <option value="">All Agencies</option>
                    <?php foreach ($agencies as $agency): ?>
                        <option value="<?php echo $agency['user_id']; ?>">
                            <?php echo htmlspecialchars($agency['agency_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-auto d-flex align-items-end">
                <button id="resetFilters" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>
</div>

                    


<!-- Active filters display -->


    <?php if (!empty($filters)): ?>
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-filter me-2"></i>
            <span>Filtered results: <strong><?php echo $active_tab === 'programs' ? count($all_programs) : count($sector_metrics, COUNT_RECURSIVE) - count($sector_metrics); ?></strong> <?php echo $active_tab === 'programs' ? 'programs' : 'metrics'; ?> found</span>
            <a href="view_all_sectors.php?tab=<?php echo $active_tab; ?><?php echo $period_id ? '&period_id=' . $period_id : ''; ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Clear All Filters
            </a>
        </div>
    </div>
    <?php endif; ?>

<?php if ($active_tab === 'programs'): ?>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">All Programs</h5>
        <div>
            <span class="badge bg-info me-2">Showing published programs only</span>
            <span class="badge bg-primary"><?php echo count($all_programs); ?> Programs</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0" id="programsTable">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-sort="name" style="width: 20%;">Program Name <i class="fas fa-sort ms-1"></i></th>

                        <th class="sortable" data-sort="agency">Agency <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="sector">Sector <i class="fas fa-sort ms-1"></i></th>
                        <th class="sortable" data-sort="rating">Rating <i class="fas fa-sort ms-1"></i></th>
                        <th>Timeline</th>
                        <th class="sortable" data-sort="date">Last Updated <i class="fas fa-sort ms-1"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_programs) || isset($all_programs['error'])): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <?php if (isset($all_programs['error'])): ?>
                                        <?php echo $all_programs['error']; ?>
                                    <?php elseif ($period_id && $period_id != ($current_period['period_id'] ?? null)): ?>
                                        No programs were submitted for this reporting period.
                                    <?php else: ?>
                                        No programs found matching your criteria.
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($all_programs as $program): ?>
                    <tr class="<?php echo (($program['sector_id'] ?? null) == $current_sector_id) ? 'current-sector-row' : ''; ?> sector-<?php echo $program['sector_id'] ?? ''; ?>" data-agency="<?php echo $program['agency_id'] ?? ''; ?>" data-rating="<?php echo $program['status'] ?? ''; ?>">
                                <td class="text-truncate" style="max-width: 200px;">
                                    <span class="program-name" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                    </span>
                                </td>

                                <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($program['sector_name']); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $rating = $program['rating'] ?? 'not-started';
                                    echo get_rating_badge($rating);
                                    ?>
                                </td>
                                <td>
                                    <?php if (isset($program['start_date']) && $program['start_date']): ?>
                                        <?php echo date('M j, Y', strtotime($program['start_date'])); ?>
                                        <?php if (isset($program['end_date']) && $program['end_date']): ?>
                                            <span class="text-muted">to</span> <?php echo date('M j, Y', strtotime($program['end_date'])); ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not specified</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($program['updated_at'])): ?>
                                        <?php echo date('M j, Y', strtotime($program['updated_at'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not available</span>
                                    <?php endif; ?>
                                </td>
                                <td>                                        <div class="btn-group btn-group-sm">
                                        <a href="../programs/program_details.php?id=<?php echo $program['program_id']; ?>&source=all_sectors" class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
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
<?php elseif ($active_tab === 'outcomes'): ?>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">All Sector Outcomes</h5>
        <div>
            <span class="badge bg-primary"><?php echo count($sector_outcomes_data); ?> Outcomes</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0" id="outcomesTable">
                <thead class="table-light">
                    <tr>
                        <th>Table Name</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sector_outcomes_data)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No outcomes found for this sector.
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sector_outcomes_data as $outcome): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($outcome['table_name']); ?></td>
                                <td>
                                    <?php
                                    $current_user_id = $_SESSION['user_id'] ?? null;
                                    if ($current_user_id && $outcome['submitted_by'] == $current_user_id) {
                                        echo 'You';
                                    } else {
                                        echo htmlspecialchars($outcome['submitted_by_username']);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i> Submitted
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="../outcomes/view_outcome.php?outcome_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i> View
                                        </a>
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
<?php endif; ?>
</div>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
