<?php
/**
 * Agency-specific functions
 * 
 * Functions used by agency users to manage programs and submit data.
 */

/**
 * Check if current user is an agency
 * @return boolean
 */
function is_agency() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }
    return $_SESSION['role'] === 'agency';
}

/**
 * Get programs owned by current agency
 * @return array List of programs
 */
function get_agency_programs() {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT p.program_id, p.program_name, p.description, p.start_date, p.end_date, p.created_at, p.updated_at,
                (SELECT ps.target FROM program_submissions ps 
                 WHERE ps.program_id = p.program_id 
                 ORDER BY ps.submission_id DESC LIMIT 1) AS current_target,
                (SELECT ps.status FROM program_submissions ps 
                 WHERE ps.program_id = p.program_id 
                 ORDER BY ps.submission_id DESC LIMIT 1) AS status
              FROM programs p
              WHERE p.owner_agency_id = ?
              ORDER BY p.created_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    return $programs;
}

// Remove these two functions as they're now in functions.php
// get_current_reporting_period()
// get_all_reporting_periods()

/**
 * Get submission status for an agency in a reporting period
 * @param int $user_id Agency user ID
 * @param int $period_id Reporting period ID
 * @return array|null Submission status or null if no data
 */
function get_agency_submission_status($user_id, $period_id) {
    global $conn;
    
    if (!$user_id || !$period_id) {
        return null;
    }
    
    // Get the number of programs and submissions
    $programs_query = "SELECT 
                            COUNT(p.program_id) AS total_programs,
                            (SELECT COUNT(DISTINCT ps.program_id) FROM program_submissions ps 
                             WHERE ps.period_id = ? AND ps.program_id IN (SELECT program_id FROM programs WHERE owner_agency_id = ?)) AS programs_submitted
                        FROM programs p
                        WHERE p.owner_agency_id = ?";
    
    $stmt = $conn->prepare($programs_query);
    $stmt->bind_param("iii", $period_id, $user_id, $user_id);
    $stmt->execute();
    $programs_result = $stmt->get_result();
    $programs_data = $programs_result->fetch_assoc();
    
    // Get the number of metrics and submissions
    $metrics_query = "SELECT 
                        (SELECT COUNT(*) FROM sector_metrics_definition smd 
                         WHERE smd.sector_id = (SELECT sector_id FROM users WHERE user_id = ?)) AS total_metrics,
                        (SELECT COUNT(*) FROM sector_metric_values smv 
                         WHERE smv.period_id = ? AND smv.agency_id = ?) AS metrics_submitted";
    
    $stmt = $conn->prepare($metrics_query);
    $stmt->bind_param("iii", $user_id, $period_id, $user_id);
    $stmt->execute();
    $metrics_result = $stmt->get_result();
    $metrics_data = $metrics_result->fetch_assoc();
    
    // Get program status distribution
    $status_query = "SELECT ps.status, COUNT(*) AS count
                     FROM program_submissions ps
                     JOIN programs p ON ps.program_id = p.program_id
                     WHERE ps.period_id = ? AND p.owner_agency_id = ?
                     GROUP BY ps.status";
    
    $stmt = $conn->prepare($status_query);
    $stmt->bind_param("ii", $period_id, $user_id);
    $stmt->execute();
    $status_result = $stmt->get_result();
    
    $program_status = [
        'on-track' => 0,
        'delayed' => 0,
        'completed' => 0,
        'not-started' => 0
    ];
    
    while ($row = $status_result->fetch_assoc()) {
        $program_status[$row['status']] = (int)$row['count'];
    }
    
    // Calculate "not-started" programs
    if ($programs_data) {
        $submitted_statuses = $program_status['on-track'] + $program_status['delayed'] + $program_status['completed'];
        $program_status['not-started'] = $programs_data['total_programs'] - $submitted_statuses;
    }
    
    return array_merge($programs_data, $metrics_data, ['program_status' => $program_status]);
}

/**
 * Get metrics for the current agency's sector
 * @param int $sector_id Agency's sector ID
 * @param int $period_id Optional - specific period ID (defaults to current period)
 * @return array List of metrics for the sector
 */
