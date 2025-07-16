<?php
/**
 * Program Status & Hold Point API
 * Handles status indicator and hold point management for programs (agency side)
 * Endpoints:
 *   - GET: status, status_history
 *   - POST: set_status, hold_point, end_hold_point
 */

require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/agencies/core.php';

header('Content-Type: application/json');

// Auth: Only agency/focal users
if (!is_agency() && !is_focal()) {
    http_response_code(403);
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? null;

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Utility: Check if user is owner/focal for a program
function can_edit_program($program_id, $user_id, $conn) {
    // Owner: created_by in programs
    $stmt = $conn->prepare('SELECT created_by FROM programs WHERE program_id = ?');
    $stmt->bind_param('i', $program_id);
    $stmt->execute();
    $stmt->bind_result($created_by);
    $stmt->fetch();
    $stmt->close();
    if ($created_by == $user_id) return true;
    // Focal: check program_user_assignments
    $stmt = $conn->prepare('SELECT 1 FROM program_user_assignments WHERE program_id = ? AND user_id = ? AND role = "editor" AND is_active = 1');
    $stmt->bind_param('ii', $program_id, $user_id);
    $stmt->execute();
    $stmt->store_result();
    $can_edit = $stmt->num_rows > 0;
    $stmt->close();
    return $can_edit;
}

$user_id = $_SESSION['user_id'];

if ($method === 'GET') {
    if ($action === 'status') {
        // Get current status and hold point for a program
        $program_id = intval($_GET['program_id'] ?? 0);
        if (!$program_id) respond(['error' => 'Missing program_id'], 400);
        $stmt = $conn->prepare('SELECT status FROM programs WHERE program_id = ?');
        $stmt->bind_param('i', $program_id);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();
        // Get current hold point (if any)
        $stmt = $conn->prepare('SELECT id, reason, remarks, created_at, ended_at FROM program_hold_points WHERE program_id = ? AND ended_at IS NULL ORDER BY created_at DESC LIMIT 1');
        $stmt->bind_param('i', $program_id);
        $stmt->execute();
        $hold = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        respond(['status' => $status, 'hold_point' => $hold]);
    } elseif ($action === 'status_history') {
        // Get full status history for a program
        $program_id = intval($_GET['program_id'] ?? 0);
        if (!$program_id) respond(['error' => 'Missing program_id'], 400);
        $stmt = $conn->prepare('SELECT status, changed_by, changed_at, remarks FROM program_status_history WHERE program_id = ? ORDER BY changed_at ASC');
        $stmt->bind_param('i', $program_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $history = [];
        while ($row = $result->fetch_assoc()) $history[] = $row;
        $stmt->close();
        // Hold points history
        $stmt = $conn->prepare('SELECT id, reason, remarks, created_at, ended_at, created_by FROM program_hold_points WHERE program_id = ? ORDER BY created_at ASC');
        $stmt->bind_param('i', $program_id);
        $stmt->execute();
        $hold_points = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) $hold_points[] = $row;
        $stmt->close();
        respond(['status_history' => $history, 'hold_points' => $hold_points]);
    } else {
        respond(['error' => 'Invalid action'], 400);
    }
} elseif ($method === 'POST') {
    $input = $_POST;
    $program_id = intval($input['program_id'] ?? 0);
    if (!$program_id) respond(['error' => 'Missing program_id'], 400);
    if (!can_edit_program($program_id, $user_id, $conn)) respond(['error' => 'No permission'], 403);
    if ($action === 'set_status') {
        $status = $input['status'] ?? '';
        $remarks = $input['remarks'] ?? null;
        $valid_statuses = ['active', 'on_hold', 'completed', 'delayed', 'cancelled'];
        if (!in_array($status, $valid_statuses)) respond(['error' => 'Invalid status'], 400);
        // Update status in programs
        $stmt = $conn->prepare('UPDATE programs SET status = ? WHERE program_id = ?');
        $stmt->bind_param('si', $status, $program_id);
        $stmt->execute();
        $stmt->close();
        // Log in status history
        $stmt = $conn->prepare('INSERT INTO program_status_history (program_id, status, changed_by, remarks) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('isis', $program_id, $status, $user_id, $remarks);
        $stmt->execute();
        $stmt->close();
        // If status is on_hold, create hold point (if not already active)
        if ($status === 'on_hold') {
            $stmt = $conn->prepare('SELECT id FROM program_hold_points WHERE program_id = ? AND ended_at IS NULL');
            $stmt->bind_param('i', $program_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 0) {
                $reason = $input['reason'] ?? '';
                $hold_remarks = $input['hold_remarks'] ?? null;
                $stmt2 = $conn->prepare('INSERT INTO program_hold_points (program_id, reason, remarks, created_by) VALUES (?, ?, ?, ?)');
                $stmt2->bind_param('issi', $program_id, $reason, $hold_remarks, $user_id);
                $stmt2->execute();
                $stmt2->close();
            }
            $stmt->close();
        } else {
            // If status is not on_hold, end any active hold point
            $stmt = $conn->prepare('UPDATE program_hold_points SET ended_at = NOW() WHERE program_id = ? AND ended_at IS NULL');
            $stmt->bind_param('i', $program_id);
            $stmt->execute();
            $stmt->close();
        }
        respond(['success' => true]);
    } elseif ($action === 'hold_point') {
        // Edit current hold point (if active)
        $reason = $input['reason'] ?? '';
        $hold_remarks = $input['hold_remarks'] ?? null;
        $stmt = $conn->prepare('UPDATE program_hold_points SET reason = ?, remarks = ? WHERE program_id = ? AND ended_at IS NULL');
        $stmt->bind_param('ssi', $reason, $hold_remarks, $program_id);
        $stmt->execute();
        $stmt->close();
        respond(['success' => true]);
    } elseif ($action === 'end_hold_point') {
        // End the current hold point and update program status to active
        $stmt = $conn->prepare('UPDATE program_hold_points SET ended_at = NOW() WHERE program_id = ? AND ended_at IS NULL');
        $stmt->bind_param('i', $program_id);
        $stmt->execute();
        $stmt->close();
        
        // Update program status from "on_hold" to "active"
        $stmt = $conn->prepare('UPDATE programs SET status = "active" WHERE program_id = ? AND status = "on_hold"');
        $stmt->bind_param('i', $program_id);
        $stmt->execute();
        $stmt->close();
        
        // Log the status change in history
        $remarks = 'Status automatically changed to active when hold point ended';
        $stmt = $conn->prepare('INSERT INTO program_status_history (program_id, status, changed_by, remarks) VALUES (?, "active", ?, ?)');
        $stmt->bind_param('iis', $program_id, $user_id, $remarks);
        $stmt->execute();
        $stmt->close();
        
        respond(['success' => true]);
    } else {
        respond(['error' => 'Invalid action'], 400);
    }
} else {
    respond(['error' => 'Invalid method'], 405);
}

// End of file 