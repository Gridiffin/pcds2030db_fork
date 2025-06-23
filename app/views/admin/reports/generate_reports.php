<?php
/**
 * Generate Reports Page
 * 
 * Administrative interface for generating PPTX reports for selected reporting periods and sectors.
 * Features include program selection, ordering, and comprehensive report generation.
 * 
 * @author PCDS Dashboard System
 * @version 2.0
 * @since 1.0
 */

// Security and initialization
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/agencies.php';

// Security check: Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Page configuration
$pageTitle = 'Generate Reports';
$pageDescription = 'Create and manage sector progress reports in PPTX format';

/**
 * Get all reporting periods for dropdown selection
 * @return array Array of reporting periods
 */
function getReportingPeriods() {
    global $conn;
    
    try {
        $query = "SELECT period_id, quarter, year, status 
                  FROM reporting_periods 
                  ORDER BY year DESC, quarter DESC";
        
        $result = $conn->query($query);
        $periods = [];
        
        if ($result && $result->num_rows > 0) {
            while ($period = $result->fetch_assoc()) {
                $periods[] = $period;
            }
        }
        
        return $periods;
    } catch (Exception $e) {
        error_log("Error fetching reporting periods: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all sectors for dropdown selection
 * @return array Array of sectors
 */
function getSectors() {
    global $conn;
    
    try {
        $query = "SELECT sector_id, sector_name, description 
                  FROM sectors 
                  ORDER BY sector_name ASC";
        
        $result = $conn->query($query);
        $sectors = [];
        
        if ($result && $result->num_rows > 0) {
            while ($sector = $result->fetch_assoc()) {
                $sectors[] = $sector;
            }
        }
        
        return $sectors;
    } catch (Exception $e) {
        error_log("Error fetching sectors: " . $e->getMessage());
        return [];
    }
}

/**
 * Get recently generated reports directly from database
 * @param int $limit Number of reports to retrieve
 * @return array Array of recent reports
 */
function getRecentReports($limit = 10) {
    global $conn;
    
    $query = "SELECT r.report_id, r.report_name, r.pptx_path, r.generated_at, r.is_public,
                     rp.quarter, rp.year, u.username
              FROM reports r 
              LEFT JOIN reporting_periods rp ON r.period_id = rp.period_id 
              LEFT JOIN users u ON r.generated_by = u.user_id 
              ORDER BY r.generated_at DESC 
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    return $reports;
}

/**
 * Format period display name
 * @param array $report Report data with quarter and year
 * @return string Formatted period name
 */
function formatPeriod($report) {
    if (!$report || !isset($report['quarter'], $report['year'])) {
        return 'Unknown';
    }
    
    $quarter = (int)$report['quarter'];
    $year = $report['year'];
    
    if ($quarter >= 1 && $quarter <= 4) {
        return "Q{$quarter} {$year}";
    } elseif ($quarter == 5) {
        return "H1 {$year}";
    } elseif ($quarter == 6) {
        return "H2 {$year}";
    }
    
    return "Period {$quarter} {$year}";
}

// Fetch data for page
$periods = getReportingPeriods();
$sectors = getSectors();
$recentReports = getRecentReports(10);
$agencies = get_all_agencies($conn);

// Additional JavaScript files required for this page (order matters!)
$additionalScripts = [
    // External dependencies (must load first)
    'https://cdn.jsdelivr.net/gh/gitbrent/pptxgenjs/dist/pptxgen.bundle.js',
    // Report modules (must load before report-generator.js)
    APP_URL . '/assets/js/report-modules/report-ui.js',
    APP_URL . '/assets/js/report-modules/report-api.js',
    APP_URL . '/assets/js/report-modules/report-slide-styler.js',
    APP_URL . '/assets/js/report-modules/report-slide-populator.js',
    // Main report generator (depends on report modules)
    APP_URL . '/assets/js/report-generator.js',
    // Program ordering functionality
    APP_URL . '/assets/js/program-ordering.js'
];

// Include header and navigation
require_once PROJECT_ROOT_PATH . 'app/views/layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Generate Reports',
    'subtitle' => 'Create and manage sector progress reports in PPTX format',
    'variant' => 'green',
    'actions' => [
        [
            'text' => 'View All Reports',
            'url' => APP_URL . '/app/views/admin/reports/view_all_reports.php',
            'class' => 'btn-light',
            'icon' => 'fas fa-list-alt'
        ]
    ]
];

// Include the modern page header
require_once '../../layouts/page_header.php';

// JavaScript Configuration Object for ReportGenerator
$jsConfig = [
    'appUrl' => APP_URL,
    'apiEndpoints' => [
        'getPeriodPrograms' => APP_URL . '/app/api/get_period_programs.php',
        'saveReport' => APP_URL . '/app/api/save_report.php',
        'deleteReport' => APP_URL . '/app/api/delete_report.php'
    ],
    'maxProgramsPerPage' => 50,
    'defaultOrderStart' => 1,
    'debug' => false
];
?>

<!-- JavaScript Configuration -->
<script>
    window.ReportGeneratorConfig = <?php echo json_encode($jsConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>

<main class="flex-fill">
<!-- Main Page Content -->
<section class="section">
    <div class="container-fluid">
        
        <!-- Recent Reports Dashboard - Full Width -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Recent Reports
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm" id="generateReportToggle">
                                <i class="fas fa-plus me-1"></i>Generate New Report
                            </button>
                        </div>
                    </div>
                    <div class="card-body" id="recentReportsContainer">
                        <?php if (!empty($recentReports)): ?>
                            <div class="recent-reports-grid">
                                <?php foreach ($recentReports as $report): ?>
                                    <div class="report-card">
                                        <div class="report-card-body">
                                            <div class="report-info">
                                                <h6 class="report-title" title="<?php echo htmlspecialchars($report['report_name']); ?>">
                                                    <?php echo htmlspecialchars($report['report_name']); ?>
                                                </h6>
                                                <div class="report-meta">
                                                    <span class="period-badge">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo formatPeriod($report); ?>
                                                    </span>
                                                    <span class="date-badge">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo date('M j, Y g:i A', strtotime($report['generated_at'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="report-actions">
                                                <?php if (!empty($report['pptx_path'])): ?>
                                                    <a href="<?php echo APP_URL; ?>/download.php?type=report&file=<?php echo urlencode($report['pptx_path']); ?>" 
                                                       class="btn btn-success btn-sm" 
                                                       title="Download Report">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm delete-report-btn" 
                                                        title="Delete Report"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteReportModal"
                                                        data-report-id="<?php echo $report['report_id']; ?>" 
                                                        data-report-name="<?php echo htmlspecialchars($report['report_name']); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state text-center py-5">
                                <i class="fas fa-file-powerpoint fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No reports generated yet</h5>
                                <p class="text-muted mb-3">Get started by generating your first report below.</p>
                                <button type="button" class="btn btn-primary" id="generateReportToggleEmpty">
                                    <i class="fas fa-plus me-1"></i>Generate First Report
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Auto refresh indicator -->
                        <div id="refreshIndicator" class="text-center mt-2" style="display: none;">
                            <small class="text-muted">
                                <i class="fas fa-sync fa-spin"></i> Refreshing...
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Generate Report Section - Collapsible -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm" id="generateReportSection" style="display: none;">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-powerpoint me-2"></i>Generate New Report
                        </h5>
                        <button type="button" class="btn btn-outline-light btn-sm" id="closeGenerateForm">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="reportGenerationForm" novalidate>
                            <!-- Period and Sector Selection -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="periodSelect" class="form-label">
                                            <i class="fas fa-calendar-alt me-1"></i>Reporting Period
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="periodSelect" name="period_id" required>
                                            <option value="">Select Reporting Period</option>
                                            <?php foreach ($periods as $period): ?>
                                                <option value="<?php echo htmlspecialchars($period['period_id']); ?>">
                                                    <?php echo htmlspecialchars(get_period_display_name($period)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Please select a reporting period.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sectorSelect" class="form-label">
                                            <i class="fas fa-industry me-1"></i>Sector
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="sectorSelect" name="sector_id" required>
                                            <option value="">Select Sector</option>
                                            <?php foreach ($sectors as $sector): ?>
                                                <option value="<?php echo htmlspecialchars($sector['sector_id']); ?>">
                                                    <?php echo htmlspecialchars($sector['sector_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Please select a sector.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Program Selection Section -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-list-check me-1"></i>Select Programs to Include
                                    <small class="text-muted">(Optional - Multi-agency reports supported)</small>
                                </label>
                                
                                <!-- Enhanced Filter Bar with Integrated Agency Filtering -->
                                <div class="filter-bar border rounded p-3 mb-3 bg-light">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                        <input type="text" 
                                                               class="form-control" 
                                                               id="programSearchInput" 
                                                               placeholder="Search programs, numbers, agencies...">
                                                        <button class="btn btn-outline-secondary" 
                                                                type="button" 
                                                                id="clearSearchBtn"
                                                                title="Clear search">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="program-count-display">
                                                        <span class="badge bg-info" id="programCountBadge">
                                                            <i class="fas fa-list me-1"></i>
                                                            <span id="programCount">0</span> programs found
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="bulk-actions d-flex gap-2 justify-content-end">
                                                <button type="button" 
                                                        class="btn btn-outline-primary btn-sm" 
                                                        id="selectAllPrograms"
                                                        title="Select all visible programs">
                                                    <i class="fas fa-check-square me-1"></i>Select All
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-secondary btn-sm" 
                                                        id="clearAllPrograms"
                                                        title="Clear all selections">
                                                    <i class="fas fa-square me-1"></i>Clear All
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-warning btn-sm" 
                                                        id="resetAllFilters"
                                                        title="Reset all filters">
                                                    <i class="fas fa-undo me-1"></i>Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Integrated Agency Filter Row -->
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <div class="agency-filter-section">
                                                <small class="text-muted fw-bold">
                                                    <i class="fas fa-building me-1"></i>Filter by Agency:
                                                </small>
                                                <div class="mt-1" id="agencyFilterTags">
                                                    <button type="button" class="btn btn-outline-primary btn-sm me-1 mb-1 agency-filter-btn active" data-agency-id="all">
                                                        <i class="fas fa-globe me-1"></i>All Agencies
                                                    </button>
                                                    <!-- Agency filter buttons will be populated here -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <div class="alert alert-info border-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Multi-agency reports:</strong> You can select programs from different agencies for one report. 
                                        Use the agency filters above to quickly find programs, or search by agency name.
                                        If no programs are selected, all filtered programs will be included.
                                    </div>
                                </div>
                                
                                <div id="programSelector" class="program-selector">
                                    <div class="program-selector-container border rounded p-3" 
                                         style="max-height: 300px; overflow-y: auto;" 
                                         role="region" 
                                         aria-label="Program selection">
                                        <div class="alert alert-light text-center">
                                            <i class="fas fa-arrow-up me-2"></i>
                                            Please select a reporting period above to load available programs.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Report Details (Moved after program selection) -->
                            <div class="mb-3">
                                <label for="reportName" class="form-label">
                                    <i class="fas fa-file-signature me-1"></i>Report Name
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="reportName" 
                                       name="report_name" 
                                       required 
                                       maxlength="255"
                                       placeholder="e.g., Forestry Sector Report - Q2 2025">
                                <div class="invalid-feedback">Please enter a report name.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reportDescription" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>Description
                                    <small class="text-muted">(Optional)</small>
                                </label>
                                <textarea class="form-control" 
                                          id="reportDescription" 
                                          name="description" 
                                          rows="3" 
                                          maxlength="1000"
                                          placeholder="Brief description of the report content"></textarea>
                                <div class="form-text">Maximum 1000 characters</div>                            
                            <!-- Report Options -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="isPublic" 
                                           name="is_public" 
                                           value="1">
                                    <label class="form-check-label" for="isPublic">
                                        <i class="fas fa-share-alt me-1"></i>Make available to agencies
                                    </label>
                                    <div class="form-text">
                                        When enabled, agencies will be able to view and download this report.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Generate Button -->
                            <div class="d-grid">
                                <button type="submit" 
                                        id="generatePptxBtn" 
                                        class="btn btn-primary btn-lg">
                                    <i class="fas fa-file-powerpoint me-2"></i>
                                    Generate PPTX Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Generation Status Alert -->
                <div class="alert alert-info mt-3 d-none" id="generationStatus" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div>
                            <h6 class="alert-heading mb-1">Generating Report</h6>
                            <p class="mb-0" id="statusMessage">Preparing report data...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Success Alert -->
                <div class="alert alert-success mt-3 d-none" id="successMessage" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-check-circle me-2"></i>Report Generated Successfully
                    </h6>
                    <p class="mb-3">Your report has been generated and saved successfully.</p>
                    <div class="d-flex gap-2">
                        <a href="#" id="downloadLink" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-1"></i>Download PPTX
                        </a>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="generateAnotherBtn">
                            <i class="fas fa-plus me-1"></i>Generate Another
                        </button>
                    </div>
                </div>
                
                <!-- Error Alert -->
                <div class="alert alert-danger mt-3 d-none" id="errorMessage" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>Error
                    </h6>
                    <p id="errorText" class="mb-3">Something went wrong. Please try again.</p>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="retryBtn">
                        <i class="fas fa-redo me-1"></i>Try Again
                    </button>                </div>
            </div>
        </div>
    </div>
</section>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteReportModal" tabindex="-1" aria-labelledby="deleteReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteReportModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Delete Report
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the report:</p>
                <p class="fw-bold text-primary" id="reportNameToDelete"></p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. The report file will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-1"></i>Delete Report
                </button>            </div>
        </div>
    </div>
</main>

<?php
// Include footer
require_once PROJECT_ROOT_PATH . 'app/views/layouts/footer.php';
?>