function get_agency_sector_metrics($sector_id, $period_id = null) {
    global $conn;
    
    if (!$sector_id) {
        return [];
    }
    
    $user_id = $_SESSION['user_id'];
    
    // If period_id not provided, use current period
    if (!$period_id) {
        $current_period = get_current_reporting_period();
        $period_id = $current_period['period_id'] ?? null;
    }
    
    $query = "SELECT smd.*, 
                CASE WHEN smv.value_id IS NOT NULL THEN 1 ELSE 0 END as is_submitted,
                smv.numeric_value, smv.text_value, smv.notes as current_notes
              FROM sector_metrics_definition smd
              LEFT JOIN sector_metric_values smv ON smd.metric_id = smv.metric_id 
                                               AND smv.agency_id = ? 
                                               AND smv.period_id = ?
              WHERE smd.sector_id = ?
              ORDER BY smd.display_order, smd.metric_name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $user_id, $period_id, $sector_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $metrics = [];
    while ($row = $result->fetch_assoc()) {
        // Format the current value based on the metric type
        if ($row['is_submitted']) {
            if ($row['metric_type'] === 'numeric' || $row['metric_type'] === 'percentage') {
                $row['current_value'] = $row['numeric_value'];
            } else {
                $row['current_value'] = $row['text_value'];
            }
        }
        
        $metrics[] = $row;
    }
    
    return $metrics;
}

/**
 * Get the name of a sector by ID
 * @param int $sector_id Sector ID
 * @return string Sector name
 */
function get_sector_name($sector_id) {
    global $conn;
    
    if (!$sector_id) {
        return 'Unknown Sector';
    }
    
    $query = "SELECT sector_name FROM sectors WHERE sector_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['sector_name'];
    }
    
    return 'Unknown Sector';
}

/**
 * Submit program data for current reporting period
 * @param array $data Program submission data
 * @return array Result of submission
 */
function submit_program_data($data) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Validate inputs
    $program_id = intval($data['program_id'] ?? 0);
    $period_id = intval($data['period_id'] ?? 0);
    $target = $conn->real_escape_string($data['target'] ?? '');
    $achievement = $conn->real_escape_string($data['achievement'] ?? '');
    $status = $conn->real_escape_string($data['status'] ?? '');
    $remarks = $conn->real_escape_string($data['remarks'] ?? '');
    
    if (!$program_id || !$period_id) {
        return ['error' => 'Invalid program or reporting period'];
    }
    
    if (!in_array($status, ['on-track', 'delayed', 'completed', 'not-started'])) {
        return ['error' => 'Invalid status value'];
    }
    
    // Verify ownership of program
    $check_query = "SELECT program_id FROM programs WHERE program_id = ? AND owner_agency_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $program_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['error' => 'You do not have permission to update this program'];
    }
    
    // Check if submission already exists
    $check_submission = "SELECT submission_id FROM program_submissions 
                          WHERE program_id = ? AND period_id = ?";
    $stmt = $conn->prepare($check_submission);
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing submission
        $submission = $result->fetch_assoc();
        $submission_id = $submission['submission_id'];
        
        $update_query = "UPDATE program_submissions 
                          SET target = ?, achievement = ?, status = ?, remarks = ?, 
                              updated_at = CURRENT_TIMESTAMP 
                          WHERE submission_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssi", $target, $achievement, $status, $remarks, $submission_id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Program data updated successfully'];
        } else {
            return ['error' => 'Failed to update program data: ' . $conn->error];
        }
    } else {
        // Create new submission
        $insert_query = "INSERT INTO program_submissions 
                          (program_id, period_id, submitted_by, target, achievement, status, remarks) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iiissss", $program_id, $period_id, $_SESSION['user_id'], $target, $achievement, $status, $remarks);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Program data submitted successfully'];
        } else {
            return ['error' => 'Failed to submit program data: ' . $conn->error];
        }
    }
}

/**
 * Submit metric values for current reporting period
 * @param array $data Metric submission data
 * @return array Result of submission
 */
