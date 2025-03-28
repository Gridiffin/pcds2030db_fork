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
 * Check if the schema has shifted to using content_json
 * @return boolean
 */
function has_content_json_schema() {
    global $conn;
    static $has_content_json = null;
    
    if ($has_content_json === null) {
        $content_json_check = $conn->query("SHOW COLUMNS FROM `program_submissions` LIKE 'content_json'");
        $has_content_json = $content_json_check->num_rows > 0;
    }
    
    return $has_content_json;
}

/**
 * Check if programs table has is_assigned column
 * @return boolean
 */
function has_is_assigned_column() {
    global $conn;
    static $has_is_assigned = null;
    
    if ($has_is_assigned === null) {
        $column_check = $conn->query("SHOW COLUMNS FROM `programs` LIKE 'is_assigned'");
        $has_is_assigned = $column_check->num_rows > 0;
    }
    
    return $has_is_assigned;
}

/**
 * Process row data with content_json if needed
 * @param array $row Row data from database
 * @return array Updated row data
 */
function process_content_json($row) {
    if (has_content_json_schema() && isset($row['content_json'])) {
        $content = json_decode($row['content_json'], true);
        if ($content) {
            $row['current_target'] = $content['target'] ?? null;
            $row['achievement'] = $content['achievement'] ?? null;
            $row['remarks'] = $content['remarks'] ?? null;
        }
        unset($row['content_json']); // Remove JSON from result
    }
    return $row;
}

/**
 * Get programs owned by current agency, separated by type
 * @return array Lists of assigned and created programs
 */
function get_agency_programs_by_type() {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    $user_id = $_SESSION['user_id'];
    $has_is_assigned = has_is_assigned_column();
    $has_content_json = has_content_json_schema();
    
    // If old schema without is_assigned column, return all programs as assigned
    if (!$has_is_assigned) {
        // Build query based on schema version
        if ($has_content_json) {
            $select_fields = "p.program_id, p.program_name, p.description, p.start_date, p.end_date, 
                      p.created_at, p.updated_at,
                      (SELECT ps.content_json FROM program_submissions ps 
                       WHERE ps.program_id = p.program_id 
                       ORDER BY ps.submission_id DESC LIMIT 1) AS content_json";
        } else {
            $select_fields = "p.program_id, p.program_name, p.description, p.start_date, p.end_date, 
                      p.created_at, p.updated_at,
                      (SELECT ps.target FROM program_submissions ps 
                       WHERE ps.program_id = p.program_id 
                       ORDER BY ps.submission_id DESC LIMIT 1) AS current_target,
                      (SELECT ps.achievement FROM program_submissions ps 
                       WHERE ps.program_id = p.program_id 
                       ORDER BY ps.submission_id DESC LIMIT 1) AS achievement";
        }
        
        // Common fields for both schemas
        $common_fields = "(SELECT ps.target_date FROM program_submissions ps 
                       WHERE ps.program_id = p.program_id 
                       ORDER BY ps.submission_id DESC LIMIT 1) AS target_date,
                      (SELECT ps.status FROM program_submissions ps 
                       WHERE ps.program_id = p.program_id 
                       ORDER BY ps.submission_id DESC LIMIT 1) AS status,
                      (SELECT ps.status_date FROM program_submissions ps 
                       WHERE ps.program_id = p.program_id 
                       ORDER BY ps.submission_id DESC LIMIT 1) AS status_date";
        
        $query = "SELECT $select_fields, $common_fields FROM programs p
                  WHERE p.owner_agency_id = ?
                  ORDER BY p.created_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $all_programs = [];
        while ($row = $result->fetch_assoc()) {
            $all_programs[] = process_content_json($row);
        }
        
        return [
            'assigned' => $all_programs,
            'created' => []
        ];
    }
    
    // Database has updated schema - can separate programs
    $assigned_programs = get_typed_programs($conn, $user_id, 1, $has_content_json);
    $created_programs = get_typed_programs($conn, $user_id, 0, $has_content_json);
    
    return [
        'assigned' => $assigned_programs,
        'created' => $created_programs
    ];
}

/**
 * Helper function to get programs of a specific type
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $is_assigned Whether program is assigned (1) or created (0)
 * @param bool $has_content_json Whether content_json schema is used
 * @return array List of programs
 */
