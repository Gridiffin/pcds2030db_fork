<?php
/**
 * Agency-specific functions
 * 
 * Functions used by agency users to manage programs and submit data.
 */

// Include utilities
require_once 'utilities.php';

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
    return has_database_column('program_submissions', 'content_json');
}

/**
 * Check if programs table has is_assigned column
 * @return boolean
 */
function has_is_assigned_column() {
    return has_database_column('programs', 'is_assigned');
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
            // Extract all content fields into the row
            $row['current_target'] = $content['target'] ?? null;
            $row['status_date'] = $content['status_date'] ?? null;
            $row['status_text'] = $content['status_text'] ?? null;
            $row['achievement'] = $content['achievement'] ?? null;
            $row['achievement_date'] = $content['achievement_date'] ?? null;
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
        
        // Common fields for both schemas - updated for JSON structure
        $common_fields = "(SELECT ps.status FROM program_submissions ps 
                       WHERE ps.program_id = p.program_id 
                       ORDER BY ps.submission_id DESC LIMIT 1) AS status";
        
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
    
    // Updated common fields to only include status which remains a column
    $common_fields = "(SELECT ps.status FROM program_submissions ps 
                     WHERE ps.program_id = p.program_id 
                     ORDER BY ps.submission_id DESC LIMIT 1) AS status";
    
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
        return format_error('Permission denied', 403);
    }
    
    // Validate and sanitize inputs
    $validated = validate_form_input($data, ['program_name', 'target', 'status']);
    if (isset($validated['error'])) {
        return $validated;
    }
    
    $program_name = $validated['program_name'];
    $description = $validated['description'] ?? '';
    $start_date = $validated['start_date'] ?? null;
    $end_date = $validated['end_date'] ?? null;
    $target = $validated['target'];
    $status = $validated['status'];
    $status_date = $validated['status_date'] ?? null;
    $status_text = $validated['status_text'] ?? '';
    
    // Validate dates
    if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
        return format_error('End date cannot be before start date');
    }
    
    // Validate status value - use the new status values
    if (!in_array($status, ['target-achieved', 'on-track-yearly', 'severe-delay', 'not-started'])) {
        return ['error' => 'Invalid status value'];
    }
    
    $user_id = $_SESSION['user_id'];
    $sector_id = $_SESSION['sector_id'];
    $has_content_json = has_content_json_schema();
    $has_is_assigned = has_is_assigned_column();
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert program
        if ($has_is_assigned) {
            $query = "INSERT INTO programs (program_name, description, start_date, end_date, 
                                        owner_agency_id, sector_id, created_by, is_assigned) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssiii", $program_name, $description, $start_date, $end_date, 
                            $user_id, $sector_id, $user_id);
        } else {
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
        
        // Insert submission based on schema
        if ($has_content_json) {
            $content = json_encode([
                'target' => $target,
                'status_date' => $status_date,
                'status_text' => $status_text
            ]);
            
            $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                        content_json, status) 
                        VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sub_query);
            $stmt->bind_param("iiiss", $program_id, $current_period['period_id'], $user_id, 
                            $content, $status);
        } else {
            $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                        target, status, status_date) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt->prepare($sub_query);
            $stmt->bind_param("iiisss", $program_id, $current_period['period_id'], $user_id, 
                            $target, $status, $status_date);
        }
        
        $stmt->execute();
        $conn->commit();
        
        return format_success('Program created successfully', ['program_id' => $program_id]);
    } catch (Exception $e) {
        $conn->rollback();
        return format_error('Failed to create program: ' . $e->getMessage());
    }
}

/**
 * Create a new program as a draft
 * This version has relaxed validation requirements
 * 
 * @param array $data Program data
 * @return array Result of creation
 */
