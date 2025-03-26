<?php
/**
 * Agency Dashboard AJAX Data Provider
 * 
 * Returns JSON with HTML content for different dashboard sections based on selected period.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/session.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/agency_functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Get the requested period ID
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;
$period = $period_id ? get_reporting_period($period_id) : get_current_reporting_period();

if (!$period) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'Period not found']);
    exit;
}

// Get data for the requested period
$submission_status = get_agency_submission_status($_SESSION['user_id'], $period_id);
$metrics = get_agency_sector_metrics($_SESSION['sector_id'], $period_id);
$other_sectors_programs = get_all_sectors_programs($period_id);

// Filter out current sector's programs
$other_sectors_programs = array_filter($other_sectors_programs, function($p) {
    return $p['sector_id'] != $_SESSION['sector_id'];
});

// Take only 5 latest programs
$other_sectors_programs = array_slice($other_sectors_programs, 0, 5);

// Start output buffer to capture HTML for various sections
ob_start();
?>

<!-- Submission Status HTML -->
<?php if ($submission_status): ?>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card submission-card">
                <div class="card-body text-center">
                    <div class="display-4 mb-2"><?php echo $submission_status['programs_submitted']; ?>/<?php echo $submission_status['total_programs']; ?></div>
                    <div class="progress mb-3" style="height: 10px;">
                        <?php $program_percent = $submission_status['total_programs'] > 0 ? ($submission_status['programs_submitted'] / $submission_status['total_programs']) * 100 : 0; ?>
                        <div class="progress-bar <?php echo $program_percent == 100 ? 'bg-success' : 'bg-primary'; ?>" role="progressbar" style="width: <?php echo $program_percent; ?>%" aria-valuenow="<?php echo $program_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <h6 class="mb-0">Programs Submitted</h6>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card submission-card">
                <div class="card-body text-center">
                    <div class="display-4 mb-2"><?php echo $submission_status['metrics_submitted']; ?>/<?php echo $submission_status['total_metrics']; ?></div>
                    <div class="progress mb-3" style="height: 10px;">
                        <?php $metrics_percent = $submission_status['total_metrics'] > 0 ? ($submission_status['metrics_submitted'] / $submission_status['total_metrics']) * 100 : 0; ?>
                        <div class="progress-bar <?php echo $metrics_percent == 100 ? 'bg-success' : 'bg-primary'; ?>" role="progressbar" style="width: <?php echo $metrics_percent; ?>%" aria-valuenow="<?php echo $metrics_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <h6 class="mb-0">Metrics Submitted</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center pb-3">
        <?php if ($program_percent == 100 && $metrics_percent == 100): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>All submissions complete for Q<?php echo $period['quarter']; ?>-<?php echo $period['year']; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>Please complete all submissions before <?php echo date('F j, Y', strtotime($period['end_date'])); ?>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="text-center py-4">
        <div class="mb-3">
            <i class="fas fa-calendar-alt fa-3x text-muted"></i>
        </div>
        <h5>No submission data</h5>
        <p class="text-muted">No data available for this reporting period.</p>
    </div>
<?php endif; ?>

<?php
$submission_html = ob_get_clean();
ob_start();
?>

<!-- Cross-sector Programs HTML -->
<?php if (empty($other_sectors_programs)): ?>
    <p class="text-muted mb-0">No programs from other sectors available to display for this period.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-sm">
            <thead>
                <tr>
                    <th>Program</th>
                    <th>Sector</th>
                    <th>Agency</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($other_sectors_programs as $program): ?>
                    <tr>
                        <td>
                            <strong><?php echo $program['program_name']; ?></strong>
                        </td>
                        <td><span class="badge bg-secondary"><?php echo $program['sector_name']; ?></span></td>
                        <td><?php echo $program['agency_name']; ?></td>
                        <td>
                            <?php if (isset($program['status']) && $program['status']): ?>
                                <?php
                                    $status_class = 'secondary';
                                    switch ($program['status']) {
                                        case 'on-track': $status_class = 'success'; break;
                                        case 'delayed': $status_class = 'warning'; break;
                                        case 'completed': $status_class = 'primary'; break;
                                        case 'not-started': $status_class = 'secondary'; break;
                                    }
                                ?>
                                <span class="badge bg-<?php echo $status_class; ?>">
                                    <?php echo ucfirst(str_replace('-', ' ', $program['status'])); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark">Not Reported</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php
$other_sectors_html = ob_get_clean();
ob_start();
?>

<!-- Metrics HTML -->
<?php if (!empty($metrics)): ?>
    <ul class="list-group list-group-flush">
        <?php 
        $count = 0;
        foreach ($metrics as $metric): 
            if ($count++ >= 5) break; // Show only 5 metrics
        ?>
            <li class="list-group-item d-flex align-items-center p-2">
                <span class="metric-icon me-2">
                    <i class="fas fa-chart-line"></i>
                </span>
                <span class="flex-grow-1"><?php echo $metric['metric_name']; ?></span>
                <?php if(isset($metric['is_submitted']) && $metric['is_submitted']): ?>
                    <span class="badge bg-success">Submitted</span>
                <?php else: ?>
                    <span class="badge bg-warning">Pending</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if (count($metrics) > 5): ?>
        <div class="text-center mt-3">
            <a href="submit_metrics.php?period_id=<?php echo $period_id; ?>" class="btn btn-outline-primary btn-sm w-100">View All Metrics</a>
        </div>
    <?php endif; ?>
<?php else: ?>
    <p class="text-muted">No metrics defined for your sector for this period.</p>
<?php endif; ?>

<?php
$metrics_html = ob_get_clean();

// Also prepare chart data for JS
$chart_data = [
    'labels' => ['On Track', 'Delayed', 'Completed', 'Not Started'],
    'data' => [
        $submission_status['program_status']['on-track'] ?? 0,
        $submission_status['program_status']['delayed'] ?? 0,
        $submission_status['program_status']['completed'] ?? 0,
        $submission_status['program_status']['not-started'] ?? 0
    ],
    'colors' => ['#28a745', '#ffc107', '#17a2b8', '#6c757d']
];

// Return all HTML and data as JSON
echo json_encode([
    'submission_section' => $submission_html,
    'other_sectors_section' => $other_sectors_html,
    'metrics_section' => $metrics_html,
    'chart_data' => $chart_data,
    'period_info' => [
        'quarter' => $period['quarter'],
        'year' => $period['year'],
        'status' => $period['status'],
        'end_date' => date('F j, Y', strtotime($period['end_date']))
    ]
]);
?>