function get_typed_programs($conn, $user_id, $is_assigned, $has_content_json) {
    if ($has_content_json) {
        $select_fields = "p.program_id, p.program_name, p.description, p.start_date, p.end_date, 
                        p.created_at, p.updated_at, p.created_by, p.is_assigned,
                        (SELECT ps.content_json FROM program_submissions ps 
                         WHERE ps.program_id = p.program_id 
                         ORDER BY ps.submission_id DESC LIMIT 1) AS content_json";
    } else {
        $select_fields = "p.program_id, p.program_name, p.description, p.start_date, p.end_date, 
                        p.created_at, p.updated_at, p.created_by, p.is_assigned,
                        (SELECT ps.target FROM program_submissions ps 
                         WHERE ps.program_id = p.program_id 
                         ORDER BY ps.submission_id DESC LIMIT 1) AS current_target,
                        (SELECT ps.achievement FROM program_submissions ps 
                         WHERE ps.program_id = p.program_id 
                         ORDER BY ps.submission_id DESC LIMIT 1) AS achievement";
    }
    
    $common_fields = "(SELECT ps.target_date FROM program_submissions ps 
                     WHERE ps.program_id = p.program_id 
                     ORDER BY ps.submission_id DESC LIMIT 1) AS target_date,
                    (SELECT ps.status FROM program_submissions ps 
                     WHERE ps.program_id = p.program_id 
                     ORDER BY ps.submission_id DESC LIMIT 1) AS status,
                    (SELECT ps.status_date FROM program_submissions ps 
                     WHERE ps.program_id = p.program_id 
                     ORDER BY ps.submission_id DESC LIMIT 1) AS status_date";
    
    $query = "SELECT $select_fields, $common_fields FROM programs p
              WHERE p.owner_agency_id = ? AND p.is_assigned = ?
              ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $is_assigned);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = process_content_json($row);
    }
    
    return $programs;
}

/**
 * Create a new program for an agency
 * @param array $data Program data
 * @return array Result of creation
 */
function create_agency_program($data) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Validate inputs
    $program_name = trim($conn->real_escape_string($data['program_name'] ?? ''));
    $description = $conn->real_escape_string($data['description'] ?? '');
    $start_date = $conn->real_escape_string($data['start_date'] ?? null);
    $end_date = $conn->real_escape_string($data['end_date'] ?? null);
    $target = $conn->real_escape_string($data['target'] ?? '');
    $target_date = $conn->real_escape_string($data['target_date'] ?? null);
    $status = $conn->real_escape_string($data['status'] ?? 'not-started');
    $status_date = $conn->real_escape_string($data['status_date'] ?? null);
    
    if (empty($program_name)) {
        return ['error' => 'Program name is required'];
    }
    
    if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
        return ['error' => 'End date cannot be before start date'];
    }
    
    if (!in_array($status, ['on-track', 'delayed', 'completed', 'not-started'])) {
        return ['error' => 'Invalid status'];
    }
    
    $user_id = $_SESSION['user_id'];
    $sector_id = $_SESSION['sector_id'];
    $has_content_json = has_content_json_schema();
    $has_is_assigned = has_is_assigned_column();
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        if ($has_is_assigned) {
            // Insert program with is_assigned
            $query = "INSERT INTO programs (program_name, description, start_date, end_date, 
                                        owner_agency_id, sector_id, created_by, is_assigned) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssiii", $program_name, $description, $start_date, $end_date, 
                            $user_id, $sector_id, $user_id);
        } else {
            // Insert program without is_assigned
            $query = "INSERT INTO programs (program_name, description, start_date, end_date, 
                                        owner_agency_id, sector_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssii", $program_name, $description, $start_date, $end_date, 
                            $user_id, $sector_id);
        }
        
        $stmt->execute();
        $program_id = $conn->insert_id;
        
        // Get current period
        $current_period = get_current_reporting_period();
        if (!$current_period) {
            throw new Exception('No active reporting period found');
        }
        
        // Insert initial submission based on schema
        if ($has_content_json) {
            $content = json_encode([
                'target' => $target,
                'achievement' => '',
                'remarks' => ''
            ]);
            
            $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                        content_json, target_date, status, status_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sub_query);
            $stmt->bind_param("iiissss", $program_id, $current_period['period_id'], $user_id, 
                            $content, $target_date, $status, $status_date);
        } else {
            $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                        target, target_date, status, status_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sub_query);
            $stmt->bind_param("iiissss", $program_id, $current_period['period_id'], $user_id, 
                            $target, $target_date, $status, $status_date);
        }
        
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        return [
            'success' => true,
            'program_id' => $program_id,
            'message' => 'Program created successfully'
        ];
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return ['error' => 'Failed to create program: ' . $e->getMessage()];
    }
}

/**
 * Update program details and submission
 * @param array $data Program data
 * @return array Result of update
 */
