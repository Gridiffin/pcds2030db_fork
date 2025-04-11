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
 * Get submission statistics for a specific reporting period
 * 
 * @param int $period_id Reporting period ID
 * @return array Statistics about agency submissions for the period
 */
function get_period_submission_stats($period_id) {
    global $conn;
    
    if (!$period_id) {
        $current_period = get_current_reporting_period();
        $period_id = $current_period ? $current_period['period_id'] : null;
    }
    
    if (!$period_id) {
        return [
            'agencies_reported' => 0,
            'total_agencies' => 0,
            'on_track_programs' => 0,
            'delayed_programs' => 0,
            'completed_programs' => 0,
            'not_started_programs' => 0,
            'total_programs' => 0,
            'completion_percentage' => 0
        ];
    }
    
    // Get total agencies count
    $agencies_query = "SELECT COUNT(*) as total FROM users WHERE role = 'agency'";
    $agencies_result = $conn->query($agencies_query);
    $total_agencies = $agencies_result->fetch_assoc()['total'] ?? 0;
    
    // Get agencies that submitted data for this period
    $reported_query = "SELECT COUNT(DISTINCT u.user_id) as reported 
                      FROM users u
                      INNER JOIN program_submissions ps 
                      ON ps.submitted_by = u.user_id
                      WHERE ps.period_id = ? AND u.role = 'agency'";
    $stmt = $conn->prepare($reported_query);
    $stmt->bind_param('i', $period_id);
    $stmt->execute();
    $reported_result = $stmt->get_result();
    $agencies_reported = $reported_result->fetch_assoc()['reported'] ?? 0;
    
    // Get program status statistics
    $status_query = "SELECT status, COUNT(*) as count
                   FROM program_submissions
                   WHERE period_id = ?
                   GROUP BY status";
    $stmt = $conn->prepare($status_query);
    $stmt->bind_param('i', $period_id);
    $stmt->execute();
    $status_result = $stmt->get_result();
    
    $on_track_programs = 0;
    $delayed_programs = 0;
    $completed_programs = 0;
    $not_started_programs = 0;
    $total_programs = 0;
    
    while ($row = $status_result->fetch_assoc()) {
        switch ($row['status']) {
            case 'target-achieved':
            case 'on-track': 
                $on_track_programs += $row['count']; 
                break;
            case 'on-track-yearly':
            case 'delayed': 
                $delayed_programs += $row['count']; 
                break;
            case 'completed': 
                $completed_programs += $row['count']; 
                break;
            case 'severe-delay':
            case 'not-started': 
                $not_started_programs += $row['count']; 
                break;
        }
        $total_programs += $row['count'];
    }
    
    // Calculate completion percentage
    $completion_percentage = $total_agencies > 0 ? 
        min(100, round(($agencies_reported / $total_agencies) * 100)) : 0;
    
    return [
        'agencies_reported' => $agencies_reported,
        'total_agencies' => $total_agencies,
        'on_track_programs' => $on_track_programs,
        'delayed_programs' => $delayed_programs, 
        'completed_programs' => $completed_programs,
        'not_started_programs' => $not_started_programs,
        'total_programs' => $total_programs,
        'completion_percentage' => $completion_percentage
    ];
}

/**
 * Get data about each sector for a specific reporting period
 * 
 * @param int $period_id Reporting period ID
 * @return array Data about each sector's submissions
 */
