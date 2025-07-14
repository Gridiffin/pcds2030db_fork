<?php
/**
 * Agency Core Functions
 * 
 * Core functions for agency-related operations
 */

require_once dirname(__DIR__) . '/session.php';

/**
 * Check if current user is an agency
 * @return boolean True if user is an agency, false otherwise
 */
function is_agency() {
    if (!is_logged_in() || !isset($_SESSION['role'])) {
        return false;
    }
    return $_SESSION['role'] === 'agency' || $_SESSION['role'] === 'focal';
}

/**
 * Redirect if user is not an agency
 * @param string $redirect_url URL to redirect to
 */
function require_agency($redirect_url = 'login.php') {
    if (!is_agency()) {
        header("Location: $redirect_url");
        exit;
    }
}

/**
 * Get current agency ID
 * @return int|null Agency ID or null if not an agency
 */
function get_agency_id() {
    return is_agency() ? ($_SESSION['agency_id'] ?? null) : null;
}

/**
 * Get all public reports available to agencies
 * @return array Array of public reports
 */
function get_public_reports() {
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
?>
