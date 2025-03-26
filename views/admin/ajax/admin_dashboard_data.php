<?php
/**
 * Admin Dashboard AJAX Data Provider
 * 
 * Returns JSON with HTML content for different dashboard sections based on selected period.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once '../../../includes/db_connect.php';
require_once '../../../includes/session.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/admin_functions.php';

// Verify user is an admin
if (!is_admin()) {
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
$submission_stats = get_period_submission_stats($period['period_id']);
$sector_data = get_sector_data_for_period($period['period_id']);
$recent_submissions = get_recent_submissions($period['period_id'], 5);

// Start output buffer to capture HTML
ob_start();
?>

<!-- Stats Section HTML -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Agencies Reporting</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $submission_stats['agencies_reported']; ?>/<?php echo $submission_stats['total_agencies']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Programs On Track</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $submission_stats['on_track_programs']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Programs Delayed</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $submission_stats['delayed_programs']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Overall Completion</div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                    <?php echo $submission_stats['completion_percentage']; ?>%
                                </div>
                            </div>
                            <div class="col">
                                <div class="progress progress-sm mr-2">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: <?php echo $submission_stats['completion_percentage']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$stats_html = ob_get_clean();
ob_start();
?>

<!-- Sector Data Section HTML -->
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="bg-light">
            <tr>
                <th>Sector</th>
                <th>Agencies</th>
                <th>Programs</th>
                <th>Submissions</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sector_data as $sector): ?>
                <tr>
                    <td><?php echo $sector['sector_name']; ?></td>
                    <td><?php echo $sector['agency_count']; ?></td>
                    <td><?php echo $sector['program_count']; ?></td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-<?php echo $sector['submission_pct'] >= 100 ? 'success' : 'primary'; ?>" 
                                 style="width: <?php echo $sector['submission_pct']; ?>%">
                                <?php echo $sector['submission_pct']; ?>%
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($sector['submission_pct'] >= 100): ?>
                            <span class="badge bg-success">Complete</span>
                        <?php elseif ($sector['submission_pct'] >= 75): ?>
                            <span class="badge bg-info">Almost Complete</span>
                        <?php elseif ($sector['submission_pct'] >= 25): ?>
                            <span class="badge bg-warning">In Progress</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Just Started</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$sectors_html = ob_get_clean();
ob_start();
?>

<!-- Recent Submissions Section HTML -->
<div class="table-responsive">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Agency</th>
                <th>Program</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recent_submissions)): ?>
                <tr>
                    <td colspan="4" class="text-center py-3">No recent submissions for this period</td>
                </tr>
            <?php else: ?>
                <?php foreach ($recent_submissions as $submission): ?>
                    <tr>
                        <td><?php echo $submission['agency_name']; ?></td>
                        <td><?php echo $submission['program_name']; ?></td>
                        <td>
                            <?php 
                                $status_class = 'secondary';
                                switch ($submission['status']) {
                                    case 'on-track': $status_class = 'success'; break;
                                    case 'delayed': $status_class = 'warning'; break;
                                    case 'completed': $status_class = 'primary'; break;
                                    case 'not-started': $status_class = 'secondary'; break;
                                }
                            ?>
                            <span class="badge bg-<?php echo $status_class; ?>">
                                <?php echo ucfirst(str_replace('-', ' ', $submission['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, g:i a', strtotime($submission['submission_date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$submissions_html = ob_get_clean();

// Return the HTML as JSON
echo json_encode([
    'stats_section' => $stats_html,
    'sectors_section' => $sectors_html,
    'submissions_section' => $submissions_html
]);
?>
