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

// Get agency programs
$programs_by_type = get_agency_programs_by_type($period_id);
$programs = array_merge($programs_by_type['assigned'], $programs_by_type['created']);

// Get agency submission status - This already uses period_id
$submission_status = get_agency_submission_status($_SESSION['user_id'], $period_id);

// Get agency sector name
$agency_sector = get_sector_name($_SESSION['sector_id']);

// Prepare program status data for chart - MODIFIED to be period-specific
$program_status_data = [
    'on-track' => $submission_status['program_status']['on-track'] ?? 0,
    'delayed' => $submission_status['program_status']['delayed'] ?? 0,
    'completed' => $submission_status['program_status']['completed'] ?? 0,
    'not-started' => $submission_status['program_status']['not-started'] ?? 0
];

// Calculate total for verification
$total_in_chart = array_sum($program_status_data);

// Filter programs to only include those with submissions for the selected period
$period_specific_programs = [];
if ($period_id) {
    // Function to check if a program has a submission for the current period
    foreach ($programs as $program) {
        // Check if there is submission data for this program in this period
        $has_submission_for_period = false;
        
        // Get submission data for this program in this period
        $submission_query = "SELECT submission_id FROM program_submissions 
                           WHERE program_id = ? AND period_id = ? LIMIT 1";
        $stmt = $conn->prepare($submission_query);
        $stmt->bind_param('ii', $program['program_id'], $period_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $has_submission_for_period = true;
            $period_specific_programs[] = $program;
        }
    }
} else {
    // If no period is specified, use all programs (fallback)
    $period_specific_programs = $programs;
}

// Check if we have any data for this period
$has_period_data = !empty($period_specific_programs);

// Calculate period-specific program counts for cards
$period_program_count = count($period_specific_programs);
$period_submitted_count = 0;

// Count submitted (non-draft) programs for this period
if ($has_period_data) {
    $period_submitted_count = count(array_filter($period_specific_programs, function($p) {
        return !isset($p['is_draft']) || $p['is_draft'] == 0;
    }));
}

// If submission_status data isn't providing accurate counts, count directly from filtered programs
if ($total_in_chart === 0 && !empty($period_specific_programs)) {
    // Reset the counts
    $direct_status_counts = [
        'on-track' => 0,
        'delayed' => 0,
        'completed' => 0,
        'not-started' => 0
    ];
    
    // Get non-draft programs first
    $non_draft_programs = array_filter($period_specific_programs, function($program) {
        return !isset($program['is_draft']) || $program['is_draft'] == 0;
    });
    
    foreach ($non_draft_programs as $program) {
        // Extract and normalize the status
        $status = isset($program['status']) ? strtolower(trim($program['status'])) : 'not-started';
        
        // Map status to standard categories
        switch($status) {
            case 'on-track':
            case 'on-track-yearly':
                $direct_status_counts['on-track']++;
                break;
            case 'delayed':
            case 'severe-delay':
                $direct_status_counts['delayed']++;
                break;
            case 'completed':
            case 'target-achieved':
                $direct_status_counts['completed']++;
                break;
            default:
                $direct_status_counts['not-started']++;
                break;
        }
    }
    
    // If we found at least one status, use the direct counts instead
    if (array_sum($direct_status_counts) > 0) {
        $program_status_data = $direct_status_counts;
        error_log("Override program status data with period-specific counts: " . json_encode($direct_status_counts));
    }
}

// Set all values to zero if no data for this period
if (!$has_period_data) {
    $program_status_data = [
        'on-track' => 0,
        'delayed' => 0,
        'completed' => 0,
        'not-started' => 0
    ];
}

// Query to fetch notifications
$notifications_query = "SELECT n.* FROM notifications n 
                       WHERE n.user_id = ? AND n.read_status = 0 
                       ORDER BY n.created_at DESC LIMIT 5";
