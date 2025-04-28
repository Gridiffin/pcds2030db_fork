<?php
/**
 * Agency Program Management Functions
 * 
 * Contains functions for managing agency programs (add, update, delete, submit)
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';

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
 * Create a new program for an agency as a draft
 * @param array $data Program data
 * @return array Result of creation
 */
function create_agency_program_draft($data) {
    global $conn;
    
    if (!is_agency()) {
        return format_error('Permission denied', 403);
    }
    
    // Basic validation - only program_name is required for drafts
    $validated = validate_form_input($data, ['program_name']);
    if (isset($validated['error'])) {
        return $validated;
    }
    
    $program_name = $validated['program_name'];
    $description = $validated['description'] ?? '';
    $start_date = $validated['start_date'] ?? null;
    $end_date = $validated['end_date'] ?? null;
    $target = $validated['target'] ?? '';
    $status = $validated['status'] ?? 'not-started';
    $status_date = $validated['status_date'] ?? date('Y-m-d');
    $status_text = $validated['status_text'] ?? '';
    
    // Validate dates if provided
    if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
        return format_error('End date cannot be before start date');
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
        
        // Insert submission as draft based on schema
        if ($has_content_json) {
            $content = json_encode([
                'target' => $target,
                'status_date' => $status_date,
                'status_text' => $status_text,
                'achievement' => '',
                'remarks' => '',
                'status' => $status // Include status in JSON content for consistency
            ]);
            
            $is_draft = 1; // Set as draft
            
            $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                        content_json, status, is_draft) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sub_query);
            $stmt->bind_param("iiissi", $program_id, $current_period['period_id'], $user_id, 
                            $content, $status, $is_draft);
        } else {
            $is_draft = 1; // Set as draft
            
            $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                        target, status, status_date, is_draft) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sub_query);
            $stmt->bind_param("iiisssi", $program_id, $current_period['period_id'], $user_id, 
                            $target, $status, $status_date, $is_draft);
        }
        
        $stmt->execute();
        $conn->commit();
        
        return format_success('Program saved as draft successfully', ['program_id' => $program_id]);
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
    
    // Create JSON content - Include status in the JSON content
    $content = [
        'target' => $target,
        'status_date' => $status_date,
        'status_text' => $status_text,
        'achievement' => $achievement,
        'remarks' => $remarks,
        'status' => $status // Store status in JSON for consistency
    ];
    
    $content_json = json_encode($content);
    $user_id = $_SESSION['user_id'];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Check if this is an assigned program
        $check_query = "SELECT is_assigned FROM programs WHERE program_id = ? AND owner_agency_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $program_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $is_assigned = 0;
        
        if ($result->num_rows > 0) {
            $program = $result->fetch_assoc();
            $is_assigned = $program['is_assigned'];
        }
        
        // First update the program's timeline if provided
        if ($start_date && $end_date) {
            // Update timeline regardless of assigned status (allow update)
            $update_program = "UPDATE programs SET start_date = ?, end_date = ?, updated_at = NOW() WHERE program_id = ?";
            $stmt = $conn->prepare($update_program);
            $stmt->bind_param("ssi", $start_date, $end_date, $program_id);
            $stmt->execute();
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
?>