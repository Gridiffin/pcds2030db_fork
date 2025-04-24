<?php
/**
 * Agency-specific functions
 * 
 * Functions used by agency users to manage programs and submit data.
 */

// Include utilities
require_once 'utilities.php';

/**
 * Get agency sector metrics
 */
function get_agency_sector_metrics(){
    global $conn;

    $query = "SELECT * FROM sector_metrics_submitted";
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
 * Get Draft Metric
*/

function get_draft_metric(){
    global $conn;

    $query = "SELECT * FROM sector_metrics_draft";
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
 * Create a new program draft for an agency
 * @param array $data Program data
 * @return array Result of creation
 */
function create_agency_program_draft($data) {
    global $conn;
    
    if (!is_agency()) {
        return format_error('Permission denied', 403);
    }
    
    // Basic validation for drafts (less strict than final submission)
    $program_name = $conn->real_escape_string($data['program_name'] ?? '');
    $description = $conn->real_escape_string($data['description'] ?? '');
    $start_date = $data['start_date'] ?? null;
    $end_date = $data['end_date'] ?? null;
    $target = $conn->real_escape_string($data['target'] ?? '');
    $status = $conn->real_escape_string($data['status'] ?? 'not-started');
    $status_date = $data['status_date'] ?? date('Y-m-d');
    $status_text = $conn->real_escape_string($data['status_text'] ?? '');
    
    // Minimal validation
    if (empty($program_name)) {
        return format_error('Program name is required, even for drafts');
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
        
        // Set draft flag
        $is_draft = 1;
        
        // Insert submission based on schema
        if ($has_content_json) {
            $content = json_encode([
                'target' => $target,
                'status_date' => $status_date,
                'status_text' => $status_text
            ]);
            
            $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                        content_json, status, is_draft) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sub_query);
            $stmt->bind_param("iiissi", $program_id, $current_period['period_id'], $user_id, 
                            $content, $status, $is_draft);
        } else {
            $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                        target, status, status_date, is_draft) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sub_query);
            $stmt->bind_param("iiisssi", $program_id, $current_period['period_id'], $user_id, 
                            $target, $status, $status_date, $is_draft);
        }
        
        $stmt->execute();
        $conn->commit();
        
        return format_success('Program draft saved successfully', ['program_id' => $program_id]);
    } catch (Exception $e) {
        $conn->rollback();
        return format_error('Failed to create program draft: ' . $e->getMessage());
    }
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
    
    // Extract timeline data for program table update
    $start_date = $validated['start_date'] ?? null;
    $end_date = $validated['end_date'] ?? null;
    
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
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // First update the program's timeline if provided
        if ($start_date && $end_date) {
            // Check if this is a user-created program (not assigned)
            $check_query = "SELECT is_assigned FROM programs WHERE program_id = ? AND owner_agency_id = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("ii", $program_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $program = $result->fetch_assoc();
                
                // Only update timeline for non-assigned programs or if user has edit permissions
                if ($program['is_assigned'] == 0) {
                    $update_program = "UPDATE programs SET start_date = ?, end_date = ?, updated_at = NOW() WHERE program_id = ?";
                    $stmt = $conn->prepare($update_program);
                    $stmt->bind_param("ssi", $start_date, $end_date, $program_id);
                    $stmt->execute();
                }
            }
        }
        
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
        
        $stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        
        return [
            'success' => true,
            'message' => $is_draft ? 'Program data saved as draft' : 'Program data submitted successfully'
        ];
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return ['error' => 'Failed to submit program data: ' . $e->getMessage()];
    }
}

/**
 * Get detailed information about a specific program
 * 
 * @param int $program_id The ID of the program to retrieve
 * @return array|false Program details array or false if not found
 */
