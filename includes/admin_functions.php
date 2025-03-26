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

/**
 * Get all system users
 * @return array List of all users with their details
 */
function get_all_users() {
    global $conn;
    
    // Only admin can view all users
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    // First, check if is_active column exists in the users table
    $check_column = "SHOW COLUMNS FROM users LIKE 'is_active'";
    $column_exists = $conn->query($check_column);
    
    // Add the column if it doesn't exist
    if ($column_exists->num_rows === 0) {
        $add_column = "ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE";
        $conn->query($add_column);
    }
    
    // Improved query with proper join to sectors table
    $query = "SELECT u.user_id, u.username, u.role, u.agency_name, u.sector_id, 
                u.created_at, s.sector_name, 
                CASE WHEN u.is_active = 1 OR u.is_active IS NULL THEN 1 ELSE 0 END as is_active
              FROM users u 
              LEFT JOIN sectors s ON u.sector_id = s.sector_id
              ORDER BY u.role ASC, u.username ASC";
    
    $result = $conn->query($query);
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    return $users;
}

/**
 * Add a new user
 * @param array $data User data
 * @return array Result of operation
 */
function add_user($data) {
    global $conn;
    
    // Only admin can add users
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    // Extract and sanitize data
    $username = trim($conn->real_escape_string($data['username']));
    $password = $data['password'];
    $role = $conn->real_escape_string($data['role']);
    $agency_name = $conn->real_escape_string($data['agency_name'] ?? null);
    $sector_id = !empty($data['sector_id']) ? intval($data['sector_id']) : null;
    
    // Validate input
    if (empty($username) || empty($password)) {
        return ['error' => 'Username and password are required'];
    }
    
    if ($role === 'agency' && (empty($agency_name) || empty($sector_id))) {
        return ['error' => 'Agency name and sector are required for agency users'];
    }
    
    // Check if username already exists
    $check_query = "SELECT user_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['error' => 'Username already exists'];
    }
    
    // Ensure sector_id is valid when role is 'agency'
    if ($role === 'agency' && !empty($sector_id)) {
        $check_sector = "SELECT sector_id FROM sectors WHERE sector_id = ?";
        $stmt = $conn->prepare($check_sector);
        $stmt->bind_param("i", $sector_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['error' => 'Invalid sector selected'];
        }
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    if ($role === 'admin') {
        // Admin doesn't need sector_id
        $query = "INSERT INTO users (username, password, role, agency_name) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $hashed_password, $role, $agency_name);
    } else {
        // Agency needs sector_id
        $query = "INSERT INTO users (username, password, role, agency_name, sector_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $username, $hashed_password, $role, $agency_name, $sector_id);
    }
    
    if ($stmt->execute()) {
        return ['success' => true, 'user_id' => $stmt->insert_id];
    } else {
        return ['error' => 'Failed to add user: ' . $stmt->error];
    }
}

/**
 * Update an existing user
 * @param array $data User data
 * @return array Result of operation
 */
function update_user($data) {
    global $conn;
    
    // Only admin can update users
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    // Extract and sanitize data
    $user_id = intval($data['user_id']);
    $username = trim($conn->real_escape_string($data['username']));
    $password = $data['password']; // May be empty if not changing
    $role = $conn->real_escape_string($data['role']);
    $agency_name = $conn->real_escape_string($data['agency_name'] ?? null);
    $sector_id = !empty($data['sector_id']) ? intval($data['sector_id']) : null;
    
    // Validate input
    if (empty($username)) {
        return ['error' => 'Username is required'];
    }
    
    if ($role === 'agency' && (empty($agency_name) || empty($sector_id))) {
        return ['error' => 'Agency name and sector are required for agency users'];
    }
    
    // Check if username exists for a different user
    $check_query = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("si", $username, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['error' => 'Username already exists'];
    }
    
    // Ensure sector_id is valid when role is 'agency'
    if ($role === 'agency' && !empty($sector_id)) {
        $check_sector = "SELECT sector_id FROM sectors WHERE sector_id = ?";
        $stmt = $conn->prepare($check_sector);
        $stmt->bind_param("i", $sector_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['error' => 'Invalid sector selected'];
        }
    }
    
    // Update user
    if (!empty($password)) {
        // Update with new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        if ($role === 'admin') {
            $query = "UPDATE users SET username = ?, password = ?, role = ?, agency_name = ?, sector_id = NULL WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssi", $username, $hashed_password, $role, $agency_name, $user_id);
        } else {
            $query = "UPDATE users SET username = ?, password = ?, role = ?, agency_name = ?, sector_id = ? WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssii", $username, $hashed_password, $role, $agency_name, $sector_id, $user_id);
        }
    } else {
        // Update without changing password
        if ($role === 'admin') {
            $query = "UPDATE users SET username = ?, role = ?, agency_name = ?, sector_id = NULL WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $username, $role, $agency_name, $user_id);
        } else {
            $query = "UPDATE users SET username = ?, role = ?, agency_name = ?, sector_id = ? WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssii", $username, $role, $agency_name, $sector_id, $user_id);
        }
    }
    
    if ($stmt->execute()) {
        return ['success' => true];
    } else {
        return ['error' => 'Failed to update user: ' . $stmt->error];
    }
}

