<?php
// Admin-specific functions

require_once 'utilities.php';

/**
 * Check if current user is admin
 * @return boolean
 */
function is_admin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }
    return $_SESSION['role'] === 'admin';
}

/**
 * Check admin permission
 * @return array|null Error message if not an admin
 */
function check_admin_permission() {
    if (!is_admin()) {
        return format_error('Permission denied', 403);
    }
    return null;
}

/**
 * Generate report for a specific period
 * @param int $period_id The reporting period ID
 * @return array Report info including paths to generated files
 */
function generate_report($period_id) {
    // Only allow admins to generate reports
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    // Implementation for report generation...
    // This would use PHPPresentation and DomPDF to generate reports
    
    // Finally, store report info in database
    $pptx_path = 'reports/pptx/report_' . $period_id . '.pptx';
    $pdf_path = 'reports/pdf/report_' . $period_id . '.pdf';
    
    // Insert into reports table...
    
    return [
        'success' => true,
        'pptx_path' => $pptx_path,
        'pdf_path' => $pdf_path
    ];
}

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
 * Manage reporting periods (open/close)
 * @param int $period_id The reporting period to update
 * @param string $status New status ('open' or 'closed')
 * @return array Result of the status update operation
 */
function update_reporting_period_status($period_id, $status) {
    global $conn;
    
    // Only admin can update period status
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    if (!in_array($status, ['open', 'closed'])) {
        return ['error' => 'Invalid status value'];
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // If setting to open, first close all other periods
        if ($status == 'open') {
            $close_query = "UPDATE reporting_periods SET status = 'closed' WHERE period_id != ?";
            $stmt = $conn->prepare($close_query);
            $stmt->bind_param("i", $period_id);
            $stmt->execute();
        }
        
        // Now update the specific period
        $query = "UPDATE reporting_periods SET status = ? WHERE period_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $period_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Get the period details for the response
        $period_query = "SELECT year, quarter FROM reporting_periods WHERE period_id = ?";
        $stmt = $conn->prepare($period_query);
        $stmt->bind_param("i", $period_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $period = $result->fetch_assoc();
        
        return [
            'success' => true,
            'message' => "Period Q{$period['quarter']}-{$period['year']} has been " . 
                        ($status == 'open' ? "opened" : "closed") . " for submissions.",
            'period_id' => $period_id,
            'new_status' => $status
        ];
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        return ['error' => 'Failed to update period status: ' . $e->getMessage()];
    }
}

/**
 * Add a new reporting period
 * @param int $year Year
 * @param int $quarter Quarter (1-4)
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @param string $status Status (open/closed)
 * @return array Result of operation
 */
function add_reporting_period($year, $quarter, $start_date, $end_date, $status = 'open') {
    global $conn;
    
    // Validate inputs
    if (!$year || !$quarter || !$start_date || !$end_date) {
        return ['error' => 'All fields are required'];
    }
    
    if ($quarter < 1 || $quarter > 4) {
        return ['error' => 'Quarter must be between 1 and 4'];
    }
    
    if (strtotime($start_date) > strtotime($end_date)) {
        return ['error' => 'End date cannot be before start date'];
    }
    
    // Check for standard dates
    $is_standard = is_standard_quarter_date($year, $quarter, $start_date, $end_date);
    
    // Check if period already exists
    $check_query = "SELECT * FROM reporting_periods WHERE year = ? AND quarter = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $year, $quarter);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['error' => "Period Q{$quarter}-{$year} already exists"];
    }
    
    // Check for date range overlap with other periods
    $overlap_query = "SELECT * FROM reporting_periods WHERE 
                      (? BETWEEN start_date AND end_date) OR 
                      (? BETWEEN start_date AND end_date) OR 
                      (start_date BETWEEN ? AND ?) OR 
                      (end_date BETWEEN ? AND ?)";
                      
    $stmt = $conn->prepare($overlap_query);
    $stmt->bind_param("ssssss", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
    $stmt->execute();
    $overlap_result = $stmt->get_result();
    
    if ($overlap_result->num_rows > 0) {
        return ['error' => 'Date range overlaps with existing period(s)'];
    }
    
    // Insert new period
    $insert_query = "INSERT INTO reporting_periods (year, quarter, start_date, end_date, status, is_standard_dates) 
                     VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iisssi", $year, $quarter, $start_date, $end_date, $status, $is_standard);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => "Reporting period Q{$quarter}-{$year} added successfully" .
                        (!$is_standard ? " (using custom date range)" : "")
        ];
    } else {
        return ['error' => 'Failed to add reporting period: ' . $stmt->error];
    }
}

