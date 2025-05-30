<?php
/**
 * Agency Program Management Functions
 * 
 * Contains functions for managing agency programs (add, update, delete, submit)
 */

require_once dirname(__DIR__) . '/utilities.php';

// Check if the core.php file exists in the agencies directory, if not we need to adapt
if (file_exists(dirname(__FILE__) . '/core.php')) {
    require_once 'core.php';
} else {
    // Include necessary functions from other places if core.php doesn't exist
    require_once dirname(__DIR__) . '/session.php';
    require_once dirname(__DIR__) . '/functions.php';
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
    
    // Get assigned programs
    $assigned = get_agency_programs_list($user_id, true);
    
    // Get agency-created programs
    $created = get_agency_programs_list($user_id, false);
    
    return [
        'assigned' => $assigned,
        'created' => $created
    ];
}

/**
 * Get agency programs with specified assignment status
 * @param int $user_id Agency user ID
 * @param bool $is_assigned Whether to get assigned or created programs
 * @return array List of programs
 */
function get_agency_programs_list($user_id, $is_assigned = false) {
    global $conn;
    
    // Check if content_json column exists
    $column_check = $conn->query("SHOW COLUMNS FROM programs LIKE 'content_json'");
    $has_content_json = $column_check->num_rows > 0;
    
    // Build SQL based on table structure
    if ($has_content_json) {
        $select_fields = "p.*, p.content_json,
                        (SELECT COALESCE(ps.content_json, '{}') FROM program_submissions ps 
                         WHERE ps.program_id = p.program_id 
                         ORDER BY ps.submission_id DESC LIMIT 1) AS submission_json";
    } else {
        $select_fields = "p.*, 
                        (SELECT JSON_EXTRACT(ps.content_json, '$.target') FROM program_submissions ps 
                         WHERE ps.program_id = p.program_id 
                         ORDER BY ps.submission_id DESC LIMIT 1) AS current_target,
                        (SELECT JSON_EXTRACT(ps.content_json, '$.achievement') FROM program_submissions ps 
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
    $validated = validate_agency_program_input($data, ['program_name', 'rating']);
    if (isset($validated['error'])) {
        return $validated;
    }
    
    $program_name = $validated['program_name'];
    $description = $validated['description'] ?? '';
    $start_date = $validated['start_date'] ?? null;
    $end_date = $validated['end_date'] ?? null;
    $rating = $validated['rating'];
    
    // Check if the current table structure uses content_json
    $column_check = $conn->query("SHOW COLUMNS FROM programs LIKE 'content_json'");
    $has_content_json = $column_check->num_rows > 0;
    
    if ($has_content_json) {
        // New structure using content_json
        $content = [
            'description' => $description,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'rating' => $rating,
            'targets' => [],
        ];
        
        // Add targets from form if provided
        if (!empty($validated['targets'])) {
            $content['targets'] = $validated['targets'];
        } else if (!empty($validated['target'])) {
            $content['targets'] = [
                [
                    'text' => $validated['target'],
                    'status_description' => $validated['status_text'] ?? ''
                ]
            ];
        }
        
        $content_json = json_encode($content);
        
        $query = "INSERT INTO programs (program_name, sector_id, owner_agency_id, is_assigned, content_json, created_at)
                 VALUES (?, ?, ?, 0, ?, NOW())";
        
        $stmt = $conn->prepare($query);
        $sector_id = FORESTRY_SECTOR_ID; // Default to forestry
        $user_id = $_SESSION['user_id'];
        $stmt->bind_param("siis", $program_name, $sector_id, $user_id, $content_json);
    } else {
        // Old structure using separate columns
        $query = "INSERT INTO programs (program_name, description, sector_id, owner_agency_id, is_assigned, created_at)
                 VALUES (?, ?, ?, ?, 0, NOW())";
        
        $stmt = $conn->prepare($query);
        $sector_id = FORESTRY_SECTOR_ID; // Default to forestry sector
        $user_id = $_SESSION['user_id'];
        $stmt->bind_param("sssii", $program_name, $description, $sector_id, $user_id);
    }
    
    if ($stmt->execute()) {
        $program_id = $conn->insert_id;
        
        // If we're using the old structure or need to create an initial submission
        if (!$has_content_json || isset($validated['create_submission'])) {
            // Create initial submission
            $target = $validated['target'] ?? '';
            $achievement = $validated['achievement'] ?? '';
            $status_text = $validated['status_text'] ?? '';
            
            $sub_query = "INSERT INTO program_submissions 
                        (program_id, period_id, target, achievement, status, status_text, is_draft, submitted_at)
                        VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";
            
            $period_id = get_current_reporting_period()['period_id'] ?? 1;
            $sub_stmt = $conn->prepare($sub_query);
            $sub_stmt->bind_param("iissss", $program_id, $period_id, $target, $achievement, $rating, $status_text);
            $sub_stmt->execute();
        }
        
        return [
            'success' => true,
            'message' => 'Program created successfully',
            'program_id' => $program_id
        ];
    } else {
        return format_error('Failed to create program: ' . $stmt->error);
    }
}

/**
 * Create a draft program for an agency.
 * @param array $program_data Data for the program to be created.
 * @return array|string Result of the operation or error message.
 */
function create_agency_program_draft($program_data) {
    global $conn;

    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }

    // Validate required fields
    if (empty($program_data['name'])) {
        return ['error' => 'Program name is required.'];
    }

    try {
        // Prepare SQL query
        $stmt = $conn->prepare("INSERT INTO programs (program_name, description, owner_agency_id, is_draft, created_at) VALUES (?, ?, ?, 1, NOW())");
        $stmt->bind_param("ssi", $program_data['name'], $program_data['description'], $_SESSION['user_id']);

        if ($stmt->execute()) {
            return ['success' => 'Program draft created successfully.', 'program_id' => $stmt->insert_id];
        } else {
            return ['error' => 'Failed to create program draft.'];
        }
    } catch (Exception $e) {
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Create a comprehensive program draft with all wizard fields
 * @param array $data All program data from wizard
 * @return array Result with success/error and program_id
 */
function create_wizard_program_draft($data) {
    global $conn;

    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }

    // Validate required fields
    if (empty($data['program_name']) || trim($data['program_name']) === '') {
        return ['error' => 'Program name is required'];
    }

    // Prepare targets and statuses
    $targets = $data['targets'] ?? [];
    $content_json = json_encode(['targets' => $targets]);

    // Ensure sector_id is provided or defaults to FORESTRY_SECTOR_ID
    $sector_id = $data['sector_id'] ?? FORESTRY_SECTOR_ID;

    // Insert program draft
    $stmt = $conn->prepare("INSERT INTO programs (program_name, sector_id, owner_agency_id, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sii", $data['program_name'], $sector_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        $program_id = $stmt->insert_id;

        // Dynamically fetch the current reporting period ID
        $period_id = $data['period_id'] ?? get_current_reporting_period()['period_id'] ?? null;

        if ($period_id === null) {
            return ['error' => 'No valid reporting period found.'];
        }

        // Include submitted_by field in the INSERT query
        $stmt = $conn->prepare("INSERT INTO program_submissions (program_id, period_id, submitted_by, content_json, is_draft, submission_date) VALUES (?, ?, ?, ?, 1, NOW())");
        $stmt->bind_param("iiis", $program_id, $period_id, $_SESSION['user_id'], $content_json);
        $stmt->execute();

        return ['success' => true, 'program_id' => $program_id];
    } else {
        return ['error' => 'Failed to create program draft: ' . $stmt->error];
    }
}

/**
 * Auto-save program draft with minimal validation
 * @param array $data Form data for auto-saving
 * @return array JSON response for AJAX
 */
function auto_save_program_draft($data) {
    global $conn;

    if (!is_agency()) {
        return ['success' => false, 'error' => 'Permission denied'];
    }

    // Only proceed if program name exists and is long enough
    if (empty($data['program_name']) || trim($data['program_name']) === '' || strlen(trim($data['program_name'])) < 3) {
        return ['success' => false, 'error' => 'Program name must be at least 3 characters for auto-save'];
    }

    // Check if this is an update to existing draft
    $program_id = isset($data['program_id']) ? intval($data['program_id']) : 0;
    
    if ($program_id > 0) {
        // Update existing draft
        return update_wizard_program_draft($program_id, $data);
    } else {
        // Dynamically fetch the current reporting period ID or default to 1
        $data['period_id'] = $data['period_id'] ?? get_current_reporting_period()['period_id'] ?? 1;

        // Create new draft
        return create_wizard_program_draft($data);
    }
}

/**
 * Update existing program draft with wizard data
 * @param int $program_id Program ID to update
 * @param array $data Updated program data
 * @return array Result with success/error
 */
function update_wizard_program_draft($program_id, $data) {
    global $conn;

    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }

    // Verify ownership
    $user_id = $_SESSION['user_id'];
    $check_stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_id = ? AND owner_agency_id = ? AND is_draft = 1");
    $check_stmt->bind_param("ii", $program_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['error' => 'Program not found or access denied'];
    }

    $program_name = trim($data['program_name']);
    $description = isset($data['description']) ? trim($data['description']) : '';
    $brief_description = isset($data['brief_description']) ? trim($data['brief_description']) : '';
    $program_type = isset($data['program_type']) ? trim($data['program_type']) : '';
    $start_date = isset($data['start_date']) && !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = isset($data['end_date']) && !empty($data['end_date']) ? $data['end_date'] : null;
    $estimated_budget = isset($data['estimated_budget']) && !empty($data['estimated_budget']) ? floatval($data['estimated_budget']) : null;
    $target_beneficiaries = isset($data['target_beneficiaries']) && !empty($data['target_beneficiaries']) ? intval($data['target_beneficiaries']) : null;
    $success_indicators = isset($data['success_indicators']) ? trim($data['success_indicators']) : '';
    $implementation_strategy = isset($data['implementation_strategy']) ? trim($data['implementation_strategy']) : '';

    try {
        // Prepare extended program data structure
        $extended_data = [
            'program_type' => $program_type,
            'brief_description' => $brief_description,
            'estimated_budget' => $estimated_budget,
            'target_beneficiaries' => $target_beneficiaries,
            'success_indicators' => $success_indicators,
            'implementation_strategy' => $implementation_strategy
        ];
        
        $extended_json = json_encode($extended_data);

        // Update program draft
        $stmt = $conn->prepare("UPDATE programs SET program_name = ?, description = ?, start_date = ?, end_date = ?, extended_data = ?, updated_at = NOW() WHERE program_id = ? AND owner_agency_id = ?");
        $stmt->bind_param("ssssii", $program_name, $description, $start_date, $end_date, $extended_json, $program_id, $user_id);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Program draft updated successfully',
                'program_id' => $program_id
            ];
        } else {
            return ['error' => 'Failed to update program draft: ' . $stmt->error];
        }
    } catch (Exception $e) {
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Create a simple program draft with basic information only
 * @param array $data Basic program data (name, description, start_date, end_date)
 * @return array Result with success/error and program_id
 */
function create_simple_program_draft($data) {
    global $conn;

    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }

    // Validate required fields - only program name is required
    if (empty($data['program_name']) || trim($data['program_name']) === '') {
        return ['error' => 'Program name is required'];
    }

    $program_name = trim($data['program_name']);
    $description = isset($data['description']) ? trim($data['description']) : '';
    $start_date = isset($data['start_date']) && !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = isset($data['end_date']) && !empty($data['end_date']) ? $data['end_date'] : null;
    $user_id = $_SESSION['user_id'];

    try {
        // Insert basic program draft
        $stmt = $conn->prepare("INSERT INTO programs (program_name, description, start_date, end_date, owner_agency_id, is_draft, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->bind_param("ssssi", $program_name, $description, $start_date, $end_date, $user_id);

        if ($stmt->execute()) {
            $program_id = $conn->insert_id;
            return [
                'success' => true,
                'message' => 'Program draft saved successfully',
                'program_id' => $program_id
            ];
        } else {
            return ['error' => 'Failed to save program draft: ' . $stmt->error];
        }
    } catch (Exception $e) {
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get a program's submissions history
 * @param int $program_id Program ID
 * @return array List of submissions with period info
 */
function get_program_submissions_history($program_id) {
    global $conn;
    
    // Verify permissions
    if (!is_agency() && !is_admin()) {
        return format_error('Permission denied', 403);
    }
    
    // If agency user, verify ownership
    if (is_agency()) {
        $query = "SELECT owner_agency_id FROM programs WHERE program_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return format_error('Program not found', 404);
        }
        
        $program = $result->fetch_assoc();
        if ($program['owner_agency_id'] != $_SESSION['user_id']) {
            return format_error('Permission denied', 403);
        }
    }
    
    // Get submissions
    $query = "SELECT ps.*, 
                    rp.quarter,
                    rp.year,
                    CONCAT('Q', rp.quarter, ' ', rp.year) AS period_name,
                    u.username as submitted_by_name
              FROM program_submissions ps
              LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
              LEFT JOIN users u ON ps.submitted_by = u.user_id
              WHERE ps.program_id = ?
              ORDER BY ps.submitted_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        // Process content_json if exists
        if (isset($row['content_json'])) {
            $content = json_decode($row['content_json'], true) ?: [];
            // Merge content fields into submission data
            foreach ($content as $key => $value) {
                if (!isset($row[$key])) {
                    $row[$key] = $value;
                }
            }
        }
        
        // Format dates for display
        $row['submitted_at_formatted'] = date('M j, Y g:i a', strtotime($row['submitted_at']));
        if (!empty($row['reviewed_at'])) {
            $row['reviewed_at_formatted'] = date('M j, Y g:i a', strtotime($row['reviewed_at']));
        }
        
        // Get rating badge details
        $rating = $row['status'] ?? 'not-started';
        $row['rating_badge'] = get_rating_badge($rating);
        
        $submissions[] = $row;
    }
    
    return $submissions;
}

/**
 * Process content_json field if it exists in program data
 * @param array $program Program data
 * @return array Processed program data with merged content fields
 */
function process_content_json($program) {
    // Process program content_json if exists
    if (isset($program['content_json']) && !empty($program['content_json'])) {
        $content = json_decode($program['content_json'], true) ?: [];
        // Merge content fields into program data
        foreach ($content as $key => $value) {
            if (!isset($program[$key]) || $program[$key] === null) {
                $program[$key] = $value;
            }
        }
    }
    
    // Process submission content_json if exists
    if (isset($program['submission_json']) && !empty($program['submission_json'])) {
        $submission = json_decode($program['submission_json'], true) ?: [];
        // Add submission data as a nested array
        $program['current_submission'] = $submission;
    }
    
    return $program;
}

/**
 * Get program edit history with submissions
 * @param int $program_id Program ID
 * @return array Program details and submissions history
 */
function get_program_edit_history($program_id) {
    global $conn;
    
    $program_id = intval($program_id);
    if (!$program_id) {
        return ['error' => 'Invalid program ID'];
    }
    
    // First get the current program details from the 'programs' table
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
                // Ensure 'rating' from content_json is used as status for that historical point
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
 * Helper function to validate form input for agency programs
 * @param array $data Input data
 * @param array $required Required fields
 * @return array Validated data or error
 */
function validate_agency_program_input($data, $required = []) {
    $validated = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            return [
                'error' => true,
                'message' => "Missing required field: $field"
            ];
        }
    }
    
    // Sanitize and process all inputs
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $validated[$key] = $value; // Arrays passed through as-is (like targets array)
        } else {
            $validated[$key] = trim($value);
        }
    }
    
    return $validated;
}

/**
 * Get edit history for a specific field across program submissions
 * @param array $submissions Array of program submissions
 * @param string $field_name Field to track changes for
 * @param array $options Optional settings for processing
 * @return array History of changes to the field
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

/**
 * Get detailed information about a specific program
 * 
 * @param int $program_id The ID of the program to retrieve
 * @param bool $allow_cross_agency Whether to allow viewing programs from other agencies
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
        if (!empty($program['submissions'])) {
            $program['current_submission'] = $program['submissions'][0];
        }
    }
    
    return $program;
}
?>