function submit_metric_values($data) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Initialize results
    $results = [
        'success' => [],
        'errors' => []
    ];
    
    // Validate period_id
    $period_id = intval($data['period_id'] ?? 0);
    if (!$period_id) {
        return ['error' => 'Invalid reporting period'];
    }
    
    // Process each metric
    foreach ($data['metrics'] as $metric_id => $value) {
        $metric_id = intval($metric_id);
        
        if (!$metric_id) {
            $results['errors'][] = "Invalid metric ID: $metric_id";
            continue;
        }
        
        // Get metric information
        $metric_query = "SELECT metric_type, sector_id FROM sector_metrics_definition WHERE metric_id = ?";
        $stmt = $conn->prepare($metric_query);
        $stmt->bind_param("i", $metric_id);
        $stmt->execute();
        $metric_result = $stmt->get_result();
        
        if ($metric_result->num_rows === 0) {
            $results['errors'][] = "Metric not found: $metric_id";
            continue;
        }
        
        $metric = $metric_result->fetch_assoc();
        
        // Verify metric belongs to agency's sector
        if ($metric['sector_id'] != $_SESSION['sector_id']) {
            $results['errors'][] = "You do not have permission to submit data for this metric";
            continue;
        }
        
        // Prepare data based on metric type
        $numeric_value = null;
        $text_value = null;
        
        if ($metric['metric_type'] === 'numeric' || $metric['metric_type'] === 'percentage') {
            $numeric_value = str_replace(',', '', $value);
            if (!is_numeric($numeric_value)) {
                $results['errors'][] = "Invalid numeric value for metric: $metric_id";
                continue;
            }
        } else {
            $text_value = $conn->real_escape_string($value);
        }
        
        // Get notes if available
        $notes = $conn->real_escape_string($data['notes'][$metric_id] ?? '');
        
        // Check if submission already exists
        $check_submission = "SELECT value_id FROM sector_metric_values 
                              WHERE metric_id = ? AND period_id = ? AND agency_id = ?";
        $stmt = $conn->prepare($check_submission);
        $stmt->bind_param("iii", $metric_id, $period_id, $_SESSION['user_id']);
        $stmt->execute();
        $check_result = $stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing submission
            $existing = $check_result->fetch_assoc();
            $value_id = $existing['value_id'];
            
            $update_query = "UPDATE sector_metric_values 
                              SET numeric_value = ?, text_value = ?, notes = ?, 
                                  updated_at = CURRENT_TIMESTAMP 
                              WHERE value_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("dssi", $numeric_value, $text_value, $notes, $value_id);
            
            if ($stmt->execute()) {
                $results['success'][] = "Metric $metric_id updated successfully";
            } else {
                $results['errors'][] = "Failed to update metric $metric_id: " . $conn->error;
            }
        } else {
            // Create new submission
            $insert_query = "INSERT INTO sector_metric_values 
                              (metric_id, period_id, agency_id, numeric_value, text_value, notes) 
                              VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iiidss", $metric_id, $period_id, $_SESSION['user_id'], $numeric_value, $text_value, $notes);
            
            if ($stmt->execute()) {
                $results['success'][] = "Metric $metric_id submitted successfully";
            } else {
                $results['errors'][] = "Failed to submit metric $metric_id: " . $conn->error;
            }
        }
    }
    
    // Return overall result
    if (empty($results['errors'])) {
        return ['success' => true, 'message' => 'All metrics submitted successfully'];
    } else {
        if (!empty($results['success'])) {
            return [
                'partial' => true, 
                'message' => 'Some metrics were successfully submitted, but others failed', 
                'details' => $results
            ];
        } else {
            return ['error' => 'Failed to submit metrics', 'details' => $results['errors']];
        }
    }
}

/**
 * Get program details by ID (with submissions history)
 * @param int $program_id Program ID
 * @return array|null Program details or null if not found/not authorized
 */
