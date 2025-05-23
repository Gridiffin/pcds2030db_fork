<?php
/**
 * Admin Programs
 * 
 * Programs overview for admin users.
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
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/statistics.php';

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

// Process filters
$filters = [
    'status' => $_GET['rating'] ?? null,
    'sector_id' => isset($_GET['sector_id']) ? intval($_GET['sector_id']) : null,
    'agency_id' => isset($_GET['agency_id']) ? intval($_GET['agency_id']) : null
    // Note: 'period_id' for filtering within get_admin_programs_list is handled by passing $period_id directly
];

// This $viewing_period is used by the period_selector.php component to show the correct selection in the dropdown
$viewing_period = $current_period; // $current_period is now correctly set based on URL or default

// Get all programs with filters
$programs = get_admin_programs_list($period_id, $filters);

// Get all sectors for filter dropdown
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
    APP_URL . '/assets/js/period_selector.js',
    APP_URL . '/assets/js/admin/programs_list.js'
];

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Set up the dashboard header variables
$title = "Programs Overview";
$subtitle = "Monitor and manage all programs across sectors";
$headerStyle = 'light';
$actions = [
    [
        'url' => 'assign_programs.php',
        'text' => 'Assign Programs',
        'icon' => 'fas fa-tasks',
        'class' => 'btn-success me-2'
    ]
];

// Include the dashboard header component
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
?>

<!-- Period Selector Component -->
<?php require_once PROJECT_ROOT_PATH . 'app/lib/period_selector.php'; ?>

<!-- Filter Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">
            <i class="fas fa-filter me-2"></i>Filter Programs
        </h5>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end" id="filterForm">
            <!-- Rating/Status Filter -->
            <div class="col-md-3">
                <label for="rating" class="form-label">Status</label>
                <div class="filter-control-wrapper">
                    <select class="form-select" id="rating" name="rating">
                        <option value="">All Statuses</option>
                        <option value="target-achieved" <?php echo ($filters['status'] === 'target-achieved') ? 'selected' : ''; ?>>Target Achieved</option>
                        <option value="on-track-yearly" <?php echo ($filters['status'] === 'on-track-yearly') ? 'selected' : ''; ?>>On Track</option>
                        <option value="severe-delay" <?php echo ($filters['status'] === 'severe-delay') ? 'selected' : ''; ?>>Severe Delay</option>
                        <option value="not-started" <?php echo ($filters['status'] === 'not-started') ? 'selected' : ''; ?>>Not Started</option>
                    </select>
                </div>
            </div>
            
            <!-- Sector Filter -->
            <div class="col-md-3">
                <label for="sector_id" class="form-label">Sector</label>
                <div class="filter-control-wrapper">
                    <select class="form-select" id="sector_id" name="sector_id">
                        <option value="">All Sectors</option>
                        <?php foreach ($sectors as $sector): ?>
                            <option value="<?php echo $sector['sector_id']; ?>" 
                                    <?php echo ($filters['sector_id'] === $sector['sector_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sector['sector_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Agency Filter -->
            <div class="col-md-3">
                <label for="agency_id" class="form-label">Agency</label>
                <div class="filter-control-wrapper">
                    <select class="form-select" id="agency_id" name="agency_id">
                        <option value="">All Agencies</option>
                        <?php foreach ($agencies as $agency): ?>
                            <option value="<?php echo $agency['user_id']; ?>" 
                                    <?php echo ($filters['agency_id'] === $agency['user_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($agency['agency_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Filter Actions -->
            <div class="col-md-3">
                <div class="filter-actions">
                    <button type="reset" class="btn btn-light" id="resetFilters">
                        <i class="fas fa-undo me-1"></i>Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Apply
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Programs Table -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Programs List</h5>
        <div class="btn-group">
            <button class="btn btn-sm btn-light border" id="refreshTable">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Sector</th>
                        <th>Agency</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Last Updated</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($programs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <?php if ($period_id && $period_id != ($current_period['period_id'] ?? null)): ?>
                                        No programs were submitted for this reporting period.
                                    <?php else: ?>
                                        No programs found matching your criteria.
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($programs as $program): ?>
                            <tr>
                                <td>
                                    <div class="fw-medium">
                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                        <?php if (isset($program['is_draft']) && $program['is_draft']): ?>
                                            <span class="badge bg-secondary ms-1">Draft</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($program['description'])): ?>
                                        <div class="text-muted small mt-1">
                                            <?php echo htmlspecialchars(substr($program['description'], 0, 100)) . (strlen($program['description']) > 100 ? '...' : ''); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($program['sector_name']); ?></td>
                                <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
                                <td class="text-center">
                                    <?php if (isset($program['current_submission'])): ?>
                                        <span class="badge rounded-pill bg-<?php echo get_status_color($program['current_submission']['status']); ?>">
                                            <?php echo get_status_display_name($program['current_submission']['status']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-secondary">Not Started</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (isset($program['current_submission'])): ?>
                                        <small><?php echo date('M j, Y g:i A', strtotime($program['current_submission']['submission_date'])); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">--</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="view_program.php?id=<?php echo $program['program_id']; ?>" 
                                           class="btn btn-light" title="View Program">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_program.php?id=<?php echo $program['program_id']; ?>" 
                                           class="btn btn-light" title="Edit Program">
                                            <i class="fas fa-edit"></i>
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

<?php
// Include footer
require_once '../layouts/footer.php';
?>



