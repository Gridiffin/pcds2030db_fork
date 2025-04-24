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

$draft_metrics = get_draft_metric();
if (!is_array($draft_metrics)) {
    $draft_metrics = [];
}

$additionalScripts = [
    APP_URL . '/assets/js/agency/metric_submission.js'
];

require_once '../layouts/header.php';
require_once '../layouts/agency_nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">Submit Sector Metrics</h1>
        <p class="text-muted">Update your sector-specific metrics for this reporting period</p>
    </div>

    <?php if ($current_period): ?>
        <div class="period-badge">
            <span class="badge bg-success">
                <i class="fas fa-calendar-alt me-1"></i>
                Q<?= $current_period['quarter'] ?>-<?= $current_period['year'] ?>
            </span>
            <span class="badge bg-success">
                <i class="fas fa-clock me-1"></i>
                Ends: <?= date('M j, Y', strtotime($current_period['end_date'])) ?>
            </span>
        </div>
    <?php else: ?>
        <div class="period-badge">
            <span class="badge bg-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                No Active Reporting Period
            </span>
        </div>
    <?php endif; ?>
</div>

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
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Sector: <?= get_sector_name($_SESSION['sector_id']) ?></h5>
            <span class="badge bg-primary"><?= count($metrics) ?> Metrics</span>
        </div>
        <div class="card-body">
            <?php
                $next_metric_id = 0;
                $result = $conn->query("SELECT MAX(metric_id) AS max_id FROM (SELECT metric_id FROM sector_metrics_submitted UNION ALL SELECT metric_id FROM sector_metrics_draft) AS combined");
                if ($result && $row = $result->fetch_assoc()) {
                    $next_metric_id = $row['max_id'] + 1;
                }
            ?>
            <a href="create_metric.php?sector_id=<?= $_SESSION['sector_id'] ?>&next_metric_id=<?= $next_metric_id ?>" class="btn"> + Create New </a>
            <?php if (empty($metrics)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No metrics for your sector yet. Contact the administrator for assistance.
                </div>
            <?php else: ?>
                <p class="mb-3">Please provide values for all required metrics for the current reporting period (Q<?= $current_period['quarter'] ?>-<?= $current_period['year'] ?>).</p>
                <form method="post" id="metricsForm">
                    <input type="hidden" name="period_id" value="<?= $current_period['period_id'] ?>">
                    <div class="table-responsive">
                        <table class="table table-hover table-custom">
                            <thead>
                                <tr>
                                    <th width="90%">Metric</th>
                                    <th width="10%">Status</th>
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
                                        <td><?= isset($metric['status']) ? htmlspecialchars($metric['status']) : 'Submitted' ?></td>
                                    </tr>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($draft_metrics)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Metric Drafts</h5>
                <span class="badge bg-primary"><?= count($draft_metrics) ?> Drafts</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th width="90%">Metric</th>
                                <th width="10%">Actions</th>
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
                                        <a href="edit_metric.php?metric_id=<?= $metric['metric_id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="submit_draft_metric.php?metric_id=<?= $metric['metric_id'] ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Are you sure you want to submit this metric draft?');">
                                            <i class="fas fa-check me-1"></i> Submit
                                        </a>
                                        <a href="delete_metric.php?metric_id=<?= $metric['metric_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this metric draft?');">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </a>
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
        <div class="card-header">
            <h5 class="card-title m-0">Guidelines for Metric Submission</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <h6><i class="fas fa-calculator me-2 text-primary"></i>Numeric Metrics</h6>
                        <p class="small">Enter whole numbers or decimal values. Do not include commas or other formatting.</p>
                        <div class="alert alert-light">Example: 1250.50</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <h6><i class="fas fa-percentage me-2 text-info"></i>Percentage Metrics</h6>
                        <p class="small">Enter percentage as a number only. The % symbol is added automatically.</p>
                        <div class="alert alert-light">Example: 95.5</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <h6><i class="fas fa-font me-2 text-success"></i>Text Metrics</h6>
                        <p class="small">Enter text according to the metric requirements. Keep responses concise.</p>
                        <div class="alert alert-light">Example: Completed phase 1</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
require_once '../layouts/footer.php';
?>
