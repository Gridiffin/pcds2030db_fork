<?php
/**
 * Program User Assignments API
 * Handles CRUD operations for program_user_assignments
 */

ob_start();
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';

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
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    $sql = "SELECT * FROM program_user_assignments WHERE 1=1";
    $params = [];
    $types = '';
    if ($program_id) {
        $sql .= " AND program_id = ?";
        $params[] = $program_id;
        $types .= 'i';
    }
    if ($user_id) {
        $sql .= " AND user_id = ?";
        $params[] = $user_id;
        $types .= 'i';
    }
    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $assignments]);
}

function handlePost($input) {
    global $conn;
    if (!is_admin() && !is_focal()) {
        http_response_code(403);
        echo json_encode(['error' => 'Permission denied']);
        return;
    }
    if (!$input || !isset($input['program_id']) || !isset($input['user_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    $program_id = intval($input['program_id']);
    $user_id = intval($input['user_id']);
    $role = isset($input['role']) ? $input['role'] : 'editor';
    $sql = "INSERT INTO program_user_assignments (program_id, user_id, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iis', $program_id, $user_id, $role);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'assignment_id' => $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create assignment']);
    }
}

function handlePut($input) {
    global $conn;
    if (!is_admin() && !is_focal()) {
        http_response_code(403);
        echo json_encode(['error' => 'Permission denied']);
        return;
    }
    if (!$input || !isset($input['assignment_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing assignment_id']);
        return;
    }
    $assignment_id = intval($input['assignment_id']);
    $role = isset($input['role']) ? $input['role'] : null;
    if ($role) {
        $sql = "UPDATE program_user_assignments SET role = ? WHERE assignment_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $role, $assignment_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update assignment']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'No fields to update']);
    }
}

function handleDelete($input) {
    global $conn;
    if (!is_admin() && !is_focal()) {
        http_response_code(403);
        echo json_encode(['error' => 'Permission denied']);
        return;
    }
    if (!$input || !isset($input['assignment_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing assignment_id']);
        return;
    }
    $assignment_id = intval($input['assignment_id']);
    $sql = "DELETE FROM program_user_assignments WHERE assignment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $assignment_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete assignment']);
    }
} 