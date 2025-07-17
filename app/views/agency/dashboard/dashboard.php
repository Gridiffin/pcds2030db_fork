<?php
/**
 * Agency Dashboard
 * 
 * Modern Bento Grid layout for agency users showing program stats and submission rating.
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

// Remove all period_id handling from user input or URL
// $period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);
// $viewing_period = $period_id ? get_reporting_period($period_id) : $current_period;
// Use only current period
$viewing_period = $current_period;
$period_id = $current_period['period_id'] ?? null;

// Initialize dashboard controller for initial rendering
$dashboardController = new DashboardController($conn);
$dashboardData = $dashboardController->getDashboardData(
    $_SESSION['agency_id'] ?? null, 
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
    asset_url('js/agency', 'dashboard.js'),
    asset_url('js/agency', 'dashboard_chart.js'),
    asset_url('js/agency', 'dashboard_charts.js'),
    asset_url('js/agency', 'bento-dashboard.js')
];

// Include header
require_once PROJECT_ROOT_PATH . 'app/views/layouts/header.php';

// Configure modern page header (remove view toggle, only refresh)
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

<!-- Initiatives Section Wrapper -->
<section class="initiatives-section mb-4">
    <div class="section-header d-flex align-items-center mb-2">
        <i class="fas fa-lightbulb me-2 text-primary"></i>
        <h2 class="h4 fw-bold mb-0">Initiatives</h2>
    </div>
    <div class="initiatives-description mb-3 text-muted">
        Explore your agency's strategic initiatives and their progress. Click an initiative for details.
    </div>
    <!-- Initiative Carousel Card (after filter) -->
    <div class="bento-card carousel-card" id="programCarouselCard">
        <div class="carousel-inner" id="initiativeCarouselInner">
            <?php
            // Fetch initiatives for the agency (from DB, not placeholder)
            require_once PROJECT_ROOT_PATH . 'app/lib/agencies/initiatives.php';
            $initiatives = get_agency_initiatives($_SESSION['agency_id']);
            if (!$initiatives || count($initiatives) === 0) {
                echo '<div class="carousel-item active text-center py-4">No initiatives found.</div>';
            } else {
                $i = 0;
                foreach ($initiatives as $initiative) {
                    $active = $i === 0 ? 'active' : '';
                    // Fetch detailed info for each initiative
                    $details = get_agency_initiative_details($initiative['initiative_id'], $_SESSION['agency_id']);
                    $name = $details['initiative_name'] ?? '';
                    $code = $details['initiative_number'] ?? '';
                    $desc = $details['initiative_description'] ?? '';
                    $is_active = $details['is_active'] ?? 0;
                    $start = $details['start_date'] ?? '';
                    $end = $details['end_date'] ?? '';
                    $program_count = $details['agency_program_count'] ?? 0;
                    $last_updated = $details['updated_at'] ?? '';
                    $health_score = 0;
                    $health_desc = 'No Data';
                    $health_color = '#6c757d';
                    // Calculate health score and description
                    if (isset($details['total_program_count']) && $details['total_program_count'] > 0) {
                        $score = 0;
                        $programs = get_initiative_programs_for_agency($details['initiative_id'], $_SESSION['agency_id']);
                        foreach ($programs as $p) {
                            $status = $p['status'] ?? 'active';
                            $normalized = [
                                'not-started' => 'active',
                                'not_started' => 'active',
                                'on-track' => 'active',
                                'on-track-yearly' => 'active',
                                'target-achieved' => 'completed',
                                'monthly_target_achieved' => 'completed',
                                'severe-delay' => 'delayed',
                                'severe_delay' => 'delayed',
                                'delayed' => 'delayed',
                                'completed' => 'completed',
                                'cancelled' => 'cancelled',
                                'on_hold' => 'on_hold',
                                'active' => 'active',
                            ];
                            $status = $normalized[$status] ?? $status;
                            switch ($status) {
                                case 'completed': $score += 100; break;
                                case 'active': $score += 75; break;
                                case 'on_hold': $score += 50; break;
                                case 'delayed': $score += 25; break;
                                case 'cancelled': $score += 10; break;
                                default: $score += 10; break;
                            }
                        }
                        $health_score = round($score / count($programs));
                        if ($health_score >= 80) {
                            $health_desc = 'Excellent – Programs performing well';
                            $health_color = '#28a745';
                        } elseif ($health_score >= 60) {
                            $health_desc = 'Good – Most programs are active';
                            $health_color = '#28a745';
                        } elseif ($health_score >= 40) {
                            $health_desc = 'Fair – Some programs on hold or delayed';
                            $health_color = '#ffc107';
                        } else {
                            $health_desc = 'Poor – Programs need improvement';
                            $health_color = '#dc3545';
                        }
                    }
                    // Timeline progress
                    $timeline_progress = 0;
                    $elapsed_years = $remaining_years = $total_years = 0;
                    if ($start && $end) {
                        $start_dt = new DateTime($start);
                        $end_dt = new DateTime($end);
                        $now = new DateTime();
                        $total_days = $start_dt->diff($end_dt)->days;
                        $elapsed_days = $start_dt->diff($now)->days;
                        $timeline_progress = $total_days > 0 ? min(100, max(0, round(($elapsed_days / $total_days) * 100))) : 0;
                        $total_years = round($total_days / 365, 1);
                        $elapsed_years = round($elapsed_days / 365, 1);
                        $remaining_years = max(0, round(($total_days - $elapsed_days) / 365, 1));
                    }
                    echo "<div class='carousel-item $active py-2 px-2' style='cursor:pointer;' onclick=\"window.location.href='../initiatives/view_initiative.php?id=" . urlencode($details['initiative_id']) . "'\">"
                        . "<div class='d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-2'>"
                        . "<div class='d-flex align-items-center gap-2'>"
                        . "<i class='fas fa-leaf fa-lg me-2'></i>"
                        . "<span class='h5 fw-bold mb-0'>" . htmlspecialchars($name) . "</span>"
                        . ($code ? "<span class='badge bg-primary ms-2'>#" . htmlspecialchars($code) . "</span>" : "")
                        . ($is_active ? "<span class='badge bg-success ms-2'>Active</span>" : "<span class='badge bg-secondary ms-2'>Inactive</span>")
                        . "</div>"
                        . "<div class='d-flex align-items-center gap-2'>"
                        . "<span class='small text-muted'>Programs: $program_count</span>"
                        . "</div>"
                        . "</div>"
                        . "<div class='mb-2 text-muted small' style='min-height:2em;'>" . htmlspecialchars(mb_strimwidth($desc, 0, 120, '...')) . "</div>"
                        . "<div class='d-flex flex-wrap align-items-center gap-3 mb-2'>"
                        . "<span><i class='fas fa-calendar-alt me-1'></i>" . ($start && $end ? date('M j, Y', strtotime($start)) . ' – ' . date('M j, Y', strtotime($end)) . " ($total_years years)" : 'Timeline not specified') . "</span>"
                        . "<span><i class='fas fa-hourglass-half me-1'></i>" . ($start && $end ? "$elapsed_years years elapsed, $remaining_years years remaining" : 'Timeline not available') . "</span>"
                        . "</div>"
                        . "<div class='mb-2'>"
                        . "<div class='progress' style='height: 8px; background: #e9ecef;'>"
                        . "<div class='progress-bar' role='progressbar' style='width: $timeline_progress%; background: #11998e;' aria-valuenow='$timeline_progress' aria-valuemin='0' aria-valuemax='100'></div>"
                        . "</div>"
                        . "<div class='small text-muted mt-1'>$timeline_progress% complete</div>"
                        . "</div>"
                        . "<div class='d-flex align-items-center gap-2 mb-2'>"
                        . "<div style='width:32px; height:32px; border-radius:50%; background:conic-gradient($health_color 0deg " . ($health_score * 3.6) . "deg, #e9ecef " . ($health_score * 3.6) . "deg 360deg); display:flex; align-items:center; justify-content:center;'>"
                        . "<span class='fw-bold' style='color:$health_color;'>$health_score</span>"
                        . "</div>"
                        . "<span class='small' style='color:$health_color;'>$health_desc</span>"
                        . "</div>"
                        . "<div class='d-flex align-items-center gap-2 mb-2'>"
                        . "<span class='small text-muted'><i class='fas fa-clock me-1'></i>Last Update: " . ($last_updated ? date('Y-m-d', strtotime($last_updated)) : 'N/A') . "</span>"
                        . "</div>"
                        // Add click for details text
                        . "<div class='text-center'><span class='small text-muted' style='font-size:0.92em; opacity:0.7;'>Click for details</span></div>"
                        . "</div>";
                    $i++;
                }
            }
            ?>
        </div>
        <button class="carousel-control-prev" type="button" id="carouselPrevBtn" aria-label="Previous">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" id="carouselNextBtn" aria-label="Next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
        <div class="carousel-indicators mt-2" id="carouselIndicators">
            <!-- JS will populate indicators -->
        </div>
    </div>
</section>

<!-- Programs Section Wrapper -->
<section class="programs-section mb-4">
    <div class="section-header d-flex align-items-center mb-2">
        <i class="fas fa-clipboard-list me-2 text-primary"></i>
        <h2 class="h4 fw-bold mb-0">Programs</h2>
    </div>
    <div class="programs-description mb-3 text-muted">
        View your agency's programs, their progress, and recent updates. Use the cards below to explore program status and activity.
    </div>
    <!-- Bento Grid Dashboard -->
    <section class="section">
        <div class="container-fluid">
            <!-- Bento Grid Layout -->
            <div class="bento-grid">
                <!-- Total Programs Card -->
                <div class="bento-card size-3x1 primary">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            Total Programs
                        </h3>
                    </div>
                    <div class="bento-card-content text-center">
                        <div class="display-4 fw-bold mb-2"><?php echo $stats['total']; ?></div>
                        <p class="mb-0 opacity-75">Active programs in your portfolio</p>
                    </div>
                </div>

                <!-- On Track Programs Card -->
                <div class="bento-card size-3x1 success">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            On Track
                        </h3>
                    </div>
                    <div class="bento-card-content text-center">
                        <div class="display-4 fw-bold mb-2"><?php echo $stats['on-track']; ?></div>
                        <p class="mb-0 opacity-75">
                            <?php echo $stats['total'] > 0 ? round(($stats['on-track'] / $stats['total']) * 100) : 0; ?>% of total
                        </p>
                    </div>
                </div>

                <!-- Delayed Programs Card -->
                <div class="bento-card size-3x1 warning">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            Delayed
                        </h3>
                    </div>
                    <div class="bento-card-content text-center">
                        <div class="display-4 fw-bold mb-2"><?php echo $stats['delayed']; ?></div>
                        <p class="mb-0 opacity-75">
                            <?php echo $stats['total'] > 0 ? round(($stats['delayed'] / $stats['total']) * 100) : 0; ?>% of total
                        </p>
                    </div>
                </div>

                <!-- Completed Programs Card -->
                <div class="bento-card size-3x1 info">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-trophy"></i>
                            </div>
                            Completed
                        </h3>
                    </div>
                    <div class="bento-card-content text-center">
                        <div class="display-4 fw-bold mb-2"><?php echo $stats['completed']; ?></div>
                        <p class="mb-0 opacity-75">
                            <?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>% of total
                        </p>
                    </div>
                </div>

                <!-- Program Rating Chart -->
                <div class="bento-card size-6x2">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: #667eea;">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            Program Rating Distribution
                        </h3>
                    </div>
                    <div class="bento-card-content">
                        <div class="chart-container" style="position: relative; height: 300px; width: 100%">
                            <canvas id="programRatingChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Program Updates -->
                <div class="bento-card size-6x2">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: #11998e;">
                                <i class="fas fa-clock"></i>
                            </div>
                            Recent Program Updates
                        </h3>
                        <span class="badge bg-primary" id="programCount"><?php echo count($recentUpdates); ?></span>
                    </div>
                    <div class="bento-card-content">
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
                                            <th class="sortable" data-sort="rating">
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
                                                </td>
                                                <td>
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
                            
                            <div class="bento-card-footer">
                                <a href="../programs/view_programs.php" class="btn btn-outline-primary">
                                    View All Programs <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="bento-card size-3x1">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: #4facfe;">
                                <i class="fas fa-bolt"></i>
                            </div>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="bento-card-content">
                        <div class="d-grid gap-2">
                            <a href="../programs/create_program.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create Program
                            </a>
                            <a href="../programs/add_submission.php" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-2"></i>Submit Data
                            </a>
                            <a href="../outcomes/submit_outcomes.php" class="btn btn-outline-success">
                                <i class="fas fa-upload me-2"></i>Submit Outcomes
                            </a>
                            <a href="../reports/view_reports.php" class="btn btn-outline-info">
                                <i class="fas fa-chart-bar me-2"></i>View Reports
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Outcomes Overview -->
                <div class="bento-card size-6x2">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: #f093fb;">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            Outcomes Overview
                        </h3>
                    </div>
                    <div class="bento-card-content">
                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="text-center p-3 bg-primary text-white rounded">
                                    <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                    <div class="h4 mb-0"><?php echo $outcomes_stats['total_outcomes']; ?></div>
                                    <small>Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 bg-success text-white rounded">
                                    <i class="fas fa-check-square fa-2x mb-2"></i>
                                    <div class="h4 mb-0"><?php echo isset($outcomes_stats['submitted_outcomes']) ? $outcomes_stats['submitted_outcomes'] : 0; ?></div>
                                    <small>Submitted</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 bg-warning text-white rounded">
                                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                                    <div class="h4 mb-0"><?php echo isset($outcomes_stats['draft_outcomes']) ? $outcomes_stats['draft_outcomes'] : 0; ?></div>
                                    <small>Drafts</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bento-card-footer">
                            <div class="bento-card-actions">
                                <a href="../outcomes/submit_outcomes.php" class="btn btn-success">
                                    <i class="fas fa-upload me-1"></i> Submit Outcomes
                                </a>
                                <a href="../outcomes/create_outcome_flexible.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Create New
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Outcomes Activity -->
                <div class="bento-card size-3x2">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: #2c3e50;">
                                <i class="fas fa-history"></i>
                            </div>
                            Recent Activity
                        </h3>
                    </div>
                    <div class="bento-card-content">
                        <?php if (empty($outcomes_stats['recent_outcomes'])): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No recent outcomes activity found.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($outcomes_stats['recent_outcomes'], 0, 5) as $outcome): ?>
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
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

