<?php
/**
 * View Reports
 * 
 * Interface for agency users to view reports related to their programs and sector.
 */

// Include necessary files
require_once ROOT_PATH . 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/$((includes/db_connect.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/session.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/functions.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/agencies/index.php -replace 'includes/', ''))';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'View Reports';

// Get available reporting periods
$reporting_periods = get_all_reporting_periods();

// Get selected period (if any)
$selected_period = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;


$additionalScripts = [
    APP_URL . '/assets/js/agency/reports.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">View Reports</h1>
        <p class="text-muted">Access and download reports for your programs and sector</p>
    </div>
</div>

<!-- Report Filter Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">Select Reporting Period</h5>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="period_id" class="form-label">Reporting Period</label>
                <select class="form-select" id="period_id" name="period_id" required>
                    <option value="">-- Select Period --</option>
                    <?php foreach ($reporting_periods as $period): ?>
                        <option value="<?php echo $period['period_id']; ?>" <?php echo $selected_period == $period['period_id'] ? 'selected' : ''; ?>>
                            Q<?php echo $period['quarter']; ?>-<?php echo $period['year']; ?> 
                            (<?php echo date('M j, Y', strtotime($period['start_date'])); ?> - 
                            <?php echo date('M j, Y', strtotime($period['end_date'])); ?>)
                            <?php echo $period['status'] === 'open' ? ' - OPEN' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <?php if ($selected_period): ?>
                    <a href="view_reports.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Reports List -->
<?php if ($selected_period): ?>
    <?php if (empty($reports)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No reports found for the selected reporting period.
        </div>
    <?php else: ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Available Reports</h5>
                <span class="badge bg-primary"><?php echo count($reports); ?> Reports</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Generated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td>
                                        <div class="fw-medium"><?php echo $report['report_name']; ?></div>
                                    </td>
                                    <td><?php echo $report['description']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $report['report_type'] === 'program' ? 'primary' : 'info'; ?>">
                                            <?php echo ucfirst($report['report_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($report['generated_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo APP_URL; ?>/reports/<?php echo $report['file_path']; ?>" class="btn btn-outline-primary" target="_blank" title="View Report">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/reports/<?php echo $report['file_path']; ?>" class="btn btn-outline-success" download title="Download Report">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Please select a reporting period to view available reports.
    </div>
<?php endif; ?>

<!-- Report Types Info Card -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="card-title m-0">Report Types</h5>
    </div>
    <div class="card-body pb-2">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <span class="badge bg-primary p-2"><i class="fas fa-project-diagram"></i></span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mt-0">Program Reports</h5>
                        <p class="mb-0">These reports contain detailed information about your specific programs including progress tracking, achievements, and targets.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <span class="badge bg-info p-2"><i class="fas fa-chart-bar"></i></span>
                    </div>                    <div class="flex-grow-1 ms-3">
                        <h5 class="mt-0">Sector Reports</h5>
                        <p class="mb-0">These reports provide an overview of your sector's performance outcomes, aggregated data, and comparative analysis.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>

