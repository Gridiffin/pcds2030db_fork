<?php
/**
 * Generate Reports Page
 * 
 * This page allows admins to generate PPTX reports for selected reporting periods and sectors.
 * Reports are generated client-side and then uploaded to the server.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admins/index.php';

// Check if user is admin
if (!is_admin()) {
    header("Location: ../../login.php");
    exit();
}

// Get periods for dropdown
$periods_query = "SELECT * FROM reporting_periods ORDER BY year DESC, quarter DESC";
$periods_result = $conn->query($periods_query);
$periods = [];
while ($period = $periods_result->fetch_assoc()) {
    $periods[] = $period;
}

// Get sectors for dropdown
$sectors_query = "SELECT * FROM sectors ORDER BY sector_name";
$sectors_result = $conn->query($sectors_query);
$sectors = [];
while ($sector = $sectors_result->fetch_assoc()) {
    $sectors[] = $sector;
}

// Get recently generated reports
$reports_query = "SELECT r.*, rp.quarter, rp.year, s.sector_name, u.username 
                 FROM reports r
                 JOIN reporting_periods rp ON r.period_id = rp.period_id
                 JOIN users u ON r.generated_by = u.user_id
                 LEFT JOIN sectors s ON s.sector_id = (
                    SELECT sector_id FROM sectors 
                    WHERE LOWER(CONVERT(r.report_name USING utf8mb4)) LIKE LOWER(CONCAT('%', CONVERT(sector_name USING utf8mb4), '%')) 
                    LIMIT 1
                 )
                 ORDER BY r.generated_at DESC 
                 LIMIT 10";
try {
    $reports_result = $conn->query($reports_query);
    $reports = [];
    if ($reports_result) {
        while ($report = $reports_result->fetch_assoc()) {
            $reports[] = $report;
        }
    }
} catch (Exception $e) {
    // Fallback query if the complex one fails
    $reports_query = "SELECT r.*, rp.quarter, rp.year, u.username 
                     FROM reports r
                     JOIN reporting_periods rp ON r.period_id = rp.period_id
                     JOIN users u ON r.generated_by = u.user_id
                     ORDER BY r.generated_at DESC 
                     LIMIT 10";
    $reports_result = $conn->query($reports_query);
    $reports = [];
    while ($report = $reports_result->fetch_assoc()) {
        $reports[] = $report;
    }
}

// Set page title
$pageTitle = "Generate Reports";

// Add page-specific CSS
$additionalStyles = [APP_URL . '/assets/css/pages/report-generator.css'];

// Additional scripts for report generation
$additionalScripts = [
    APP_URL . '/assets/js/report-generator.js'
];

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Set up the page header variables
$title = "Generate Reports";
$subtitle = "Create and manage sector progress reports in PPTX format";
$headerStyle = 'standard-white'; // Updated to use standardized white variant
$headerClass = ''; // Removed custom class as it's no longer needed
$actions = [
    [
        'url' => 'view_all_reports.php',
        'text' => 'View All Reports',
        'icon' => 'fa-list-alt',
        'class' => 'btn-outline-primary' // Blue outline button for contrast on white
    ]
];

// --- Include the Dashboard Header ---
require_once '../../includes/dashboard_header.php';
?>

<!-- Main Page Content -->
<section class="section">
    <div class="container-fluid">
        <div class="row">
            <!-- Report Generation Form -->
            <div class="col-lg-7 col-md-12 mb-4">
                <div class="card report-generator-card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-powerpoint me-2"></i>Generate New Report</h5>
                    </div>
                    <div class="card-body">
                        <form id="reportGenerationForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="report-form-group mb-3">
                                        <label for="periodSelect" class="form-label">Reporting Period</label>
                                        <select class="form-select" id="periodSelect" name="period_id" required>
                                            <option value="">Select Reporting Period</option>
                                            <?php foreach ($periods as $period): ?>
                                                <option value="<?php echo $period['period_id']; ?>">
                                                    Q<?php echo $period['quarter']; ?> <?php echo $period['year']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="report-form-group mb-3">
                                        <label for="sectorSelect" class="form-label">Sector</label>
                                        <select class="form-select" id="sectorSelect" name="sector_id" required>
                                            <option value="">Select Sector</option>
                                            <?php foreach ($sectors as $sector): ?>
                                                <option value="<?php echo $sector['sector_id']; ?>">
                                                    <?php echo htmlspecialchars($sector['sector_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="report-form-group mb-3">
                                <label for="reportName" class="form-label">Report Name</label>
                                <input type="text" class="form-control" id="reportName" name="report_name" required placeholder="e.g., Forestry Sector Report - Q2 2025">
                            </div>
                            
                            <div class="report-form-group mb-3">
                                <label for="reportDescription" class="form-label">Description (Optional)</label>
                                <textarea class="form-control" id="reportDescription" name="description" rows="2" placeholder="Brief description of the report content"></textarea>
                            </div>
                            
                            <div class="report-form-group mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="isPublic" name="is_public" value="1">
                                <label class="form-check-label" for="isPublic">Make available to agencies</label>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="button" id="generatePptxBtn" class="btn btn-primary generate-btn btn-lg">
                                    <i class="fas fa-file-powerpoint me-2"></i> Generate PPTX Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Generation Status -->
                <div class="card generation-status-card mt-3 d-none shadow-sm" id="generationStatus">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border text-primary me-3 animated-spinner" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div>
                                <h5 class="mb-1">Generating Report</h5>
                                <p class="mb-0" id="statusMessage">Fetching data...</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Success Message -->
                <div class="alert report-success-msg mt-3 d-none shadow-sm" id="successMessage">
                    <h5><i class="fas fa-check-circle"></i> Report Generated Successfully</h5>
                    <p>Your report has been generated and saved.</p>
                    <div class="mt-2">
                        <a href="#" id="downloadLink" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-download"></i> Download PPTX
                        </a>
                        <a href="generate_reports.php" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="fas fa-plus-circle"></i> Generate Another
                        </a>
                    </div>
                </div>
                
                <!-- Error Message -->
                <div class="alert report-error-msg mt-3 d-none shadow-sm" id="errorMessage">
                    <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
                    <p id="errorText">Something went wrong. Please try again.</p>
                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="$('#errorMessage').addClass('d-none');">
                        <i class="fas fa-arrow-counterclockwise"></i> Try Again
                    </button>
                </div>
            </div>
            
            <!-- Recent Reports -->
            <div class="col-lg-5 col-md-12 mb-4">
                <div class="card report-generator-card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Reports</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($reports) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover reports-table">
                                    <thead>
                                        <tr>
                                            <th>Report Name</th>
                                            <th>Period</th>
                                            <th>Generated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reports as $report): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($report['report_name']); ?></td>
                                                <td>Q<?php echo $report['quarter']; ?> <?php echo $report['year']; ?></td>
                                                <td><?php echo date('M j, Y', strtotime($report['generated_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="../../download.php?type=report&file=<?php echo $report['pptx_path']; ?>" class="btn btn-sm btn-outline-secondary action-btn action-btn-download" title="Download Report">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary action-btn action-btn-delete" title="Delete Report" 
                                                                data-bs-toggle="modal" data-bs-target="#deleteReportModal" 
                                                                data-report-id="<?php echo $report['report_id']; ?>" 
                                                                data-report-name="<?php echo htmlspecialchars($report['report_name']); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="reports-empty-state">
                                <p class="text-muted">No reports generated yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Load PptxGenJS Library -->
<script src="https://cdn.jsdelivr.net/npm/pptxgenjs@3.12.0/dist/pptxgen.bundle.js"></script>
<script src="../../assets/js/report-generator.js"></script>

<!-- Delete Report Confirmation Modal -->
<div class="modal fade delete-confirm-modal" id="deleteReportModal" tabindex="-1" aria-labelledby="deleteReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteReportModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the report: <strong id="reportNameToDelete"></strong>?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-1"></i>Delete Report
                </button>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../views/layouts/footer.php'; ?>