<?php
/**
 * Admin Dashboard Data AJAX Endpoint
 * 
 * Provides dashboard data based on selected period_id
 */

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/admin_functions.php';

// Verify user is admin
if (!is_admin()) {
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

// Get data for the dashboard
$submission_stats = get_period_submission_stats($period_id);
$sector_data = get_sector_data_for_period($period_id);
$recent_submissions = get_recent_submissions($period_id, 5);

// Start output buffering to capture HTML
ob_start();
?>

<div class="row">
    <!-- Agencies Reporting Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="stat-card-header">
                    <div class="icon-container">
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                </div>
                <div class="stat-card-content">
                    <div class="stat-title">Agencies Reporting</div>
                    <div class="stat-value">
                        <?php echo $submission_stats['agencies_reported'] ?? 0; ?>/<?php echo $submission_stats['total_agencies'] ?? 0; ?>
                    </div>
                    <div class="stat-subtitle">
                        <i class="fas fa-check me-1"></i>
                        <?php echo $submission_stats['agencies_reported'] ?? 0; ?> Agencies Reported
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Programs On Track Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="stat-card-header">
                    <div class="icon-container">
                        <i class="fas fa-check-circle stat-icon"></i>
                    </div>
                </div>
                <div class="stat-card-content">
                    <div class="stat-title">Programs On Track</div>
                    <div class="stat-value">
                        <?php echo $submission_stats['on_track_programs'] ?? 0; ?>
                    </div>
                    <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
                    <div class="stat-subtitle">
                        <i class="fas fa-chart-line me-1"></i>
                        <?php echo round(($submission_stats['on_track_programs'] / $submission_stats['total_programs']) * 100); ?>% of total
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Programs Delayed Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="stat-card-header">
                    <div class="icon-container">
                        <i class="fas fa-exclamation-triangle stat-icon"></i>
                    </div>
                </div>
                <div class="stat-card-content">
                    <div class="stat-title">Programs Delayed</div>
                    <div class="stat-value">
                        <?php echo $submission_stats['delayed_programs'] ?? 0; ?>
                    </div>
                    <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
                    <div class="stat-subtitle">
                        <i class="fas fa-chart-line me-1"></i>
                        <?php echo round(($submission_stats['delayed_programs'] / $submission_stats['total_programs']) * 100); ?>% of total
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Completion Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="stat-card-header">
                    <div class="icon-container">
                        <i class="fas fa-clipboard-list stat-icon"></i>
                    </div>
                </div>
                <div class="stat-card-content">
                    <div class="stat-title">Overall Completion</div>
                    <div class="stat-value">
                        <?php echo $submission_stats['completion_percentage'] ?? 0; ?>%
                    </div>
                    <div class="stat-subtitle progress mt-2" style="height: 10px;">
                        <div class="progress-bar bg-info" role="progressbar" 
                             style="width: <?php echo $submission_stats['completion_percentage'] ?? 0; ?>%"
                             aria-valuenow="<?php echo $submission_stats['completion_percentage'] ?? 0; ?>" 
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$stats_section = ob_get_clean();

// Generate period info HTML and other sections as needed
// ...existing code for generating other sections...

// Prepare JSON response
$response = [
    'stats_section' => $stats_section,
    'period_info' => $period_info ?? '',
    'sectors_section' => $sectors_section ?? '',
    'submissions_section' => $submissions_section ?? ''
];

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
