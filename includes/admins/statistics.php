<?php
/**
 * Admin Statistics Functions
 * 
 * Contains functions for retrieving statistics and data for admin dashboards
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';

/**
 * Get dashboard statistics for admin overview
 * @return array Statistics for admin dashboard
 */
function get_admin_dashboard_stats() {
    global $conn;
    
    // Only admin should access this
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    // Initialize stats array
    $stats = [
        'total_agencies' => 0,
        'total_programs' => 0,
        'submissions_complete' => 0,
        'submissions_pending' => 0,
        'program_status' => [],
        'sector_programs' => []
    ];
    
    // Get current period
    $current_period = get_current_reporting_period();
    $period_id = $current_period['period_id'] ?? null;
    
    // Get counts
    $query = "SELECT 
                (SELECT COUNT(*) FROM users WHERE role = 'agency') AS total_agencies,
                (SELECT COUNT(*) FROM programs) AS total_programs";
    
    $result = $conn->query($query);
    $counts = $result->fetch_assoc();
    
    $stats['total_agencies'] = $counts['total_agencies'];
    $stats['total_programs'] = $counts['total_programs'];
    
    // If we have an active period, get submission status
    if ($period_id) {
        // Get program submission counts
        $query = "SELECT 
                    u.user_id,
                    (SELECT COUNT(*) FROM programs p WHERE p.owner_agency_id = u.user_id) AS agency_programs,
                    (SELECT COUNT(*) FROM program_submissions ps 
                     JOIN programs p ON ps.program_id = p.program_id 
                     WHERE p.owner_agency_id = u.user_id AND ps.period_id = ?) AS submitted_programs
                  FROM users u
                  WHERE u.role = 'agency'";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $period_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $completed = 0;
        $pending = 0;
        
        while ($row = $result->fetch_assoc()) {
            if ($row['agency_programs'] > 0) {
                if ($row['submitted_programs'] >= $row['agency_programs']) {
                    $completed++;
                } else {
                    $pending++;
                }
            }
        }
        
        $stats['submissions_complete'] = $completed;
        $stats['submissions_pending'] = $pending;
        
        // Get program status distribution
        $query = "SELECT ps.status, COUNT(*) as count
                  FROM program_submissions ps 
                  WHERE ps.period_id = ?
                  GROUP BY ps.status";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $period_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $status_data = [
            'labels' => [],
            'data' => [],
            'backgroundColor' => [
                '#28a745', // on-track (green)
                '#ffc107', // delayed (yellow)
                '#17a2b8', // completed (blue)
                '#6c757d'  // not-started (gray)
            ]
        ];
        
        while ($row = $result->fetch_assoc()) {
            $status_data['labels'][] = ucfirst($row['status']);
            $status_data['data'][] = $row['count'];
        }
        
        $stats['program_status'] = $status_data;
    }
    
    // Get programs by sector
    $query = "SELECT s.sector_name, COUNT(p.program_id) as program_count
              FROM sectors s
              LEFT JOIN programs p ON s.sector_id = p.sector_id
              GROUP BY s.sector_id
              ORDER BY program_count DESC";
    
    $result = $conn->query($query);
    
    $sector_data = [
        'labels' => [],
        'data' => [],
        'backgroundColor' => [
            '#8591a4', // Primary color
            '#A49885', // Secondary color
            '#607b9b', // Variation of primary
            '#b3a996', // Variation of secondary
            '#4f616f'  // Another variation
        ]
    ];
    
    while ($row = $result->fetch_assoc()) {
        $sector_data['labels'][] = $row['sector_name'];
        $sector_data['data'][] = $row['program_count'];
    }
    
    $stats['sector_programs'] = $sector_data;
    
    return $stats;
}

/**
 * Get submission statistics for a specific reporting period
 * 
 * @param int $period_id - The ID of the reporting period
 * @return array - Array containing submission statistics
 */
function get_period_submission_stats($period_id) {
    global $conn;
    
    // Initialize stats array
    $stats = [
        'agencies_reported' => 0,
        'total_agencies' => 0,
        'on_track_programs' => 0,
        'delayed_programs' => 0,
        'total_programs' => 0,
        'completion_percentage' => 0
    ];
    
    // Get total agencies
    $query = "SELECT COUNT(*) as total FROM users WHERE role = 'agency' AND is_active = 1";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_agencies'] = $row['total'];
    }
    
    // Get agencies that have reported
    $query = "SELECT COUNT(DISTINCT submitted_by) as reported 
              FROM program_submissions 
              WHERE period_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $stats['agencies_reported'] = $row['reported'];
    }
    
    // Get program status counts
    $query = "SELECT status, COUNT(*) as count 
              FROM program_submissions 
              WHERE period_id = ? 
              GROUP BY status";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($result && $row = $result->fetch_assoc()) {
        $status = $row['status'];
        
        if (in_array($status, ['on-track', 'on-track-yearly', 'completed', 'target-achieved'])) {
            $stats['on_track_programs'] += $row['count'];
        } elseif (in_array($status, ['delayed', 'severe-delay'])) {
            $stats['delayed_programs'] += $row['count'];
        }
    }
    
    // Get total programs
    $query = "SELECT COUNT(*) as total FROM programs";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_programs'] = $row['total'];
    }
    
    // Calculate completion percentage
    if ($stats['total_agencies'] > 0) {
        $stats['completion_percentage'] = round(($stats['agencies_reported'] / $stats['total_agencies']) * 100);
    }
    
    return $stats;
}

