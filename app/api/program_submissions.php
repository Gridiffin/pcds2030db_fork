<?php
/**
 * Program Submissions API
 * Handles CRUD operations for program_submissions (period-specific, draft/submitted logic)
 */

ob_start();
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/notifications_core.php';
// Ensure is_focal() is available
if (!function_exists('is_focal')) {
    /**
     * Checks if the current user is a focal user.
     * Adjust this logic as per your application's requirements.
     * Example: return isset($_SESSION['role']) && $_SESSION['role'] === 'focal';
     */
    function is_focal() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'focal';
    }
}

// Only allow logged-in users
if (!is_logged_in()) {
    ob_end_clean();
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

ob_end_clean();
header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            handlePost($input);
            break;
        case 'PUT':
            handlePut($input);
            break;
        case 'DELETE':
            handleDelete($input);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

function handleGet() {
    global $conn;
    $program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : null;
    $period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;
    $is_draft = isset($_GET['is_draft']) ? intval($_GET['is_draft']) : null;
    $is_submitted = isset($_GET['is_submitted']) ? intval($_GET['is_submitted']) : null;
    $sql = "SELECT * FROM program_submissions WHERE is_deleted = 0";
    $params = [];
    $types = '';
    if ($program_id) {
        $sql .= " AND program_id = ?";
        $params[] = $program_id;
        $types .= 'i';
    }
    if ($period_id) {
        $sql .= " AND period_id = ?";
        $params[] = $period_id;
        $types .= 'i';
    }
    if ($is_draft !== null) {
        $sql .= " AND is_draft = ?";
        $params[] = $is_draft;
        $types .= 'i';
    }
    if ($is_submitted !== null) {
        $sql .= " AND is_submitted = ?";
        $params[] = $is_submitted;
        $types .= 'i';
    }
    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $submissions]);
}

/**
 * Strictly validate a date string as YYYY-MM-DD or empty/null.
 * Returns the date if valid, or null if empty, or false if invalid.
 */
function validate_program_date($date) {
    if (empty($date)) return null;
    $date = trim($date);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $date;
    }
    return false;
}

function handlePost($input) {
    global $conn;
    if (!is_focal()) {
        http_response_code(403);
        echo json_encode(['error' => 'Permission denied']);
        return;
    }
    if (!$input || !isset($input['program_id']) || !isset($input['period_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    $program_id = intval($input['program_id']);
    $period_id = intval($input['period_id']);
    $is_draft = isset($input['is_draft']) ? intval($input['is_draft']) : 1;
    $is_submitted = isset($input['is_submitted']) ? intval($input['is_submitted']) : 0;
    $description = isset($input['description']) ? $input['description'] : null;
    $start_date = validate_program_date($input['start_date'] ?? '');
    $end_date = validate_program_date($input['end_date'] ?? '');
    if ($start_date === false) {
        http_response_code(400);
        echo json_encode(['error' => 'Start Date must be in YYYY-MM-DD format']);
        return;
    }
    if ($end_date === false) {
        http_response_code(400);
        echo json_encode(['error' => 'End Date must be in YYYY-MM-DD format']);
        return;
    }
    $submitted_by = $is_submitted ? $_SESSION['user_id'] : null;
    $submitted_at = $is_submitted ? date('Y-m-d H:i:s') : null;
    $sql = "INSERT INTO program_submissions (program_id, period_id, is_draft, is_submitted, description, start_date, end_date, submitted_by, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiiisssss', $program_id, $period_id, $is_draft, $is_submitted, $description, $start_date, $end_date, $submitted_by, $submitted_at);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'submission_id' => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create submission']);
    }
}

function handlePut($input) {
    global $conn;
    if (!is_focal()) {
        http_response_code(403);
        echo json_encode(['error' => 'Permission denied']);
        return;
    }
    if (!$input || !isset($input['submission_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing submission_id']);
        return;
    }
    $submission_id = intval($input['submission_id']);
    $fields = [];
    $params = [];
    $types = '';
    $allowed = ['is_draft','is_submitted','description','is_deleted'];
    foreach ($allowed as $field) {
        if (isset($input[$field])) {
            $fields[] = "$field = ?";
            $params[] = $input[$field];
            $types .= is_int($input[$field]) ? 'i' : 's';
        }
    }
    // Handle start_date and end_date with validation
    if (isset($input['start_date'])) {
        $start_date = validate_program_date($input['start_date']);
        if ($start_date === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Start Date must be in YYYY-MM-DD format']);
            return;
        }
        $fields[] = "start_date = ?";
        $params[] = $start_date;
        $types .= 's';
    }
    if (isset($input['end_date'])) {
        $end_date = validate_program_date($input['end_date']);
        if ($end_date === false) {
            http_response_code(400);
            echo json_encode(['error' => 'End Date must be in YYYY-MM-DD format']);
            return;
        }
        $fields[] = "end_date = ?";
        $params[] = $end_date;
        $types .= 's';
    }
    if (isset($input['is_submitted']) && $input['is_submitted']) {
        $fields[] = "submitted_by = ?";
        $fields[] = "submitted_at = ?";
        $params[] = $_SESSION['user_id'];
        $params[] = date('Y-m-d H:i:s');
        $types .= 'is';
    }
    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'No fields to update']);
        return;
    }
    $params[] = $submission_id;
    $types .= 'i';
    $sql = "UPDATE program_submissions SET ".implode(', ', $fields).", updated_at = NOW() WHERE submission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update submission']);
    }
}

function handleDelete($input) {
    global $conn;
    if (!is_focal()) {
        http_response_code(403);
        echo json_encode(['error' => 'Permission denied']);
        return;
    }
    if (!$input || !isset($input['submission_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing submission_id']);
        return;
    }
    $submission_id = intval($input['submission_id']);
    
    // Get submission data before deletion for notifications
    $query = "SELECT ps.submission_id, ps.program_id, p.program_name, p.agency_id, a.agency_name, 
                     rp.period_type, rp.year, ps.is_draft
              FROM program_submissions ps
              JOIN programs p ON ps.program_id = p.program_id
              JOIN agency a ON p.agency_id = a.agency_id
              JOIN reporting_periods rp ON ps.period_id = rp.period_id
              WHERE ps.submission_id = ? AND ps.is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $submission_id);
    $stmt->execute();
    $submission_data = $stmt->get_result()->fetch_assoc();
    
    if (!$submission_data) {
        http_response_code(404);
        echo json_encode(['error' => 'Submission not found or already deleted']);
        return;
    }
    
    // Prepare submission data for notification
    $notification_data = [
        'program_name' => $submission_data['program_name'],
        'agency_id' => $submission_data['agency_id'],
        'agency_name' => $submission_data['agency_name'],
        'period_text' => $submission_data['period_type'] . ' ' . $submission_data['year']
    ];
    
    $sql = "UPDATE program_submissions SET is_deleted = 1 WHERE submission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $submission_id);
    if ($stmt->execute()) {
        // Send deletion notifications
        notify_submission_deleted($submission_id, $submission_data['program_id'], $_SESSION['user_id'], $notification_data);
        
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete submission']);
    }
} 