function update_agency_program($data) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Validate inputs
    $program_id = intval($data['program_id'] ?? 0);
    $program_name = trim($conn->real_escape_string($data['program_name'] ?? ''));
    $description = $conn->real_escape_string($data['description'] ?? '');
    $start_date = $conn->real_escape_string($data['start_date'] ?? null);
    $end_date = $conn->real_escape_string($data['end_date'] ?? null);
    $target = $conn->real_escape_string($data['target'] ?? '');
    $target_date = $conn->real_escape_string($data['target_date'] ?? null);
    $status = $conn->real_escape_string($data['status'] ?? '');
    $status_date = $conn->real_escape_string($data['status_date'] ?? null);
    $achievement = $conn->real_escape_string($data['achievement'] ?? '');
    $remarks = $conn->real_escape_string($data['remarks'] ?? '');
    
    if (!$program_id) {
        return ['error' => 'Invalid program ID'];
    }
    
    if (empty($program_name)) {
        return ['error' => 'Program name is required'];
    }
    
    if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
        return ['error' => 'End date cannot be before start date'];
    }
    
    if (!in_array($status, ['on-track', 'delayed', 'completed', 'not-started'])) {
        return ['error' => 'Invalid status'];
    }
    
    $user_id = $_SESSION['user_id'];
    $has_content_json = has_content_json_schema();
    $has_is_assigned = has_is_assigned_column();
    
    // Verify ownership
    $check_query = "SELECT program_id" . ($has_is_assigned ? ", is_assigned" : "") . 
                  " FROM programs WHERE program_id = ? AND owner_agency_id = ?";
    
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $program_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['error' => 'You do not have permission to update this program'];
    }
    
    $program = $result->fetch_assoc();
    $is_assigned = $has_is_assigned ? $program['is_assigned'] : 1; // Default to assigned if column doesn't exist
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Update program details
        if ($is_assigned) {
            // For assigned programs, only update description
            $update_query = "UPDATE programs SET description = ?, updated_at = NOW() WHERE program_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $description, $program_id);
        } else {
            // For agency-created programs, allow updating all fields
            $update_query = "UPDATE programs SET program_name = ?, description = ?, start_date = ?, end_date = ?, updated_at = NOW() WHERE program_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssi", $program_name, $description, $start_date, $end_date, $program_id);
        }
        $stmt->execute();
        
        // Get current period
        $current_period = get_current_reporting_period();
        if (!$current_period) {
            throw new Exception('No active reporting period found');
        }
        
        // Check if submission exists for current period
        $check_sub = "SELECT submission_id FROM program_submissions WHERE program_id = ? AND period_id = ?";
        $stmt = $conn->prepare($check_sub);
        $stmt->bind_param("ii", $program_id, $current_period['period_id']);
        $stmt->execute();
        $sub_result = $stmt->get_result();
        
        if ($sub_result->num_rows > 0) {
            $sub_id = $sub_result->fetch_assoc()['submission_id'];
            
            // Update existing submission based on schema
            if ($has_content_json) {
                $content = json_encode([
                    'target' => $target,
                    'achievement' => $achievement,
                    'remarks' => $remarks
                ]);
                
                $sub_query = "UPDATE program_submissions SET content_json = ?, target_date = ?, status = ?, 
                             status_date = ?, updated_at = NOW() WHERE submission_id = ?";
                $stmt = $conn->prepare($sub_query);
                $stmt->bind_param("ssssi", $content, $target_date, $status, $status_date, $sub_id);
            } else {
                $sub_query = "UPDATE program_submissions SET target = ?, target_date = ?, status = ?, 
                             status_date = ?, achievement = ?, remarks = ?, updated_at = NOW() 
                             WHERE submission_id = ?";
                $stmt = $conn->prepare($sub_query);
                $stmt->bind_param("ssssssi", $target, $target_date, $status, $status_date, $achievement, $remarks, $sub_id);
            }
        } else {
            // Create new submission for current period based on schema
            if ($has_content_json) {
                $content = json_encode([
                    'target' => $target,
                    'achievement' => $achievement,
                    'remarks' => $remarks
                ]);
                
                $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, content_json, 
                             target_date, status, status_date) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sub_query);
                $stmt->bind_param("iiissss", $program_id, $current_period['period_id'], $user_id, 
                                $content, $target_date, $status, $status_date);
            } else {
                $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, target, 
                             target_date, status, status_date, achievement, remarks) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sub_query);
                $stmt->bind_param("iiissssss", $program_id, $current_period['period_id'], $user_id, 
                                $target, $target_date, $status, $status_date, $achievement, $remarks);
            }
        }
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        return [
            'success' => true,
            'message' => 'Program updated successfully'
        ];
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return ['error' => 'Failed to update program: ' . $e->getMessage()];
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
    $has_is_assigned = has_is_assigned_column();
    $has_content_json = has_content_json_schema();
    
    // Get program info
    $program_query = "SELECT p.*, s.sector_name" . ($has_is_assigned ? "" : ", 1 as is_assigned") . 
                    " FROM programs p JOIN sectors s ON p.sector_id = s.sector_id
                     WHERE p.program_id = ? AND p.owner_agency_id = ?";
    
    $stmt = $conn->prepare($program_query);
    $stmt->bind_param("ii", $program_id, $user_id);
    $stmt->execute();
    $program_result = $stmt->get_result();
    
    if ($program_result->num_rows === 0) {
        return null; // Program not found or not owned by this agency
    }
    
    $program = $program_result->fetch_assoc();
    
    // Get all submissions
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
        if ($has_content_json && isset($row['content_json'])) {
            $row = process_content_json($row);
        }
        $submissions[] = $row;
    }
    
    $program['submissions'] = $submissions;
    
    // Get current submission if available
    $current_period = get_current_reporting_period();
    if ($current_period) {
        $current_query = "SELECT * FROM program_submissions 
                          WHERE program_id = ? AND period_id = ?";
        
        $stmt = $conn->prepare($current_query);
        $stmt->bind_param("ii", $program_id, $current_period['period_id']);
        $stmt->execute();
        $current_result = $stmt->get_result();
        
        if ($current_result->num_rows > 0) {
            $current_submission = $current_result->fetch_assoc();
            if ($has_content_json && isset($current_submission['content_json'])) {
                $current_submission = process_content_json($current_submission);
            }
            $program['current_submission'] = $current_submission;
        }
    }
    
    return $program;
}

