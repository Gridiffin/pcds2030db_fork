<?php
/**
 * Agency Dashboard
 * 
 * Main dashboard for agency users showing program stats and submission status.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';
require_once '../../includes/status_helpers.php';
require_once '../../includes/DashboardController.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
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
    true  // Default to including assigned programs for initial load
);

// Extract initial data for page rendering
$stats = $dashboardData['stats'];
$chartData = $dashboardData['chart_data'];
$recentUpdates = $dashboardData['recent_updates'];

// Additional scripts needed for dashboard
$additionalScripts = [
    APP_URL . '/assets/js/period_selector.js',
    APP_URL . '/assets/js/agency/dashboard.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Set up the dashboard header variables
$title = "Agency Dashboard";
$subtitle = $_SESSION['agency_name'] . ' - ' . $agency_sector . ' Sector';
$headerStyle = 'primary'; // Use primary (blue) style for the dashboard home
$actions = [
    [
        'url' => '#',
        'text' => 'Refresh Data',
        'icon' => 'fas fa-sync-alt',
        'id' => 'refreshPage',
        'class' => 'btn-light border border-white text-white' // Updated to match admin dashboard
    ]
];

$headerClass = 'homepage-header';
// Include the dashboard header component with the primary style
require_once '../../includes/dashboard_header.php';
?>

<!-- Dashboard Content -->
<section class="section">
    <div class="container-fluid">
        <!-- Period Selector Component -->
        <?php require_once '../../includes/period_selector.php'; ?>
        
        <!-- Dashboard Controls Bar -->
        <div class="card shadow-sm mb-4">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="m-0 font-weight-bold">Dashboard View Options</h6>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="form-check form-switch d-inline-flex align-items-center ms-md-auto">
                            <input class="form-check-input me-2" type="checkbox" id="includeAssignedToggle" checked>
                            <label class="form-check-label" for="includeAssignedToggle">
                                Include Assigned Programs
                                <i class="fas fa-info-circle ms-1" data-bs-toggle="tooltip" 
                                   title="Toggle to include or exclude assigned programs in your dashboard"></i>
                            </label>
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
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-title">Total Programs</div>
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
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-title">On Track Programs</div>
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
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-title">Delayed Programs</div>
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
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-title">Completed Programs</div>
                            <div class="stat-value"><?php echo $stats['completed']; ?></div>
                            <div class="stat-subtitle">
                                <i class="fas fa-chart-line me-1"></i>
                                <?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>% of total
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Program Status Chart -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-white">Program Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:250px; width:100%">
                            <canvas id="programStatusChart"></canvas>
                        </div>
                        <div class="mt-4" id="programStatusLegend">
                            <span class="chart-legend-item">
                                <i class="fas fa-circle text-warning"></i> On Track
                            </span>
                            <span class="chart-legend-item">
                                <i class="fas fa-circle text-danger"></i> Delayed
                            </span>
                            <span class="chart-legend-item">
                                <i class="fas fa-circle text-success"></i> Target Achieved
                            </span>
                            <span class="chart-legend-item">
                                <i class="fas fa-circle text-gray"></i> Not Started
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Program Updates -->
            <div class="col-lg-8 mb-4">
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
                                        ?>
                                            <tr data-program-type="<?php echo $program_type; ?>">
                                                <td>
                                                    <div class="fw-medium">
                                                        <?php echo htmlspecialchars($program['program_name']); ?>
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