function create_agency_program_draft($data) {
    global $conn;
    
    if (!is_agency()) {
        return format_error('Permission denied', 403);
    }
    
    // Basic validation - only program name is required for drafts
    if (empty($data['program_name'])) {
        return format_error('Program name is required even for drafts');
    }
    
    // Sanitize inputs
    $program_name = $conn->real_escape_string($data['program_name']);
    $description = $conn->real_escape_string($data['description'] ?? '');
    $start_date = $conn->real_escape_string($data['start_date'] ?? null);
    $end_date = $conn->real_escape_string($data['end_date'] ?? null);
    $target = $conn->real_escape_string($data['target'] ?? '');
    $status = $conn->real_escape_string($data['status'] ?? 'not-started');
    $status_date = $conn->real_escape_string($data['status_date'] ?? date('Y-m-d'));
    
    // Basic date validation if dates are provided
    if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
        return format_error('End date cannot be before start date');
    }
    
    $user_id = $_SESSION['user_id'];
    $sector_id = $_SESSION['sector_id'];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert program
        $query = "INSERT INTO programs (program_name, description, start_date, end_date, 
                                    owner_agency_id, sector_id, created_by, is_assigned) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssiii", $program_name, $description, $start_date, $end_date, 
                        $user_id, $sector_id, $user_id);
        $stmt->execute();
        $program_id = $conn->insert_id;
        
        // Get current period
        $current_period = get_current_reporting_period();
        if (!$current_period) {
            throw new Exception('No active reporting period found');
        }
        
        // Create content JSON
        $content = json_encode([
            'target' => $target,
            'status_date' => $status_date,
            'status_text' => '',
            'achievement' => '',
            'remarks' => ''
        ]);
        
        // Insert as draft submission
        $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                    content_json, status, is_draft) 
                    VALUES (?, ?, ?, ?, ?, 1)";
        
        $stmt = $conn->prepare($sub_query);
        $stmt->bind_param("iiiss", $program_id, $current_period['period_id'], $user_id, 
                        $content, $status);
        $stmt->execute();
        
        $conn->commit();
        
        return format_success('Program saved as draft successfully', ['program_id' => $program_id]);
    } catch (Exception $e) {
        $conn->rollback();
        return format_error('Failed to create program draft: ' . $e->getMessage());
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
    $status = $conn->real_escape_string($data['status'] ?? '');
    $status_date = $conn->real_escape_string($data['status_date'] ?? null);
    $status_text = $conn->real_escape_string($data['status_text'] ?? '');
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
                    'status_date' => $status_date,
                    'status_text' => $status_text
                ]);
                
                // Simplified update with minimal columns
                $sub_query = "UPDATE program_submissions SET content_json = ?, status = ?, 
                             updated_at = NOW() WHERE submission_id = ?";
                $stmt = $conn->prepare($sub_query);
                $stmt->bind_param("ssi", $content, $status, $sub_id);
            } else {
                $sub_query = "UPDATE program_submissions SET target = ?, status = ?, 
                             status_date = ?, achievement = ?, remarks = ?, updated_at = NOW() 
                             WHERE submission_id = ?";
                $stmt = $conn->prepare($sub_query);
                $stmt->bind_param("sssssi", $target, $status, $status_date, $achievement, $remarks, $sub_id);
            }
        } else {
            // Create new submission for current period
            if ($has_content_json) {
                $content = json_encode([
                    'target' => $target,
                    'status_date' => $status_date,
                    'status_text' => $status_text
                ]);
                
                // Simplified insert with minimal columns
                $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                             content_json, status) 
                             VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sub_query);
                $stmt->bind_param("iiiss", $program_id, $current_period['period_id'], $user_id, 
                                $content, $status);
            } else {
                $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, target, 
                             status, status_date, achievement, remarks) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sub_query);
                $stmt->bind_param("iiisssss", $program_id, $current_period['period_id'], $user_id, 
                                $target, $status, $status_date, $achievement, $remarks);
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
                $sub_query = "SELECT content_json, status
                              FROM program_submissions
                              WHERE program_id = ? AND period_id = ?
                              ORDER BY submission_date DESC
                              LIMIT 1";
            } else {
                $sub_query = "SELECT target, achievement, status, status_date, remarks
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

/**
 * Enhanced submission with better JSON usage
 */
function submit_program_data_enhanced($data) {
    global $conn;
    
    // Core fields stay as columns for performance/integrity
    $program_id = intval($data['program_id']);
    $period_id = intval($data['period_id']);
    $status = $conn->real_escape_string($data['status']);
    $status_date = $data['status_date'];
    
    // Put all variable content in JSON
    $content = [
        'target' => $data['target'],
        'achievement' => $data['achievement'] ?? null,
        'remarks' => $data['remarks'] ?? null,
        // Add any new fields without schema changes
        'custom_metrics' => $data['custom_metrics'] ?? null,
        'attachments' => $data['attachments'] ?? null
    ];
    
    $content_json = json_encode($content);
    
    // Insert with minimal columns + rich JSON content
    $query = "INSERT INTO program_submissions (program_id, period_id, status, status_date, content_json) 
              VALUES (?, ?, ?, ?, ?)";
    // ...existing code...
}

/**
 * Submit data for a program
 * @param array $data Program data
 * @param bool $is_draft Whether this is a draft submission
 * @return array Result of submission
 */
function submit_program_data($data, $is_draft = false) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Validate and sanitize inputs
    $program_id = intval($data['program_id'] ?? 0);
    $period_id = intval($data['period_id'] ?? 0);
    
    if (!$program_id || !$period_id) {
        return ['error' => 'Missing required parameters'];
    }
    
    // Only perform full validation if not a draft
    if (!$is_draft) {
        $validation_fields = ['target', 'status'];
        $validated = validate_form_input($data, $validation_fields);
        if (isset($validated['error'])) {
            return $validated;
        }
    } else {
        // Basic validation even for drafts
        $validated = [];
        foreach ($data as $key => $value) {
            $validated[$key] = $conn->real_escape_string($value);
        }
    }
    
    // Extract data
    $target = $validated['target'] ?? '';
    $achievement = $validated['achievement'] ?? '';
    $status = $validated['status'] ?? 'not-started';
    $status_date = $validated['status_date'] ?? date('Y-m-d');
    $status_text = $validated['status_text'] ?? '';
    $remarks = $validated['remarks'] ?? '';
    
    // Create JSON content
    $content = [
        'target' => $target,
        'status_date' => $status_date,
        'status_text' => $status_text,
        'achievement' => $achievement,
        'remarks' => $remarks
    ];
    
    $content_json = json_encode($content);
    $user_id = $_SESSION['user_id'];
    
    // Check if a submission already exists for this program and period
    $check_query = "SELECT submission_id FROM program_submissions 
                   WHERE program_id = ? AND period_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing submission
        $submission_id = $result->fetch_assoc()['submission_id'];
        $query = "UPDATE program_submissions SET content_json = ?, status = ?, is_draft = ?, 
                 updated_at = NOW() WHERE submission_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $content_json, $status, $is_draft, $submission_id);
    } else {
        // Create new submission
        $query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                 content_json, status, is_draft) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiissi", $program_id, $period_id, $user_id, $content_json, $status, $is_draft);
    }
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => $is_draft ? 'Program data saved as draft' : 'Program data submitted successfully'
        ];
    } else {
        return ['error' => 'Failed to submit program data: ' . $stmt->error];
    }
}