function get_sector_data_for_period($period_id) {
    global $conn;
    
    if (!$period_id) {
        return [];
    }
    
    // Get all sectors
    $sectors_query = "SELECT sector_id, sector_name FROM sectors ORDER BY sector_name";
    $sectors_result = $conn->query($sectors_query);
    
    $sector_data = [];
    
    while ($sector = $sectors_result->fetch_assoc()) {
        $sector_id = $sector['sector_id'];
        
        // Get agency count for this sector
        $agency_query = "SELECT COUNT(*) as count FROM users WHERE sector_id = ? AND role = 'agency'";
        $stmt = $conn->prepare($agency_query);
        $stmt->bind_param('i', $sector_id);
        $stmt->execute();
        $agency_result = $stmt->get_result();
        $agency_count = $agency_result->fetch_assoc()['count'] ?? 0;
        
        // Get program count for this sector
        $program_query = "SELECT COUNT(*) as count FROM programs WHERE sector_id = ?";
        $stmt = $conn->prepare($program_query);
        $stmt->bind_param('i', $sector_id);
        $stmt->execute();
        $program_result = $stmt->get_result();
        $program_count = $program_result->fetch_assoc()['count'] ?? 0;
        
        // Get submission count for this sector
        $submission_query = "SELECT COUNT(*) as count 
                           FROM program_submissions ps
                           JOIN programs p ON ps.program_id = p.program_id
                           WHERE ps.period_id = ? AND p.sector_id = ?";
        $stmt = $conn->prepare($submission_query);
        $stmt->bind_param('ii', $period_id, $sector_id);
        $stmt->execute();
        $submission_result = $stmt->get_result();
        $submission_count = $submission_result->fetch_assoc()['count'] ?? 0;
        
        // Calculate submission percentage
        $submission_pct = $program_count > 0 ? 
            min(100, round(($submission_count / $program_count) * 100)) : 0;
        
        // Add to sector data array
        $sector_data[] = [
            'sector_id' => $sector_id,
            'sector_name' => $sector['sector_name'],
            'agency_count' => $agency_count,
            'program_count' => $program_count,
            'submission_pct' => $submission_pct
        ];
    }
    
    return $sector_data;
}

/**
 * Get recent program submissions
 * 
 * @param int $period_id Reporting period ID
 * @param int $limit Number of submissions to return
 * @return array Recent program submissions
 */
function get_recent_submissions($period_id, $limit = 5) {
    global $conn;
    
    if (!$period_id) {
        return [];
    }
    
    $query = "SELECT ps.*, p.program_name, u.agency_name
              FROM program_submissions ps
              JOIN programs p ON ps.program_id = p.program_id
              JOIN users u ON ps.submitted_by = u.user_id
              WHERE ps.period_id = ?
              ORDER BY ps.submission_date DESC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $period_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    return $submissions;
}

/**
 * ==============
 * SECTOR MANAGEMENT
 * ==============
 */

/**
 * Get all sectors
 * @return array List of all sectors
 */
function get_all_sectors() {
    global $conn;
    
    // Get all sectors
    $query = "SELECT * FROM sectors ORDER BY sector_name";
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
 * Add a new sector
 * @param string $sector_name Sector name
 * @param string $description Sector description (optional)
 * @return array Result of the operation
 */
function add_sector($sector_name, $description = null) {
    global $conn;
    
    // Verify admin permission
    if (!is_admin()) {
        return format_error('Permission denied', 403);
    }
    
    // Validate sector name
    if (empty($sector_name)) {
        return format_error('Sector name is required');
    }
    
    $sector_name = trim($conn->real_escape_string($sector_name));
    $description = $description ? trim($conn->real_escape_string($description)) : null;
    
    // Check if sector already exists
    $check_query = "SELECT * FROM sectors WHERE LOWER(sector_name) = LOWER(?)";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $sector_name);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return format_error('Sector with this name already exists');
    }
    
    // Insert sector
    $insert_query = "INSERT INTO sectors (sector_name, description) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ss", $sector_name, $description);
    
    if ($stmt->execute()) {
        return format_success('Sector added successfully', ['sector_id' => $conn->insert_id]);
    } else {
        return format_error('Failed to add sector: ' . $stmt->error);
    }
}

/**
 * Update a sector
 * @param int $sector_id Sector ID
 * @param string $sector_name Sector name
 * @param string $description Sector description (optional)
 * @return array Result of the operation
 */
function update_sector($sector_id, $sector_name, $description = null) {
    global $conn;
    
    // Verify admin permission
    if (!is_admin()) {
        return format_error('Permission denied', 403);
    }
    
    // Validate inputs
    $sector_id = intval($sector_id);
    
    if (empty($sector_name)) {
        return format_error('Sector name is required');
    }
    
    $sector_name = trim($conn->real_escape_string($sector_name));
    $description = $description ? trim($conn->real_escape_string($description)) : null;
    
    // Check if sector exists
    $check_query = "SELECT * FROM sectors WHERE sector_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        return format_error('Sector not found');
    }
    
    // Check if another sector has this name
    $check_name_query = "SELECT * FROM sectors WHERE LOWER(sector_name) = LOWER(?) AND sector_id != ?";
    $stmt = $conn->prepare($check_name_query);
    $stmt->bind_param("si", $sector_name, $sector_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return format_error('Another sector with this name already exists');
    }
    
    // Update sector
    $update_query = "UPDATE sectors SET sector_name = ?, description = ? WHERE sector_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $sector_name, $description, $sector_id);
    
    if ($stmt->execute()) {
        return format_success('Sector updated successfully');
    } else {
        return format_error('Failed to update sector: ' . $stmt->error);
    }
}

