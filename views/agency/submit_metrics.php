<?php
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';

if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'Submit Sector Metrics';

$current_period = get_current_reporting_period();
$show_form = $current_period && $current_period['status'] === 'open';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_metrics'])) {
    $result = submit_metric_values($_POST);

    if (isset($result['success'])) {
        $message = $result['message'] ?? 'Metrics submitted successfully.';
        $message_type = 'success';
    } elseif (isset($result['partial'])) {
        $message = $result['message'] ?? 'Some metrics were submitted, but others failed.';
        $message_type = 'warning';
    } else {
        $message = $result['error'] ?? 'Failed to submit metrics.';
        $message_type = 'danger';
    }
}

$metrics = get_agency_sector_metrics($_SESSION['sector_id']);
if (!is_array($metrics)) {
    $metrics = [];
}

$draft_metrics = get_draft_metric($_SESSION['sector_id']);
if (!is_array($draft_metrics)) {
    $draft_metrics = [];
}

$additionalScripts = [
    APP_URL . '/assets/js/agency/metric_submission.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Set up the page header variables for dashboard_header.php
$title = "Submit Sector Metrics";
$subtitle = "Update your sector-specific metrics for this reporting period";
$headerStyle = 'light'; // Use light (white) style for inner pages

// Get next metric ID for the Create New Metric button
$next_metric_id = 0;
$result = $conn->query("SELECT MAX(metric_id) AS max_id FROM (SELECT metric_id FROM sector_metrics_submitted UNION ALL SELECT metric_id FROM sector_metrics_draft) AS combined");
if ($result && $row = $result->fetch_assoc()) {
    $next_metric_id = $row['max_id'] + 1;
}

// Set up period badge for actions array if period exists
$actions = [];

// Add period badges first
if ($current_period) {
    $actions[] = [
        'html' => '<span class="badge bg-success"><i class="fas fa-calendar-alt me-1"></i> Q' . $current_period['quarter'] . '-' . $current_period['year'] . '</span>'
    ];
    $actions[] = [
        'html' => '<span class="badge bg-success ms-2"><i class="fas fa-clock me-1"></i> Ends: ' . date('M j, Y', strtotime($current_period['end_date'])) . '</span>'
    ];
} else {
    $actions[] = [
        'html' => '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle me-1"></i> No Active Reporting Period</span>'
    ];
}

// Add Create New Metric button after the period badges
$actions[] = [
    'url' => "create_metric.php?sector_id={$_SESSION['sector_id']}&next_metric_id={$next_metric_id}",
    'text' => 'Create New Metric',
    'icon' => 'fa-plus-circle',
    'class' => 'btn-primary ms-3' // Added margin-start for spacing from badges
];

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';
?>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : ($message_type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle') ?> me-2"></i>
            <div><?= $message ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<?php if (!$show_form): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= $error_message ?? 'No active reporting period is currently open.' ?> Please try again when a reporting period is active.
    </div>
<?php else: ?>
    <!-- Period Information -->
    <div class="alert alert-info">
        <div class="d-flex align-items-center">
            <i class="fas fa-calendar-alt me-2"></i>
            <div>
                <strong>Current Reporting Period:</strong> 
                Q<?= $current_period['quarter'] ?>-<?= $current_period['year'] ?> 
                (<?= date('d M Y', strtotime($current_period['start_date'])) ?> - 
                <?= date('d M Y', strtotime($current_period['end_date'])) ?>)
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-chart-bar me-2"></i>Submitted Metrics
            </h5>
            <span class="badge bg-light text-primary"><?= count(array_unique(array_column($metrics, 'metric_id'))) ?> Metrics</span>
        </div>
        <div class="card-body">
            <?php if (empty($metrics)): ?>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    No metrics have been submitted for your sector yet. Click the "Create New Metric" button to get started.
                </div>
            <?php else: ?>
                <p class="mb-3">These metrics have been submitted for the current reporting period (Q<?= $current_period['quarter'] ?>-<?= $current_period['year'] ?>).</p>
                <div class="table-responsive">
                    <table class="table table-hover border">
                        <thead class="table-light">
                            <tr>
                                <th width="70%">Metric Name</th>
                                <th width="30%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $unique_metrics = [];
                            foreach ($metrics as $metric):
                                if (!in_array($metric['metric_id'], $unique_metrics)):
                                    $unique_metrics[] = $metric['metric_id'];
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($metric['table_name']) ?></strong></td>
                                    <td class="text-center">
                                        <a href="view_metric.php?metric_id=<?= $metric['metric_id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($draft_metrics)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">
                    <i class="fas fa-edit me-2"></i>Metric Drafts
                </h5>
                <span class="badge bg-light text-primary"><?= count(array_unique(array_column($draft_metrics, 'metric_id'))) ?> Drafts</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover border">
                        <thead class="table-light">
                            <tr>
                                <th width="60%">Metric</th>
                                <th width="40%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $unique_metrics = [];
                            foreach ($draft_metrics as $metric) {
                                if (!in_array($metric['metric_id'], $unique_metrics)) {
                                    $unique_metrics[] = $metric['metric_id'];
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($metric['table_name']) ?></strong></td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="edit_metric.php?metric_id=<?= $metric['metric_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </a>
                                            <a href="submit_draft_metric.php?metric_id=<?= $metric['metric_id'] ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Are you sure you want to submit this metric draft?');">
                                                <i class="fas fa-check me-1"></i> Submit
                                            </a>
                                            <a href="delete_metric.php?metric_id=<?= $metric['metric_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this metric draft?');">
                                                <i class="fas fa-trash-alt me-1"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title m-0">
                <i class="fas fa-info-circle me-2"></i>Guidelines for Metrics
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-table me-2 text-primary"></i>Metric Tables</h6>
                        <p class="small mb-1">Create a table for each related set of metrics that share the same reporting frequency.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Example: "Timber Production Volume"</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-columns me-2 text-success"></i>Metric Columns</h6>
                        <p class="small mb-1">Each column represents a specific metric with its own unit of measurement.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Example: "Timber Exports", "Forest Coverage"</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-ruler me-2 text-info"></i>Measurement Units</h6>
                        <p class="small mb-1">Specify the appropriate unit for each metric. Units can be set individually or for all columns.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Examples: RM, Ha, %, tons, m³</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-chart-line me-2 text-danger"></i>Data Formatting</h6>
                        <p class="small mb-1">Enter numbers directly without commas or symbols. Use consistent decimal places.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Correct: 1250.50 <br>Incorrect: 1,250.50 RM</div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-primary mt-4 mb-0">
                <div class="d-flex">
                    <div class="me-3 fs-4">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading">Tips for Effective Metrics</h6>
                        <ul class="mb-0 ps-3">
                            <li>Use clear, descriptive names for tables and metrics</li>
                            <li>Ensure consistent units across similar metrics</li>
                            <li>Review your data before submission for accuracy</li>
                            <li>For metrics with multiple units, use the "Set All Units" button for consistency</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
require_once '../layouts/footer.php';
?>
