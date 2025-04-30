<?php
/**
 * Generate Reports
 * 
 * Interface for users to generate various reports.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Generate Reports';

// Get available reporting periods
$reporting_periods = get_all_reporting_periods();

// Get selected period (if any)
$selected_period = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;

// Get current period if no period is selected
if (!$selected_period) {
    $current_period = get_current_reporting_period();
    $selected_period = $current_period['period_id'] ?? null;
}

// Get sector data for the selected period (for report generation)
$sectors = [];
if ($selected_period) {
    $sectors = get_sector_data_for_period($selected_period);
}

// Get period details if a period is selected
$period_details = null;
if ($selected_period) {
    $period_details = get_reporting_period($selected_period);
}

// Setup additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/admin/generate_reports.js'
];

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Set up the dashboard header variables
$title = "Generate Reports";
$subtitle = "Create and download detailed reports for reporting periods";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = [
    [
        'url' => '#',
        'text' => 'Refresh',
        'icon' => 'fas fa-sync-alt',
        'id' => 'refreshPage',
        'class' => 'btn-light border border-primary text-primary'
    ]
];

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';
?>

<div class="container-fluid px-4">
    <!-- Alert container for notifications -->
    <div id="alertContainer"></div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <div><?php echo $message; ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Period Selector Card -->
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
                        <a href="generate_reports.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selected_period && $period_details): ?>
        <!-- Report Generation Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title m-0">
                    <i class="fas fa-file-powerpoint me-2"></i>Generate Report for Q<?php echo $period_details['quarter']; ?>-<?php echo $period_details['year']; ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            You are about to generate a comprehensive report for Q<?php echo $period_details['quarter']; ?>-<?php echo $period_details['year']; ?> 
                            (<?php echo date('M j, Y', strtotime($period_details['start_date'])); ?> - 
                            <?php echo date('M j, Y', strtotime($period_details['end_date'])); ?>).
                            This will include data from all sectors and agencies.
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="includeSectorBreakdown" checked>
                            <label class="form-check-label" for="includeSectorBreakdown">
                                Include sector-by-sector breakdown
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="includeCharts" checked>
                            <label class="form-check-label" for="includeCharts">
                                Include visualizations and charts
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="includeComparisons">
                            <label class="form-check-label" for="includeComparisons">
                                Include comparisons with previous quarters
                            </label>
                        </div>

                        <div class="mt-4">
                            <button id="generateReportBtn" class="btn btn-success" data-period-id="<?php echo $selected_period; ?>">
                                <i class="fas fa-file-export me-2"></i>Generate Report
                            </button>
                            <div class="spinner-border text-primary d-none ms-2" role="status" id="generateSpinner">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h6 class="card-title m-0">Report Details</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-5">Period:</dt>
                                    <dd class="col-sm-7">Q<?php echo $period_details['quarter']; ?>-<?php echo $period_details['year']; ?></dd>
                                    
                                    <dt class="col-sm-5">Status:</dt>
                                    <dd class="col-sm-7">
                                        <span class="badge bg-<?php echo ($period_details['status'] === 'open') ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($period_details['status']); ?>
                                        </span>
                                    </dd>
                                    
                                    <dt class="col-sm-5">Sectors:</dt>
                                    <dd class="col-sm-7"><?php echo count($sectors); ?> sectors</dd>
                                    
                                    <dt class="col-sm-5">Format:</dt>
                                    <dd class="col-sm-7">PowerPoint (.pptx)</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generated Reports Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Previously Generated Reports</h5>
                <button class="btn btn-sm btn-outline-primary" id="refreshReportList">
                    <i class="fas fa-sync-alt me-1"></i> Refresh List
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Period</th>
                                <th>Generated On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reportsList">
                            <!-- Reports will be populated dynamically -->
                            <tr>
                                <td colspan="4" class="text-center py-3">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No reports have been generated for this period yet.
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Please select a valid reporting period to generate reports.
        </div>
    <?php endif; ?>

    <!-- Report Types Info Card -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title m-0">About Report Generation</h5>
        </div>
        <div class="card-body pb-2">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary p-2"><i class="fas fa-file-powerpoint"></i></span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mt-0">PowerPoint Reports</h5>
                            <p class="mb-0">These reports are generated in PowerPoint format for easy presentation. They include visualizations and progress tracking for all programs across sectors.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <span class="badge bg-success p-2"><i class="fas fa-cogs"></i></span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mt-0">Automated Generation</h5>
                            <p class="mb-0">Reports are generated automatically based on the latest data in the system. The generation process may take a few moments depending on the amount of data.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script for handling report generation -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const generateBtn = document.getElementById('generateReportBtn');
        if (generateBtn) {
            generateBtn.addEventListener('click', function() {
                // Show spinner
                this.disabled = true;
                document.getElementById('generateSpinner').classList.remove('d-none');
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
                
                // In a real implementation, this would make an AJAX call to the server
                // For this UI demonstration, we'll just simulate a delay
                setTimeout(() => {
                    // Hide spinner and re-enable button
                    this.disabled = false;
                    document.getElementById('generateSpinner').classList.add('d-none');
                    this.innerHTML = '<i class="fas fa-file-export me-2"></i>Generate Report';
                    
                    // Show success message
                    const alertContainer = document.getElementById('alertContainer');
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2"></i>
                            <div>Report for Q<?php echo $period_details['quarter'] ?? ''; ?>-<?php echo $period_details['year'] ?? ''; ?> has been generated successfully!</div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    alertContainer.appendChild(alert);
                    
                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        alert.classList.remove('show');
                        setTimeout(() => alertContainer.removeChild(alert), 150);
                    }, 5000);
                    
                    // Update the reports list
                    updateReportsList();
                }, 2000);
            });
        }
        
        // Function to update the reports list
        function updateReportsList() {
            const reportsList = document.getElementById('reportsList');
            if (reportsList) {
                reportsList.innerHTML = `
                    <tr>
                        <td>
                            <div class="fw-medium">Q<?php echo $period_details['quarter'] ?? ''; ?>-<?php echo $period_details['year'] ?? ''; ?> Comprehensive Report</div>
                            <div class="small text-muted">All sectors included</div>
                        </td>
                        <td>Q<?php echo $period_details['quarter'] ?? ''; ?>-<?php echo $period_details['year'] ?? ''; ?></td>
                        <td>${new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="#" class="btn btn-outline-primary" title="View Report">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn btn-outline-success" download title="Download Report">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" title="Delete Report">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }
        
        // Handle refresh button
        const refreshBtn = document.getElementById('refreshReportList');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
                
                // Simulate refresh delay
                setTimeout(() => {
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Refresh List';
                }, 1000);
            });
        }
    });
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>