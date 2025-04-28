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
require_once '../../includes/agencies/index.php';
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

// Process filters
$filters = [];
if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
if (isset($_GET['sector_id'])) $filters['sector_id'] = intval($_GET['sector_id']);
if (isset($_GET['agency_id'])) $filters['agency_id'] = intval($_GET['agency_id']);
if (isset($_GET['search'])) $filters['search'] = trim($_GET['search']);

// Add period_id handling for historical views
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);
$viewing_period = $period_id ? get_reporting_period($period_id) : $current_period;

// Get all sectors programs with filters
$all_programs = get_all_sectors_programs($period_id, $filters);

// Filter out draft programs for agency view
if (!empty($all_programs) && !isset($all_programs['error'])) {
    $all_programs = array_filter($all_programs, function($program) {
        return !isset($program['is_draft']) || $program['is_draft'] == 0;
    });
    // Reindex array after filtering
    $all_programs = array_values($all_programs);
}

// Get current agency's sector
$current_sector_id = $_SESSION['sector_id'];

// Get all sectors from the database instead of extracting them from programs
$sectors = get_all_sectors();

// Get all agencies for filter dropdown
$agencies = [];
$agencies_query = "SELECT user_id, agency_name FROM users WHERE role = 'agency' ORDER BY agency_name";
$agencies_result = $conn->query($agencies_query);
while ($row = $agencies_result->fetch_assoc()) {
    $agencies[] = $row;
}

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/status_utils.js',
    APP_URL . '/assets/js/period_selector.js',
    APP_URL . '/assets/js/agency/all_sectors.js'
];

// Add custom CSS
$additionalStyles = '
<style>
    .filter-control-wrapper {
        position: relative;
    }
    
    /* Highlight current agency sector programs */
    tr.current-sector-row {
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }
</style>';

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

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title m-0">
                <i class="fas fa-filter me-2"></i>Filter Programs
            </h5>
        </div>
        <div class="card-body">
            <form method="get" id="filterForm">
                <!-- Preserve period_id when filtering -->
                <?php if ($period_id): ?>
                <input type="hidden" name="period_id" value="<?php echo $period_id; ?>">
                <?php endif; ?>
                
                <div class="row g-3">
                    <div class="col-md-3 filter-control-wrapper">
                        <label for="search" class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search" 
                                placeholder="Program name or description" 
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-2 filter-control-wrapper">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="target-achieved" <?php if(isset($_GET['status']) && $_GET['status'] === 'target-achieved') echo 'selected'; ?>>Target Achieved</option>
                            <option value="on-track-yearly" <?php if(isset($_GET['status']) && $_GET['status'] === 'on-track-yearly') echo 'selected'; ?>>On Track</option>
                            <option value="severe-delay" <?php if(isset($_GET['status']) && $_GET['status'] === 'severe-delay') echo 'selected'; ?>>Delayed</option>
                            <option value="not-started" <?php if(isset($_GET['status']) && $_GET['status'] === 'not-started') echo 'selected'; ?>>Not Started</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 filter-control-wrapper">
                        <label for="sector_id" class="form-label">Sector</label>
                        <select class="form-select" id="sector_id" name="sector_id">
                            <option value="">All Sectors</option>
                            <?php foreach ($sectors as $sector): ?>
                                <option value="<?php echo $sector['sector_id']; ?>" 
                                    <?php if(isset($_GET['sector_id']) && $_GET['sector_id'] == $sector['sector_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($sector['sector_name']); ?>
                                    <?php if ($sector['sector_id'] == $current_sector_id): ?> (Your Sector)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 filter-control-wrapper">
                        <label for="agency_id" class="form-label">Agency</label>
                        <select class="form-select" id="agency_id" name="agency_id">
                            <option value="">All Agencies</option>
                            <?php foreach ($agencies as $agency): ?>
                                <option value="<?php echo $agency['user_id']; ?>" 
                                    <?php if(isset($_GET['agency_id']) && $_GET['agency_id'] == $agency['user_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($agency['agency_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
<div class="col-auto d-flex align-items-end">
    <div class="btn-group w-100">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter me-1"></i> Apply
        </button>
        <a href="view_all_sectors.php<?php echo $period_id ? '?period_id=' . $period_id : ''; ?>" class="btn btn-outline-secondary">
            <i class="fas fa-undo me-1"></i> Reset
        </a>
    </div>
</div>
                </div>
            </form>
        </div>
    </div>

    <!-- Active filters display -->
    <?php if (!empty($filters)): ?>
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-filter me-2"></i>
            <span>Filtered results: <strong><?php echo count($all_programs); ?></strong> programs found</span>
            <a href="view_all_sectors.php<?php echo $period_id ? '?period_id=' . $period_id : ''; ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Clear All Filters
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Programs List -->
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
                <table class="table table-hover" id="programsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Program Name</th>
                            <th>Agency</th>
                            <th>Sector</th>
                            <th>Status</th>
                            <th>Timeline</th>
                            <th>Last Updated</th>
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
                                <tr class="<?php echo ($program['sector_id'] == $current_sector_id) ? 'current-sector-row' : ''; ?>">
                                    <td>
                                        <div class="fw-medium">
                                            <?php echo htmlspecialchars($program['program_name']); ?>
                                        </div>
                                        <?php if (!empty($program['description'])): ?>
                                            <div class="small text-muted"><?php echo substr(htmlspecialchars($program['description']), 0, 50); ?><?php echo strlen($program['description']) > 50 ? '...' : ''; ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($program['sector_name']); ?></span>
                                    </td>
                                    <td>
                                        <?php if (isset($program['status'])): ?>
                                            <?php echo get_status_badge($program['status']); ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Reported</span>
                                        <?php endif; ?>
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
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="program_details.php?id=<?php echo $program['program_id']; ?>" class="btn btn-outline-primary" title="View Details">
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
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
