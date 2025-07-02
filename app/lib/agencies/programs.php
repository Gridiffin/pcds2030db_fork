<?php
/**
 * Agency Program Management Functions
 * 
 * Contains functions for managing agency programs (add, update, delete, submit)
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once dirname(__DIR__) . '/numbering_helpers.php';

if (file_exists(dirname(__FILE__) . '/core.php')) {
    require_once 'core.php';
} else {
    require_once dirname(__DIR__) . '/session.php';
    require_once dirname(__DIR__) . '/functions.php';
}

// Include audit logging
require_once dirname(__DIR__) . '/audit_log.php';
require_once dirname(__DIR__) . '/admins/users.php'; // Add this to use get_user_by_id

// Include numbering helpers for hierarchical program numbering
require_once dirname(__DIR__) . '/numbering_helpers.php';

/**
 * Get programs owned by current agency, separated by type
 */
function get_agency_programs_by_type() {
    global $conn;
    if (!is_agency()) return ['error' => 'Permission denied'];
    $user_id = $_SESSION['user_id'];
    return [
        'assigned' => get_agency_programs_list($user_id, true),
        'created' => get_agency_programs_list($user_id, false)
    ];
}

/**
 * Get agency programs with specified assignment status
 */
function get_agency_programs_list($user_id, $is_assigned = false) {
    global $conn;
    $column_check = $conn->query("SHOW COLUMNS FROM programs LIKE 'content_json'");
    $has_content_json = $column_check->num_rows > 0;
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
    $query = "SELECT $select_fields FROM programs p
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
 */
function create_agency_program($data) {
    global $conn;
    if (!is_agency()) return format_error('Permission denied', 403);
    $validated = validate_agency_program_input($data, ['program_name', 'rating']);
    if (isset($validated['error'])) return $validated;
    $program_name = $validated['program_name'];
    $program_number = $validated['program_number'] ?? null;
    // Validate program_number format if provided
    if ($program_number && !is_valid_program_number_format($program_number, false)) {
        return format_error(get_program_number_format_error(false), 400);
    }
    $start_date = $validated['start_date'] ?? null;
    $end_date = $validated['end_date'] ?? null;
    $rating = $validated['rating'];
    $column_check = $conn->query("SHOW COLUMNS FROM programs LIKE 'content_json'");
    $has_content_json = $column_check->num_rows > 0;
    $user_id = $_SESSION['user_id'];
    $user = get_user_by_id($conn, $user_id);
    $agency_group_id = $user ? $user['agency_group_id'] : null;
    $sector_id = FORESTRY_SECTOR_ID;
    if ($has_content_json) {
        $content = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'rating' => $rating,
            'targets' => [],
        ];
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
        $query = "INSERT INTO programs (program_name, program_number, sector_id, owner_agency_id, agency_group, is_assigned, content_json, created_at)
                 VALUES (?, ?, ?, ?, ?, 0, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssiiis", $program_name, $program_number, $sector_id, $user_id, $agency_group_id, $content_json);
    } else {
        $query = "INSERT INTO programs (program_name, program_number, sector_id, owner_agency_id, agency_group, is_assigned, created_at)
                 VALUES (?, ?, ?, ?, ?, 0, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssiii", $program_name, $program_number, $sector_id, $user_id, $agency_group_id);
    }
    if ($stmt->execute()) {
        $program_id = $conn->insert_id;
        if (!$has_content_json || isset($validated['create_submission'])) {
            $target = $validated['target'] ?? '';
            $achievement = $validated['achievement'] ?? '';
            $status_text = $validated['status_text'] ?? '';
            $sub_query = "INSERT INTO program_submissions 
                        (program_id, period_id, target, achievement, status_text, is_draft, submission_date)
                        VALUES (?, ?, ?, ?, ?, 1, NOW())";
            $period_id = get_current_reporting_period()['period_id'] ?? 1;
            $sub_stmt = $conn->prepare($sub_query);
            $sub_stmt->bind_param("iisss", $program_id, $period_id, $target, $achievement, $status_text);
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
 * Create a comprehensive program draft with correct data architecture
 */
function create_wizard_program_draft($data) {
    global $conn;
    if (!is_agency()) return ['error' => 'Permission denied'];
    if (empty($data['program_name']) || trim($data['program_name']) === '') {
        return ['error' => 'Program name is required'];
    }
    $program_name = trim($data['program_name']);
    $program_number = isset($data['program_number']) ? trim($data['program_number']) : null;
    $brief_description = isset($data['brief_description']) ? trim($data['brief_description']) : '';
    $start_date = isset($data['start_date']) && !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = isset($data['end_date']) && !empty($data['end_date']) ? $data['end_date'] : null;
    $targets = isset($data['targets']) && is_array($data['targets']) ? $data['targets'] : [];
    $initiative_id = isset($data['initiative_id']) && !empty($data['initiative_id']) ? intval($data['initiative_id']) : null;
    // Validate program_number format if provided
    if ($program_number && !is_valid_program_number_format($program_number, false)) {
        return ['error' => get_program_number_format_error(false)];
    }
    // Additional validation for hierarchical format if initiative is linked
    if ($program_number && $initiative_id) {
        $format_validation = validate_program_number_format($program_number, $initiative_id);
        if (!$format_validation['valid']) {
            return ['error' => $format_validation['message']];
        }
        // Check if number is already in use
        if (!is_program_number_available($program_number)) {
            return ['error' => 'Program number is already in use'];
        }
    }
    $user_id = $_SESSION['user_id'];
    $user = get_user_by_id($conn, $user_id);
    $agency_group_id = $user ? $user['agency_group_id'] : null;
    $sector_id = FORESTRY_SECTOR_ID;
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare("INSERT INTO programs (program_name, program_number, start_date, end_date, owner_agency_id, agency_group, sector_id, initiative_id, is_assigned, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");
        $stmt->bind_param("ssssiiii", $program_name, $program_number, $start_date, $end_date, $user_id, $agency_group_id, $sector_id, $initiative_id);
        if (!$stmt->execute()) throw new Exception('Failed to create program: ' . $stmt->error);
        $program_id = $conn->insert_id;
        if (!empty($targets) || !empty($brief_description)) {
            // Prioritize open quarterly periods (1-4)
            $period_query = "SELECT period_id FROM reporting_periods WHERE status = 'open' AND quarter BETWEEN 1 AND 4 ORDER BY year DESC, quarter DESC LIMIT 1";
            $period_result = $conn->query($period_query);

            if ($period_result && $period_result->num_rows > 0) {
                $current_period_id = $period_result->fetch_assoc()['period_id'];
            } else {
                // Fallback to any open period (including half-yearly) if no quarterly period is open
                $fallback_query = "SELECT period_id FROM reporting_periods WHERE status = 'open' ORDER BY year DESC, quarter DESC LIMIT 1";
                $fallback_result = $conn->query($fallback_query);
                $current_period_id = $fallback_result && $fallback_result->num_rows > 0 ? $fallback_result->fetch_assoc()['period_id'] : 1;
            }
            
            $content_json = [];
            if (!empty($targets)) $content_json['targets'] = $targets;
            if (!empty($brief_description)) $content_json['brief_description'] = $brief_description;
            $content_json_string = json_encode($content_json);
            $submission_stmt = $conn->prepare("INSERT INTO program_submissions (program_id, period_id, submitted_by, content_json, is_draft, submission_date, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
            $submission_stmt->bind_param("iiis", $program_id, $current_period_id, $user_id, $content_json_string);
            if (!$submission_stmt->execute()) throw new Exception('Failed to create program submission: ' . $submission_stmt->error);
        }
        $conn->commit();
        log_audit_action('create_program', "Program Name: $program_name | Program ID: $program_id", 'success', $user_id);
        return [
            'success' => true, 
            'message' => 'Program draft created successfully',
            'program_id' => $program_id
        ];
    } catch (Exception $e) {
        $conn->rollback();
        log_audit_action('create_program_failed', "Program Name: $program_name | Error: " . $e->getMessage(), 'failure', $user_id);
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Auto-save program draft with minimal validation
 */
function auto_save_program_draft($data) {
    global $conn;
    if (!is_agency()) return ['success' => false, 'error' => 'Permission denied'];
    if (empty($data['program_name']) || trim($data['program_name']) === '' || strlen(trim($data['program_name'])) < 3) {
        return ['success' => false, 'error' => 'Program name must be at least 3 characters for auto-save'];
    }
    $program_id = isset($data['program_id']) ? intval($data['program_id']) : 0;
    if ($program_id > 0) {
        return update_program_draft_only($program_id, $data);
    } else {
        return create_wizard_program_draft($data);
    }
}

/**
 * Update existing program draft with correct data architecture - used for auto-save
 */
function update_program_draft_only($program_id, $data) {
    global $conn;
    if (!is_agency()) return ['success' => false, 'error' => 'Permission denied'];
    $user_id = $_SESSION['user_id'];

    // Allow focal users to update any program draft, bypass ownership check
    require_once dirname(__DIR__) . '/session.php';
    if (!is_focal_user()) {
        $check_stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_id = ? AND owner_agency_id = ?");
        $check_stmt->bind_param("ii", $program_id, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows === 0) {
            return ['success' => false, 'error' => 'Program not found or access denied'];
        }
    }

    $program_name = trim($data['program_name']);
    $program_number = isset($data['program_number']) ? trim($data['program_number']) : null;
    // Validate program_number format if provided
    if ($program_number && !is_valid_program_number_format($program_number, false)) {
        return ['success' => false, 'error' => get_program_number_format_error(false)];
    }
    
    // Additional validation for hierarchical format if initiative is linked
    $initiative_id = isset($data['initiative_id']) && !empty($data['initiative_id']) ? intval($data['initiative_id']) : null;
    if ($program_number && $initiative_id) {
        $format_validation = validate_program_number_format($program_number, $initiative_id);
        if (!$format_validation['valid']) {
            return ['success' => false, 'error' => $format_validation['message']];
        }
        
        // Check if number is already in use (excluding current program)
        if (!is_program_number_available($program_number, $program_id)) {
            return ['success' => false, 'error' => 'Program number is already in use'];
        }
    }
    
    $start_date = isset($data['start_date']) && !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = isset($data['end_date']) && !empty($data['end_date']) ? $data['end_date'] : null;
    $targets = isset($data['targets']) && is_array($data['targets']) ? $data['targets'] : [];
    $brief_description = isset($data['brief_description']) ? trim($data['brief_description']) : '';
    
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE programs SET program_name = ?, program_number = ?, start_date = ?, end_date = ?, initiative_id = ?, updated_at = NOW() WHERE program_id = ? AND owner_agency_id = ?");
        $stmt->bind_param("sssssii", $program_name, $program_number, $start_date, $end_date, $initiative_id, $program_id, $user_id);
        if (!$stmt->execute()) throw new Exception('Failed to update program: ' . $stmt->error);
        
        // Handle content submission for auto-save
        if (!empty($targets) || !empty($brief_description)) {
            $check_stmt = $conn->prepare("SELECT submission_id FROM program_submissions WHERE program_id = ?");
            $check_stmt->bind_param("i", $program_id);
            $check_stmt->execute();
            $submission_result = $check_stmt->get_result();
            
            $content_json = [];
            if (!empty($targets)) $content_json['targets'] = $targets;
            if (!empty($brief_description)) $content_json['brief_description'] = $brief_description;
            $content_json_string = json_encode($content_json);
            
            if ($submission_result->num_rows > 0) {
                $submission = $submission_result->fetch_assoc();
                $submission_id = $submission['submission_id'];
                $update_stmt = $conn->prepare("UPDATE program_submissions SET content_json = ?, updated_at = NOW() WHERE submission_id = ?");
                $update_stmt->bind_param("si", $content_json_string, $submission_id);
                if (!$update_stmt->execute()) throw new Exception('Failed to update program submission: ' . $update_stmt->error);
            } else {
                // Prioritize open quarterly periods (1-4)
                $period_query = "SELECT period_id FROM reporting_periods WHERE status = 'open' AND quarter BETWEEN 1 AND 4 ORDER BY year DESC, quarter DESC LIMIT 1";
                $period_result = $conn->query($period_query);

                if ($period_result && $period_result->num_rows > 0) {
                    $current_period_id = $period_result->fetch_assoc()['period_id'];
                } else {
                    // Fallback to any open period (including half-yearly) if no quarterly period is open
                    $fallback_query = "SELECT period_id FROM reporting_periods WHERE status = 'open' ORDER BY year DESC, quarter DESC LIMIT 1";
                    $fallback_result = $conn->query($fallback_query);
                    $current_period_id = $fallback_result && $fallback_result->num_rows > 0 ? $fallback_result->fetch_assoc()['period_id'] : 1;
                }
                
                $insert_stmt = $conn->prepare("INSERT INTO program_submissions (program_id, period_id, submitted_by, content_json, is_draft, submission_date, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
                $insert_stmt->bind_param("iiis", $program_id, $current_period_id, $user_id, $content_json_string);
                if (!$insert_stmt->execute()) throw new Exception('Failed to create program submission: ' . $insert_stmt->error);
            }
        }
        
        $conn->commit();
        return [
            'success' => true,
            'message' => 'Program draft updated successfully',
            'program_id' => $program_id
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update existing program draft with wizard data (for manual saves/edits)
 */
function update_wizard_program_draft($program_id, $data) {
    global $conn;
    if (!is_agency()) return ['error' => 'Permission denied'];
    $user_id = $_SESSION['user_id'];
    $check_stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_id = ? AND owner_agency_id = ?");
    $check_stmt->bind_param("ii", $program_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    if ($result->num_rows === 0) {
        return ['error' => 'Program not found or access denied'];
    }    $program_name = trim($data['program_name']);
    $program_number = isset($data['program_number']) ? trim($data['program_number']) : null;
    $brief_description = isset($data['brief_description']) ? trim($data['brief_description']) : '';
    $start_date = isset($data['start_date']) && !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = isset($data['end_date']) && !empty($data['end_date']) ? $data['end_date'] : null;
    $targets = isset($data['targets']) && is_array($data['targets']) ? $data['targets'] : [];
    $initiative_id = isset($data['initiative_id']) && !empty($data['initiative_id']) ? intval($data['initiative_id']) : null;
    
    // Validate program_number format if provided
    if ($program_number && !is_valid_program_number_format($program_number, false)) {
        return ['error' => get_program_number_format_error(false)];
    }
    
    // Additional validation for hierarchical format if initiative is linked
    if ($program_number && $initiative_id) {
        $format_validation = validate_program_number_format($program_number, $initiative_id);
        if (!$format_validation['valid']) {
            return ['error' => $format_validation['message']];
        }
        
        // Check if number is already in use (excluding current program)
        if (!is_program_number_available($program_number, $program_id)) {
            return ['error' => 'Program number is already in use'];
        }
    }
    
    try {
        $conn->begin_transaction();
          // Get current program data to check if initiative changed
        $current_query = "SELECT initiative_id, program_number FROM programs WHERE program_id = ?";
        $current_stmt = $conn->prepare($current_query);
        $current_stmt->bind_param("i", $program_id);
        $current_stmt->execute();
        $current_result = $current_stmt->get_result();
        $current_program = $current_result->fetch_assoc();
        
        $old_initiative_id = $current_program['initiative_id'];
        $current_program_number = $current_program['program_number'];
        
        // Use the provided program number (no auto-generation)
        // If no program_number provided, keep existing number unless initiative is being removed
        $new_program_number = $program_number;
        if (!$new_program_number && $old_initiative_id != $initiative_id) {
            if (!$initiative_id) {
                // Remove from initiative - clear program number
                $new_program_number = null;
            } else {
                // Keep existing number when changing initiatives
                $new_program_number = $current_program_number;
            }
        }
        
        $stmt = $conn->prepare("UPDATE programs SET program_name = ?, program_number = ?, start_date = ?, end_date = ?, initiative_id = ?, updated_at = NOW() WHERE program_id = ? AND owner_agency_id = ?");
        $stmt->bind_param("sssssii", $program_name, $new_program_number, $start_date, $end_date, $initiative_id, $program_id, $user_id);
        if (!$stmt->execute()) throw new Exception('Failed to update program: ' . $stmt->error);
        if (!empty($targets) || !empty($brief_description)) {
            $check_stmt = $conn->prepare("SELECT submission_id FROM program_submissions WHERE program_id = ?");
            $check_stmt->bind_param("i", $program_id);
            $check_stmt->execute();
            $submission_result = $check_stmt->get_result();
            
            $content_json = [];
            if (!empty($targets)) $content_json['targets'] = $targets;
            if (!empty($brief_description)) $content_json['brief_description'] = $brief_description;
            $content_json_string = json_encode($content_json);
            
            if ($submission_result->num_rows > 0) {
                $submission = $submission_result->fetch_assoc();
                $submission_id = $submission['submission_id'];
                $update_stmt = $conn->prepare("UPDATE program_submissions SET content_json = ?, updated_at = NOW() WHERE submission_id = ?");
                $update_stmt->bind_param("si", $content_json_string, $submission_id);
                if (!$update_stmt->execute()) throw new Exception('Failed to update program submission: ' . $update_stmt->error);
            } else {
                // Prioritize open quarterly periods (1-4)
                $period_query = "SELECT period_id FROM reporting_periods WHERE status = 'open' AND quarter BETWEEN 1 AND 4 ORDER BY year DESC, quarter DESC LIMIT 1";
                $period_result = $conn->query($period_query);

                if ($period_result && $period_result->num_rows > 0) {
                    $current_period_id = $period_result->fetch_assoc()['period_id'];
                } else {
                    // Fallback to any open period (including half-yearly) if no quarterly period is open
                    $fallback_query = "SELECT period_id FROM reporting_periods WHERE status = 'open' ORDER BY year DESC, quarter DESC LIMIT 1";
                    $fallback_result = $conn->query($fallback_query);
                    $current_period_id = $fallback_result && $fallback_result->num_rows > 0 ? $fallback_result->fetch_assoc()['period_id'] : 1;
                }
                
                $insert_stmt = $conn->prepare("INSERT INTO program_submissions (program_id, period_id, submitted_by, content_json, is_draft, submission_date, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
                $insert_stmt->bind_param("iiis", $program_id, $current_period_id, $user_id, $content_json_string);
                if (!$insert_stmt->execute()) throw new Exception('Failed to create program submission: ' . $insert_stmt->error);
            }
        }
        $conn->commit();
        
        // Log successful program update
        log_audit_action('update_program', "Program Name: $program_name | Program ID: $program_id", 'success', $user_id);
        
        return [
            'success' => true,
            'message' => 'Program draft updated successfully',
            'program_id' => $program_id
        ];
    } catch (Exception $e) {
        $conn->rollback();
        
        // Log failed program update
        log_audit_action('update_program_failed', "Program Name: $program_name | Program ID: $program_id | Error: " . $e->getMessage(), 'failure', $user_id);
        
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Process content_json field if it exists in program data
 */
function process_content_json($program) {
    if (isset($program['content_json']) && !empty($program['content_json'])) {
        $content = json_decode($program['content_json'], true) ?: [];
        foreach ($content as $key => $value) {
            if (!isset($program[$key]) || $program[$key] === null) {
                $program[$key] = $value;
            }
        }
    }
    if (isset($program['submission_json']) && !empty($program['submission_json'])) {
        $submission = json_decode($program['submission_json'], true) ?: [];
        $program['current_submission'] = $submission;
    }
    return $program;
}

/**
 * Validate form input for agency programs
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
    foreach ($data as $key => $value) {
        $validated[$key] = is_array($value) ? $value : trim($value);
    }
    return $validated;
}

/**
 * Get detailed information about a specific program for agency view
 * Based on get_admin_program_details but with agency-specific access controls
 * 
 * @param int $program_id The ID of the program to retrieve
 * @param bool $allow_cross_agency Whether to allow viewing programs from other agencies (default: false)
 * @return array|false Program details array or false if not found/unauthorized
 */
function get_program_details($program_id, $allow_cross_agency = false) {
    global $conn;
    
    // Validate input
    $program_id = intval($program_id);
    if ($program_id <= 0) {
        return false;
    }
    
    // Check if user is agency
    if (!is_agency()) {
        return false;
    }
    
    $current_user_id = $_SESSION['user_id'];
      // Base query to get program details with initiative information
    $stmt = $conn->prepare("SELECT p.*, s.sector_name, u.agency_name, u.user_id as owner_agency_id,
                                   i.initiative_id, i.initiative_name, i.initiative_number,
                                   i.initiative_description, i.start_date as initiative_start_date,
                                   i.end_date as initiative_end_date, i.created_at as initiative_created_at
                          FROM programs p
                          LEFT JOIN sectors s ON p.sector_id = s.sector_id
                          LEFT JOIN users u ON p.owner_agency_id = u.user_id
                          LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id
                          WHERE p.program_id = ?");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $program = $result->fetch_assoc();
    
    // Access control: Check if user can view this program
    if (!$allow_cross_agency && $program['owner_agency_id'] != $current_user_id) {
        return false;
    }
    
    // Get submissions for this program with reporting period details
    $stmt = $conn->prepare("SELECT ps.*, rp.year, rp.quarter
                          FROM program_submissions ps 
                          JOIN reporting_periods rp ON ps.period_id = rp.period_id
                          WHERE ps.program_id = ? 
                          ORDER BY ps.submission_id DESC");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $submissions_result = $stmt->get_result();
    
    $program['submissions'] = [];
    
    if ($submissions_result->num_rows > 0) {
        while ($submission = $submissions_result->fetch_assoc()) {
            // Process content_json if applicable
            if (isset($submission['content_json']) && is_string($submission['content_json'])) {
                $content = json_decode($submission['content_json'], true);
                if ($content) {
                    // Extract fields from content JSON
                    $submission['target'] = $content['target'] ?? '';
                    $submission['achievement'] = $content['achievement'] ?? '';
                    $submission['remarks'] = $content['remarks'] ?? '';
                    $submission['status_date'] = $content['status_date'] ?? '';
                    $submission['status_text'] = $content['status_text'] ?? '';                    $submission['targets'] = $content['targets'] ?? [];
                    $submission['status'] = $content['status'] ?? 'not-started';
                    $submission['brief_description'] = $content['brief_description'] ?? '';
                }
            }
            $program['submissions'][] = $submission;
        }
        
        // Set current submission (most recent)
        $program['current_submission'] = $program['submissions'][0];
        
        // Extract brief_description from the most recent submission if not in program table
        if (!isset($program['brief_description']) || empty($program['brief_description'])) {
            if (isset($program['current_submission']['brief_description'])) {
                $program['brief_description'] = $program['current_submission']['brief_description'];
            }
        }
    }
    
    return $program;
}

/**
 * Get program edit history for display in UI
 * Returns formatted submission history with dates and draft/final status
 */
function get_program_edit_history($program_id) {
    global $conn;    // Get all submissions for this program with period information
    $stmt = $conn->prepare("
        SELECT ps.*, rp.year, rp.quarter,
               CONCAT('Q', rp.quarter, ' ', rp.year) as period_name,
               ps.submission_date as effective_date,
               u.username as submitted_by_name
        FROM program_submissions ps 
        LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
        LEFT JOIN users u ON ps.submitted_by = u.user_id
        WHERE ps.program_id = ? 
        ORDER BY ps.submission_id DESC, ps.submission_date DESC
    ");
    
    if (!$stmt) {
        error_log("Database error in get_program_edit_history: " . $conn->error);
        return ['submissions' => []];
    }
    
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    
    while ($row = $result->fetch_assoc()) {
        // Process content_json if it exists
        if (isset($row['content_json']) && is_string($row['content_json'])) {
            $content = json_decode($row['content_json'], true);
            if ($content) {
                // Extract fields from content JSON
                foreach ($content as $key => $value) {
                    if (!isset($row[$key])) {
                        $row[$key] = $value;
                    }
                }
            }
        }
        
        // Format the date for display with both date and time
        if ($row['effective_date']) {
            $row['formatted_date'] = date('M j, Y g:i A', strtotime($row['effective_date']));
        } else {
            $row['formatted_date'] = 'Unknown date';
        }
        
        // Set draft/final label
        $row['is_draft_label'] = ($row['is_draft'] ?? 0) ? 'Draft' : 'Final';
        
        // Format period name if available
        if (isset($row['year']) && isset($row['quarter'])) {
            $row['period_display'] = "Q{$row['quarter']}-{$row['year']}";
        } elseif (isset($row['period_name'])) {
            $row['period_display'] = $row['period_name'];
        } else {
            $row['period_display'] = 'Unknown period';
        }
        
        $submissions[] = $row;
    }
    
    $stmt->close();
    
    return ['submissions' => $submissions];
}

/**
 * Get program edit history with pagination support
 * Returns formatted submission history with pagination info
 */
function get_program_edit_history_paginated($program_id, $page = 1, $per_page = 10) {
    global $conn;
    
    // Calculate offset
    $offset = ($page - 1) * $per_page;
    
    // Get total count
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM program_submissions WHERE program_id = ?");
    if (!$count_stmt) {
        error_log("Database error in get_program_edit_history_paginated count: " . $conn->error);
        return ['submissions' => [], 'pagination' => ['total' => 0, 'pages' => 0, 'current_page' => 1]];
    }
    
    $count_stmt->bind_param("i", $program_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_entries = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
    
    // Calculate pagination info
    $total_pages = ceil($total_entries / $per_page);
    $current_page = max(1, min($page, $total_pages));
    
    // Get paginated submissions
    $stmt = $conn->prepare("
        SELECT ps.*, rp.year, rp.quarter,
               CONCAT('Q', rp.quarter, ' ', rp.year) as period_name,
               ps.submission_date as effective_date,
               u.username as submitted_by_name,
               u.agency_name as submitted_by_agency
        FROM program_submissions ps 
        LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
        LEFT JOIN users u ON ps.submitted_by = u.user_id
        WHERE ps.program_id = ? 
        ORDER BY ps.submission_id DESC, ps.submission_date DESC
        LIMIT ? OFFSET ?
    ");
    
    if (!$stmt) {
        error_log("Database error in get_program_edit_history_paginated: " . $conn->error);
        return ['submissions' => [], 'pagination' => ['total' => 0, 'pages' => 0, 'current_page' => 1]];
    }
    
    $stmt->bind_param("iii", $program_id, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    
    while ($row = $result->fetch_assoc()) {
        // Process content_json if it exists
        if (isset($row['content_json']) && is_string($row['content_json'])) {
            $content = json_decode($row['content_json'], true);
            if ($content) {
                // Extract fields from content JSON
                foreach ($content as $key => $value) {
                    if (!isset($row[$key])) {
                        $row[$key] = $value;
                    }
                }
            }
        }
        
        // Format the date for display with both date and time
        if ($row['effective_date']) {
            $row['formatted_date'] = date('M j, Y g:i A', strtotime($row['effective_date']));
        } else {
            $row['formatted_date'] = 'Unknown date';
        }
        
        // Set draft/final label
        $row['is_draft_label'] = ($row['is_draft'] ?? 0) ? 'Draft' : 'Final';
        
        // Format period name if available
        if (isset($row['year']) && isset($row['quarter'])) {
            $row['period_display'] = "Q{$row['quarter']}-{$row['year']}";
        } elseif (isset($row['period_name'])) {
            $row['period_display'] = $row['period_name'];
        } else {
            $row['period_display'] = 'Unknown period';
        }
        
        $submissions[] = $row;
    }
    
    $stmt->close();
    
    // Calculate display range
    $start_entry = $offset + 1;
    $end_entry = min($offset + $per_page, $total_entries);
    
    return [
        'submissions' => $submissions,
        'pagination' => [
            'total' => $total_entries,
            'pages' => $total_pages,
            'current_page' => $current_page,
            'per_page' => $per_page,
            'start_entry' => $start_entry,
            'end_entry' => $end_entry,
            'has_previous' => $current_page > 1,
            'has_next' => $current_page < $total_pages
        ]
    ];
}

/**
 * Get field edit history for a specific field from program submissions
 * Used to show how specific fields have changed over time
 */
function get_field_edit_history($submissions, $field_name) {
    $history = [];
    $seen_values = [];

    if (!is_array($submissions)) {
        return $history;
    }

    foreach ($submissions as $submission) {
        $value = null;

        // Try to get the field from the top-level first
        if (array_key_exists($field_name, $submission)) {
            $value = $submission[$field_name];
        }

        // If not found, try to get it from content_json
        if (($value === null || $value === '' || (is_array($value) && empty($value))) && isset($submission['content_json'])) {
            $content = is_array($submission['content_json']) ? $submission['content_json'] : json_decode($submission['content_json'], true);
            if (is_array($content) && array_key_exists($field_name, $content)) {
                $value = $content[$field_name];
            }
        }

        // Special handling for targets (legacy and new)
        if ($field_name === 'targets') {
            if (isset($submission['targets']) && is_array($submission['targets'])) {
                $value = $submission['targets'];
            } elseif (isset($submission['target']) && !empty($submission['target'])) {
                $value = [['text' => $submission['target'], 'target_text' => $submission['target']]];
            }
        }

        // Skip if no value or empty value
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            continue;
        }

        // Create a hash of the value to detect duplicates
        $value_hash = is_array($value) ? md5(json_encode($value)) : md5((string)$value);
        if (in_array($value_hash, $seen_values)) {
            continue;
        }
        $seen_values[] = $value_hash;

        // Format timestamp with date and time
        $timestamp = 'Unknown date';
        if (isset($submission['formatted_date'])) {
            $timestamp = $submission['formatted_date'];
        } elseif (isset($submission['effective_date'])) {
            $timestamp = date('M j, Y g:i A', strtotime($submission['effective_date']));
        } elseif (isset($submission['submission_date'])) {
            $timestamp = date('M j, Y g:i A', strtotime($submission['submission_date']));
        } elseif (isset($submission['created_at'])) {
            $timestamp = date('M j, Y g:i A', strtotime($submission['created_at']));
        }

        $history[] = [
            'value' => $value,
            'timestamp' => $timestamp,
            'is_draft' => $submission['is_draft'] ?? 0,
            'submission_id' => $submission['submission_id'] ?? 0,
            'period_name' => $submission['period_display'] ?? ''
        ];
    }

    return $history;
}

/**
 * Get related programs under the same initiative
 * Returns programs that share the same initiative_id, excluding the current program
 */
function get_related_programs_by_initiative($initiative_id, $current_program_id = null, $allow_cross_agency = false) {
    global $conn;
    
    if (!$initiative_id) {
        return [];
    }
    
    $current_user_id = $_SESSION['user_id'];
    
    // Base query for related programs
    $where_conditions = ["p.initiative_id = ?"];
    $params = [$initiative_id];
    $param_types = "i";
    
    // Exclude current program if specified
    if ($current_program_id) {
        $where_conditions[] = "p.program_id != ?";
        $params[] = $current_program_id;
        $param_types .= "i";
    }
    
    // Access control - only show programs user can access
    if (!$allow_cross_agency) {
        $where_conditions[] = "p.owner_agency_id = ?";
        $params[] = $current_user_id;
        $param_types .= "i";
    }
    
    $sql = "SELECT p.program_id, p.program_name, p.program_number, p.owner_agency_id,
                   u.agency_name, 
                   COALESCE(latest_sub.is_draft, 1) as is_draft,
                   COALESCE(JSON_UNQUOTE(JSON_EXTRACT(latest_sub.content_json, '$.rating')), 'not-started') as rating
            FROM programs p
            LEFT JOIN users u ON p.owner_agency_id = u.user_id
            LEFT JOIN (
                SELECT ps1.*
                FROM program_submissions ps1
                INNER JOIN (
                    SELECT program_id, MAX(submission_id) as max_submission_id
                    FROM program_submissions
                    GROUP BY program_id
                ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
            ) latest_sub ON p.program_id = latest_sub.program_id
            WHERE " . implode(' AND ', $where_conditions) . "
            ORDER BY p.program_name";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $related_programs = [];
    while ($row = $result->fetch_assoc()) {
        $related_programs[] = $row;
    }
    
    return $related_programs;
}

/**
 * Capture current program state before making changes
 * Returns complete state including program table data and latest submission content
 */
function get_current_program_state($program_id) {
    global $conn;
    
    // Get program table data
    $stmt = $conn->prepare("
        SELECT p.program_name, p.program_number, p.owner_agency_id, p.sector_id, 
               p.start_date, p.end_date, p.is_assigned, p.edit_permissions,
               u.agency_name as owner_agency_name,
               s.sector_name
        FROM programs p
        LEFT JOIN users u ON p.owner_agency_id = u.user_id
        LEFT JOIN sectors s ON p.sector_id = s.sector_id
        WHERE p.program_id = ?
    ");
    
    if (!$stmt) {
        error_log("Error preparing query in get_current_program_state: " . $conn->error);
        return array();
    }
    
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $program_data = $result->fetch_assoc();
    if (!$program_data) {
        $program_data = array();
    }
    $stmt->close();
    
    // Get latest submission content
    $submission_stmt = $conn->prepare("
        SELECT content_json 
        FROM program_submissions 
        WHERE program_id = ? 
        ORDER BY submission_id DESC 
        LIMIT 1
    ");
    
    if ($submission_stmt) {
        $submission_stmt->bind_param("i", $program_id);
        $submission_stmt->execute();
        $submission_result = $submission_stmt->get_result();
        $submission_data = $submission_result->fetch_assoc();
        $submission_stmt->close();
        
        if ($submission_data && !empty($submission_data['content_json'])) {
            $content = json_decode($submission_data['content_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($content)) {
                // Merge submission content with program data
                $program_data = array_merge($program_data, $content);
            }
        }
    }
    
    // Get current outcome links
    $outcome_links_stmt = $conn->prepare("
        SELECT outcome_id 
        FROM program_outcome_links 
        WHERE program_id = ? 
        ORDER BY outcome_id
    ");
    
    if ($outcome_links_stmt) {
        $outcome_links_stmt->bind_param("i", $program_id);
        $outcome_links_stmt->execute();
        $outcome_result = $outcome_links_stmt->get_result();
        
        $linked_outcomes = array();
        while ($row = $outcome_result->fetch_assoc()) {
            $linked_outcomes[] = $row['outcome_id'];
        }
        $outcome_links_stmt->close();
        
        $program_data['linked_outcomes'] = $linked_outcomes;
    }

    return $program_data;
}

/**
 * Generate before/after changes by comparing current state with new state
 */
function generate_field_changes($before_state, $after_state) {
    $changes = array();
    
    // Define trackable fields with their labels
    $trackable_fields = array(
        'program_name' => 'Program Name',
        'program_number' => 'Program Number',
        'brief_description' => 'Brief Description',
        'owner_agency_name' => 'Owner Agency',
        'sector_name' => 'Sector',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'is_assigned' => 'Assignment Status',
        'rating' => 'Rating/Status',
        'remarks' => 'Remarks'
    );
    
    // Check basic fields for changes
    foreach ($trackable_fields as $field => $label) {
        $before_value = trim(isset($before_state[$field]) ? $before_state[$field] : '');
        $after_value = trim(isset($after_state[$field]) ? $after_state[$field] : '');
        
        // Special handling for assignment status
        if ($field === 'is_assigned') {
            $before_value = $before_value ? 'Assigned' : 'Not Assigned';
            $after_value = $after_value ? 'Assigned' : 'Not Assigned';
        }
        
        // Special handling for dates
        if (in_array($field, array('start_date', 'end_date'))) {
            if ($before_value) $before_value = date('Y-m-d', strtotime($before_value));
            if ($after_value) $after_value = date('Y-m-d', strtotime($after_value));
        }
        
        // Check if values are different
        if ($before_value !== $after_value) {
            $changes[] = array(
                'field' => $field,
                'field_label' => $label,
                'before' => $before_value ? $before_value : '(empty)',
                'after' => $after_value ? $after_value : '(empty)',
                'change_type' => 'modified'
            );
        }
    }
    
    // Check targets for changes
    $before_targets = isset($before_state['targets']) ? $before_state['targets'] : array();
    $after_targets = isset($after_state['targets']) ? $after_state['targets'] : array();
    
    if (!is_array($before_targets)) $before_targets = array();
    if (!is_array($after_targets)) $after_targets = array();
    
    // Check outcome links for changes
    $before_outcomes = isset($before_state['linked_outcomes']) ? $before_state['linked_outcomes'] : array();
    $after_outcomes = isset($after_state['linked_outcomes']) ? $after_state['linked_outcomes'] : array();
    
    if (!is_array($before_outcomes)) $before_outcomes = array();
    if (!is_array($after_outcomes)) $after_outcomes = array();
    
    // Compare outcome lists
    $added_outcomes = array_diff($after_outcomes, $before_outcomes);
    $removed_outcomes = array_diff($before_outcomes, $after_outcomes);
    
    // Track added outcomes
    foreach ($added_outcomes as $outcome_id) {
        $outcome_name = get_outcome_name_by_id($outcome_id);
        $changes[] = array(
            'field' => 'outcome_links',
            'field_label' => 'Linked Outcomes',
            'before' => null,
            'after' => $outcome_name,
            'change_type' => 'added'
        );
    }
    
    // Track removed outcomes
    foreach ($removed_outcomes as $outcome_id) {
        $outcome_name = get_outcome_name_by_id($outcome_id);
        $changes[] = array(
            'field' => 'outcome_links',
            'field_label' => 'Linked Outcomes',
            'before' => $outcome_name,
            'after' => null,
            'change_type' => 'removed'
        );
    }

    // Check for target changes
    $max_targets = max(count($before_targets), count($after_targets));
    
    for ($i = 0; $i < $max_targets; $i++) {
        $target_num = $i + 1;
        $before_target = isset($before_targets[$i]) ? $before_targets[$i] : array();
        $after_target = isset($after_targets[$i]) ? $after_targets[$i] : array();
        
        $before_text = trim(isset($before_target['target']) ? $before_target['target'] : (isset($before_target['target_text']) ? $before_target['target_text'] : ''));
        $after_text = trim(isset($after_target['target']) ? $after_target['target'] : (isset($after_target['target_text']) ? $after_target['target_text'] : ''));
        $before_status = trim(isset($before_target['status_description']) ? $before_target['status_description'] : '');
        $after_status = trim(isset($after_target['status_description']) ? $after_target['status_description'] : '');
        
        // Check target text changes
        if ($before_text !== $after_text) {
            if (empty($before_text) && !empty($after_text)) {
                // New target added
                $changes[] = array(
                    'field' => "target_{$target_num}",
                    'field_label' => "Target {$target_num}",
                    'before' => null,
                    'after' => $after_text,
                    'change_type' => 'added'
                );
            } elseif (!empty($before_text) && empty($after_text)) {
                // Target removed
                $changes[] = array(
                    'field' => "target_{$target_num}",
                    'field_label' => "Target {$target_num}",
                    'before' => $before_text,
                    'after' => null,
                    'change_type' => 'removed'
                );
            } else {
                // Target modified
                $changes[] = array(
                    'field' => "target_{$target_num}",
                    'field_label' => "Target {$target_num}",
                    'before' => $before_text ? $before_text : '(empty)',
                    'after' => $after_text ? $after_text : '(empty)',
                    'change_type' => 'modified'
                );
            }
        }
        
        // Check target status changes
        if ($before_status !== $after_status && (!empty($before_text) || !empty($after_text))) {
            $changes[] = array(
                'field' => "target_{$target_num}_status",
                'field_label' => "Target {$target_num} Status",
                'before' => $before_status ? $before_status : '(empty)',
                'after' => $after_status ? $after_status : '(empty)',
                'change_type' => 'modified'
            );
        }
    }
    
    return $changes;
}

/**
 * Display before/after changes in a readable format for the history table
 */
function display_before_after_changes($changes_made) {
    if (empty($changes_made) || !is_array($changes_made)) {
        return '<span class="text-muted">No changes recorded</span>';
    }
    
    $output = '<div class="changes-detail">';
    
    foreach ($changes_made as $change) {
        $field_label = htmlspecialchars(isset($change['field_label']) ? $change['field_label'] : 'Unknown Field');
        $change_type = isset($change['change_type']) ? $change['change_type'] : 'modified';
        $before = htmlspecialchars(isset($change['before']) ? $change['before'] : '');
        $after = htmlspecialchars(isset($change['after']) ? $change['after'] : '');
        
        $output .= '<div class="change-item mb-2">';
        
        if ($change_type === 'added') {
            $output .= '<strong class="text-success">' . $field_label . ':</strong><br>';
            $output .= '<span class="text-success">Added:</span> "' . $after . '"';
        } elseif ($change_type === 'removed') {
            $output .= '<strong class="text-danger">' . $field_label . ':</strong><br>';
            $output .= '<span class="text-danger">Removed:</span> "' . $before . '"';
        } else {
            $output .= '<strong>' . $field_label . ':</strong><br>';
            $output .= '<span class="text-muted">From:</span> "' . $before . '"<br>';
            $output .= '<span class="text-muted">To:</span> "' . $after . '"';
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Get outcome name by outcome ID
 * Helper function for change tracking
 */
function get_outcome_name_by_id($outcome_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT detail_name FROM outcomes_details WHERE detail_id = ?");
    $stmt->bind_param("i", $outcome_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['detail_name'];
    }
    
    $stmt->close();
    return "Unknown Outcome (ID: $outcome_id)";
}