/**
 * Finalize a draft submission
 * @param int $submission_id The ID of the draft submission to finalize
 * @return array Result of finalization
 */
function finalize_draft_submission($submission_id) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    $submission_id = intval($submission_id);
    $user_id = $_SESSION['user_id'];
    
    // Verify ownership and draft status
    $check_query = "SELECT ps.* FROM program_submissions ps
                   JOIN programs p ON ps.program_id = p.program_id
                   WHERE ps.submission_id = ? AND p.owner_agency_id = ? AND ps.is_draft = 1";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $submission_id, $user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        return ['error' => 'Draft not found or not owned by your agency'];
    }
    
    // Perform validation on the draft before finalizing
    $get_data = "SELECT * FROM program_submissions WHERE submission_id = ?";
    $stmt = $conn->prepare($get_data);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $submission = $stmt->get_result()->fetch_assoc();
    
    // Decode JSON content
    $content = json_decode($submission['content_json'], true);
    
    // Validate required fields
    if (empty($content['target'])) {
        return ['error' => 'Target is required before finalizing'];
    }
    
    if (empty($submission['status'])) {
        return ['error' => 'Status is required before finalizing'];
    }
    
    // Update to mark as finalized (not a draft)
    $query = "UPDATE program_submissions SET is_draft = 0, updated_at = NOW() WHERE submission_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $submission_id);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Draft finalized successfully'
        ];
    } else {
        return ['error' => 'Failed to finalize draft: ' . $stmt->error];
    }
}