/**
 * Delete a sector
 * @param int $sector_id Sector ID to delete
 * @return array Result of the operation
 */
function delete_sector($sector_id) {
    global $conn;
    
    // Verify admin permission
    if (!is_admin()) {
        return format_error('Permission denied', 403);
    }
    
    $sector_id = intval($sector_id);
    
    // Check if sector exists
    $check_query = "SELECT * FROM sectors WHERE sector_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        return format_error('Sector not found');
    }
    
    // Check if any users are assigned to this sector
    $check_users_query = "SELECT COUNT(*) as count FROM users WHERE sector_id = ?";
    $stmt = $conn->prepare($check_users_query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    $user_count = $stmt->get_result()->fetch_assoc()['count'];
    
    if ($user_count > 0) {
        return format_error('Cannot delete sector with associated users');
    }
    
    // Check if any programs are assigned to this sector
    $check_programs_query = "SELECT COUNT(*) as count FROM programs WHERE sector_id = ?";
    $stmt = $conn->prepare($check_programs_query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    $program_count = $stmt->get_result()->fetch_assoc()['count'];
    
    if ($program_count > 0) {
        return format_error('Cannot delete sector with associated programs');
    }
    
    // Delete sector
    $delete_query = "DELETE FROM sectors WHERE sector_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $sector_id);
    
    if ($stmt->execute()) {
        return format_success('Sector deleted successfully');
    } else {
        return format_error('Failed to delete sector: ' . $stmt->error);
    }
}

/**
 * =============
 * USER MANAGEMENT
 * =============
 */

/**
 * Get all user accounts
 * @return array List of user accounts
 */
function get_all_users() {
    global $conn;
    
    // Verify admin permission
    if (!is_admin()) {
        return format_error('Permission denied', 403);
    }
    
    $query = "SELECT u.*, s.sector_name 
              FROM users u 
              LEFT JOIN sectors s ON u.sector_id = s.sector_id 
              ORDER BY u.username ASC";
    
    $result = $conn->query($query);
    $users = array();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
}

/**
 * Add a new user account
 * @param array $data User account data
 * @return array Result of the operation
 */
function add_user($data) {
    global $conn;
    
    // Verify admin permission
    if (!is_admin()) {
        return format_error('Permission denied', 403);
    }
    
    // Validate required fields
    $required_fields = ['username', 'password', 'role'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return format_error("Missing required field: {$field}");
        }
    }
    
    // Validate inputs
    $username = trim($conn->real_escape_string($data['username']));
    $password = $data['password'];
    $role = $conn->real_escape_string($data['role']);
    $agency_name = ($role === 'agency') ? trim($conn->real_escape_string($data['agency_name'])) : null;
    $sector_id = ($role === 'agency' && !empty($data['sector_id'])) ? intval($data['sector_id']) : null;
    
    // Check password strength
    if (strlen($password) < 8) {
        return format_error('Password must be at least 8 characters long');
    }
    
    // Check if username already exists
    $check_query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return format_error('Username already exists');
    }
    
    // For agency users, ensure sector_id is valid
    if ($role === 'agency') {
        if (empty($agency_name)) {
            return format_error('Agency name is required for agency users');
        }
        
        if (!$sector_id) {
            return format_error('Sector is required for agency users');
        }
        
        // Verify sector exists
        $sector_check = "SELECT * FROM sectors WHERE sector_id = ?";
        $stmt = $conn->prepare($sector_check);
        $stmt->bind_param("i", $sector_id);
        $stmt->execute();
        $sector_result = $stmt->get_result();
        
        if ($sector_result->num_rows === 0) {
            return format_error('Selected sector does not exist');
        }
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert user
        $insert_query = "INSERT INTO users (username, password, agency_name, role, sector_id, is_active) VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssi", $username, $hashed_password, $agency_name, $role, $sector_id);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $user_id = $conn->insert_id;
        
        // Commit transaction
        $conn->commit();
        
        return format_success('User added successfully', ['user_id' => $user_id]);
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return format_error('Failed to add user: ' . $e->getMessage());
    }
}