/**
 * Update an existing reporting period
 * @param int $period_id Period ID
 * @param int $year Year
 * @param int $quarter Quarter (1-4)
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @param string $status Status (open/closed)
 * @return array Result of operation
 */
function update_reporting_period($period_id, $year, $quarter, $start_date, $end_date, $status) {
    global $conn;
    
    // Validate inputs
    if (!$period_id || !$year || !$quarter || !$start_date || !$end_date) {
        return ['error' => 'All fields are required'];
    }
    
    if ($quarter < 1 || $quarter > 4) {
        return ['error' => 'Quarter must be between 1 and 4'];
    }
    
    if (strtotime($start_date) > strtotime($end_date)) {
        return ['error' => 'End date cannot be before start date'];
    }
    
    // Check for standard dates
    $is_standard = is_standard_quarter_date($year, $quarter, $start_date, $end_date);
    
    // Check if another period already exists with same year/quarter
    $check_query = "SELECT * FROM reporting_periods WHERE year = ? AND quarter = ? AND period_id != ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("iii", $year, $quarter, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['error' => "Another period with Q{$quarter}-{$year} already exists"];
    }
    
    // Check for date range overlap with other periods
    $overlap_query = "SELECT * FROM reporting_periods WHERE 
                      ((? BETWEEN start_date AND end_date) OR 
                      (? BETWEEN start_date AND end_date) OR 
                      (start_date BETWEEN ? AND ?) OR 
                      (end_date BETWEEN ? AND ?)) AND 
                      period_id != ?";
                      
    $stmt = $conn->prepare($overlap_query);
    $stmt->bind_param("ssssssi", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $period_id);
    $stmt->execute();
    $overlap_result = $stmt->get_result();
    
    if ($overlap_result->num_rows > 0) {
        return ['error' => 'Date range overlaps with existing period(s)'];
    }
    
    // Update period
    $update_query = "UPDATE reporting_periods 
                     SET year = ?, quarter = ?, start_date = ?, end_date = ?, status = ?, is_standard_dates = ? 
                     WHERE period_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("iisssii", $year, $quarter, $start_date, $end_date, $status, $is_standard, $period_id);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => "Reporting period Q{$quarter}-{$year} updated successfully" .
                        (!$is_standard ? " (using custom date range)" : "")
        ];
    } else {
        return ['error' => 'Failed to update reporting period: ' . $stmt->error];
    }
}

/**
 * Delete a reporting period
 * @param int $period_id Period ID to delete
 * @return array Result of the operation
 */
function delete_reporting_period($period_id) {
    global $conn;
    
    // Only admin can delete periods
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    // Validate period ID
    $period_id = intval($period_id);
    if (!$period_id) {
        return ['error' => 'Invalid period ID'];
    }
    
    // Check if period exists and get its details for the response message
    $check_query = "SELECT year, quarter FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['error' => 'Reporting period not found'];
    }
    
    $period = $result->fetch_assoc();
    
    // Check if this period has any associated submissions
    $submission_check = "SELECT COUNT(*) as count FROM program_submissions WHERE period_id = ?";
    $stmt = $conn->prepare($submission_check);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $submission_result = $stmt->get_result();
    $submission_count = $submission_result->fetch_assoc()['count'];
    
    if ($submission_count > 0) {
        return [
            'error' => "Cannot delete period Q{$period['quarter']}-{$period['year']} because it has {$submission_count} associated submissions. Delete the submissions first or contact system administrator."
        ];
    }
    
    // Delete the reporting period
    $delete_query = "DELETE FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $period_id);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => "Reporting period Q{$period['quarter']}-{$period['year']} deleted successfully"
        ];
    } else {
        return ['error' => 'Failed to delete reporting period: ' . $stmt->error];
    }
}

/**
 * Check if dates match standard quarter dates
 * @param int $year Year
 * @param int $quarter Quarter (1-4)
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @return bool True if dates match standard quarter dates
 */
function is_standard_quarter_date($year, $quarter, $start_date, $end_date) {
    $standard_dates = [
        1 => [
            'start' => "$year-01-01",
            'end' => "$year-03-31"
        ],
        2 => [
            'start' => "$year-04-01",
            'end' => "$year-06-30"
        ],
        3 => [
            'start' => "$year-07-01",
            'end' => "$year-09-30"
        ],
        4 => [
            'start' => "$year-10-01",
            'end' => "$year-12-31"
        ]
    ];
    
    $quarter = intval($quarter);
    if (!isset($standard_dates[$quarter])) {
        return false;
    }
    
    return ($start_date === $standard_dates[$quarter]['start'] && 
            $end_date === $standard_dates[$quarter]['end']);
}

