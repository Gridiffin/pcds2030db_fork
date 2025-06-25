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
    'remarks'
];

if (!in_array($field_name, $allowed_fields)) {
    error_log("get_field_history.php: Invalid field name: $field_name");
    http_response_code(400);
    echo json_encode(['error' => 'Invalid field name']);
    exit;
}

error_log("get_field_history.php: Starting request - program_id: $program_id, period_id: $period_id, field_name: $field_name, offset: $offset, limit: $limit");

try {
    // Check if user has access to this program
    $stmt = $conn->prepare("
        SELECT p.id, p.agency_id, a.name as agency_name 
        FROM programs p 
        JOIN agencies a ON p.agency_id = a.id 
        WHERE p.id = ?
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
    if ($_SESSION['role'] === 'agency' && $_SESSION['agency_id'] != $program['agency_id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
    
    // Get the field history with pagination
    $field_history = get_field_edit_history_paginated($program_id, $field_name, $period_id, $offset, $limit);
    
    // Get total count for this field
    $total_count = get_field_edit_history_count($program_id, $field_name, $period_id);
    
    // Calculate if there are more records
    $has_more = ($offset + $limit) < $total_count;
    
    // Format the response
    $response = [
        'success' => true,
        'field_name' => $field_name,
        'entries' => $field_history,
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
    
    $stmt = $conn->prepare("
        SELECT 
            s.id,
            s.submitted_at,
            s.submission_data,
            COALESCE(CONCAT(u.first_name, ' ', u.last_name), 'System') as submitted_by,
            u.email as submitter_email
        FROM program_submissions s
        LEFT JOIN users u ON s.submitted_by = u.id
        WHERE s.program_id = ?
        AND s.period_id = ?
        AND s.submission_data IS NOT NULL
        AND JSON_EXTRACT(s.submission_data, CONCAT('$.', ?)) IS NOT NULL
        ORDER BY s.submitted_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bind_param("iisii", $program_id, $period_id, $field_name, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $submissions = $result->fetch_all(MYSQLI_ASSOC);
    
    $history = [];
    $seen_values = [];
    
    foreach ($submissions as $submission) {
        $data = json_decode($submission['submission_data'], true);
        $value = $data[$field_name] ?? null;
        
        // Special handling for targets field (legacy and new)
        if ($field_name === 'targets') {
            if (isset($data['targets']) && is_array($data['targets'])) {
                $value = $data['targets'];
            } elseif (isset($data['target']) && !empty($data['target'])) {
                $value = [['text' => $data['target'], 'target_text' => $data['target']]];
            }
        }
        
        // Skip if no value or empty value
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            continue;
        }
        
        // Create a hash of the value to detect duplicates/changes
        $value_hash = is_array($value) ? md5(json_encode($value)) : md5((string)$value);
        if (in_array($value_hash, $seen_values)) {
            continue;
        }
        $seen_values[] = $value_hash;
        
        $history[] = [
            'submission_id' => $submission['id'],
            'submitted_at' => $submission['submitted_at'],
            'submitted_by' => $submission['submitted_by'],
            'submitter_email' => $submission['submitter_email'],
            'value' => $value, // Keep original structure for rendering
            'value_length' => is_array($value) ? count($value) : strlen($value),
            'formatted_date' => date('M j, Y g:i A', strtotime($submission['submitted_at'])),
            'is_draft' => 0 // TODO: Add draft detection if needed
        ];
    }
    
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
    
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT s.id) as count
        FROM program_submissions s
        WHERE s.program_id = ?
        AND s.period_id = ?
        AND s.submission_data IS NOT NULL
        AND JSON_EXTRACT(s.submission_data, CONCAT('$.', ?)) IS NOT NULL
    ");
    
    $stmt->bind_param("iis", $program_id, $period_id, $field_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return (int)$row['count'];
}
?>