/**
 * Update an existing user account
 * @param array $data User account data
 * @return array Result of the operation
 */
function update_user($data) {
    global $conn;
    
    // Verify admin permission
    if (!is_admin()) {
        return format_error('Permission denied', 403);
    }
    
    // Validate required fields
    if (empty($data['user_id']) || empty($data['username']) || empty($data['role'])) {
        return format_error('Missing required fields');
    }
    
    // Validate inputs
    $user_id = intval($data['user_id']);
    $username = trim($conn->real_escape_string($data['username']));
    $role = $conn->real_escape_string($data['role']);
    $agency_name = ($role === 'agency') ? trim($conn->real_escape_string($data['agency_name'])) : null;
    $sector_id = ($role === 'agency' && !empty($data['sector_id'])) ? intval($data['sector_id']) : null;
    $is_active = isset($data['is_active']) ? 1 : 0;
    
    // Don't allow deactivating your own account
    if ($user_id === $_SESSION['user_id'] && $is_active === 0) {
        return format_error('You cannot deactivate your own account');
    }
    
    // Check if username already exists for another user
    $check_query = "SELECT * FROM users WHERE username = ? AND user_id != ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("si", $username, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return format_error('Username already exists');
    }
    
    // For agency users, ensure sector_id is valid
    if ($role === 'agency') {
        if (empty($agency_name)) {
            return format_error('Agency name is required for agency users');
        }
        
        if (!$sector_id) {
            return format_error('Sector is required for agency users');
        }
        
        // Verify sector exists
        $sector_check = "SELECT * FROM sectors WHERE sector_id = ?";
        $stmt = $conn->prepare($sector_check);
        $stmt->bind_param("i", $sector_id);
        $stmt->execute();
        $sector_result = $stmt->get_result();
        
        if ($sector_result->num_rows === 0) {
            return format_error('Selected sector does not exist');
        }
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Check if password is being updated
        if (!empty($data['password'])) {
            $password = $data['password'];
            
            // Check password strength
            if (strlen($password) < 8) {
                throw new Exception('Password must be at least 8 characters long');
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Update user with password
            $update_query = "UPDATE users SET username = ?, password = ?, agency_name = ?, role = ?, sector_id = ?, is_active = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssiis", $username, $hashed_password, $agency_name, $role, $sector_id, $is_active, $user_id);
        } else {
            // Update user without changing password
            $update_query = "UPDATE users SET username = ?, agency_name = ?, role = ?, sector_id = ?, is_active = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssis", $username, $agency_name, $role, $sector_id, $is_active, $user_id);
        }
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        return format_success('User updated successfully');
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return format_error('Failed to update user: ' . $e->getMessage());
    }
}

/**
 * Delete a user account
 * @param int $user_id User ID to delete
 * @return array Result of the operation
 */
function delete_user($user_id) {
    global $conn;
    
    // Verify admin permission
    if (!is_admin()) {
        return format_error('Permission denied', 403);
    }
    
    // Validate user ID
    $user_id = intval($user_id);
    
    // Don't allow deleting your own account
    if ($user_id === $_SESSION['user_id']) {
        return format_error('You cannot delete your own account');
    }
    
    // Check if user exists
    $check_query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return format_error('User not found');
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Check if user has associated programs or submissions
        $program_check = "SELECT COUNT(*) as count FROM programs WHERE owner_agency_id = ?";
        $stmt = $conn->prepare($program_check);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $program_result = $stmt->get_result();
        $program_count = $program_result->fetch_assoc()['count'];
        
        if ($program_count > 0) {
            throw new Exception('Cannot delete user with associated programs');
        }
        
        // Delete user
        $delete_query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        return format_success('User deleted successfully');
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return format_error('Failed to delete user: ' . $e->getMessage());
    }
}
?>