/**
 * Delete a user
 * @param int $user_id User ID to delete
 * @return array Result of operation
 */
function delete_user($user_id) {
    global $conn;
    
    // Only admin can delete users
    if (!is_admin()) {
        return ['error' => 'Permission denied'];
    }
    
    // Prevent deleting self
    if ($user_id == $_SESSION['user_id']) {
        return ['error' => 'You cannot delete your own account'];
    }
    
    $user_id = intval($user_id);
    
    // Delete the user
    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        return ['success' => true];
    } else {
        return ['error' => 'Failed to delete user: ' . $stmt->error];
    }
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
        return [
            'agencies_reported' => 0,
            'total_agencies' => 0,
            'on_track_programs' => 0,
            'delayed_programs' => 0,
            'completion_percentage' => 0
        ];
    }
    
    // Get total agencies count
    $agencies_query = "SELECT COUNT(*) as total FROM users WHERE role = 'agency'";
    $agencies_result = $conn->query($agencies_query);
    $total_agencies = $agencies_result->fetch_assoc()['total'] ?? 0;
    
    // Get agencies that submitted data for this period
    $reported_query = "SELECT COUNT(DISTINCT agency_id) as reported 
                       FROM sector_metric_values 
                       WHERE period_id = ?";
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
    
    $on_track = 0;
    $delayed = 0;
    $completed = 0;
    $not_started = 0;
    
    while ($row = $status_result->fetch_assoc()) {
        switch ($row['status']) {
            case 'on-track': $on_track = $row['count']; break;
            case 'delayed': $delayed = $row['count']; break;
            case 'completed': $completed = $row['count']; break;
            case 'not-started': $not_started = $row['count']; break;
        }
    }
    
    // Calculate overall completion percentage
    $total_possible_submissions = $total_agencies * 
        (get_required_metrics_count() + get_active_programs_count());
    
    $total_submissions = "SELECT 
                            (SELECT COUNT(*) FROM program_submissions WHERE period_id = ?) +
                            (SELECT COUNT(*) FROM sector_metric_values WHERE period_id = ?) as total";
    $stmt = $conn->prepare($total_submissions);
    $stmt->bind_param('ii', $period_id, $period_id);
    $stmt->execute();
    $submissions_result = $stmt->get_result();
    $actual_submissions = $submissions_result->fetch_assoc()['total'] ?? 0;
    
    // Calculate percentage (prevent division by zero)
    $completion_percentage = $total_possible_submissions > 0 ? 
        min(100, round(($actual_submissions / $total_possible_submissions) * 100)) : 0;
    
    return [
        'agencies_reported' => $agencies_reported,
        'total_agencies' => $total_agencies,
        'on_track_programs' => $on_track,
        'delayed_programs' => $delayed, 
        'completed_programs' => $completed,
        'not_started_programs' => $not_started,
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
        
        // Get submission percentage
        $submission_query = "SELECT 
            (SELECT COUNT(*) FROM sector_metric_values smv 
             JOIN sector_metrics_definition smd ON smv.metric_id = smd.metric_id
             WHERE smv.period_id = ? AND smd.sector_id = ?) as metric_submissions,
            (SELECT COUNT(*) FROM program_submissions ps 
             JOIN programs p ON ps.program_id = p.program_id
             WHERE ps.period_id = ? AND p.sector_id = ?) as program_submissions,
            (SELECT COUNT(*) FROM sector_metrics_definition WHERE sector_id = ?) as required_metrics,
            (SELECT COUNT(*) FROM programs WHERE sector_id = ?) as total_programs";
        
        $stmt = $conn->prepare($submission_query);
        $stmt->bind_param('iiiiii', $period_id, $sector_id, $period_id, $sector_id, $sector_id, $sector_id);
        $stmt->execute();
        $sub_result = $stmt->get_result();
        $sub_data = $sub_result->fetch_assoc();
        
        // Calculate submission percentage
        $total_expected = ($sub_data['required_metrics'] * $agency_count) + $sub_data['total_programs'];
        $total_submitted = $sub_data['metric_submissions'] + $sub_data['program_submissions'];
        
        $submission_pct = $total_expected > 0 ? 
            min(100, round(($total_submitted / $total_expected) * 100)) : 0;
        
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
 * Get count of required metrics across all sectors
 * @return int Count of metrics
 */
function get_required_metrics_count() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM sector_metrics_definition WHERE is_required = 1";
    $result = $conn->query($query);
    return $result->fetch_assoc()['count'] ?? 0;
}

/**
 * Get count of active programs
 * @return int Count of programs
 */
function get_active_programs_count() {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM programs";
    $result = $conn->query($query);
    return $result->fetch_assoc()['count'] ?? 0;
}
?>
