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
    $validated = validate_form_input($data, ['program_name', 'rating']);
    if (isset($validated['error'])) {
        return $validated;
    }
    
    $program_name = $validated['program_name'];
    $description = $validated['description'] ?? '';
    $start_date = $validated['start_date'] ?? null;
    $end_date = $validated['end_date'] ?? null;
    $rating = $validated['rating'];
    
    // Get targets array
    $targets = [];
    if (isset($data['targets']) && is_array($data['targets'])) {
        foreach ($data['targets'] as $target_data) {
            if (!empty($target_data['text'])) {
                $targets[] = [
                    'target_text' => $conn->real_escape_string($target_data['text']),
                    'status_description' => $conn->real_escape_string($target_data['status_description'] ?? '')
                ];
            }
        }
    }
    
    // Validate at least one target
    if (empty($targets)) {
        return format_error('At least one target is required');
    }
    
    // Validate dates
    if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
        return format_error('End date cannot be before start date');
    }
    
    // Validate rating value
    if (!in_array($rating, ['target-achieved', 'on-track-yearly', 'severe-delay', 'not-started'])) {
        return ['error' => 'Invalid rating value'];
    }
    
    $user_id = $_SESSION['user_id'];
    $sector_id = $_SESSION['sector_id'];
    $has_content_json = has_content_json_schema(); // Assuming this remains relevant for other logic not shown
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
        
        // Insert submission with the new structure
        $content = [
            'program_name' => $program_name, // Snapshot program name
            'description' => $description,   // Snapshot description
            'rating' => $rating,
            'targets' => $targets,
            'remarks' => $data['remarks'] ?? '' // Snapshot remarks if provided
        ];
        
        $content_json = json_encode($content);
        
        $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                    content_json, status, submission_date) 
                    VALUES (?, ?, ?, ?, ?, NOW())"; // Added submission_date
        
        $stmt = $conn->prepare($sub_query);
        $stmt->bind_param("iiiss", $program_id, $current_period['period_id'], $user_id, 
                        $content_json, $rating);
        
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
    $rating = $validated['rating'] ?? 'not-started';
    
    // Get targets array (even empty for drafts)
    $targets = [];
    if (isset($data['targets']) && is_array($data['targets'])) {
        foreach ($data['targets'] as $target_data) {
            if (!empty($target_data['text'])) {
                $targets[] = [
                    'target_text' => $conn->real_escape_string($target_data['text']),
                    'status_description' => $conn->real_escape_string($target_data['status_description'] ?? '')
                ];
            }
        }
    }
    
    // For drafts, we don't require targets to be filled out
    // Just add an empty target if none provided
    if (empty($targets)) {
        $targets[] = [
            'target_text' => '',
            'status_description' => ''
        ];
    }
    
    // Validate dates if provided
    if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
        return format_error('End date cannot be before start date');
    }
    
    $user_id = $_SESSION['user_id'];
    $sector_id = $_SESSION['sector_id'];
    $has_content_json = has_content_json_schema(); // Assuming this remains relevant
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
        
        // Insert submission as draft with new structure
        $content = [
            'program_name' => $program_name, // Snapshot program name
            'description' => $description,   // Snapshot description
            'rating' => $rating,
            'targets' => $targets,
            'remarks' => $data['remarks'] ?? ''
        ];
        
        $content_json = json_encode($content);
        $is_draft = 1; // Set as draft
        
        $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                    content_json, status, is_draft, submission_date) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())"; // Added submission_date
        
        $stmt = $conn->prepare($sub_query);
        $stmt->bind_param("iiissi", $program_id, $current_period['period_id'], $user_id, 
                        $content_json, $rating, $is_draft);
        
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
    $user_id = $_SESSION['user_id']; // Moved user_id up for use in program update ownership check

    if (!$program_id || !$period_id) {
        return ['error' => 'Missing required parameters'];
    }
    
    // Basic validation for all fields that might be submitted
    // $validated array will hold sanitized values
    $validated = [];
    $allowed_keys = ['rating', 'remarks', 'start_date', 'end_date', 'program_name', 'description'];
    foreach ($allowed_keys as $key) {
        if (isset($data[$key])) {
            // For arrays (like targets), handle separately. For strings, sanitize.
            if (!is_array($data[$key])) {
                 $validated[$key] = $conn->real_escape_string(trim($data[$key]));
            } else {
                 $validated[$key] = $data[$key]; // Targets are processed later
            }
        }
    }
    
    // Only perform full validation for rating and targets if not a draft
    if (!$is_draft) {
        if (empty($validated['rating'])) {
             return ['error' => 'Rating is required for final submission.'];
        }
        if (empty($data['targets']) || !is_array($data['targets'])) {
            return ['error' => 'At least one target is required for final submission.'];
        }
    }
    
    // Extract program-level data
    $rating = $validated['rating'] ?? 'not-started';
    $remarks = $validated['remarks'] ?? '';
    
    // Extract timeline and name/description data for program table update
    $program_name_from_data = $validated['program_name'] ?? null;
    $description_from_data = $validated['description'] ?? null;
    $start_date_from_data = !empty($validated['start_date']) ? $validated['start_date'] : null;
    $end_date_from_data = !empty($validated['end_date']) ? $validated['end_date'] : null;

    // Process targets
    $targets = [];
    if (isset($data['targets']) && is_array($data['targets'])) {
        foreach ($data['targets'] as $target_data) {
            $target_text = $conn->real_escape_string($target_data['text'] ?? '');
            if (!$is_draft && empty($target_text)) { // For non-drafts, require target text
                continue; 
            }
            $targets[] = [
                'target_text' => $target_text,
                'status_description' => $conn->real_escape_string($target_data['status_description'] ?? '')
            ];
        }
    }
    
    if (!$is_draft && empty($targets)) {
        return ['error' => 'At least one target with text is required for final submission.'];
    }
    
    if ($is_draft && empty($targets) && !(isset($data['targets']) && is_array($data['targets']))) {
        // If it\'s a draft and no targets array was submitted at all, don\'t force an empty one.
        // Only add an empty one if \'targets\' was an empty array.
        // This preserves existing targets if only other fields are saved in a draft.
        // However, for a *new* submission history entry, we should snapshot current targets.
        // To simplify and ensure snapshot, let's assume if targets are not in $data, they are not being changed for this submission.
        // The $content_json will then reflect this.
    } else if ($is_draft && empty($targets) && isset($data['targets']) && is_array($data['targets'])) {
         // If 'targets' was submitted as an empty array for a draft, then record that.
         $targets = []; // Explicitly empty, or add a placeholder if business logic requires
    }


    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Check if this program belongs to the current agency (security check)
        $check_owner_stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_id = ? AND owner_agency_id = ?");
        $check_owner_stmt->bind_param("ii", $program_id, $user_id);
        $check_owner_stmt->execute();
        if ($check_owner_stmt->get_result()->num_rows === 0) {
            $conn->rollback();
            return ['error' => 'Permission denied or program not found.'];
        }
        $check_owner_stmt->close();

        // Update program\'s main details (name, description, timeline) in \'programs\' table if provided
        $update_fields_pg = [];
        $update_params_pg = [];
        $update_types_pg = "";

        if ($program_name_from_data !== null && !empty(trim($program_name_from_data))) {
            $update_fields_pg[] = "program_name = ?";
            $update_params_pg[] = $program_name_from_data;
            $update_types_pg .= "s";
        }
        if ($description_from_data !== null) { // Allow empty description
            $update_fields_pg[] = "description = ?";
            $update_params_pg[] = $description_from_data;
            $update_types_pg .= "s";
        }
        if ($start_date_from_data) {
            $update_fields_pg[] = "start_date = ?";
            $update_params_pg[] = $start_date_from_data;
            $update_types_pg .= "s";
        }
        if ($end_date_from_data) {
            $update_fields_pg[] = "end_date = ?";
            $update_params_pg[] = $end_date_from_data;
            $update_types_pg .= "s";
        }

        if (!empty($update_fields_pg)) {
            $update_fields_pg[] = "updated_at = NOW()";
            $query_update_program = "UPDATE programs SET " . implode(", ", $update_fields_pg) . " WHERE program_id = ? AND owner_agency_id = ?";
            $update_params_pg[] = $program_id;
            $update_params_pg[] = $user_id;
            $update_types_pg .= "ii";
            
            $stmt_update_program = $conn->prepare($query_update_program);
            if (!$stmt_update_program) { throw new Exception("Prepare failed for program update: " . $conn->error); }
            $stmt_update_program->bind_param($update_types_pg, ...$update_params_pg);
            if (!$stmt_update_program->execute()) { throw new Exception("Execute failed for program update: " . $stmt_update_program->error); }
            $stmt_update_program->close();
        }

        // Fetch the current program_name and description (authoritative, could have been just updated)
        // Also fetch current targets if not provided in $data, to ensure content_json is a full snapshot
        $stmt_fetch_details = $conn->prepare(
            "SELECT p.program_name, p.description, ps_latest.content_json as latest_content 
             FROM programs p
             LEFT JOIN (
                 SELECT program_id, content_json 
                 FROM program_submissions 
                 WHERE program_id = ?
                 ORDER BY submission_date DESC, submission_id DESC LIMIT 1
             ) ps_latest ON p.program_id = ps_latest.program_id
             WHERE p.program_id = ?"
        );
        $stmt_fetch_details->bind_param("ii", $program_id, $program_id);
        $stmt_fetch_details->execute();
        $result_program_details = $stmt_fetch_details->get_result();
        if ($result_program_details->num_rows === 0) {
            throw new Exception("Failed to fetch current program details after update.");
        }
        $current_program_data = $result_program_details->fetch_assoc();
        $current_program_name = $current_program_data['program_name'];
        $current_description = $current_program_data['description'];
        $stmt_fetch_details->close();

        // Determine targets for snapshot: use $data['targets'] if provided, else use latest known targets
        $snapshot_targets = $targets; // Targets processed from $data
        if (!isset($data['targets']) && $current_program_data['latest_content']) {
            $latest_content_decoded = json_decode($current_program_data['latest_content'], true);
            if ($latest_content_decoded && isset($latest_content_decoded['targets'])) {
                $snapshot_targets = $latest_content_decoded['targets'];
            }
        }


        // Create new JSON content for the submission history
        $content = [
            'program_name' => $current_program_name,
            'description' => $current_description,
            'rating' => $rating,
            'targets' => $snapshot_targets, // Use determined targets for snapshot
            'remarks' => $remarks
        ];
        $content_json = json_encode($content);
        
        // Always INSERT a new submission record for history
        $query_insert_submission = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                                 content_json, status, is_draft, submission_date) 
                                 VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt_insert_submission = $conn->prepare($query_insert_submission);
        if (!$stmt_insert_submission) { throw new Exception("Prepare failed for submission insert: " . $conn->error); }
        // $rating is used as status, $is_draft is 0 or 1
        $stmt_insert_submission->bind_param("iiissi", $program_id, $period_id, $user_id, $content_json, $rating, $is_draft);
        if (!$stmt_insert_submission->execute()) { throw new Exception("Execute failed for submission insert: " . $stmt_insert_submission->error); }
        $stmt_insert_submission->close();
        
        $conn->commit();
        
        return [
            'success' => true,
            'message' => $is_draft ? 'Program data saved as draft' : 'Program data submitted successfully'
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return ['error' => 'Failed to submit program data: ' . $e->getMessage()];
    }
}