/**
 * Admin Functions
 * 
 * Functions specific to admin users
 */

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
 * Admin Functions
 * 
 * Functions specifically for admin use throughout the application
 */

/**
 * Get all users in the system
 * 
 * @return array List of all users with their details
 */
function get_all_users() {
    global $conn;
    
    $query = "SELECT u.*, s.sector_name 
              FROM users u 
              LEFT JOIN sectors s ON u.sector_id = s.sector_id 
              ORDER BY u.role = 'admin' DESC, u.username ASC";
              
    $result = $conn->query($query);
    
    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
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

/**
 * Get all metrics with JSON-based storage
 *
 * @return array List of metrics
 */
function get_all_metrics_data() {
    global $conn;
    
    $query = "SELECT smd.metric_id, smd.sector_id, smd.table_name, s.sector_name 
              FROM sector_metrics_data smd
              LEFT JOIN sectors s ON smd.sector_id = s.sector_id
              WHERE smd.is_draft = 0
              ORDER BY smd.metric_id DESC";
    
    $result = $conn->query($query);
    
    $metrics = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $metrics[] = $row;
        }
    }
    
    return $metrics;
}

/**
 * Add a new user to the system
 * 
 * @param array $data Post data from add user form
 * @return array Result of the operation
 */
function add_user($data) {
    global $conn;
    
    // Validate required fields
    $required_fields = ['username', 'role', 'password', 'confirm_password'];
    
    // Add agency-specific required fields
    if (isset($data['role']) && $data['role'] === 'agency') {
        $required_fields[] = 'agency_name';
        $required_fields[] = 'sector_id';
    }
    
    // Check for missing required fields
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            return ['error' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
        }
    }
    
    // Validate username uniqueness
    $username = trim($data['username']);
    $check_query = "SELECT user_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['error' => "Username '$username' already exists"];
    }
    
    // Validate password
    $password = $data['password'];
    $confirm_password = $data['confirm_password'];
    
    if (strlen($password) < 8) {
        return ['error' => 'Password must be at least 8 characters long'];
    }
    
    if ($password !== $confirm_password) {
        return ['error' => 'Passwords do not match'];
    }
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Prepare basic user data
        $role = $data['role'];
        $is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;
        
        // Set agency-specific fields
        $agency_name = null;
        $sector_id = null;
        
        if ($role === 'agency') {
            $agency_name = trim($data['agency_name']);
            $sector_id = intval($data['sector_id']);
            
            // Verify sector exists
            $sector_check = "SELECT sector_id FROM sectors WHERE sector_id = ?";
            $stmt = $conn->prepare($sector_check);
            $stmt->bind_param("i", $sector_id);
            $stmt->execute();
            $sector_result = $stmt->get_result();
            
            if ($sector_result->num_rows === 0) {
                $conn->rollback();
                return ['error' => 'Invalid sector selected'];
            }
        }
        
        // Insert user record
        $insert_query = "INSERT INTO users (username, password, agency_name, role, sector_id, is_active) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssii", $username, $hashed_password, $agency_name, $role, $sector_id, $is_active);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $user_id = $conn->insert_id;
        
        // Commit transaction
        $conn->commit();
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'message' => "User '$username' successfully added"
        ];
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update an existing user
 * 
 * @param array $data Post data from edit user form
 * @return array Result of the operation
 */