$stmt = $conn->prepare($notifications_query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Count unread notifications
$unread_count = count($notifications);

// Additional scripts
$additionalScripts = [
    'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js',
    APP_URL . '/assets/js/utilities/status_utils.js',
    APP_URL . '/assets/js/agency/dashboard.js',
    APP_URL . '/assets/js/agency/dashboard_chart.js',
    APP_URL . '/assets/js/period_selector.js'
];

// Include custom CSS for dashboard components
if (!isset($additionalStyles)) {
    $additionalStyles = '';
}

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

<!-- Debug output for chart data -->
<div style="display: none;" id="chartDebugData">
    <pre>
    <?php 
        echo "Program Status Data from submission_status:\n";
        echo "Total programs: " . ($submission_status['total_programs'] ?? '0') . "\n";
        var_dump($submission_status['program_status'] ?? []); 
        
        echo "\n\nProgram Status Data after processing:\n";
        var_dump($program_status_data);
        
        echo "\n\nTotal Programs in array: " . count($programs) . "\n";
        
        // Show first 3 programs with their status for debugging
        echo "Sample Programs:\n";
        $counter = 0;
        foreach ($programs as $program) {
            if ($counter++ < 3) {
                echo "Program: " . $program['program_name'] . ", Status: " . ($program['status'] ?? 'none') . "\n";
            }
        }
    ?>
    </pre>
</div>

<!-- Dashboard Content -->
<section class="section">
    <div class="container-fluid">
        <!-- Period Selector Component -->
        <?php require_once '../../includes/period_selector.php'; ?>

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
                            <div class="stat-value">
                                <?php 
                                // Use period-specific program count
                                echo $has_period_data ? $period_program_count : 0;
                                ?>
                            </div>
                            <div class="stat-subtitle">
                                <i class="fas fa-check me-1"></i>
                                <?php echo $has_period_data ? $period_submitted_count : 0; ?> Programs Submitted
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
                            <div class="stat-value"><?php echo $program_status_data['on-track']; ?></div>
                            <?php if ($has_period_data && $period_program_count > 0): ?>
                            <div class="stat-subtitle">
                                <i class="fas fa-chart-line me-1"></i>
                                <?php echo round(($program_status_data['on-track'] / $period_program_count) * 100); ?>% of total
                            </div>
                            <?php else: ?>
                            <div class="stat-subtitle text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                No data for this period
                            </div>
                            <?php endif; ?>
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
                            <div class="stat-value"><?php echo $program_status_data['delayed']; ?></div>
                            <?php if ($has_period_data && $period_program_count > 0): ?>
                            <div class="stat-subtitle">
                                <i class="fas fa-chart-line me-1"></i>
                                <?php echo round(($program_status_data['delayed'] / $period_program_count) * 100); ?>% of total
                            </div>
                            <?php else: ?>
                            <div class="stat-subtitle text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                No data for this period
                            </div>
                            <?php endif; ?>
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
                            <div class="stat-title">Monthly Target Achieved</div>
                            <div class="stat-value"><?php echo $program_status_data['completed']; ?></div>
                            <?php if ($has_period_data && $period_program_count > 0): ?>
                            <div class="stat-subtitle">
                                <i class="fas fa-chart-line me-1"></i>
                                <?php echo round(($program_status_data['completed'] / $period_program_count) * 100); ?>% of total
                            </div>
                            <?php else: ?>
                            <div class="stat-subtitle text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                No data for this period
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="row">
            <!-- Program Status Chart -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-white">Program Status Distribution</h6>
                        <div class="chart-toggle-wrapper bg-white px-2 py-1 rounded-pill">
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input custom-toggle" type="checkbox" id="includeAssignedToggle" checked>
                                <label class="form-check-label text-primary ms-2 small" for="includeAssignedToggle">
                                    Include Assigned
                                </label>
                            </div>
                        </div>
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
                                <i class="fas fa-circle text-success"></i> Monthly Target Achieved
                            </span>
                            <span class="chart-legend-item">
                                <i class="fas fa-circle text-gray"></i> Not Started
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Program Updates with Simple Filter -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-white">Program Updates</h6>
                        <div>
                            <select class="form-select form-select-sm" id="dashboardProgramTypeFilter">
                                <option value="all">All Programs</option>
                                <option value="assigned">Assigned</option>
                                <option value="created">Agency-Created</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($programs)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p>No programs found for your agency.</p>
                                <a href="<?php echo APP_URL; ?>/views/agency/create_program.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus-circle me-1"></i> Create New Program
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
                                        <?php 
                                        // Take the most recent 5 programs
                                        $recent_programs = array_slice($programs, 0, 5);
                                        
                                        if (empty($recent_programs)):
                                        ?>
                                            <tr>
                                                <td colspan="3" class="text-center py-4">
                                                    <i class="fas fa-info-circle text-info me-2"></i>
                                                    No program updates for the selected reporting period.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recent_programs as $program): 
                                                $program_type = isset($program['is_assigned']) && $program['is_assigned'] ? 'assigned' : 'created';
                                                $program_type_label = $program_type === 'assigned' ? 'Assigned' : 'Agency-Created';
                                                $is_draft = isset($program['is_draft']) && $program['is_draft'];
                                            ?>
                                                <tr class="<?php echo $is_draft ? 'draft-program' : ''; ?>" 
                                                    data-program-type="<?php echo $program_type; ?>">
                                                    <td>
                                                        <div class="fw-medium">
                                                            <?php echo htmlspecialchars($program['program_name']); ?>
                                                            <?php if ($is_draft): ?>
                                                                <span class="draft-indicator" title="Draft"></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="small text-muted program-type-indicator">
                                                            <i class="fas fa-<?php echo $program_type === 'assigned' ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                                            <?php echo $program_type_label; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        // Ensure we're using the new status values by converting any legacy status
                                                        $current_status = isset($program['status']) ? (function_exists('convert_legacy_status') ? convert_legacy_status($program['status']) : $program['status']) : 'not-started';
                                                        
                                                        // Map database status values to display labels and classes
                                                        $status_map = [
                                                            'on-track' => ['label' => 'On Track', 'class' => 'warning'],
                                                            'on-track-yearly' => ['label' => 'On Track for Year', 'class' => 'warning'],
                                                            'target-achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success'],
                                                            'completed' => ['label' => 'Monthly Target Achieved', 'class' => 'success'],
                                                            'delayed' => ['label' => 'Delayed', 'class' => 'danger'],
                                                            'severe-delay' => ['label' => 'Severe Delays', 'class' => 'danger'],
                                                            'not-started' => ['label' => 'Not Started', 'class' => 'secondary']
                                                        ];
                                                        
                                                        // Set default if status is not in our map
                                                        if (!isset($status_map[$current_status])) {
                                                            $current_status = 'not-started';
                                                        }
                                                        
                                                        // Get the label and class from our map
                                                        $status_label = $status_map[$current_status]['label'];
                                                        $status_class = $status_map[$current_status]['class'];
                                                        ?>
                                                        <span class="badge bg-<?php echo $status_class; ?>">
                                                            <?php echo $status_label; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        if (isset($program['updated_at']) && $program['updated_at']) {
                                                            echo date('M j, Y', strtotime($program['updated_at']));
                                                        } elseif (isset($program['created_at']) && $program['created_at']) {
                                                            echo date('M j, Y', strtotime($program['created_at']));
                                                        } else {
                                                            echo 'Not set';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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

<!-- Sector-Specific Metrics Section (Placeholder) -->
<section class="section mt-4">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-white"><?php echo htmlspecialchars($agency_sector); ?> Sector Metrics</h6>
                        <a href="submit_metrics.php" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-edit me-1"></i> Update Metrics
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Placeholder for future sector-specific metrics -->
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p>Sector-specific metrics will be available soon.</p>
                            <p class="text-muted small mb-0">This section will display performance metrics specific to your agency's sector.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pass data to chart -->
<script>
    // Add debug console logging
    console.log("Passing data to chart:", <?php echo json_encode($program_status_data); ?>);
    
    // Initialize with current data
    const programStatusChartData = {
        data: [
            <?php echo $program_status_data['on-track']; ?>,
            <?php echo $program_status_data['delayed']; ?>,
            <?php echo $program_status_data['completed']; ?>,
            <?php echo $program_status_data['not-started']; ?>
        ],
        hasPeriodData: <?php echo $has_period_data ? 'true' : 'false'; ?> // Flag to indicate if period has data
    };
    
    // Debug output to verify data structure
    console.log("Chart data being sent:", programStatusChartData);
    
    // Initialize the chart on page load using the new method
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initializeDashboardChart === 'function') {
            console.log("Initializing chart with data:", programStatusChartData);
            initializeDashboardChart(programStatusChartData);
        } else {
            console.error("initializeDashboardChart function not found!");
        }
    });
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