function get_program_details($program_id) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    $user_id = $_SESSION['user_id'];
    $program_id = intval($program_id);
    
    // Get program info
    $program_query = "SELECT p.*, s.sector_name 
                      FROM programs p
                      JOIN sectors s ON p.sector_id = s.sector_id
                      WHERE p.program_id = ? AND p.owner_agency_id = ?";
    
    $stmt = $conn->prepare($program_query);
    $stmt->bind_param("ii", $program_id, $user_id);
    $stmt->execute();
    $program_result = $stmt->get_result();
    
    if ($program_result->num_rows === 0) {
        return null; // Program not found or not owned by this agency
    }
    
    $program = $program_result->fetch_assoc();
    
    // Get submission history
    $submissions_query = "SELECT ps.*, rp.quarter, rp.year
                          FROM program_submissions ps
                          JOIN reporting_periods rp ON ps.period_id = rp.period_id
                          WHERE ps.program_id = ?
                          ORDER BY rp.year DESC, rp.quarter DESC";
    
    $stmt = $conn->prepare($submissions_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $submissions_result = $stmt->get_result();
    
    $submissions = [];
    while ($row = $submissions_result->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    $program['submissions'] = $submissions;
    
    return $program;
}

/**
 * Get agency reports for a specific period
 * @param int $user_id Agency user ID
 * @param int $period_id Reporting period ID
 * @return array List of reports
 */
function get_agency_reports($user_id, $period_id) {
    global $conn;
    
    if (!$user_id || !$period_id) {
        return [];
    }
    
    $query = "SELECT r.* FROM reports r
              LEFT JOIN report_access ra ON r.report_id = ra.report_id
              WHERE (r.period_id = ? AND (r.report_scope = 'public' OR ra.user_id = ?))
              ORDER BY r.generated_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $period_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    return $reports;
}

/**
 * Get programs from all sectors
 * 
 * This function allows agency users to view programs from other sectors (read-only)
 * 
 * @param int $current_period_id Optional - filter by reporting period
 * @return array Programs from all sectors with their details
 */
function get_all_sectors_programs($current_period_id = null) {
    global $conn;
    
    // Base query to get all programs with their sector and agency details
    $query = "SELECT p.program_id, p.program_name, p.description,
                    s.sector_id, s.sector_name,
                    u.user_id AS agency_id, u.agency_name,
                    p.start_date, p.end_date
              FROM programs p
              JOIN sectors s ON p.sector_id = s.sector_id
              JOIN users u ON p.owner_agency_id = u.user_id
              ORDER BY s.sector_name, u.agency_name, p.program_name";
              
    $result = $conn->query($query);
    
    if (!$result) {
        return ['error' => 'Database error: ' . $conn->error];
    }
    
    $programs = [];
    
    while ($row = $result->fetch_assoc()) {
        // Get latest submission data if period is specified
        if ($current_period_id) {
            $sub_query = "SELECT target, achievement, status, remarks
                          FROM program_submissions
                          WHERE program_id = ? AND period_id = ?
                          ORDER BY submission_date DESC
                          LIMIT 1";
                          
            $stmt = $conn->prepare($sub_query);
            $stmt->bind_param('ii', $row['program_id'], $current_period_id);
            $stmt->execute();
            $sub_result = $stmt->get_result();
            
            if ($sub_result && $sub_result->num_rows > 0) {
                $sub_data = $sub_result->fetch_assoc();
                $row = array_merge($row, $sub_data);
            } else {
                $row['target'] = null;
                $row['achievement'] = null;
                $row['status'] = null;
                $row['remarks'] = null;
            }
            $stmt->close();
        }
        
        $programs[] = $row;
    }
    
    return $programs;
}

/**
 * Create a new program for agency
 * @param array $data Program data from form submission
 * @return array Result of program creation with success/error message
 */
function agency_create_program($data) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Extract and sanitize data
    $program_name = trim($conn->real_escape_string($data['program_name'] ?? ''));
    $description = $conn->real_escape_string($data['description'] ?? '');
    $start_date = $conn->real_escape_string($data['start_date'] ?? '');
    $end_date = $conn->real_escape_string($data['end_date'] ?? '');
    $target = $conn->real_escape_string($data['target'] ?? '');
    
    // Set owner to current agency
    $owner_agency_id = $_SESSION['user_id'];
    $sector_id = $_SESSION['sector_id'];
    
    // Validate input
    if (empty($program_name)) {
        return ['error' => 'Program name is required'];
    }
    
    if (!empty($start_date) && !empty($end_date)) {
        // Validate date range
        if (strtotime($start_date) > strtotime($end_date)) {
            return ['error' => 'End date cannot be before start date'];
        }
    }
    
    // Insert new program
    $query = "INSERT INTO programs (program_name, description, owner_agency_id, sector_id, start_date, end_date) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiiss", $program_name, $description, $owner_agency_id, $sector_id, $start_date, $end_date);
    
    if (!$stmt->execute()) {
        return ['error' => 'Failed to create program: ' . $stmt->error];
    }
    
    $program_id = $stmt->insert_id;
    
    // If target is provided and current period exists, create initial submission
    if (!empty($target)) {
        $current_period = get_current_reporting_period();
        
        if ($current_period && $current_period['status'] === 'open') {
            $period_id = $current_period['period_id'];
            $status = 'not-started'; // Default status for new programs
            
            $sub_query = "INSERT INTO program_submissions 
                          (program_id, period_id, submitted_by, target, achievement, status, remarks) 
                          VALUES (?, ?, ?, ?, '', ?, '')";
            
            $stmt = $conn->prepare($sub_query);
            $stmt->bind_param("iiiss", $program_id, $period_id, $owner_agency_id, $target, $status);
            $stmt->execute();
        }
    }
    
    return [
        'success' => true, 
        'program_id' => $program_id,
        'message' => 'Program created successfully'
    ];
}
?>