function update_user($data) {
    global $conn;
    
    // Validate required fields
    if (!isset($data['user_id']) || !intval($data['user_id'])) {
        return ['error' => 'Invalid user ID'];
    }
    
    $user_id = intval($data['user_id']);
    
    // Validate required fields (only if they are present in the data)
    // This allows for partial updates (e.g. just updating is_active status)
    if (isset($data['username']) && isset($data['role'])) {
        $required_fields = ['username', 'role'];
        
        // Add agency-specific required fields
        if (isset($data['role']) && $data['role'] === 'agency') {
            $required_fields[] = 'agency_name';
            $required_fields[] = 'sector_id';
        }
        
        // Check for missing required fields
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                return ['error' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
            }
        }
    }
    
    // Check if user exists
    $user_check = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_check);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['error' => 'User not found'];
    }
    
    $existing_user = $result->fetch_assoc();
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Prepare data for update - only update fields that are provided
        $update_fields = [];
        $bind_params = [];
        $param_types = "";
        
        // Handle username if provided
        if (isset($data['username'])) {
            $username = trim($data['username']);
            
            // Validate username uniqueness (only if username changed)
            if ($username !== $existing_user['username']) {
                $check_query = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
                $stmt = $conn->prepare($check_query);
                $stmt->bind_param("si", $username, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $conn->rollback();
                    return ['error' => "Username '$username' already exists"];
                }
            }
            
            $update_fields[] = "username = ?";
            $bind_params[] = $username;
            $param_types .= "s";
        }
        
        // Handle role if provided
        if (isset($data['role'])) {
            $role = $data['role'];
            $update_fields[] = "role = ?";
            $bind_params[] = $role;
            $param_types .= "s";
        }
        
        // Handle agency_name if provided
        if (isset($data['agency_name'])) {
            $agency_name = trim($data['agency_name']);
            $update_fields[] = "agency_name = ?";
            $bind_params[] = $agency_name;
            $param_types .= "s";
        } else {
            // Reset agency_name if role is not agency
            if (isset($data['role']) && $data['role'] !== 'agency') {
                $update_fields[] = "agency_name = NULL";
            }
        }
        
        // Handle sector_id if provided
        if (isset($data['sector_id'])) {
            $sector_id = !empty($data['sector_id']) ? intval($data['sector_id']) : null;
            $update_fields[] = "sector_id = ?";
            $bind_params[] = $sector_id;
            $param_types .= "i";
            
            // Verify sector exists if provided
            if ($sector_id) {
                $sector_check = "SELECT sector_id FROM sectors WHERE sector_id = ?";
                $stmt = $conn->prepare($sector_check);
                $stmt->bind_param("i", $sector_id);
                $stmt->execute();
                $sector_result = $stmt->get_result();
                
                if ($sector_result->num_rows === 0) {
                    $conn->rollback();
                    return ['error' => 'Invalid sector selected'];
                }
            }
        } else {
            // Reset sector_id if role is not agency
            if (isset($data['role']) && $data['role'] !== 'agency') {
                $update_fields[] = "sector_id = NULL";
            }
        }
        
        // Handle password if provided
        $password_updated = false;
        if (!empty($data['password']) || !empty($data['confirm_password'])) {
            // Both fields must be provided
            if (empty($data['password']) || empty($data['confirm_password'])) {
                $conn->rollback();
                return ['error' => 'Both password and confirm password are required to change password'];
            }
            
            $password = $data['password'];
            $confirm_password = $data['confirm_password'];
            
            if (strlen($password) < 8) {
                $conn->rollback();
                return ['error' => 'Password must be at least 8 characters long'];
            }
            
            if ($password !== $confirm_password) {
                $conn->rollback();
                return ['error' => 'Passwords do not match'];
            }
            
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_fields[] = "password = ?";
            $bind_params[] = $hashed_password;
            $param_types .= "s";
            $password_updated = true;
        }
        
        // Handle is_active - checkboxes aren't sent when unchecked, so we need special handling
        if (array_key_exists('is_active', $data)) {
            // Convert to integer (1 for active, 0 for inactive)
            $is_active = isset($data['is_active']) && ($data['is_active'] === '1' || $data['is_active'] === 1 || $data['is_active'] === true) ? 1 : 0;
            $update_fields[] = "is_active = ?";
            $bind_params[] = $is_active;
            $param_types .= "i";
        }
        
        // If no fields to update, return success without doing anything
        if (empty($update_fields)) {
            $conn->rollback();
            return ['success' => true, 'message' => 'No changes made'];
        }
        
        // Update user record
        $update_query = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE user_id = ?";
        $bind_params[] = $user_id;
        $param_types .= "i";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param($param_types, ...$bind_params);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'message' => "User successfully updated"
        ];
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Delete a user
 * 
 * @param int $user_id User ID to delete
 * @return array Result of the operation
 */
function delete_user($user_id) {
    global $conn;
    
    $user_id = intval($user_id);
    
    // Verify user exists
    $check_query = "SELECT username, role FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['error' => 'User not found'];
    }
    
    $user = $result->fetch_assoc();
    
    // Check if user has any programs
    $program_check = "SELECT COUNT(*) as count FROM programs WHERE owner_agency_id = ?";
    $stmt = $conn->prepare($program_check);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $program_result = $stmt->get_result();
    $program_count = $program_result->fetch_assoc()['count'];
    
    if ($program_count > 0) {
        return [
            'error' => "Cannot delete user '{$user['username']}' because they own $program_count program(s). Reassign these programs first."
        ];
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete the user
        $delete_query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        return [
            'success' => true,
            'message' => "User '{$user['username']}' successfully deleted"
        ];
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}
?>
