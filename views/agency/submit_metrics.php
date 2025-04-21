<?php
/**
 * Submit Sector Metrics
 * 
 * Interface for agency users to submit sector-specific metrics.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Submit Sector Metrics';

// Get current reporting period
$current_period = get_current_reporting_period();
if (!$current_period || $current_period['status'] !== 'open') {
    $error_message = 'No active reporting period is currently open.';
    $show_form = false;
} else {
    $show_form = true;
}

// Process form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_metrics'])) {
    $result = submit_metric_values($_POST);
    
    if (isset($result['success'])) {
        $message = $result['message'] ?? 'Metrics submitted successfully.';
        $message_type = 'success';
    } else if (isset($result['partial'])) {
        $message = $result['message'] ?? 'Some metrics were submitted, but others failed.';
        $message_type = 'warning';
    } else {
        $message = $result['error'] ?? 'Failed to submit metrics.';
        $message_type = 'danger';
    }
}

// Get metrics for the agency's sector
$metrics = get_agency_sector_metrics($_SESSION['sector_id']);
if (!is_array($metrics)) {
    $metrics = [];
}


$additionalScripts = [
    APP_URL . '/assets/js/agency/metric_submission.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
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
                Q<?php echo $current_period['quarter']; ?>-<?php echo $current_period['year']; ?>
            </span>
            <span class="badge bg-success">
                <i class="fas fa-clock me-1"></i>
                Ends: <?php echo date('M j, Y', strtotime($current_period['end_date'])); ?>
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
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?> me-2"></i>
            <div><?php echo $message; ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<?php if (!$show_form): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo $error_message; ?> Please try again when a reporting period is active.
    </div>
<?php else: ?>
    <!-- Sector Information Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Sector: <?php echo get_sector_name($_SESSION['sector_id']); ?></h5>
            <span class="badge bg-primary"><?php echo count($metrics); ?> Metrics</span>
        </div>
        <div class="card-body">
            <a href="create_metric.php?sector_id=<?php echo $_SESSION['sector_id']; ?>" class="btn"> + Create New </a>
            <?php if (empty($metrics)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No metrics for your sector yet. Contact the administrator for assistance.
                </div>
            <?php else: ?>
                
                <p class="mb-3">Please provide values for all required metrics for the current reporting period (Q<?php echo $current_period['quarter']; ?>-<?php echo $current_period['year']; ?>).</p>
                
                <form method="post" id="metricsForm">
                    <input type="hidden" name="period_id" value="<?php echo $current_period['period_id']; ?>">
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-custom">
                            <thead>
                                <tr>
                                    <th width="40%">Metric</th>
                                    <th width="15%">Type</th>
                                    <th width="25%">Value</th>
                                    <th width="20%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($metrics as $metric): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $metric['metric_name']; ?></strong>
                                            <?php if ($metric['description']): ?>
                                                <div class="small text-muted"><?php echo $metric['description']; ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                                $type_badge = 'secondary';
                                                switch ($metric['metric_type']) {
                                                    case 'numeric': $type_badge = 'primary'; break;
                                                    case 'percentage': $type_badge = 'info'; break;
                                                    case 'text': $type_badge = 'success'; break;
                                                }
                                            ?>
                                            <span class="badge bg-<?php echo $type_badge; ?>">
                                                <?php echo ucfirst($metric['metric_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($metric['metric_type'] === 'numeric'): ?>
                                                <input type="text" class="form-control numeric-input" 
                                                       name="metrics[<?php echo $metric['metric_id']; ?>]" 
                                                       placeholder="Enter a number" 
                                                       pattern="[0-9]*(\.[0-9]+)?" 
                                                       required
                                                       <?php echo $metric['is_submitted'] ? 'value="' . $metric['current_value'] . '"' : ''; ?>>
                                            <?php elseif ($metric['metric_type'] === 'percentage'): ?>
                                                <div class="input-group">
                                                    <input type="text" class="form-control percentage-input" 
                                                           name="metrics[<?php echo $metric['metric_id']; ?>]" 
                                                           placeholder="Enter percentage" 
                                                           pattern="[0-9]*(\.[0-9]+)?" 
                                                           required
                                                           <?php echo $metric['is_submitted'] ? 'value="' . $metric['current_value'] . '"' : ''; ?>>
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            <?php else: ?>
                                                <input type="text" class="form-control" 
                                                       name="metrics[<?php echo $metric['metric_id']; ?>]" 
                                                       placeholder="Enter value" 
                                                       required
                                                       <?php echo $metric['is_submitted'] ? 'value="' . $metric['current_value'] . '"' : ''; ?>>
                                            <?php endif; ?>
                                            
                                            <div class="mt-2">
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="notes[<?php echo $metric['metric_id']; ?>]" 
                                                       placeholder="Optional notes"
                                                       <?php echo $metric['is_submitted'] ? 'value="' . $metric['notes'] . '"' : ''; ?>>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($metric['is_submitted']): ?>
                                                <span class="badge bg-success">Submitted</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-3">
                        <a href="dashboard.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" name="submit_metrics" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Submit Sector Metrics
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Metric Definitions and Guidelines Card -->
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
// Include footer
require_once '../layouts/footer.php';
?>