/**
 * Check if a program has a draft submission for the current period
 * @param int $program_id The program ID to check
 * @return bool True if the program has a draft submission
 */
function is_program_draft($program_id) {
    global $conn;
    $current_period = get_current_reporting_period();
    
    if (!$current_period) {
        return false;
    }
    
    $period_id = $current_period['period_id'];
    $query = "SELECT is_draft FROM program_submissions 
             WHERE program_id = ? AND period_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['is_draft'] == 1;
    }
    
    return false;
}

/**
 * Delete a program
 * @param int $program_id The program ID to delete
 * @return array Result of deletion operation
 */
function delete_agency_program($program_id) {
    global $conn;
    
    if (!is_agency()) {
        return format_error('Permission denied', 403);
    }
    
    $program_id = intval($program_id);
    $user_id = $_SESSION['user_id'];
    
    // Verify program exists and belongs to this agency
    $query = "SELECT * FROM programs WHERE program_id = ? AND owner_agency_id = ? AND is_assigned = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $program_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return format_error('Program not found or you do not have permission to delete it');
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete program submissions first
        $delete_submissions = "DELETE FROM program_submissions WHERE program_id = ?";
        $stmt = $conn->prepare($delete_submissions);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        
        // Then delete the program
        $delete_program = "DELETE FROM programs WHERE program_id = ?";
        $stmt = $conn->prepare($delete_program);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        return format_success('Program deleted successfully');
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return format_error('Failed to delete program: ' . $e->getMessage());
    }
}

/**
 * Get all programs for an agency
 * @param int $agency_id Agency ID
 * @return array List of programs for the agency
 */
if (!function_exists('get_agency_programs')) {
    function get_agency_programs($agency_id) {
        global $conn;
        
        $query = "SELECT p.*, 
                  (SELECT ps.status FROM program_submissions ps 
                   WHERE ps.program_id = p.program_id 
                   ORDER BY ps.submission_date DESC LIMIT 1) as status,
                  (SELECT ps.is_draft FROM program_submissions ps 
                   WHERE ps.program_id = p.program_id 
                   ORDER BY ps.submission_date DESC LIMIT 1) as is_draft,
                  (SELECT ps.submission_date FROM program_submissions ps 
                   WHERE ps.program_id = p.program_id 
                   ORDER BY ps.submission_date DESC LIMIT 1) as updated_at
                  FROM programs p 
                  WHERE p.owner_agency_id = ?
                  ORDER BY p.program_name";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $agency_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $programs = [];
        while ($row = $result->fetch_assoc()) {
            $programs[] = $row;
        }
        
        return $programs;
    }
}

/**
 * Get programs grouped by type (assigned/created)
 * @param int $period_id Optional reporting period ID
 * @return array Programs grouped by type
 */
if (!function_exists('get_agency_programs_by_type')) {
    function get_agency_programs_by_type($period_id = null) {
        global $conn;
        $agency_id = $_SESSION['user_id'];
        
        // Base query
        $query = "SELECT p.*, 
                  (SELECT ps.status FROM program_submissions ps 
                   WHERE ps.program_id = p.program_id 
                   ORDER BY ps.submission_date DESC LIMIT 1) as status,
                  (SELECT ps.submission_date FROM program_submissions ps 
                   WHERE ps.program_id = p.program_id 
                   ORDER BY ps.submission_date DESC LIMIT 1) as updated_at
                  FROM programs p 
                  WHERE p.owner_agency_id = ?";
                  
        // Add filters
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $agency_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $assigned = [];
        $created = [];
        
        while ($row = $result->fetch_assoc()) {
            if ($row['is_assigned']) {
                $assigned[] = $row;
            } else {
                $created[] = $row;
            }
        }
        
        return [
            'assigned' => $assigned,
            'created' => $created
        ];
    }
}

/**
 * Update program submission
 * 
 * @param int $program_id Program ID
 * @param int $period_id Reporting period ID
 * @param string $status Program status
 * @param float $progress Progress percentage
 * @param string $current_target Current target
 * @param string $year_end_target Year-end target
 * @param string $narrative Progress narrative
 * @param string $challenges Challenges encountered
 * @param string $next_steps Next steps planned
 * @param bool $is_draft Whether this is a draft submission
 * @return array Result of the operation
 */
