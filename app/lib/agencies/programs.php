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