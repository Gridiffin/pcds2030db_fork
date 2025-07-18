<?php
/**
 * Admin Reports Helper Library
 *
 * Contains helper functions for report generation and management in the admin panel.
 *
 * @author PCDS Dashboard System
 * @version 1.0
 */

// Ensure this file is only loaded once
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';

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