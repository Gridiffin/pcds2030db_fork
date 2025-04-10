<?php
/**
 * View All Sectors Programs
 * 
 * Page for agency users to view programs from all sectors (read-only).
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

// Set page title
$pageTitle = 'View All Sectors Programs';

// Get current reporting period
$current_period = get_current_reporting_period();

// Add period_id handling for historical views
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);
$viewing_period = $period_id ? get_reporting_period($period_id) : $current_period;

// Get all sectors programs
$all_programs = get_all_sectors_programs($period_id);

// Get current agency's sector
$current_sector_id = $_SESSION['sector_id'];

// Get all sectors from the database instead of extracting them from programs
$sectors = get_all_sectors();

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/status_utils.js',
    APP_URL . '/assets/js/agency/view_programs.js',
    APP_URL . '/assets/js/period_selector.js',
    APP_URL . '/assets/js/agency/all_sectors.js' // New JS file for sectors-specific functionality
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Set up the page header variables
$title = "Cross-Sector Programs";
$subtitle = "View and track programs across all sectors";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = []; // No actions needed for this view

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';
?>

<div class="container-fluid px-4">
    <!-- Period Selector Component -->
    <?php require_once '../../includes/period_selector.php'; ?>

    <!-- Enhanced Filtering Controls -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary">
            <h5 class="card-title m-0 text-white"><i class="fas fa-filter me-2 text-white"></i>Search & Filter</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="searchPrograms" class="form-label">Search Programs</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchPrograms" placeholder="Search by program name or description...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="sectorFilter" class="form-label">Sector</label>
                    <select class="form-select" id="sectorFilter">
                        <option value="">All Sectors</option>
                        <?php foreach ($sectors as $sector): ?>
                            <option value="<?php echo $sector['sector_id']; ?>">
                                <?php echo htmlspecialchars($sector['sector_name']); ?>
                                <?php if ($sector['sector_id'] == $current_sector_id): ?> (Your Sector)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="target-achieved">Monthly Target Achieved</option>
                        <option value="on-track-yearly">On Track for Year</option>
                        <option value="severe-delay">Severe Delays</option>
                        <option value="not-started">Not Started</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" id="resetFilters">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
            
            <!-- Active filters indicator -->
            <div class="filter-indicators mt-3" id="activeFilters" style="display: none;">
                <div class="d-flex align-items-center">
                    <span class="badge rounded-pill bg-light text-dark border me-2">
                        <i class="fas fa-filter me-1"></i> Active Filters:
                    </span>
                    <div id="filterBadges" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>
    </div>

    <div data-period-content="programs_content">
        <?php if (empty($all_programs) || isset($all_programs['error'])): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo isset($all_programs['error']) ? $all_programs['error'] : 'No programs found across sectors.'; ?>
            </div>
        <?php else: ?>
            <!-- Unified Programs Table (replacing tabs) -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0 text-white"><i class="fas fa-list me-2 text-white"></i>Programs</h5>
                    <span class="badge bg-light text-primary" id="programCount"><?php echo count($all_programs); ?> Programs</span>
                </div>
                <div class="card-body">
                    <!-- No Results Message (initially hidden) -->
                    <div id="noResultsMessage" class="alert alert-info" style="display: none;">
                        <i class="fas fa-search me-2"></i>
                        No programs match your search criteria. Try adjusting your filters.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="programsTable">
                            <thead>
                                <tr>
                                    <th>Program Name</th>
                                    <th>Sector</th>
                                    <th>Agency</th>
                                    <th>Status</th>
                                    <th>Timeline</th>
                                    <th>Current Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($all_programs)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                No programs found for this view.
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($all_programs as $program): ?>
                                        <tr class="<?php echo ($program['sector_id'] == $current_sector_id) ? 'current-sector-row' : ''; ?>"
                                            data-program-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                            data-agency="<?php echo htmlspecialchars($program['agency_name']); ?>"
                                            data-sector="<?php echo htmlspecialchars($program['sector_name']); ?>"
                                            data-sector-id="<?php echo $program['sector_id']; ?>"
                                            data-status="<?php echo htmlspecialchars(convert_legacy_status($program['status'] ?? 'not-reported')); ?>">
                                            <td>
                                                <strong><?php echo htmlspecialchars($program['program_name']); ?></strong>
                                                <?php if (!empty($program['description'])): ?>
                                                    <div class="small text-muted"><?php echo substr(htmlspecialchars($program['description']), 0, 100); ?><?php echo strlen($program['description']) > 100 ? '...' : ''; ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($program['sector_name']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
                                            <td>
                                                <?php if (isset($program['status']) && $program['status']): ?>
                                                    <?php echo get_status_badge($program['status']); ?>
                                                    <?php if (isset($program['status_date']) && $program['status_date']): ?>
                                                        <div class="small text-muted mt-1">
                                                            <i class="fas fa-calendar-day"></i> <?php echo date('M j, Y', strtotime($program['status_date'])); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">Not Reported</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($program['start_date']) && $program['start_date']): ?>
                                                    <div>
                                                        <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                                        <?php echo date('M j, Y', strtotime($program['start_date'])); ?>
                                                        <?php if (isset($program['end_date']) && $program['end_date']): ?>
                                                            - <?php echo date('M j, Y', strtotime($program['end_date'])); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Not specified</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($program['achievement']) && $program['achievement']): ?>
                                                    <?php echo htmlspecialchars($program['achievement']); ?>
                                                <?php elseif (isset($program['status_text']) && $program['status_text']): ?>
                                                    <?php echo htmlspecialchars($program['status_text']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No progress reported</span>
                                                <?php endif; ?>
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
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
