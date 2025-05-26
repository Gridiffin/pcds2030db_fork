<?php
/**
 * Agency Dashboard
 * 
 * Main dashboard for agency users showing program stats and submission status.
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
require_once PROJECT_ROOT_PATH . 'app/controllers/DashboardController.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Agency Dashboard';

// Get current reporting period
$current_period = get_current_reporting_period();

// Add period_id handling for historical views
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);
$viewing_period = $period_id ? get_reporting_period($period_id) : $current_period;

// Get agency sector name
$agency_sector = get_sector_name($_SESSION['sector_id']);

// Initialize dashboard controller for initial rendering
$dashboardController = new DashboardController($conn);
$dashboardData = $dashboardController->getDashboardData(
    $_SESSION['user_id'], 
    $period_id,
    false  // Default to excluding assigned programs for initial load
);

// Extract initial data for page rendering
$stats = $dashboardData['stats'];
$chartData = $dashboardData['chart_data'];
$recentUpdates = $dashboardData['recent_updates'];

// Additional scripts needed for dashboard
$additionalScripts = [
    asset_url('js', 'period_selector.js'),
    asset_url('js/agency', 'dashboard.js')
];

// Include header - removed dashboard-specific body class to fix navbar appearance
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Set up the dashboard header variables
$title = "Agency Dashboard";
$subtitle = "Program tracking and reporting";
$headerStyle = 'standard-blue'; // Updated to use standardized blue variant
$headerClass = ''; // Removed homepage-header class as it's no longer needed
$actions = [
    [
        'url' => '#',
        'id' => 'refreshDashboard',
        'text' => 'Refresh Data',
        'icon' => 'fas fa-sync-alt',
        'class' => 'btn-light' // White outline button on blue background
    ]
];

// Include the dashboard header component with the primary style
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
?>

<!-- Dashboard Content -->
<section class="section">
    <div class="container-fluid">
        <!-- Period Selector Component -->
        <?php require_once PROJECT_ROOT_PATH . 'app/lib/period_selector.php'; ?>
        
        <!-- Dashboard Controls Bar -->
        <div class="card shadow-sm mb-4">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="m-0 font-weight-bold">Dashboard View Options</h6>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="form-check form-switch d-inline-flex align-items-center ms-md-auto">
                            <input class="form-check-input me-2" type="checkbox" id="includeAssignedToggle">
                            <label class="form-check-label" for="includeAssignedToggle">
                                Include Assigned Programs
                                <i class="fas fa-info-circle ms-1" data-bs-toggle="tooltip" 
                                   title="Toggle to include or exclude assigned programs in your dashboard"></i>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Add note about draft programs and charts -->
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="alert alert-info p-2 small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Note:</strong> Draft programs and newly assigned programs appear in "Recent Updates" but are excluded from statistics and charts until finalized.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="row">
            <!-- Programs Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card primary">
                    <div class="card-body">
                        <div class="icon-container">
                            <i class="fas fa-clipboard-list stat-icon"></i>
                        </div>                        <div class="stat-card-content text-dark">
                            <div class="stat-title fw-bold">Total Programs</div>
                            <div class="stat-value"><?php echo $stats['total']; ?></div>
                            <div class="stat-subtitle">
                                <i class="fas fa-check me-1"></i>
                                <?php echo $stats['total']; ?> Programs
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- On Track Programs Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card warning">
                    <div class="card-body">
                        <div class="icon-container">
                            <i class="fas fa-calendar-check stat-icon"></i>
                        </div>                        <div class="stat-card-content text-dark">
                            <div class="stat-title fw-bold">On Track Programs</div>
                            <div class="stat-value"><?php echo $stats['on-track']; ?></div>
                            <div class="stat-subtitle">
                                <i class="fas fa-chart-line me-1"></i>
                                <?php echo $stats['total'] > 0 ? round(($stats['on-track'] / $stats['total']) * 100) : 0; ?>% of total
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delayed Programs Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card danger">
                    <div class="card-body">
                        <div class="icon-container">
                            <i class="fas fa-exclamation-triangle stat-icon"></i>
                        </div>                        <div class="stat-card-content text-dark">
                            <div class="stat-title fw-bold">Delayed Programs</div>
                            <div class="stat-value"><?php echo $stats['delayed']; ?></div>
                            <div class="stat-subtitle">
                                <i class="fas fa-chart-line me-1"></i>
                                <?php echo $stats['total'] > 0 ? round(($stats['delayed'] / $stats['total']) * 100) : 0; ?>% of total
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Programs Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card success">
                    <div class="card-body">
                        <div class="icon-container">
                            <i class="fas fa-trophy stat-icon"></i>
                        </div>                        <div class="stat-card-content text-dark">
                            <div class="stat-title fw-bold">Completed Programs</div>
                            <div class="stat-value"><?php echo $stats['completed']; ?></div>
                            <div class="stat-subtitle">
                                <i class="fas fa-chart-line me-1"></i>
                                <?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>% of total
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        <div class="row">            <!-- Program Status Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-white">Program Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:300px; width:100%">
                            <canvas id="programStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Program Updates -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-white">Recent Program Updates</h6>
                        <span class="badge bg-light text-primary" id="programCount"><?php echo count($recentUpdates); ?></span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentUpdates)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                <p>No recent program updates found.</p>
                                <a href="submit_program_data.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i> Update Program Data
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="sortable" data-sort="name">
                                                Program Name <i class="fas fa-sort ms-1"></i>
                                            </th>
                                            <th class="sortable" data-sort="status">
                                                Status <i class="fas fa-sort ms-1"></i>
                                            </th>
                                            <th class="sortable" data-sort="date">
                                                Last Updated <i class="fas fa-sort ms-1"></i>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="dashboardProgramsTable">
                                        <?php foreach ($recentUpdates as $program): 
                                            $program_type = isset($program['is_assigned']) && $program['is_assigned'] ? 'assigned' : 'created';
                                            $program_type_label = $program_type === 'assigned' ? 'Assigned' : 'Agency-Created';
                                            $is_draft = isset($program['is_draft']) && $program['is_draft'] == 1;
                                            $is_new_assigned = $program_type === 'assigned' && !isset($program['status']);
                                        ?>
                                            <tr data-program-type="<?php echo $program_type; ?>" 
                                               class="<?php echo ($is_draft || $is_new_assigned) ? 'draft-program' : ''; ?>">
                                                <td>
                                                    <div class="fw-medium">
                                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                                        <?php if ($is_draft || $is_new_assigned): ?>
                                                            <span class="badge bg-secondary ms-1">Draft</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="small text-muted program-type-indicator">
                                                        <i class="fas fa-<?php echo $program_type === 'assigned' ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                                        <?php echo $program_type_label; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $status = $program['status'] ?? 'not-started';
                                                    $status_class = 'secondary';
                                                    
                                                    switch($status) {
                                                        case 'on-track':
                                                        case 'on-track-yearly':
                                                            $status_class = 'warning';
                                                            break;
                                                        case 'delayed':
                                                        case 'severe-delay':
                                                            $status_class = 'danger';
                                                            break;
                                                        case 'completed':
                                                        case 'target-achieved':
                                                            $status_class = 'success';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo ucfirst(str_replace('-', ' ', $status)); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo isset($program['updated_at']) && $program['updated_at'] ? date('M j, Y', strtotime($program['updated_at'])) : 'N/A'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="view_programs.php" class="btn btn-outline-primary">
                                    View All Programs <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pass chart data to JavaScript -->
<script>
    // Initialize chart with data
    const programStatusChartData = {
        labels: <?php echo json_encode($chartData['labels']); ?>,
        data: <?php echo json_encode($chartData['data']); ?>
    };
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>