function update_program_submission($program_id, $period_id, $status, $progress, $current_target, $year_end_target, $narrative, $challenges, $next_steps, $is_draft = true) {
    global $conn;
    
    // Validate inputs
    if (!$program_id || !$period_id) {
        return ['error' => 'Invalid program or reporting period.'];
    }
    
    // Ensure status is valid
    if (!is_valid_status($status)) {
        return ['error' => 'Invalid status value.'];
    }
    
    // Check if user has permission to update this program
    if (!can_access_program($program_id)) {
        return ['error' => 'You do not have permission to update this program.'];
    }
    
    // Check if this program already has a submission for this period
    $check_query = "SELECT submission_id FROM program_submissions 
                    WHERE program_id = ? AND period_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing submission
        $submission = $result->fetch_assoc();
        $submission_id = $submission['submission_id'];
        
        // Check if this is a final submission that's already been submitted
        $check_draft_query = "SELECT is_draft FROM program_submissions WHERE submission_id = ?";
        $stmt = $conn->prepare($check_draft_query);
        $stmt->bind_param("i", $submission_id);
        $stmt->execute();
        $draft_result = $stmt->get_result();
        $draft_status = $draft_result->fetch_assoc();
        
        // If the submission is already finalized (is_draft = 0), don't allow updates
        if (isset($draft_status['is_draft']) && $draft_status['is_draft'] == 0 && !is_admin()) {
            return ['error' => 'This program has been submitted as final and cannot be edited.'];
        }
        
        $update_query = "UPDATE program_submissions SET 
                        status = ?, 
                        progress_percentage = ?, 
                        current_target = ?, 
                        year_end_target = ?, 
                        narrative = ?, 
                        challenges = ?, 
                        next_steps = ?,
                        is_draft = ?,
                        submission_date = NOW(), 
                        submitted_by = ? 
                        WHERE submission_id = ?";
        
        $stmt = $conn->prepare($update_query);
        $user_id = $_SESSION['user_id'];
        $is_draft_int = $is_draft ? 1 : 0;
        
        $stmt->bind_param("sdsssssiis", $status, $progress, $current_target, $year_end_target, 
                         $narrative, $challenges, $next_steps, $is_draft_int, $user_id, $submission_id);
    } else {
        // Create new submission
        $insert_query = "INSERT INTO program_submissions 
                        (program_id, period_id, status, progress_percentage, current_target, 
                         year_end_target, narrative, challenges, next_steps, is_draft, submission_date, submitted_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        
        $stmt = $conn->prepare($insert_query);
        $user_id = $_SESSION['user_id'];
        $is_draft_int = $is_draft ? 1 : 0;
        
        $stmt->bind_param("iisdsssssis", $program_id, $period_id, $status, $progress, $current_target, 
                        $year_end_target, $narrative, $challenges, $next_steps, $is_draft_int, $user_id);
    }
    
    if ($stmt->execute()) {
        // Also update the status in the programs table
        $update_program = "UPDATE programs SET 
                           last_status = ?, 
                           last_update = NOW() 
                           WHERE program_id = ?";
        $stmt = $conn->prepare($update_program);
        $stmt->bind_param("si", $status, $program_id);
        $stmt->execute();
        
        return ['success' => true];
    } else {
        return ['error' => 'Database error: ' . $stmt->error];
    }
}

/**
 * Check if a program has been submitted as final
 * @param int $program_id Program ID
 * @param int $period_id Period ID
 * @return bool True if program has been submitted as final
 */
function is_program_submitted_final($program_id, $period_id) {
    global $conn;
    
    $query = "SELECT is_draft FROM program_submissions 
              WHERE program_id = ? AND period_id = ?
              ORDER BY submission_id DESC LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $submission = $result->fetch_assoc();
        return $submission['is_draft'] == 0;
    }
    
    return false;
}

/**
 * Check if user can access a program
 * @param int $program_id Program ID to check
 * @return bool True if user can access the program
 */
function can_access_program($program_id) {
    global $conn;
    
    // Admin can access all programs
    if (is_admin()) {
        return true;
    }
    
    // Agency can only access their own programs
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $query = "SELECT program_id FROM programs 
              WHERE program_id = ? AND owner_agency_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $program_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}
?>
