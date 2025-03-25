<?php
// Admin-specific functions

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
 * @return boolean Success status
 */
function update_reporting_period_status($period_id, $status) {
    // Only admin can update period status
    if (!is_admin()) {
        return false;
    }
    
    // Implementation to update the period status...
    
    return true;
}

/**
 * Create a new program (admin can create for any sector)
 * @param string $program_name Program name
 * @param string $description Program description
 * @param int $sector_id The sector ID this program belongs to
 * @param int $owner_agency_id (Optional) The agency that will own this program
 * @param string $start_date (Optional) Start date in 'YYYY-MM-DD' format
 * @param string $end_date (Optional) End date in 'YYYY-MM-DD' format
 * @return array Result of program creation
 */
function admin_create_program($program_name, $description, $sector_id, $owner_agency_id = null, $start_date = null, $end_date = null) {
    global $conn;
    
    // Only admin can create programs for any sector
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    // If owner_agency_id is not provided, set the current admin user as owner
    if ($owner_agency_id === null) {
        $owner_agency_id = $_SESSION['user_id'];
    } 
    // If provided, verify the owner_agency_id exists and is an agency
    else {
        // Check if the provided owner_agency_id is valid
        $query = "SELECT user_id, role, sector_id FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $owner_agency_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['error' => 'Invalid owner agency ID'];
        }
        
        $agency = $result->fetch_assoc();
        
        // If the selected owner is an agency, verify they belong to the correct sector
        if ($agency['role'] === 'agency' && $agency['sector_id'] != $sector_id) {
            return ['error' => 'Agency does not belong to the selected sector'];
        }
    }
    
    // Verify the sector exists
    $query = "SELECT sector_id FROM sectors WHERE sector_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        return ['error' => 'Invalid sector ID'];
    }
    
    // Insert the new program
    $query = "INSERT INTO programs (program_name, description, owner_agency_id, sector_id, start_date, end_date) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiiss", $program_name, $description, $owner_agency_id, $sector_id, $start_date, $end_date);
    
    if ($stmt->execute()) {
        $program_id = $stmt->insert_id;
        return [
            'success' => true,
            'program_id' => $program_id,
            'message' => 'Program created successfully'
        ];
    } else {
        return ['error' => 'Failed to create program: ' . $stmt->error];
    }
}

/**
 * Get all sectors for admin program creation
 * @return array List of all sectors
 */
function get_all_sectors() {
    global $conn;
    
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    $query = "SELECT sector_id, sector_name, description FROM sectors ORDER BY sector_name";
    $result = $conn->query($query);
    
    $sectors = [];
    while ($row = $result->fetch_assoc()) {
        $sectors[] = $row;
    }
    
    return $sectors;
}

/**
 * Get all agencies for admin program assignment
 * @param int $sector_id (Optional) Filter by sector ID
 * @return array List of agencies
 */
function get_agencies_for_assignment($sector_id = null) {
    global $conn;
    
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    $query = "SELECT user_id, agency_name, sector_id FROM users WHERE role = 'agency'";
    
    if ($sector_id !== null) {
        $query .= " AND sector_id = " . intval($sector_id);
    }
    
    $query .= " ORDER BY agency_name";
    $result = $conn->query($query);
    
    $agencies = [];
    while ($row = $result->fetch_assoc()) {
        $agencies[] = $row;
    }
    
    return $agencies;
}

/**
 * Get current reporting period
 * @return array|null Current active reporting period or null if none
 */
function get_current_reporting_period() {
    global $conn;
    
    $query = "SELECT * FROM reporting_periods WHERE status = 'open' ORDER BY year DESC, quarter DESC LIMIT 1";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get agency submission status for a reporting period
 * @param int $period_id Reporting period ID
 * @return array List of agencies with submission status
 */
function get_agency_submission_status($period_id) {
    global $conn;
    
    if (!$period_id) {
        return [];
    }
    
    $query = "SELECT u.user_id, u.agency_name, s.sector_name,
                (SELECT COUNT(*) FROM programs p WHERE p.owner_agency_id = u.user_id) AS total_programs,
                (SELECT COUNT(*) FROM program_submissions ps 
                 JOIN programs p ON ps.program_id = p.program_id 
                 WHERE p.owner_agency_id = u.user_id AND ps.period_id = ?) AS programs_submitted,
                (SELECT COUNT(*) FROM sector_metrics_definition smd WHERE smd.sector_id = u.sector_id) AS total_metrics,
                (SELECT COUNT(*) FROM sector_metric_values smv 
                 JOIN sector_metrics_definition smd ON smv.metric_id = smd.metric_id
                 WHERE smv.agency_id = u.user_id AND smv.period_id = ?) AS metrics_submitted
              FROM users u
              JOIN sectors s ON u.sector_id = s.sector_id
              WHERE u.role = 'agency'
              ORDER BY u.agency_name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $period_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $agencies = [];
    while ($row = $result->fetch_assoc()) {
        $agencies[] = $row;
    }
    
    return $agencies;
}

/**
 * Get recent programs
 * @param int $limit Number of programs to retrieve
 * @return array List of recent programs
 */
function get_recent_programs($limit = 5) {
    global $conn;
    
    $query = "SELECT p.program_id, p.program_name, u.agency_name, s.sector_name,
                (SELECT ps.status FROM program_submissions ps 
                 WHERE ps.program_id = p.program_id 
                 ORDER BY ps.submission_id DESC LIMIT 1) AS status
              FROM programs p
              JOIN users u ON p.owner_agency_id = u.user_id
              JOIN sectors s ON p.sector_id = s.sector_id
              ORDER BY p.created_at DESC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    return $programs;
}
?>
