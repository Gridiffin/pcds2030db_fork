<?php
/**
 * AJAX endpoint for retrieving paginated field history
 * Returns a specific range of edit history entries for a given field
 */

require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/agencies/programs.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("get_field_history.php: User not logged in");
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("get_field_history.php: Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get and validate input parameters
$program_id = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
$period_id = filter_input(INPUT_POST, 'period_id', FILTER_VALIDATE_INT);
$field_name = filter_input(INPUT_POST, 'field_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$offset = filter_input(INPUT_POST, 'offset', FILTER_VALIDATE_INT) ?: 0;
$limit = filter_input(INPUT_POST, 'limit', FILTER_VALIDATE_INT) ?: 5;

// Validate required parameters
if (!$program_id || !$period_id || !$field_name) {
    error_log("get_field_history.php: Missing parameters - program_id: $program_id, period_id: $period_id, field_name: $field_name");
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: program_id, period_id, and field_name']);
    exit;
}

// Validate limit to prevent excessive data requests
if ($limit > 50) {
    $limit = 50;
}

// Validate field name against allowed fields
$allowed_fields = [
    'program_name',
    'brief_description', 
    'full_description',
    'deliverables',
    'collaboration',
    'methodology',
    'activity_type',
    'target_participants',
    'impact_measurement',
    'outcome_measurement',
    'targets', // Re-enabled for history functionality
    'remarks',
    'target_number',
    'target_description',
    'status_indicator',
    'status_description',
    'start_date',
    'end_date',
    'description'
];

if (!in_array($field_name, $allowed_fields)) {
    error_log("get_field_history.php: Invalid field name: $field_name");
    http_response_code(400);
    echo json_encode(['error' => 'Invalid field name']);
    exit;
}

// Add field label mapping
$field_labels = [
    'target_number' => 'Target Number',
    'target_description' => 'Target Description',
    'status_indicator' => 'Status Indicator',
    'status_description' => 'Achievements/Status',
    'remarks' => 'Remarks',
    'start_date' => 'Start Date',
    'end_date' => 'End Date',
    'description' => 'Description'
];

error_log("get_field_history.php: Starting request - program_id: $program_id, period_id: $period_id, field_name: $field_name, offset: $offset, limit: $limit");

try {
    // Check if user has access to this program
    $stmt = $conn->prepare("
        SELECT p.program_id, p.agency_id, a.agency_name 
        FROM programs p 
        JOIN agency a ON p.agency_id = a.agency_id 
        WHERE p.program_id = ?
    ");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $program = $result->fetch_assoc();
    
    if (!$program) {
        http_response_code(404);
        echo json_encode(['error' => 'Program not found']);
        exit;
    }
    
    // Check if user belongs to the program's agency (for agency users)
    if ($_SESSION['role'] !== 'admin') {
        // Get user's agency_id
        $user_agency_query = "SELECT agency_id FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($user_agency_query);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user = $user_result->fetch_assoc();
        
        if (!$user || $user['agency_id'] != $program['agency_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }
    }
    
    // Get the field history with pagination
    $field_history = get_field_edit_history_paginated($program_id, $field_name, $period_id, $offset, $limit);
    
    // Get total count for this field
    $total_count = get_field_edit_history_count($program_id, $field_name, $period_id);
    
    // Debug logging
    error_log("get_field_history.php: Found {$total_count} total records, returning " . count($field_history) . " records");
    
    // Calculate if there are more records
    $has_more = ($offset + $limit) < $total_count;
    
    // Format the response
    $response = [
        'success' => true,
        'field_name' => $field_name,
        'field_label' => $field_labels[$field_name] ?? $field_name,
        'history' => array_map(function($entry) use ($field_labels, $field_name) {
            $entry['field_label'] = $field_labels[$field_name] ?? $field_name;
            return $entry;
        }, $field_history),
        'has_more' => $has_more,
        'total_count' => $total_count,
        'current_offset' => $offset,
        'current_limit' => $limit,
        'next_offset' => $has_more ? ($offset + $limit) : null
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error in get_field_history.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

/**
 * Get paginated field edit history for a specific field
 * 
 * @param int $program_id Program ID
 * @param string $field_name Field name to get history for
 * @param int $period_id Period ID to filter by
 * @param int $offset Starting offset for pagination
 * @param int $limit Number of records to return
 * @return array Array of history entries
 */
function get_field_edit_history_paginated($program_id, $field_name, $period_id, $offset = 0, $limit = 5) {
    global $conn;
    
    // Get submission IDs for this program and period
    $submission_query = "SELECT submission_id FROM program_submissions 
                        WHERE program_id = ? AND period_id = ? AND is_deleted = 0";
    $stmt = $conn->prepare($submission_query);
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();
    $submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    error_log("get_field_edit_history_paginated: Found " . count($submissions) . " submissions for program_id={$program_id}, period_id={$period_id}");
    
    if (empty($submissions)) {
        return [];
    }
    
    $submission_ids = array_column($submissions, 'submission_id');
    $placeholders = str_repeat('?,', count($submission_ids) - 1) . '?';
    
    // Query audit field changes with target information
    $query = "
        SELECT 
            afc.change_id,
            afc.audit_log_id,
            afc.target_id,
            afc.field_name,
            afc.old_value,
            afc.new_value,
            afc.change_type,
            afc.created_at,
            al.user_id,
            u.fullname as submitted_by,
            u.email as submitter_email,
            ps.submission_id,
            ps.is_draft,
            ps.submitted_at,
            pt.target_number
        FROM audit_field_changes afc
        JOIN audit_logs al ON afc.audit_log_id = al.id
        LEFT JOIN users u ON al.user_id = u.user_id
        LEFT JOIN program_submissions ps ON al.details LIKE CONCAT('%submission ID: ', ps.submission_id, '%')
        LEFT JOIN program_targets pt ON afc.target_id = pt.target_id
        WHERE ps.submission_id IN ($placeholders)
        AND afc.field_name = ?
        ORDER BY afc.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params = array_merge($submission_ids, [$field_name, $limit, $offset]);
    $types = str_repeat('i', count($submission_ids)) . 'sii';
    
    error_log("get_field_edit_history_paginated: Query params - submission_ids: " . implode(',', $submission_ids) . ", field_name: {$field_name}");
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($record = $result->fetch_assoc()) {
        $history[] = [
            'change_id' => $record['change_id'],
            'audit_log_id' => $record['audit_log_id'],
            'target_id' => $record['target_id'],
            'target_number' => $record['target_number'],
            'field_name' => $record['field_name'],
            'old_value' => $record['old_value'],
            'new_value' => $record['new_value'],
            'change_type' => $record['change_type'],
            'submitted_at' => $record['created_at'],
            'submitted_by' => $record['submitted_by'] ?: 'System',
            'submitter_email' => $record['submitter_email'],
            'submission_id' => $record['submission_id'],
            'is_draft' => $record['is_draft'],
            'formatted_date' => $record['created_at'] ? date('M j, Y g:i A', strtotime($record['created_at'])) : 'Not specified'
        ];
    }
    
    error_log("get_field_edit_history_paginated: Returning " . count($history) . " history records");
    
    return $history;
}

/**
 * Get total count of field history entries for a specific field
 * 
 * @param int $program_id Program ID
 * @param string $field_name Field name to count history for
 * @param int $period_id Period ID to filter by
 * @return int Total count of history entries
 */
function get_field_edit_history_count($program_id, $field_name, $period_id) {
    global $conn;
    
    // Get submission IDs for this program and period
    $submission_query = "SELECT submission_id FROM program_submissions 
                        WHERE program_id = ? AND period_id = ? AND is_deleted = 0";
    $stmt = $conn->prepare($submission_query);
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();
    $submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (empty($submissions)) {
        return 0;
    }
    
    $submission_ids = array_column($submissions, 'submission_id');
    $placeholders = str_repeat('?,', count($submission_ids) - 1) . '?';
    
    $query = "
        SELECT COUNT(*) as count
        FROM audit_field_changes afc
        JOIN audit_logs al ON afc.audit_log_id = al.id
        LEFT JOIN program_submissions ps ON al.details LIKE CONCAT('%submission ID: ', ps.submission_id, '%')
        WHERE ps.submission_id IN ($placeholders)
        AND afc.field_name = ?
    ";
    
    $params = array_merge($submission_ids, [$field_name]);
    $types = str_repeat('i', count($submission_ids)) . 's';
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return (int)$row['count'];
}
?>
