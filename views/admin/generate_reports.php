<?php
/**
 * Generate Reports
 * 
 * Admin page for generating and managing reports.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin_functions.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Generate Reports';

// Get all reporting periods
$reporting_periods = get_all_reporting_periods();

// Get current reporting period
$current_period = get_current_reporting_period();

// Check if a specific period is selected
$selected_period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? 0);

// Get existing reports
$existing_reports = [];

// Check if reports table exists
$check_table_query = "SHOW TABLES LIKE 'reports'";
$table_result = $conn->query($check_table_query);

// If reports table exists, get the reports
if ($table_result && $table_result->num_rows > 0) {
    // Get existing reports
    $reports_query = "SELECT * FROM reports ORDER BY generated_at DESC";
    
    // Filter by period if specified
    if ($selected_period_id) {
        $reports_query = "SELECT * FROM reports WHERE period_id = ? ORDER BY generated_at DESC";
        $stmt = $conn->prepare($reports_query);
        $stmt->bind_param("i", $selected_period_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($reports_query);
    }
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $existing_reports[] = $row;
        }
    }
}

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/admin/generate_reports.js'
];

// Set up the dashboard header variables
$title = "Generate Reports";
$subtitle = "Generate and manage reports for agencies and sectors";
$headerStyle = 'light'; // Use light style to match other admin pages
$actions = [];

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';
?>

<!-- Alert for messages -->
<div id="alertContainer"></div>

<!-- Report Generation Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">Generate New Report</h5>
    </div>
    <div class="card-body">
        <form id="generateReportForm" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="period_id" class="form-label">Select Reporting Period</label>
                <select class="form-select" id="period_id" name="period_id" required>
                    <option value="">-- Select Period --</option>
                    <?php foreach ($reporting_periods as $period): ?>
                        <option value="<?php echo $period['period_id']; ?>" <?php echo $selected_period_id == $period['period_id'] ? 'selected' : ''; ?>>
                            Q<?php echo $period['quarter']; ?>-<?php echo $period['year']; ?> 
                            (<?php echo date('M j, Y', strtotime($period['start_date'])); ?> - 
                            <?php echo date('M j, Y', strtotime($period['end_date'])); ?>)
                            <?php echo $period['status'] === 'open' ? ' - OPEN' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Select the reporting period for which you want to generate reports.</div>
            </div>
            <div class="col-md-6">
                <div class="d-grid gap-2 d-md-flex">
                    <button type="button" id="generateReportBtn" class="btn btn-primary" disabled>
                        <i class="fas fa-file-export me-1"></i> Generate Reports
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="refreshReportList">
                        <i class="fas fa-sync-alt me-1"></i> Refresh List
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Report Information Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Report Information</h5>
        <span class="badge bg-info" id="reportCount"><?php echo count($existing_reports); ?> Reports</span>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle fa-2x me-3"></i>
                </div>
                <div>
                    <h5 class="alert-heading">About Report Generation</h5>
                    <p class="mb-0">Reports are generated in both PDF and PowerPoint (PPTX) formats. The PDF report is suitable for printing and sharing, while the PowerPoint presentation can be used for meetings and presentations.</p>
                    <hr>
                    <p class="mb-0">Reports include program statistics, submission rates, and performance metrics aggregated by sectors. Each report is specific to a reporting period.</p>
                </div>
            </div>
        </div>

        <!-- Existing Reports Table -->
        <div class="table-responsive" id="reportsTableContainer">
            <?php if (empty($existing_reports)): ?>
                <div class="alert alert-secondary">
                    <i class="fas fa-folder-open me-2"></i>
                    No reports have been generated yet. Select a reporting period above and click "Generate Reports" to create new reports.
                </div>
            <?php else: ?>
                <table class="table table-hover" id="reportsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Period</th>
                            <th>Report Type</th>
                            <th>Generated On</th>
                            <th>Generated By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($existing_reports as $report): ?>
                            <?php 
                            // Get period info
                            $period_info = get_reporting_period($report['period_id']);
                            // Get user info
                            $user_query = "SELECT username FROM users WHERE user_id = ?";
                            $stmt = $conn->prepare($user_query);
                            $stmt->bind_param("i", $report['generated_by']);
                            $stmt->execute();
                            $user_result = $stmt->get_result();
                            $username = ($user_result && $user_result->num_rows > 0) ? $user_result->fetch_assoc()['username'] : 'Unknown';
                            ?>
                            <tr>
                                <td>
                                    Q<?php echo $period_info['quarter']; ?>-<?php echo $period_info['year']; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary me-1">PDF</span>
                                    <span class="badge bg-danger">PPTX</span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($report['generated_at'])); ?></td>
                                <td><?php echo $username; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo APP_URL; ?>/reports/pdf/<?php echo $report['pdf_path']; ?>" class="btn btn-outline-primary" target="_blank" title="View PDF Report">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/reports/pptx/<?php echo $report['pptx_path']; ?>" class="btn btn-outline-danger" download title="Download PowerPoint">
                                            <i class="fas fa-file-powerpoint"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Report Generation Modal -->
<div class="modal fade" id="reportGenerationModal" tabindex="-1" aria-labelledby="reportGenerationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportGenerationModalLabel">Generating Reports</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3" id="generationStatus">Generating reports, please wait...</p>
                </div>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to handle report generation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable/disable generate button based on period selection
    const periodSelect = document.getElementById('period_id');
    const generateBtn = document.getElementById('generateReportBtn');
    
    if (periodSelect && generateBtn) {
        periodSelect.addEventListener('change', function() {
            generateBtn.disabled = !this.value;
        });
        
        // Initial check
        generateBtn.disabled = !periodSelect.value;
    }
    
    // Handle generate button click
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            const periodId = periodSelect.value;
            if (!periodId) return;
            
            // Show the generation modal
            const modal = new bootstrap.Modal(document.getElementById('reportGenerationModal'));
            modal.show();
            
            // Simulate progress
            const progressBar = document.querySelector('.progress-bar');
            let progress = 0;
            const progressInterval = setInterval(function() {
                progress += 5;
                if (progress > 90) {
                    clearInterval(progressInterval);
                }
                progressBar.style.width = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);
            }, 300);
            
            // Call the report generation endpoint
            fetch('../../controllers/admin/reports_controller.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=generate_report&period_id=' + periodId
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                progressBar.setAttribute('aria-valuenow', 100);
                
                const statusElement = document.getElementById('generationStatus');
                
                if (data.success) {
                    statusElement.textContent = 'Reports generated successfully!';
                    statusElement.className = 'mt-3 text-success';
                    
                    // Show success alert
                    showAlert('success', 'Reports generated successfully! Refreshing report list...');
                    
                    // Refresh the page after a delay
                    setTimeout(function() {
                        window.location.href = 'generate_reports.php?period_id=' + periodId;
                    }, 2000);
                } else {
                    statusElement.textContent = 'Error: ' + (data.error || 'Failed to generate reports');
                    statusElement.className = 'mt-3 text-danger';
                    
                    // Show error alert
                    showAlert('danger', 'Error: ' + (data.error || 'Failed to generate reports'));
                }
            })
            .catch(error => {
                clearInterval(progressInterval);
                console.error('Error:', error);
                
                const statusElement = document.getElementById('generationStatus');
                statusElement.textContent = 'Error: ' + error.message;
                statusElement.className = 'mt-3 text-danger';
                
                // Show error alert
                showAlert('danger', 'Error: ' + error.message);
            });
        });
    }
    
    // Refresh report list button
    const refreshBtn = document.getElementById('refreshReportList');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const periodId = periodSelect.value || '';
            window.location.href = 'generate_reports.php' + (periodId ? '?period_id=' + periodId : '');
        });
    }
    
    // Helper function to show alerts
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        if (!alertContainer) return;
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                <div>${message}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        alertContainer.appendChild(alert);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            alert.classList.remove('show');
            setTimeout(function() {
                alertContainer.removeChild(alert);
            }, 150);
        }, 5000);
    }
});
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>