function get_program_details($program_id) {
    global $conn;
    
    $agency_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT p.*, s.sector_name as sector_name, p.created_at, p.updated_at
                          FROM programs p
                          LEFT JOIN sectors s ON p.sector_id = s.sector_id
                          WHERE p.program_id = ? AND p.owner_agency_id = ?");
    $stmt->bind_param("ii", $program_id, $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $program = $result->fetch_assoc();
    
    // Get submissions for this program
    $stmt = $conn->prepare("SELECT * FROM program_submissions 
                          WHERE program_id = ? 
                          ORDER BY submission_id DESC");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $submissions_result = $stmt->get_result();
    
    $program['submissions'] = [];
    
    if ($submissions_result->num_rows > 0) {
        while ($submission = $submissions_result->fetch_assoc()) {
            $program['submissions'][] = $submission;
        }
        
        // Set current submission (most recent)
        $program['current_submission'] = $program['submissions'][0];
    }
    
    return $program;
}

/**
 * Finalizes a draft submission by changing the is_draft status to 0
 *
 * @param int $submission_id The ID of the submission to finalize
 * @return array Result of the operation with success or error message
 */
function finalize_draft_submission($submission_id) {
    global $conn;
    
    $submission_id = intval($submission_id);
    
    if (!$submission_id) {
        return [
            'error' => 'Invalid submission ID'
        ];
    }
    
    try {
        $stmt = $conn->prepare("UPDATE program_submissions SET is_draft = 0 WHERE submission_id = ?");
        $stmt->bind_param("i", $submission_id);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Draft successfully finalized'
            ];
        } else {
            return [
                'error' => 'Failed to finalize draft: ' . $stmt->error
            ];
        }
    } catch (Exception $e) {
        return [
            'error' => 'Database error: ' . $e->getMessage()
        ];
    }
}

/**
 * Get submission status for an agency
 * 
 * This function retrieves statistics about program submissions for a specific agency
 * 
 * @param int $agency_id The ID of the agency
 * @param int $period_id Optional period ID to filter by specific reporting period
 * @return array Statistics about program submissions
 */