/**
 * Get all sectors programs
 * 
 * This function allows agency users to view programs from other sectors (read-only)
 * 
 * @param int $current_period_id Optional - filter by reporting period
 * @return array Programs from all sectors with their details
 */
function get_all_sectors_programs($current_period_id = null) {
    global $conn;
    
    $has_content_json = has_content_json_schema();
    
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
            if ($has_content_json) {
                $sub_query = "SELECT content_json, target_date, status, status_date
                              FROM program_submissions
                              WHERE program_id = ? AND period_id = ?
                              ORDER BY submission_date DESC
                              LIMIT 1";
            } else {
                $sub_query = "SELECT target, target_date, achievement, status, status_date, remarks
                              FROM program_submissions
                              WHERE program_id = ? AND period_id = ?
                              ORDER BY submission_date DESC
                              LIMIT 1";
            }
                          
            $stmt = $conn->prepare($sub_query);
            $stmt->bind_param('ii', $row['program_id'], $current_period_id);
            $stmt->execute();
            $sub_result = $stmt->get_result();
            
            if ($sub_result && $sub_result->num_rows > 0) {
                $sub_data = $sub_result->fetch_assoc();
                if ($has_content_json) {
                    $sub_data = process_content_json($sub_data);
                }
                $row = array_merge($row, $sub_data);
            } else {
                $row['target'] = null;
                $row['target_date'] = null;
                $row['achievement'] = null;
                $row['status'] = null;
                $row['status_date'] = null;
                $row['remarks'] = null;
            }
            $stmt->close();
        }
        
        $programs[] = $row;
    }
    
    return $programs;
}

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
    
    // Get the number of metrics and submissions - check if tables exist first
    $metrics_data = ['total_metrics' => 0, 'metrics_submitted' => 0];
    $metric_tables_check = $conn->query("SHOW TABLES LIKE 'sector_metrics_definition'");
    
    if ($metric_tables_check->num_rows > 0) {
        $metrics_query = "SELECT 
                        (SELECT COUNT(*) FROM sector_metrics_definition smd 
                         WHERE smd.sector_id = (SELECT sector_id FROM users WHERE user_id = ?)) AS total_metrics,
                        (SELECT COUNT(*) FROM sector_metric_values smv 
                         WHERE smv.period_id = ? AND smv.agency_id = ?) AS metrics_submitted";
        
        $stmt = $conn->prepare($metrics_query);
        $stmt->bind_param("iii", $user_id, $period_id, $user_id);
        $stmt->execute();
        $metrics_result = $stmt->get_result();
        if ($metrics_result->num_rows > 0) {
            $metrics_data = $metrics_result->fetch_assoc();
        }
    }
    
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
 * Get a specific reporting period by ID
 * @param int $period_id Period ID
 * @return array|null Period details or null if not found
 */
function get_agency_reporting_period($period_id) {
    global $conn;
    
    $period_id = intval($period_id);
    if (!$period_id) return null;
    
    $query = "SELECT * FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}
?>
