<?php
/**
 * Agency Reports Management Functions
 * 
 * Functions for managing reports in the agency context
 */

// Define PROJECT_ROOT_PATH if not already defined
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';

/**
 * Get reports for a specific agency and period
 * @param int $agency_id Agency ID
 * @param int|null $period_id Period ID (optional)
 * @return array Array of reports
 */
function get_agency_reports($agency_id = null, $period_id = null) {
    global $conn;
    
    // If no agency_id provided, use session agency_id
    if (!$agency_id) {
        $agency_id = $_SESSION['agency_id'] ?? null;
    }
    
    if (!$agency_id) {
        return [];
    }
    
    $query = "SELECT r.report_id, r.report_name, r.description, r.pdf_path, r.pptx_path, 
                     r.generated_at, r.report_type, r.is_public,
                     rp.period_type, rp.period_number, rp.year
              FROM reports r
              LEFT JOIN reporting_periods rp ON r.period_id = rp.period_id
              WHERE (r.agency_id = ? OR r.is_public = 1)";
    
    $params = [$agency_id];
    $param_types = 'i';
    
    if ($period_id) {
        $query .= " AND r.period_id = ?";
        $params[] = $period_id;
        $param_types .= 'i';
    }
    
    $query .= " ORDER BY r.generated_at DESC";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare agency reports query: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        // Determine which file to use (prefer PPTX, fallback to PDF)
        $file_path = !empty($row['pptx_path']) ? $row['pptx_path'] : $row['pdf_path'];
        $file_type = !empty($row['pptx_path']) ? 'pptx' : 'pdf';
        
        $row['file_path'] = $file_path;
        $row['file_type'] = $file_type;
        
        $reports[] = $row;
    }
    
    return $reports;
}

/**
 * Get agency-specific public reports (avoiding conflict with core.php)
 * @return array Array of public reports with additional agency context
 */
function get_agency_public_reports() {
    global $conn;
    
    $query = "SELECT report_id, report_name, description, pdf_path, pptx_path, generated_at 
              FROM reports 
              WHERE is_public = 1 
              ORDER BY generated_at DESC";
    
    $stmt = $conn->prepare($query);
    $reports = [];
    
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Determine which file to use (prefer PPTX, fallback to PDF)
            $file_path = !empty($row['pptx_path']) ? $row['pptx_path'] : $row['pdf_path'];
            $report_type = !empty($row['pptx_path']) ? 'pptx' : 'pdf';
            
            $row['file_path'] = $file_path;
            $row['report_type'] = $report_type;
            
            $reports[] = $row;
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare public reports query: " . $conn->error);
    }
    
    return $reports;
}

/**
 * Get report statistics for agency dashboard
 * @param int|null $agency_id Agency ID
 * @return array Report statistics
 */
function get_agency_report_stats($agency_id = null) {
    global $conn;
    
    if (!$agency_id) {
        $agency_id = $_SESSION['agency_id'] ?? null;
    }
    
    if (!$agency_id) {
        return [
            'total_reports' => 0,
            'agency_reports' => 0,
            'public_reports' => 0,
            'recent_reports' => []
        ];
    }
    
    // Get total counts
    $query = "SELECT 
                (SELECT COUNT(*) FROM reports WHERE agency_id = ?) as agency_reports,
                (SELECT COUNT(*) FROM reports WHERE is_public = 1) as public_reports,
                (SELECT COUNT(*) FROM reports WHERE agency_id = ? OR is_public = 1) as total_reports";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare report stats query: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param('ii', $agency_id, $agency_id);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    
    // Get recent reports
    $recent_query = "SELECT report_name, generated_at, report_type 
                     FROM reports 
                     WHERE agency_id = ? OR is_public = 1 
                     ORDER BY generated_at DESC 
                     LIMIT 5";
    
    $stmt = $conn->prepare($recent_query);
    if ($stmt) {
        $stmt->bind_param('i', $agency_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recent_reports = [];
        while ($row = $result->fetch_assoc()) {
            $recent_reports[] = $row;
        }
        
        $stats['recent_reports'] = $recent_reports;
    }
    
    return $stats;
}