function get_agency_submission_status($agency_id, $period_id = null) {
    global $conn;
    
    // Ensure we have valid input
    $agency_id = intval($agency_id);
    if (!$agency_id) {
        return ['error' => 'Invalid agency ID'];
    }
    
    // Initialize return data structure
    $result = [
        'total_programs' => 0,
        'submitted_count' => 0,
        'draft_count' => 0,
        'not_submitted' => 0,
        'program_status' => [
            'on-track' => 0,
            'delayed' => 0,
            'completed' => 0,
            'not-started' => 0
        ]
    ];
    
    try {
        // Get total programs for this agency
        $programs_query = "SELECT COUNT(*) as total FROM programs WHERE owner_agency_id = ?";
        $stmt = $conn->prepare($programs_query);
        $stmt->bind_param("i", $agency_id);
        $stmt->execute();
        $programs_result = $stmt->get_result();
        $result['total_programs'] = $programs_result->fetch_assoc()['total'];
        
        // If no programs, return early
        if ($result['total_programs'] == 0) {
            return $result;
        }
        
        // Get submission status counts
        $status_query = "SELECT 
                            ps.status,
                            COUNT(*) as count,
                            SUM(CASE WHEN ps.is_draft = 1 THEN 1 ELSE 0 END) as draft_count,
                            SUM(CASE WHEN ps.is_draft = 0 THEN 1 ELSE 0 END) as submitted_count
                        FROM programs p
                        LEFT JOIN (
                            SELECT program_id, status, is_draft, 
                                   ROW_NUMBER() OVER (PARTITION BY program_id ORDER BY submission_id DESC) as rn
                            FROM program_submissions";
        
        // Add period filter if specified
        if ($period_id) {
            $status_query .= " WHERE period_id = " . intval($period_id);
        }
        
        $status_query .= ") ps ON p.program_id = ps.program_id AND ps.rn = 1
                          WHERE p.owner_agency_id = ?
                          GROUP BY ps.status";
        
        $stmt = $conn->prepare($status_query);
        $stmt->bind_param("i", $agency_id);
        $stmt->execute();
        $status_result = $stmt->get_result();
        
        // Initialize counters
        $submitted_total = 0;
        $draft_total = 0;
        
        // Process each status group
        while ($status_row = $status_result->fetch_assoc()) {
            $status = $status_row['status'] ?? 'not-started';
            $count = $status_row['count'] ?? 0;
            $draft_count = $status_row['draft_count'] ?? 0;
            $submitted_count = $status_row['submitted_count'] ?? 0;
            
            // Map status to our categories and increment counters
            switch (strtolower($status)) {
                case 'on-track':
                case 'on-track-yearly':
                    $result['program_status']['on-track'] += $submitted_count;
                    break;
                case 'delayed':
                case 'severe-delay':
                    $result['program_status']['delayed'] += $submitted_count;
                    break;
                case 'completed':
                case 'target-achieved':
                    $result['program_status']['completed'] += $submitted_count;
                    break;
                case 'not-started':
                default:
                    $result['program_status']['not-started'] += $submitted_count;
                    break;
            }
            
            // Update totals
            $submitted_total += $submitted_count;
            $draft_total += $draft_count;
        }
        
        // Update summary stats
        $result['submitted_count'] = $submitted_total;
        $result['draft_count'] = $draft_total;
        $result['not_submitted'] = $result['total_programs'] - ($submitted_total + $draft_total);
        
        return $result;
    } catch (Exception $e) {
        error_log("Error in get_agency_submission_status: " . $e->getMessage());
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get all programs from all sectors, optionally filtered by period
 * 
 * This function retrieves programs from all sectors, for the agency view
 * 
 * @param int $period_id Optional period ID to filter by specific reporting period
 * @return array List of programs from all sectors
 */
function get_all_sectors_programs($period_id = null) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    // Get current agency's sector for highlighting
    $agency_id = $_SESSION['user_id'];
    $current_sector_id = $_SESSION['sector_id'] ?? 0;
    
    // Initialize query parts
    $has_content_json = has_content_json_schema();
    
    // Base query
    $query = "SELECT 
                p.program_id, 
                p.program_name, 
                p.description, 
                p.start_date, 
                p.end_date,
                p.created_at,
                p.updated_at,
                p.sector_id,
                s.sector_name,
                u.agency_name";
    
    // Add content json field or target/achievement fields based on schema
    if ($has_content_json) {
        $query .= ", ps.content_json";
    } else {
        $query .= ", ps.target, ps.achievement, ps.status_date, ps.status_text";
    }
    
    // Always add status
    $query .= ", ps.status, ps.is_draft
              FROM programs p
              JOIN sectors s ON p.sector_id = s.sector_id
              JOIN users u ON p.owner_agency_id = u.user_id
              LEFT JOIN (";
    
    // Subquery to get latest submission for each program 
    $subquery = "SELECT 
                    program_id, 
                    status, 
                    is_draft";
    
    if ($has_content_json) {
        $subquery .= ", content_json";
    } else {
        $subquery .= ", target, achievement, status_date, status_text";
    }
    
    $subquery .= " FROM program_submissions";
    
    // Add period filter if specified
    if ($period_id) {
        $subquery .= " WHERE period_id = " . intval($period_id);
    }
    
    // Use window function to rank submissions by recency
    $subquery .= " ORDER BY CASE WHEN period_id = " . ($period_id ?? 0) . " THEN 0 ELSE 1 END, 
                   submission_id DESC";
    
    $query .= $subquery . ") ps ON p.program_id = ps.program_id
              GROUP BY p.program_id 
              ORDER BY (p.sector_id = ?) DESC, p.created_at DESC";
    
    // Execute query
    try {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $current_sector_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $programs = [];
        while ($row = $result->fetch_assoc()) {
            // Process content_json field if needed
            $programs[] = process_content_json($row);
        }
        
        return $programs;
    } catch (Exception $e) {
        error_log("Error in get_all_sectors_programs: " . $e->getMessage());
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get the name of a sector by its ID
 * 
 * @param int $sector_id The ID of the sector
 * @return string The name of the sector or 'Unknown Sector' if not found
 */
function get_sector_name($sector_id) {
    global $conn;
    
    $sector_id = intval($sector_id);
    if (!$sector_id) {
        return 'Unknown Sector';
    }
    
    try {
        $query = "SELECT sector_name FROM sectors WHERE sector_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $sector_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['sector_name'];
        } else {
            return 'Unknown Sector';
        }
    } catch (Exception $e) {
        error_log("Error in get_sector_name: " . $e->getMessage());
        return 'Unknown Sector';
    }
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
