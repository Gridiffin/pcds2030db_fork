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
        
        // Insert submission with the new structure
        // The content_json now contains a rating and an array of targets
        $content = [
            'rating' => $rating,
            'targets' => $targets
        ];
        
        $content_json = json_encode($content);
        
        $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                    content_json, status) 
                    VALUES (?, ?, ?, ?, ?)";
        
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
        
        // Insert submission as draft with new structure
        $content = [
            'rating' => $rating,
            'targets' => $targets,
            'remarks' => $data['remarks'] ?? ''
        ];
        
        $content_json = json_encode($content);
        $is_draft = 1; // Set as draft
        
        $sub_query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                    content_json, status, is_draft) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        
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
    
    if (!$program_id || !$period_id) {
        return ['error' => 'Missing required parameters'];
    }
    
    // Only perform full validation if not a draft
    if (!$is_draft) {
        $validation_fields = ['rating'];
        $validated = validate_form_input($data, $validation_fields);
        if (isset($validated['error'])) {
            return $validated;
        }
        
        // Validate at least one target for non-drafts
        if (empty($data['targets']) || !is_array($data['targets'])) {
            return ['error' => 'At least one target is required'];
        }
    } else {
        // Basic validation even for drafts
        $validated = [];
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $validated[$key] = $conn->real_escape_string($value);
            }
        }
    }
    
    // Extract program-level data
    $rating = $validated['rating'] ?? 'not-started';
    $remarks = $validated['remarks'] ?? '';
    
    // Extract timeline data for program table update
    $start_date = $validated['start_date'] ?? null;
    $end_date = $validated['end_date'] ?? null;
    
    // Process targets
    $targets = [];
    if (isset($data['targets']) && is_array($data['targets'])) {
        foreach ($data['targets'] as $target_data) {
            // For non-drafts, require target text
            if (!$is_draft && empty($target_data['text'])) {
                continue;
            }
            
            $targets[] = [
                'target_text' => $conn->real_escape_string($target_data['text'] ?? ''),
                'status_description' => $conn->real_escape_string($target_data['status_description'] ?? '')
            ];
        }
    }
    
    // For non-drafts, require at least one filled target
    if (!$is_draft && empty($targets)) {
        return ['error' => 'At least one target with text is required'];
    }
    
    // If it's a draft and no targets provided, add an empty one
    if ($is_draft && empty($targets)) {
        $targets[] = [
            'target_text' => '',
            'status_description' => ''
        ];
    }
    
    // Create new JSON content with the updated structure
    $content = [
        'rating' => $rating,
        'targets' => $targets,
        'remarks' => $remarks
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
            $stmt->bind_param("ssii", $content_json, $rating, $is_draft, $submission_id);
        } else {
            // Create new submission
            $query = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                     content_json, status, is_draft) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iiissi", $program_id, $period_id, $user_id, $content_json, $rating, $is_draft);
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
    
    // First get the program details
    $program = get_program_details($program_id, true); // Allow cross-agency viewing for history
    
    if (!$program || isset($program['error'])) {
        return ['error' => 'Program not found'];
    }
      // Get all submissions for this program, ordered by date
    $stmt = $conn->prepare("SELECT ps.*, 
                           CONCAT('Q', rp.quarter, '-', rp.year) as period_name, 
                           rp.start_date as period_start, rp.end_date as period_end,
                           u.agency_name as submitted_by_name
                           FROM program_submissions ps
                           LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
                           LEFT JOIN users u ON ps.submitted_by = u.user_id
                           WHERE ps.program_id = ?
                           ORDER BY ps.submission_date DESC");
    
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    $has_content_json = has_content_json_schema();
    
    while ($row = $result->fetch_assoc()) {
        // Process submission data based on schema
        if ($has_content_json && isset($row['content_json']) && !empty($row['content_json'])) {
            // Parse content_json for newer schema
            $content = json_decode($row['content_json'], true);
            if ($content) {
                // Extract content fields for easier access
                foreach ($content as $key => $value) {
                    $row[$key] = $value;
                }
            }
        }
        
        // Format dates for display
        $row['formatted_date'] = date('M j, Y', strtotime($row['submission_date']));
        $row['is_draft_label'] = $row['is_draft'] ? 'Draft' : 'Final';
        
        $submissions[] = $row;
    }
    
    // Add original program creation date as first "version"
    $program_creation = [
        'submission_id' => 0,
        'submission_date' => $program['created_at'],
        'formatted_date' => date('M j, Y', strtotime($program['created_at'])),
        'period_name' => 'Initial Creation',
        'status' => 'not-started',
        'is_draft' => 0,
        'is_draft_label' => 'Creation',
        'program_name' => $program['program_name'],
        'description' => $program['description'],
        'start_date' => $program['start_date'],
        'end_date' => $program['end_date'],
        'targets' => [],
        'remarks' => ''
    ];
    
    // Add to end of submissions array (it's in reverse chronological order)
    $submissions[] = $program_creation;
    
    return [
        'program' => $program,
        'submissions' => $submissions
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