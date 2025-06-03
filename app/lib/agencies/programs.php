<?php
/**
 * Agency Program Management Functions
 * 
 * Contains functions for managing agency programs (add, update, delete, submit)
 */

require_once dirname(__DIR__) . '/utilities.php';

if (file_exists(dirname(__FILE__) . '/core.php')) {
    require_once 'core.php';
} else {
    require_once dirname(__DIR__) . '/session.php';
    require_once dirname(__DIR__) . '/functions.php';
}

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
    $start_date = $validated['start_date'] ?? null;
    $end_date = $validated['end_date'] ?? null;
    $rating = $validated['rating'];
    $column_check = $conn->query("SHOW COLUMNS FROM programs LIKE 'content_json'");
    $has_content_json = $column_check->num_rows > 0;
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
        $query = "INSERT INTO programs (program_name, sector_id, owner_agency_id, is_assigned, content_json, created_at)
                 VALUES (?, ?, ?, 0, ?, NOW())";
        $stmt = $conn->prepare($query);
        $sector_id = FORESTRY_SECTOR_ID;
        $user_id = $_SESSION['user_id'];
        $stmt->bind_param("siis", $program_name, $sector_id, $user_id, $content_json);
    } else {
        $query = "INSERT INTO programs (program_name, sector_id, owner_agency_id, is_assigned, created_at)
                 VALUES (?, ?, ?, 0, NOW())";
        $stmt = $conn->prepare($query);
        $sector_id = FORESTRY_SECTOR_ID;
        $user_id = $_SESSION['user_id'];
        $stmt->bind_param("siii", $program_name, $sector_id, $user_id);
    }
    if ($stmt->execute()) {
        $program_id = $conn->insert_id;
        if (!$has_content_json || isset($validated['create_submission'])) {
            $target = $validated['target'] ?? '';
            $achievement = $validated['achievement'] ?? '';
            $status_text = $validated['status_text'] ?? '';
            $sub_query = "INSERT INTO program_submissions 
                        (program_id, period_id, target, achievement, status_text, is_draft, submitted_at)
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
    $brief_description = isset($data['brief_description']) ? trim($data['brief_description']) : '';
    $start_date = isset($data['start_date']) && !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = isset($data['end_date']) && !empty($data['end_date']) ? $data['end_date'] : null;
    $target = isset($data['target']) ? trim($data['target']) : '';
    $status_description = isset($data['status_description']) ? trim($data['status_description']) : '';
    $user_id = $_SESSION['user_id'];
    $sector_id = FORESTRY_SECTOR_ID;
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare("INSERT INTO programs (program_name, start_date, end_date, owner_agency_id, sector_id, is_assigned, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
        $stmt->bind_param("sssii", $program_name, $start_date, $end_date, $user_id, $sector_id);
        if (!$stmt->execute()) throw new Exception('Failed to create program: ' . $stmt->error);
        $program_id = $conn->insert_id;
        if (!empty($target) || !empty($status_description) || !empty($brief_description)) {
            $period_query = "SELECT period_id FROM reporting_periods WHERE status = 'open' ORDER BY year DESC, quarter DESC LIMIT 1";
            $period_result = $conn->query($period_query);
            $current_period_id = $period_result && $period_result->num_rows > 0 ? $period_result->fetch_assoc()['period_id'] : 1;
            $content_json = [];
            if (!empty($target)) $content_json['target'] = $target;
            if (!empty($status_description)) $content_json['status_description'] = $status_description;
            if (!empty($brief_description)) $content_json['brief_description'] = $brief_description;
            $content_json_string = json_encode($content_json);
            // status column removed from insert
            $submission_stmt = $conn->prepare("INSERT INTO program_submissions (program_id, period_id, submitted_by, content_json, is_draft, submission_date, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
            $submission_stmt->bind_param("iiis", $program_id, $current_period_id, $user_id, $content_json_string);
            if (!$submission_stmt->execute()) throw new Exception('Failed to create program submission: ' . $submission_stmt->error);
        }
        $conn->commit();
        return [
            'success' => true, 
            'message' => 'Program draft created successfully',
            'program_id' => $program_id
        ];
    } catch (Exception $e) {
        $conn->rollback();
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
    $check_stmt = $conn->prepare("SELECT program_id FROM programs WHERE program_id = ? AND owner_agency_id = ?");
    $check_stmt->bind_param("ii", $program_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    if ($result->num_rows === 0) {
        return ['success' => false, 'error' => 'Program not found or access denied'];
    }
    $program_name = trim($data['program_name']);
    $start_date = isset($data['start_date']) && !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = isset($data['end_date']) && !empty($data['end_date']) ? $data['end_date'] : null;
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE programs SET program_name = ?, start_date = ?, end_date = ?, updated_at = NOW() WHERE program_id = ? AND owner_agency_id = ?");
        $stmt->bind_param("sssii", $program_name, $start_date, $end_date, $program_id, $user_id);
        if (!$stmt->execute()) throw new Exception('Failed to update program: ' . $stmt->error);
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
    }
    $program_name = trim($data['program_name']);
    $brief_description = isset($data['brief_description']) ? trim($data['brief_description']) : '';
    $start_date = isset($data['start_date']) && !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = isset($data['end_date']) && !empty($data['end_date']) ? $data['end_date'] : null;
    $target = isset($data['target']) ? trim($data['target']) : '';
    $status_description = isset($data['status_description']) ? trim($data['status_description']) : '';
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE programs SET program_name = ?, start_date = ?, end_date = ?, updated_at = NOW() WHERE program_id = ? AND owner_agency_id = ?");
        $stmt->bind_param("sssii", $program_name, $start_date, $end_date, $program_id, $user_id);
        if (!$stmt->execute()) throw new Exception('Failed to update program: ' . $stmt->error);
        if (!empty($target) || !empty($status_description) || !empty($brief_description)) {
            $check_stmt = $conn->prepare("SELECT submission_id FROM program_submissions WHERE program_id = ?");
            $check_stmt->bind_param("i", $program_id);
            $check_stmt->execute();
            $submission_result = $check_stmt->get_result();
            $content_json = [];
            if (!empty($target)) $content_json['target'] = $target;
            if (!empty($status_description)) $content_json['status_description'] = $status_description;
            if (!empty($brief_description)) $content_json['brief_description'] = $brief_description;
            $content_json_string = json_encode($content_json);
            if ($submission_result->num_rows > 0) {
                $submission = $submission_result->fetch_assoc();
                $submission_id = $submission['submission_id'];
                $update_stmt = $conn->prepare("UPDATE program_submissions SET content_json = ?, updated_at = NOW() WHERE submission_id = ?");
                $update_stmt->bind_param("si", $content_json_string, $submission_id);
                if (!$update_stmt->execute()) throw new Exception('Failed to update program submission: ' . $update_stmt->error);
            } else {
                $period_query = "SELECT period_id FROM reporting_periods WHERE status = 'open' ORDER BY year DESC, quarter DESC LIMIT 1";
                $period_result = $conn->query($period_query);
                $current_period_id = $period_result && $period_result->num_rows > 0 ? $period_result->fetch_assoc()['period_id'] : 1;
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
    
    // Base query to get program details
    $stmt = $conn->prepare("SELECT p.*, s.sector_name, u.agency_name, u.user_id as owner_agency_id
                          FROM programs p
                          LEFT JOIN sectors s ON p.sector_id = s.sector_id
                          LEFT JOIN users u ON p.owner_agency_id = u.user_id
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
                    $submission['status_text'] = $content['status_text'] ?? '';
                    $submission['targets'] = $content['targets'] ?? [];
                    $submission['status'] = $content['status'] ?? 'not-started';
                }
            }
            $program['submissions'][] = $submission;
        }
        
        // Set current submission (most recent)
        $program['current_submission'] = $program['submissions'][0];
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
               ps.submission_date as effective_date
        FROM program_submissions ps 
        LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
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
        
        // Format the date for display
        if ($row['effective_date']) {
            $row['formatted_date'] = date('M j, Y', strtotime($row['effective_date']));
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
        
        // Extract the field value based on field name
        switch ($field_name) {
            case 'targets':
                if (isset($submission['targets']) && is_array($submission['targets'])) {
                    $value = $submission['targets'];
                } elseif (isset($submission['target']) && !empty($submission['target'])) {
                    // Convert legacy single target to array format
                    $value = [['text' => $submission['target'], 'target_text' => $submission['target']]];
                }
                break;
                
            case 'program_name':
                $value = $submission['program_name'] ?? null;
                break;
                
            case 'description':
                $value = $submission['description'] ?? null;
                break;
                
            case 'target':
                $value = $submission['target'] ?? null;
                break;
                
            case 'achievement':
                $value = $submission['achievement'] ?? null;
                break;
                
            case 'remarks':
                $value = $submission['remarks'] ?? null;
                break;
                
            case 'status':
                $value = $submission['status'] ?? null;
                break;
                
            default:
                // Try to get the field directly
                $value = $submission[$field_name] ?? null;
                break;
        }
        
        // Skip if no value or empty value
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            continue;
        }
        
        // Create a hash of the value to detect duplicates
        $value_hash = is_array($value) ? md5(json_encode($value)) : md5((string)$value);
        
        // Skip duplicate values
        if (in_array($value_hash, $seen_values)) {
            continue;
        }
        
        $seen_values[] = $value_hash;
        
        // Format timestamp
        $timestamp = 'Unknown date';
        if (isset($submission['formatted_date'])) {
            $timestamp = $submission['formatted_date'];
        } elseif (isset($submission['effective_date'])) {
            $timestamp = date('M j, Y', strtotime($submission['effective_date']));
        } elseif (isset($submission['submission_date'])) {
            $timestamp = date('M j, Y', strtotime($submission['submission_date']));
        } elseif (isset($submission['created_at'])) {
            $timestamp = date('M j, Y', strtotime($submission['created_at']));
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