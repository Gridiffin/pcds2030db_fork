<?php
/**
 * Agency Dashboard
 * 
 * Main dashboard for agency users showing program stats and submission rating.
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
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/outcomes.php';
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

// Agency dashboard - no sector functionality needed

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

// Get outcomes statistics for the agency
$outcomes_stats = get_agency_outcomes_statistics(null, $period_id);

// Additional scripts needed for dashboard
$additionalScripts = [
    asset_url('js', 'period_selector.js'),
    asset_url('js/agency', 'dashboard.js'),
    asset_url('js/agency', 'dashboard_chart.js'),
    asset_url('js/agency', 'dashboard_charts.js')
];

// Include header - removed dashboard-specific body class to fix navbar appearance
require_once PROJECT_ROOT_PATH . 'app/views/layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Agency Dashboard',
    'subtitle' => 'Program tracking and reporting',
    'variant' => 'green',
    'actions' => [
        [
            'url' => '#',
            'id' => 'refreshDashboard',
            'text' => 'Refresh Data',
            'icon' => 'fas fa-sync-alt',
            'class' => 'btn-light'
        ]
    ]
];

// Include modern page header
require_once PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php';
?>

<!-- Dashboard Content -->
<section class="section">
    <div class="container-fluid">
        <!-- Period Selector Component -->
        <?php require_once PROJECT_ROOT_PATH . 'app/lib/period_selector_dashboard.php'; ?>
        
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
        </div>        <div class="row">            <!-- Program Rating Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-white">Program Rating Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:300px; width:100%">
                            <canvas id="programRatingChart"></canvas>
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
                                            </th>                                            <th class="sortable" data-sort="rating">
                                                Rating <i class="fas fa-sort ms-1"></i>
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
                                            $is_new_assigned = $program_type === 'assigned' && !isset($program['rating']);
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
                                                </td>                                                <td>
                                                    <?php 
                                                    $rating = $program['rating'] ?? 'not-started';
                                                    $rating_class = 'secondary';
                                                    
                                                    switch($rating) {
                                                        case 'on-track':
                                                        case 'on-track-yearly':
                                                            $rating_class = 'warning';
                                                            break;
                                                        case 'delayed':
                                                        case 'severe-delay':
                                                            $rating_class = 'danger';
                                                            break;
                                                        case 'completed':
                                                        case 'target-achieved':
                                                            $rating_class = 'success';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $rating_class; ?>">
                                                        <?php echo ucfirst(str_replace('-', ' ', $rating)); ?>
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
                                <a href="../programs/view_programs.php" class="btn btn-outline-primary">
                                    View All Programs <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outcomes Overview Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title m-0 text-white">
                            <i class="fas fa-clipboard-list me-2 text-warning"></i>Outcomes Overview
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row gx-4 gy-4">
                            <!-- Outcomes Statistics Cards -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card bg-primary text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                        <h4><?php echo $outcomes_stats['total_outcomes']; ?></h4>
                                        <p class="mb-0">Total Outcomes</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-6">
                                <div class="card bg-success text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-check-square fa-3x mb-3"></i>
                                        <h4><?php echo $outcomes_stats['submitted_outcomes']; ?></h4>
                                        <p class="mb-0">Submitted</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-6">
                                <div class="card bg-warning text-white h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                                        <h4><?php echo $outcomes_stats['draft_outcomes']; ?></h4>
                                        <p class="mb-0">Drafts</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Outcomes Actions -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="p-3 border rounded h-100 bg-light">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-success me-2" style="min-width: 90px;">Submit</span>
                                        <span class="fw-bold">Manage Your Outcomes</span>
                                    </div>
                                    <p class="text-muted mb-3">Submit and manage outcomes data for your sector</p>
                                    <div class="d-flex gap-2">
                                        <a href="../outcomes/submit_outcomes.php" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-upload me-1"></i> Submit Outcomes
                                        </a>
                                        <a href="../outcomes/create_outcome_flexible.php" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-plus-circle me-1"></i> Create New
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-3 border rounded h-100 bg-light">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-info me-2" style="min-width: 90px;">Activity</span>
                                        <span class="fw-bold">Recent Outcomes Activity</span>
                                    </div>
                                    <?php if (empty($outcomes_stats['recent_outcomes'])): ?>
                                        <div class="alert alert-light">
                                            <i class="fas fa-info-circle me-2"></i>No recent outcomes activity found.
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach (array_slice($outcomes_stats['recent_outcomes'], 0, 3) as $outcome): ?>
                                                <div class="list-group-item px-0 py-2 border-0">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($outcome['table_name']); ?></h6>
                                                            <small class="text-muted"><?php echo date('M j, Y', strtotime($outcome['updated_at'])); ?></small>
                                                        </div>
                                                        <span class="badge bg-<?php echo $outcome['is_draft'] ? 'warning' : 'success'; ?>">
                                                            <?php echo $outcome['is_draft'] ? 'Draft' : 'Submitted'; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="text-center mt-2">
                                            <a href="../outcomes/submit_outcomes.php" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye me-1"></i> View All Outcomes <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pass chart data to JavaScript -->
<script>
    // Initialize chart with data
    const programRatingChartData = {
        labels: <?php echo json_encode($chartData['labels']); ?>,
        data: <?php echo json_encode($chartData['data']); ?>
    };
    
    // Simple, direct chart initialization
    let programRatingChart = null;
    
    function createSimpleChart() {
        const canvas = document.getElementById('programRatingChart');
        if (!canvas) {
            console.error('Chart canvas not found');
            return;
        }
        
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
            return;
        }
        
        // Destroy existing chart if it exists
        if (programRatingChart) {
            programRatingChart.destroy();
        }
        
        try {
            programRatingChart = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: programRatingChartData.labels,
                    datasets: [{
                        data: programRatingChartData.data,
                        backgroundColor: ['#ffc107', '#dc3545', '#28a745', '#6c757d'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
            
            // Make chart globally accessible for updates
            window.programRatingChart = programRatingChart;
            window.dashboardChart = {
                update: function(newData) {
                    if (programRatingChart && newData) {
                        programRatingChart.data.datasets[0].data = newData.data;
                        programRatingChart.update();
                    }
                }
            };
            
        } catch (error) {
            console.error('Error creating chart:', error);
        }
    }
    
    // Initialize when DOM is ready and Chart.js is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Check if Chart.js is already loaded
        if (typeof Chart !== 'undefined') {
            createSimpleChart();
        } else {
            // Wait for Chart.js to load
            let checkCount = 0;
            const checkInterval = setInterval(function() {
                checkCount++;
                if (typeof Chart !== 'undefined') {
                    createSimpleChart();
                    clearInterval(checkInterval);
                } else if (checkCount > 50) { // 5 seconds max wait
                    console.error('Chart.js failed to load after 5 seconds');
                    clearInterval(checkInterval);
                }
            }, 100);
        }
    });
</script>

<?php
// Include footer
require_once PROJECT_ROOT_PATH . 'app/views/layouts/footer.php';
?>