/**
 * Get detailed information about a specific program
 * Modified to allow viewing from All Sectors page
 * 
 * @param int $program_id The ID of the program to retrieve
 * @param bool $allow_cross_agency Whether to allow viewing programs from other agencies (for All Sectors view)
 * @return array|false Program details array or false if not found
 */
function get_program_details($program_id, $allow_cross_agency = false) {
    global $conn;
    
    $agency_id = $_SESSION['user_id'];
    
    // Only filter by owner_agency_id if cross-agency viewing is not allowed
    if ($allow_cross_agency) {
        $stmt = $conn->prepare("SELECT p.*, s.sector_name as sector_name, p.created_at, p.updated_at,
                              u.agency_name as agency_name
                              FROM programs p
                              LEFT JOIN sectors s ON p.sector_id = s.sector_id
                              LEFT JOIN users u ON p.owner_agency_id = u.user_id
                              WHERE p.program_id = ?");
        $stmt->bind_param("i", $program_id);
    } else {
        $stmt = $conn->prepare("SELECT p.*, s.sector_name as sector_name, p.created_at, p.updated_at,
                              u.agency_name as agency_name
                              FROM programs p
                              LEFT JOIN sectors s ON p.sector_id = s.sector_id
                              LEFT JOIN users u ON p.owner_agency_id = u.user_id
                              WHERE p.program_id = ? AND p.owner_agency_id = ?");
        $stmt->bind_param("ii", $program_id, $agency_id);
    }
    
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
            // Process content JSON for each submission
            if (isset($submission['content_json']) && !empty($submission['content_json'])) {
                $content = json_decode($submission['content_json'], true);
                if ($content) {
                    // Extract contents into submission array for easier access
                    $submission = array_merge($submission, $content);
                }
            }
            
            // Get period info for this submission
            $submission['period_info'] = get_reporting_period($submission['period_id']);
            
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
 * Get program edit history with all previous submissions
 * Shows how program data has changed over time
 * 
 * @param int $program_id The program ID to get history for
 * @return array Program history with all submissions
 */
function get_program_edit_history($program_id) {
    global $conn;
    
    $program_id = intval($program_id);
    if (!$program_id) {
        return ['error' => 'Invalid program ID'];
    }
    
    // First get the current program details from the \'programs\' table
    $stmt_program = $conn->prepare("SELECT p.*, u_creator.agency_name as creator_agency_name 
                                  FROM programs p 
                                  LEFT JOIN users u_creator ON p.created_by = u_creator.user_id
                                  WHERE p.program_id = ?");
    $stmt_program->bind_param("i", $program_id);
    $stmt_program->execute();
    $result_program = $stmt_program->get_result();
    
    if ($result_program->num_rows === 0) {
        return ['error' => 'Program not found'];
    }
    $program_details_current = $result_program->fetch_assoc();
    $stmt_program->close();
      
    // Get all submissions for this program, ordered by date (newest first)
    $stmt_submissions = $conn->prepare("SELECT ps.*, 
                           CONCAT('Q', rp.quarter, '-', rp.year) as period_name, 
                           rp.start_date as period_start, rp.end_date as period_end,
                           u.agency_name as submitted_by_name
                           FROM program_submissions ps
                           LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
                           LEFT JOIN users u ON ps.submitted_by = u.user_id
                           WHERE ps.program_id = ?
                           ORDER BY ps.submission_date DESC, ps.submission_id DESC");
    
    $stmt_submissions->bind_param("i", $program_id);
    $stmt_submissions->execute();
    $result_submissions = $stmt_submissions->get_result();
    
    $submissions_history = [];
    
    while ($row = $result_submissions->fetch_assoc()) {
        if (isset($row['content_json']) && !empty($row['content_json'])) {
            $content = json_decode($row['content_json'], true);
            if ($content) {
                foreach ($content as $key => $value) {
                    // Prefer values from content_json as they are the snapshot for that submission
                    $row[$key] = $value;
                }
                // Ensure \'rating\' from content_json is used as status for that historical point
                if (isset($content['rating'])){
                    $row['status'] = $content['rating'];
                }
            }
        }
        
        $row['formatted_date'] = date('M j, Y H:i', strtotime($row['submission_date']));
        $row['is_draft_label'] = ($row['is_draft'] ?? 0) ? 'Draft' : 'Final'; // Handle potential null is_draft
        
        $submissions_history[] = $row;
    }
    $stmt_submissions->close();
    
    if (empty($submissions_history)) {
        $creator_display_name = $program_details_current['creator_agency_name'] ?? (isset($program_details_current['created_by']) ? 'User ID: ' . $program_details_current['created_by'] : 'N/A');
        $submissions_history[] = [
            'submission_id' => 0, 
            'submission_date' => $program_details_current['created_at'],
            'formatted_date' => date('M j, Y H:i', strtotime($program_details_current['created_at'])),
            'period_name' => 'Initial Record',
            'status' => 'not-started', 
            'is_draft' => 0, 
            'is_draft_label' => 'Created',
            'program_name' => $program_details_current['program_name'], 
            'description' => $program_details_current['description'], 
            'targets' => [], 
            'remarks' => 'Program created.',
            'submitted_by_name' => $creator_display_name
        ];
    }
    
    return [
        'program' => $program_details_current, 
        'submissions' => $submissions_history
    ];
}

/**
 * Compare two program submission values and format for display
 * 
 * @param mixed $current_value Current value
 * @param mixed $previous_value Previous value
 * @param bool $show_diff Whether to show the difference
 * @return array Formatted comparison result
 */
function compare_submission_values($current_value, $previous_value, $show_diff = true) {
    $has_changed = false;
    $formatted_diff = '';
    
    // Handle arrays (like targets)
    if (is_array($current_value) && is_array($previous_value)) {
        return [
            'has_changed' => true, // Arrays are complex, always show history option
            'formatted_diff' => 'Click to view previous versions',
            'previous_value' => $previous_value,
            'current_value' => $current_value
        ];
    }
    
    // Handle numeric values
    if (is_numeric($current_value) && is_numeric($previous_value)) {
        $diff = $current_value - $previous_value;
        $has_changed = ($diff != 0);
        
        if ($has_changed && $show_diff) {
            $sign = ($diff > 0) ? '+' : '';
            $formatted_diff = "{$sign}{$diff}";
        }
    } 
    // Handle string values
    else if (is_string($current_value) && is_string($previous_value)) {
        $has_changed = ($current_value !== $previous_value);
        
        // For dates, format them nicely
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $current_value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $previous_value)) {
            $current_formatted = date('M j, Y', strtotime($current_value));
            $previous_formatted = date('M j, Y', strtotime($previous_value));
            $previous_value = $previous_formatted;
        }
    }
    // Other types
    else {
        $has_changed = ($current_value != $previous_value);
    }
    
    return [
        'has_changed' => $has_changed,
        'formatted_diff' => $formatted_diff,
        'previous_value' => $previous_value,
        'current_value' => $current_value
    ];
}

/**
 * Get complete edit history for a specific field in a program
 * Shows all changes to a field over time with timestamps
 * 
 * @param array $submissions Array of program submissions 
 * @param string $field_name The field name to track history for
 * @param array $options Additional options (nested_field, use_formatted_date)
 * @return array Array of field values with timestamps
 */
function get_field_edit_history($submissions, $field_name, $options = []) {
    $history = [];
    $nested_field = $options['nested_field'] ?? null;
    $use_formatted_date = $options['use_formatted_date'] ?? true;
    $previous_value = null;
    
    // Process submissions in reverse (from oldest to newest)
    $submissions_reversed = array_reverse($submissions);
    
    foreach ($submissions_reversed as $idx => $submission) {
        $timestamp = $use_formatted_date ? 
            $submission['formatted_date'] : 
            date('Y-m-d H:i:s', strtotime($submission['submission_date'] ?? $submission['created_at']));
        
        // Handle nested field within JSON content (e.g., targets[0].target_text)
        if ($nested_field && isset($submission[$field_name]) && is_array($submission[$field_name])) {
            $path = explode('.', $nested_field);
            $value = $submission[$field_name];
            
            // Navigate the nested path
            foreach ($path as $key) {
                // Handle array index notation like targets[0]
                if (preg_match('/^(\w+)\[(\d+)\]$/', $key, $matches)) {
                    $array_key = $matches[1];
                    $index = (int)$matches[2];
                    
                    if (isset($value[$array_key]) && isset($value[$array_key][$index])) {
                        $value = $value[$array_key][$index];
                    } else {
                        $value = null;
                        break;
                    }
                } else if (isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    $value = null;
                    break;
                }
            }
        } else {
            // Direct field access
            $value = $submission[$field_name] ?? null;
        }
        
        // Only add to history if value exists and is different from previous value
        if ($value !== null && $value !== $previous_value) {
            $history[] = [
                'timestamp' => $timestamp,
                'value' => $value,
                'is_draft' => $submission['is_draft'] ?? 0,
                'submission_id' => $submission['submission_id'] ?? 0,
                'period_name' => $submission['period_name'] ?? null
            ];
            $previous_value = $value;
        }
    }
    
    return $history;
}
?>