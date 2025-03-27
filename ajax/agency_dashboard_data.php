<?php
/**
 * Agency Dashboard Data AJAX Endpoint
 * 
 * Provides dashboard data based on selected period_id
 */

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/agency_functions.php';
require_once '../includes/status_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if this is an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Get period id from request
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;

// Get current reporting period for comparison
$current_period = get_current_reporting_period();
$viewing_period = $period_id ? get_reporting_period($period_id) : $current_period;

// Get agency data for the selected period
$programs_by_type = get_agency_programs_by_type($period_id);
$programs = array_merge($programs_by_type['assigned'], $programs_by_type['created']);
$submission_status = get_agency_submission_status($_SESSION['user_id'], $period_id);
$agency_sector = get_sector_name($_SESSION['sector_id']);

// Program status data for chart
$program_status_data = [
    'on-track' => $submission_status['program_status']['on-track'] ?? 0,
    'delayed' => $submission_status['program_status']['delayed'] ?? 0,
    'completed' => $submission_status['program_status']['completed'] ?? 0,
    'not-started' => $submission_status['program_status']['not-started'] ?? 0
];

// Start output buffering to capture HTML
ob_start();
?>

<div class="row">
    <!-- Programs Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card primary">
            <div class="card-body">
                <i class="fas fa-clipboard-list stat-icon"></i>
                <div class="stat-title">Total Programs</div>
                <div class="stat-value"><?php echo $submission_status['total_programs'] ?? 0; ?></div>
                <div class="stat-subtitle">
                    <i class="fas fa-check me-1"></i>
                    <?php echo $submission_status['programs_submitted'] ?? 0; ?> Programs Submitted
                </div>
            </div>
        </div>
    </div>

    <!-- On Track Programs Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success">
            <div class="card-body">
                <i class="fas fa-check-circle stat-icon"></i>
                <div class="stat-title">On Track Programs</div>
                <div class="stat-value"><?php echo $program_status_data['on-track']; ?></div>
                <?php if (isset($submission_status['total_programs']) && $submission_status['total_programs'] > 0): ?>
                <div class="stat-subtitle">
                    <i class="fas fa-chart-line me-1"></i>
                    <?php echo round(($program_status_data['on-track'] / $submission_status['total_programs']) * 100); ?>% of total
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delayed Programs Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card warning">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle stat-icon"></i>
                <div class="stat-title">Delayed Programs</div>
                <div class="stat-value"><?php echo $program_status_data['delayed']; ?></div>
                <?php if (isset($submission_status['total_programs']) && $submission_status['total_programs'] > 0): ?>
                <div class="stat-subtitle">
                    <i class="fas fa-chart-line me-1"></i>
                    <?php echo round(($program_status_data['delayed'] / $submission_status['total_programs']) * 100); ?>% of total
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Completed Programs Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card info">
            <div class="card-body">
                <i class="fas fa-trophy stat-icon"></i>
                <div class="stat-title">Completed Programs</div>
                <div class="stat-value"><?php echo $program_status_data['completed']; ?></div>
                <?php if (isset($submission_status['total_programs']) && $submission_status['total_programs'] > 0): ?>
                <div class="stat-subtitle">
                    <i class="fas fa-chart-line me-1"></i>
                    <?php echo round(($program_status_data['completed'] / $submission_status['total_programs']) * 100); ?>% of total
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$stats_section = ob_get_clean();

// Generate period info HTML
ob_start();
?>
<h5 class="mb-0 d-flex align-items-center">
    <?php if ($viewing_period): ?>
        Q<?php echo $viewing_period['quarter']; ?>-<?php echo $viewing_period['year']; ?> 
        <span class="badge ms-2 <?php echo ($viewing_period['status'] === 'open') ? 'bg-success' : 'bg-secondary'; ?>">
            <?php echo ($viewing_period['status'] === 'open') ? 'Active Period' : 'Closed'; ?>
        </span>
    <?php else: ?>
        Select Reporting Period
    <?php endif; ?>
</h5>
<?php if ($viewing_period): ?>
    <p class="text-muted mb-0 small">
        <?php echo date('M j, Y', strtotime($viewing_period['start_date'])); ?> - 
        <?php echo date('M j, Y', strtotime($viewing_period['end_date'])); ?>
    </p>
<?php endif; ?>

<?php
$period_info = ob_get_clean();

// Create a chart data script
$chart_data = [
    'data' => [
        $program_status_data['on-track'],
        $program_status_data['delayed'],
        $program_status_data['completed'],
        $program_status_data['not-started']
    ],
    'colors' => ['#28a745', '#ffc107', '#17a2b8', '#6c757d']
];

// Prepare JSON response
$response = [
    'stats_section' => $stats_section,
    'period_info' => $period_info,
    'chart_data' => $chart_data
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