/**
 * Get all programs with detailed information for admin display
 * 
 * @param int $period_id Optional period ID to filter submissions
 * @param array $filters Optional filters (status, sector_id, agency_id, search)
 * @return array List of all programs with their details and status
 */
function get_admin_programs_list($period_id = null, $filters = []) {
    global $conn;
    
    $conditions = [];
    $params = [];
    $param_types = "";
    
    // Get period details if period_id is provided
    $period_info = null;
    if ($period_id) {
        $period_query = "SELECT period_id, start_date, end_date FROM reporting_periods WHERE period_id = ?";
        $period_stmt = $conn->prepare($period_query);
        $period_stmt->bind_param("i", $period_id);
        $period_stmt->execute();
        $period_result = $period_stmt->get_result();
        
        if ($period_result->num_rows > 0) {
            $period_info = $period_result->fetch_assoc();
        }
    }
    
    // Base query
    $sql = "SELECT p.*, u.agency_name, s.sector_name, ps.status, ps.is_draft, ps.submission_date, ps.updated_at 
            FROM programs p 
            LEFT JOIN users u ON p.owner_agency_id = u.user_id 
            LEFT JOIN sectors s ON p.sector_id = s.sector_id 
            LEFT JOIN (
                SELECT * FROM program_submissions 
                WHERE " . ($period_id ? "period_id = ?" : "period_id = (SELECT MAX(period_id) FROM reporting_periods WHERE status = 'open')") . "
            ) ps ON p.program_id = ps.program_id";
            
    // Add period_id parameter if provided
    if ($period_id) {
        $params[] = $period_id;
        $param_types .= "i";
        
        // Get the period when each program was created by finding the first reporting period
        // that contains the program's creation date
        if ($period_info) {
            $conditions[] = "p.created_at BETWEEN ? AND ?";
            $params[] = $period_info['start_date'] . ' 00:00:00';
            $params[] = $period_info['end_date'] . ' 23:59:59';
            $param_types .= "ss";
        }
    }
    
    // Filter by is_assigned status
    if (isset($filters['is_assigned'])) {
        $conditions[] = "p.is_assigned = ?";
        $params[] = $filters['is_assigned'] ? 1 : 0;
        $param_types .= "i";
    }
    
    // Add other existing filters
    if (isset($filters['status'])) {
        $conditions[] = "ps.status = ?";
        $params[] = $filters['status'];
        $param_types .= "s";
    }
    
    if (isset($filters['sector_id'])) {
        $conditions[] = "p.sector_id = ?";
        $params[] = $filters['sector_id'];
        $param_types .= "i";
    }
    
    if (isset($filters['agency_id'])) {
        $conditions[] = "p.owner_agency_id = ?";
        $params[] = $filters['agency_id'];
        $param_types .= "i";
    }
    
    if (isset($filters['search'])) {
        $search_term = '%' . $filters['search'] . '%';
        $conditions[] = "(p.program_name LIKE ? OR p.description LIKE ?)";
        $params[] = $search_term;
        $params[] = $search_term;
        $param_types .= "ss";
    }
    
    // Combine conditions
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    // Group and order
    $sql .= " GROUP BY p.program_id ORDER BY p.created_at DESC";
    
    // Prepare and execute statement
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    return $programs;
}

/**
 * Get sector data for a specific reporting period
 *
 * @param int $period_id The reporting period ID
 * @return array Sector data including name, agency count, program count, and submission percentage
 */
function get_sector_data_for_period($period_id) {
    global $conn;
    
    $sector_data = array();
    
    $sql = "SELECT 
                s.sector_id,
                s.sector_name,
                COUNT(DISTINCT u.user_id) as agency_count,
                COUNT(DISTINCT p.program_id) as program_count,
                IFNULL(ROUND((COUNT(DISTINCT ps.submission_id) / 
                    NULLIF(COUNT(DISTINCT p.program_id), 0)) * 100, 0), 0) as submission_pct
            FROM 
                sectors s
                LEFT JOIN users u ON s.sector_id = u.sector_id AND u.role = 'agency'
                LEFT JOIN programs p ON u.user_id = p.owner_agency_id
                LEFT JOIN program_submissions ps ON p.program_id = ps.program_id AND ps.period_id = ?
            GROUP BY 
                s.sector_id, s.sector_name
            ORDER BY 
                s.sector_name ASC";
                
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $sector_data[] = $row;
    }
    
    return $sector_data;
}

/**
 * Get recent submissions for the specified period
 * 
 * @param int|null $period_id The reporting period ID
 * @param int $limit Maximum number of submissions to return
 * @return array List of recent submissions
 */
function get_recent_submissions($period_id = null, $limit = 5) {
    global $conn;
    
    $query = "SELECT ps.*, 
              u.agency_name, 
              p.program_name 
              FROM program_submissions ps
              JOIN users u ON ps.submitted_by = u.user_id
              JOIN programs p ON ps.program_id = p.program_id
              WHERE 1=1";
    
    if ($period_id) {
        $query .= " AND ps.period_id = ?";
        $params = [$period_id];
    } else {
        $params = [];
    }
    
    $query .= " ORDER BY ps.submission_date DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return [];
    }
    
    if (!empty($params)) {
        $types = str_repeat('i', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $submissions = [];
    
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    return $submissions;
}

/**
 * Get all sectors
 * 
 * @return array List of all sectors
 */
function get_all_sectors() {
    global $conn;
    
    $query = "SELECT sector_id, sector_name FROM sectors ORDER BY sector_name";
    $result = $conn->query($query);
    
    $sectors = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $sectors[] = $row;
        }
    }
    
    return $sectors;
}
?>