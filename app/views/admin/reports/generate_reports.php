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
        $query = "SELECT period_id, period_type, period_number, year, status 
                  FROM reporting_periods 
                  ORDER BY year DESC, period_number DESC";
        
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
    // Since sectors table has been removed, return a default sector
    // This maintains backward compatibility while the system transitions
    return [
        [
            'sector_id' => 1,
            'sector_name' => 'Forestry Sector',
            'description' => 'Forestry and environmental management programs'
        ]
    ];
}

/**
 * Check if a report should display the "NEW" badge
 * @param array $report Report data
 * @return bool True if report should show NEW badge
 */
function shouldShowNewBadge($report) {
    if (!$report || !isset($report['generated_at'])) {
        return false;
    }
    
    // Show badge for reports generated in the last 10 minutes
    $generatedTime = strtotime($report['generated_at']);
    $currentTime = time();
    $tenMinutesAgo = $currentTime - (10 * 60); // 10 minutes in seconds
    
    return $generatedTime > $tenMinutesAgo;
}

/**
 * Get recently generated reports directly from database
 * @param int $limit Number of reports to retrieve
 * @return array Array of recent reports
 */
function getRecentReports($limit = 10) {
    global $conn;
    
    $query = "SELECT r.report_id, r.report_name, r.pptx_path, r.generated_at, r.is_public,
                     rp.period_type, rp.period_number, rp.year, u.username
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
    if (!$report || !isset($report['period_type'], $report['period_number'], $report['year'])) {
        return 'Unknown';
    }
    
    return get_period_display_name($report);
}

// Fetch data for page
$periods = getReportingPeriods();
// System is configured for Forestry Sector only (sector_id = 1)
// Note: $agencies variable was removed as it's not used in this file

// Additional CSS files for this page
$additionalStyles = [
    asset_url('css/admin', 'reports-pagination.css')
];

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
    APP_URL . '/assets/js/program-ordering.js',
    // Pagination functionality (must load after other modules)
    APP_URL . '/assets/js/admin/reports-pagination.js'
];

// Set up variables for base layout
$cssBundle = 'main'; // Use main CSS bundle which includes all necessary styles
$jsBundle = 'admin-reports';
$additionalStyles = [
    // Add admin-specific CSS files that may not be in the main bundle
    APP_URL . '/assets/css/admin/admin-common.css',
    APP_URL . '/assets/css/admin/reports.css',
    APP_URL . '/assets/css/custom/admin.css'
];

// Configure modern page header
$header_config = [
    'title' => 'Generate Reports',
    'subtitle' => 'Create and manage sector progress reports in PPTX format',
    'variant' => 'green',
    'actions' => []
];

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

// Set content file that contains the main page content
$contentFile = __DIR__ . '/partials/generate_reports_content.php';

include PROJECT_ROOT_PATH . '/app/views/layouts/base.php';
?>