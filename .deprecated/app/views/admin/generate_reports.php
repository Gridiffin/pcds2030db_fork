<?php
/**
 * Generate Reports Page
 * 
 * This page allows admins to generate PPTX reports for selected reporting periods and sectors.
 * Reports are generated client-side and then uploaded to the server.
 */

// Include necessary files
require_once ROOT_PATH . 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/$((includes/db_connect.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/session.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/functions.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/admins/index.php -replace 'includes/', ''))';

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

// Get available programs for selection grouped by sector
// We'll fetch this when a period is selected via AJAX to ensure only relevant programs are shown
$available_programs = [];

// Prepare a default structure for the programs
foreach ($sectors as $sector) {
    $available_programs[$sector['sector_id']] = [
        'sector_name' => $sector['sector_name'],
        'programs' => []
    ];
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
$additionalStyles = [
    APP_URL . '/assets/css/pages/report-generator.css',
    APP_URL . '/assets/css/pages/program-ordering.css'
];

// Additional scripts for report generation
$additionalScripts = [
    APP_URL . '/assets/js/report-generator.js',
    APP_URL . '/assets/js/program-ordering.js'
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
require_once ROOT_PATH . 'app/lib/$((includes/dashboard_header.php -replace 'includes/', ''))';
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
                                                    <?php
                                                    $display_text = '';
                                                    if ($period['quarter'] == 5) { // Q5 maps to Half Yearly 1
                                                        $display_text = 'Half Yearly 1 ' . $period['year'];
                                                    } elseif ($period['quarter'] == 6) { // Q6 maps to Half Yearly 2
                                                        $display_text = 'Half Yearly 2 ' . $period['year'];
                                                    } else {
                                                        // Fallback for Q1, Q2, Q3, Q4 and any other quarter values
                                                        $display_text = 'Q' . $period['quarter'] . ' ' . $period['year'];
                                                    }
                                                    echo htmlspecialchars($display_text);
                                                    ?>
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
                                </div>                            </div>
                            <div class="report-form-group mb-3">
                                <label for="reportName" class="form-label">Report Name</label>
                                <input type="text" class="form-control" id="reportName" name="report_name" required placeholder="e.g., Forestry Sector Report - Q2 2025">
                            </div>
                            
                            <div class="report-form-group mb-3">
                                <label for="reportDescription" class="form-label">Description (Optional)</label>
                                <textarea class="form-control" id="reportDescription" name="description" rows="2" placeholder="Brief description of the report content"></textarea>
                            </div>
                            
                            <div class="report-form-group mb-3">
                                <label class="form-label">Select Programs to Include (Optional)</label>
                                <div id="programSelector" class="program-selector">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Select the programs you want to include in the report. If none are selected, all programs for the chosen sector will be included.
                                    </div>                                    <div class="program-selector-container border rounded p-2" style="max-height: 250px; overflow-y: auto;" role="list">
                                        <?php if (!empty($available_programs)): ?>
                                            <div class="pb-2 mb-2 border-bottom">
                                                <div class="row align-items-center">
                                                    <div class="col">
                                                        <h6 class="m-0 program-selection-title">Programs <span id="programCount" class="badge bg-primary">0</span></h6>
                                                    </div>                                                <div class="col-auto">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPrograms">
                                                            <i class="fas fa-check-square me-1"></i> Select All
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllPrograms">
                                                            <i class="fas fa-square me-1"></i> Deselect All
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info ms-1" id="sortProgramOrder" title="Sort programs by their assigned numbers">
                                                            <i class="fas fa-sort-numeric-down me-1"></i> Sort Numerically
                                                        </button>
                                                    </div>
                                                </div>                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <small class="text-muted">
                                                            <i class="fas fa-info-circle me-1"></i> 
                                                            Select programs and use the number inputs to set the display order in the report. Lower numbers will appear first.
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="row mt-1">
                                                    <div class="col-12">
                                                        <small class="text-muted fst-italic">
                                                            <i class="fas fa-sort-numeric-down me-1"></i>
                                                            Order numbers will appear automatically when you select programs.
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php foreach ($available_programs as $sector_id => $sector_data): ?>
                                                <div class="sector-programs mb-2" data-sector-id="<?php echo $sector_id; ?>" role="group" aria-label="<?php echo htmlspecialchars($sector_data['sector_name']); ?> programs">
                                                    <h6 class="sector-name fw-bold ms-2 mb-1"><?php echo htmlspecialchars($sector_data['sector_name']); ?></h6>
                                                    <div class="ms-3" role="listbox">
                                                        <?php foreach ($sector_data['programs'] as $program): ?>
                                                            <div class="form-check program-checkbox-container" draggable="true" 
                                                                 data-program-id="<?php echo $program['program_id']; ?>"
                                                                 role="listitem"
                                                                 aria-label="<?php echo htmlspecialchars($program['program_name']); ?>"
                                                                 title="Drag to reorder">
                                                                <i class="fas fa-grip-vertical drag-handle" aria-hidden="true"></i>
                                                                <input class="form-check-input program-checkbox" 
                                                                       type="checkbox" 
                                                                       name="selected_program_ids[]" 
                                                                       value="<?php echo $program['program_id']; ?>" 
                                                                       id="program_<?php echo $program['program_id']; ?>"
                                                                       aria-label="Select <?php echo htmlspecialchars($program['program_name']); ?>">
                                                                <label class="form-check-label" for="program_<?php echo $program['program_id']; ?>">
                                                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                                                </label>
                                                                <input type="number" 
                                                                       min="1" 
                                                                       class="program-order-input" 
                                                                       name="program_order_<?php echo $program['program_id']; ?>" 
                                                                       id="order_<?php echo $program['program_id']; ?>" 
                                                                       aria-label="Order for <?php echo htmlspecialchars($program['program_name']); ?>" 
                                                                       style="display: none;" 
                                                                       placeholder="#">
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-muted">No programs available for selection.</p>
                                        <?php endif; ?>
                                    </div>
                                    <small class="form-text text-muted">Selected programs will appear in the report. If none are selected, all programs for the sector will be included.</small>
                                </div>
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
                    <div class="card-body" id="recentReportsContainer">
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

<!-- Load external libraries -->
<script src="https://cdn.jsdelivr.net/gh/gitbrent/pptxgenjs/dist/pptxgen.bundle.js"></script>

<!-- Load utilities and modules in the correct order -->
<!-- program-ordering.js is already included in the header -->
<script src="../../assets/js/report-modules/report-slide-styler.js"></script>
<script src="../../assets/js/report-modules/report-api.js"></script>
<script src="../../assets/js/report-modules/report-slide-populator.js"></script>
<script src="../../assets/js/report-modules/report-ui.js"></script>

<!-- Main report generator controller -->
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
