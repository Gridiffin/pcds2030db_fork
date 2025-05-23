<?php
/**
 * View All Sectors Programs
 * 
 * Page for agency users to view programs from all sectors (read-only).
 * Currently focused only on Forestry sector based on system configuration.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
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

// Process filters
$filters = [];
if (isset($_GET['status'])) $filters['status'] = $_GET['status'];

// If multi-sector is disabled, force the sector filter to be Forestry
if (!MULTI_SECTOR_ENABLED) {
    $filters['sector_id'] = FORESTRY_SECTOR_ID;
} else if (isset($_GET['sector_id'])) {
    $filters['sector_id'] = intval($_GET['sector_id']);
}

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

// Get all sectors from the database
if (MULTI_SECTOR_ENABLED) {
    $sectors = get_all_sectors();
} else {
    // Only get the Forestry sector
    $sectors = array_filter(get_all_sectors(), function($sector) {
        return $sector['sector_id'] == FORESTRY_SECTOR_ID;
    });
}

// Get all metrics for the selected period if tab is metrics
$sector_metrics = [];
if ($active_tab === 'metrics') {
    // If multi-sector is disabled or specific sector is selected, get metrics only for that sector
    if (!MULTI_SECTOR_ENABLED || (isset($filters['sector_id']) && $filters['sector_id'] > 0)) {
        $target_sector_id = MULTI_SECTOR_ENABLED ? $filters['sector_id'] : FORESTRY_SECTOR_ID;
        
        // Check permissions - agencies can only see their own sector metrics and public metrics
        if ($target_sector_id == $current_sector_id) {
            // Own sector - can see all metrics
            $sector_metrics[$target_sector_id] = get_agency_metrics_data($target_sector_id, $period_id);
        } else {
            // Get public metrics for the selected sector - for now, assume all submitted metrics are public
            $sector_metrics[$target_sector_id] = get_agency_metrics_data($target_sector_id, $period_id);
        }
    } else if (MULTI_SECTOR_ENABLED) {
        // No specific sector selected and multi-sector is enabled, get all sectors' metrics with permission checks
        foreach ($sectors as $sector) {
            // For current agency's sector, get all metrics
            if ($sector['sector_id'] == $current_sector_id) {
                $sector_metrics[$sector['sector_id']] = get_agency_metrics_data($sector['sector_id'], $period_id);
            } else {
                // For other sectors, get only public metrics (for now, assume all submitted metrics are public)
                $sector_metrics[$sector['sector_id']] = get_agency_metrics_data($sector['sector_id'], $period_id);
            }
        }
    }
    
    // Apply search filter if specified
    if (isset($filters['search']) && !empty($filters['search'])) {
        $search_term = strtolower($filters['search']);
        foreach ($sector_metrics as $sector_id => $metrics) {
            $sector_metrics[$sector_id] = array_filter($metrics, function($metric) use ($search_term) {
                // Search in metric name/table_name
                return stripos(strtolower($metric['table_name']), $search_term) !== false;
            });
        }
        
        // Remove sectors with no matching metrics after filtering
        $sector_metrics = array_filter($sector_metrics, function($metrics) {
            return !empty($metrics);
        });
    }
}

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

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/rating_utils.js',
    APP_URL . '/assets/js/period_selector.js',
    APP_URL . '/assets/js/agency/all_sectors.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

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
    <ul class="nav nav-tabs mb-4" id="viewTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $active_tab === 'programs' ? 'active' : ''; ?>" href="?tab=programs<?php echo $period_id ? '&period_id=' . $period_id : ''; ?><?php echo isset($_GET['sector_id']) ? '&sector_id=' . $_GET['sector_id'] : ''; ?><?php echo isset($_GET['agency_id']) ? '&agency_id=' . $_GET['agency_id'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" id="programs-tab">
                <i class="fas fa-project-diagram me-1"></i> Programs
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo $active_tab === 'metrics' ? 'active' : ''; ?>" href="?tab=metrics<?php echo $period_id ? '&period_id=' . $period_id : ''; ?><?php echo isset($_GET['sector_id']) ? '&sector_id=' . $_GET['sector_id'] : ''; ?>" id="metrics-tab">
                <i class="fas fa-chart-line me-1"></i> Metrics
            </a>
        </li>
    </ul>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title m-0">
                <i class="fas fa-filter me-2"></i>Filter <?php echo $active_tab === 'programs' ? 'Programs' : 'Metrics'; ?>
            </h5>
        </div>
        <div class="card-body">
            <form method="get" id="filterForm">
                <!-- Preserve active tab and period_id when filtering -->
                <input type="hidden" name="tab" value="<?php echo $active_tab; ?>">
                <?php if ($period_id): ?>
                <input type="hidden" name="period_id" value="<?php echo $period_id; ?>">
                <?php endif; ?>
                
                <div class="row g-3">
                    <?php if ($active_tab === 'programs'): ?>
                    <!-- Program-specific filters -->
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
                        <label for="status" class="form-label">Rating</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Ratings</option>
                            <option class="status-target-achieved" value="target-achieved" <?php if(isset($_GET['status']) && $_GET['status'] === 'target-achieved') echo 'selected'; ?>>Target Achieved</option>
                            <option class="status-on-track-yearly" value="on-track-yearly" <?php if(isset($_GET['status']) && $_GET['status'] === 'on-track-yearly') echo 'selected'; ?>>On Track</option>
                            <option class="status-severe-delay" value="severe-delay" <?php if(isset($_GET['status']) && $_GET['status'] === 'severe-delay') echo 'selected'; ?>>Delayed</option>
                            <option class="status-not-started" value="not-started" <?php if(isset($_GET['status']) && $_GET['status'] === 'not-started') echo 'selected'; ?>>Not Started</option>
                        </select>
                    </div>
                    
                    <?php if (MULTI_SECTOR_ENABLED): ?>
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
                    <?php else: ?>
                    <!-- When multi-sector is disabled, only show agencies filter -->
                    <div class="col-md-4 filter-control-wrapper">
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
                    <?php endif; ?>
                    <?php else: ?>
                    <!-- Metrics-specific filters -->
                    <div class="col-md-4 filter-control-wrapper">
                        <label for="search" class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search" 
                                placeholder="Metric name" 
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                    </div>
                    
                    <?php if (MULTI_SECTOR_ENABLED): ?>
                    <div class="col-md-6 filter-control-wrapper">
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
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="col-auto d-flex align-items-end">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Apply
                            </button>
                            <a href="view_all_sectors.php?tab=<?php echo $active_tab; ?><?php echo $period_id ? '&period_id=' . $period_id : ''; ?>" class="btn btn-outline-secondary">
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
            <span>Filtered results: <strong><?php echo $active_tab === 'programs' ? count($all_programs) : count($sector_metrics, COUNT_RECURSIVE) - count($sector_metrics); ?></strong> <?php echo $active_tab === 'programs' ? 'programs' : 'metrics'; ?> found</span>
            <a href="view_all_sectors.php?tab=<?php echo $active_tab; ?><?php echo $period_id ? '&period_id=' . $period_id : ''; ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Clear All Filters
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($active_tab === 'programs'): ?>
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
                            <th>Rating</th>
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
                            <tr class="<?php echo ($program['sector_id'] == $current_sector_id) ? 'current-sector-row' : ''; ?> sector-<?php echo $program['sector_id']; ?>">
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
                                            <a href="program_details.php?id=<?php echo $program['program_id']; ?>&source=all_sectors" class="btn btn-outline-primary" title="View Details">
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
    <?php else: ?>
    <!-- Metrics List -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Cross-Sector Metrics</h5>
            <div>
                <span class="badge bg-info me-2">Showing submitted metrics only</span>
                <?php 
                $metrics_count = 0;
                foreach($sector_metrics as $sector_id => $metrics) {
                    $metrics_count += count($metrics);
                }
                ?>
                <span class="badge bg-primary"><?php echo $metrics_count; ?> Metrics</span>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($sector_metrics)): ?>
                <div class="text-center py-4">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        No metrics found for the selected criteria.
                    </div>
                </div>
            <?php else: ?>
                <div class="accordion" id="metricsAccordion">
                    <?php foreach ($sectors as $sector): ?>
                        <?php if (isset($sector_metrics[$sector['sector_id']]) && !empty($sector_metrics[$sector['sector_id']])): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo $sector['sector_id']; ?>">
                                    <button class="accordion-button <?php echo ($sector['sector_id'] != $current_sector_id) ? 'collapsed' : ''; ?>" 
                                            type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?php echo $sector['sector_id']; ?>" 
                                            aria-expanded="<?php echo ($sector['sector_id'] == $current_sector_id) ? 'true' : 'false'; ?>" 
                                            aria-controls="collapse<?php echo $sector['sector_id']; ?>">
                                        <?php echo htmlspecialchars($sector['sector_name']); ?>
                                        <?php if ($sector['sector_id'] == $current_sector_id): ?>
                                            <span class="badge bg-primary ms-2">Your Sector</span>
                                        <?php endif; ?>
                                        <span class="badge bg-secondary ms-2"><?php echo count($sector_metrics[$sector['sector_id']]); ?> metrics</span>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $sector['sector_id']; ?>" 
                                     class="accordion-collapse collapse <?php echo ($sector['sector_id'] == $current_sector_id) ? 'show' : ''; ?>" 
                                     aria-labelledby="heading<?php echo $sector['sector_id']; ?>" 
                                     data-bs-parent="#metricsAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Metric ID</th>
                                                        <th>Name</th>
                                                        <th>Period</th>
                                                        <th>Last Updated</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($sector_metrics[$sector['sector_id']] as $metric): ?>
                                                        <tr>
                                                            <td><?php echo $metric['metric_id']; ?></td>
                                                            <td>
                                                                <div class="fw-medium">
                                                                    <?php echo htmlspecialchars($metric['table_name']); ?>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <?php if (isset($metric['quarter']) && isset($metric['year'])): ?>
                                                                    Q<?php echo $metric['quarter']; ?>-<?php echo $metric['year']; ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Not available</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($metric['updated_at'])): ?>
                                                                    <?php echo date('M j, Y', strtotime($metric['updated_at'])); ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Not available</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="view_metric.php?metric_id=<?php echo $metric['metric_id']; ?>" class="btn btn-outline-primary" title="View Metric">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>


