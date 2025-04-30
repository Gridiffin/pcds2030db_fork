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

// Get recently generated reports - modified to avoid collation issues
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
    // If the modified query still fails, use a simpler query as fallback
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
$page_title = "Generate Reports";

// Include header
include_once '../../views/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Left sidebar -->
        <?php include_once '../../views/layouts/admin_nav.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Generate Reports</h1>
            </div>
            
            <div class="row">
                <!-- Report Generation Form -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Generate New Report</h5>
                        </div>
                        <div class="card-body">
                            <form id="reportGenerationForm">
                                <div class="mb-3">
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
                                
                                <div class="mb-3">
                                    <label for="sectorSelect" class="form-label">Sector</label>
                                    <select class="form-select" id="sectorSelect" name="sector_id" required>
                                        <option value="">Select Sector</option>
                                        <?php foreach ($sectors as $sector): ?>
                                            <option value="<?php echo $sector['sector_id']; ?>">
                                                <?php echo $sector['sector_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reportName" class="form-label">Report Name</label>
                                    <input type="text" class="form-control" id="reportName" name="report_name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reportDescription" class="form-label">Description (Optional)</label>
                                    <textarea class="form-control" id="reportDescription" name="description" rows="2"></textarea>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="isPublic" name="is_public" value="1">
                                    <label class="form-check-label" for="isPublic">Make available to agencies</label>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="button" id="generatePptxBtn" class="btn btn-primary">
                                        <i class="bi bi-file-earmark-ppt"></i> Generate PPTX Report
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Generation Status -->
                    <div class="card mt-3 d-none" id="generationStatus">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border text-primary me-3" role="status">
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
                    <div class="alert alert-success mt-3 d-none" id="successMessage">
                        <h5><i class="bi bi-check-circle"></i> Report Generated Successfully</h5>
                        <p>Your report has been generated and saved.</p>
                        <div class="mt-2">
                            <a href="#" id="downloadLink" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-download"></i> Download PPTX
                            </a>
                            <a href="generate_reports.php" class="btn btn-sm btn-outline-primary ms-2">
                                <i class="bi bi-plus-circle"></i> Generate Another
                            </a>
                        </div>
                    </div>
                    
                    <!-- Error Message -->
                    <div class="alert alert-danger mt-3 d-none" id="errorMessage">
                        <h5><i class="bi bi-exclamation-triangle"></i> Error</h5>
                        <p id="errorText">Something went wrong. Please try again.</p>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="$('#errorMessage').addClass('d-none');">
                            <i class="bi bi-arrow-counterclockwise"></i> Try Again
                        </button>
                    </div>
                </div>
                
                <!-- Recent Reports -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Reports</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($reports) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
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
                                                        <a href="../../download.php?type=report&file=<?php echo $report['pptx_path']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <p class="text-muted">No reports generated yet.</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="text-end mt-2">
                                <a href="view_all_reports.php" class="btn btn-sm btn-outline-secondary">
                                    View All Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Load PptxGenJS Library -->
<script src="https://cdn.jsdelivr.net/npm/pptxgenjs@3.12.0/dist/pptxgen.bundle.js"></script>
<script src="../../assets/js/report-generator.js"></script>

<?php include_once '../../views/layouts/footer.php'; ?>