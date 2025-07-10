<?php
/**
 * AJAX endpoint to get audit history for a specific target field
 * Params: target_id, field_name
 * Returns: JSON array of changes (old_value, new_value, user, timestamp)
 */
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$target_id = isset($_GET['target_id']) ? intval($_GET['target_id']) : 0;
$field_name = isset($_GET['field_name']) ? $_GET['field_name'] : '';

if (!$target_id || !$field_name) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing target_id or field_name']);
    exit;
}

try {
    // Find all audit log changes for this target and field
    $query = "
        SELECT afc.old_value, afc.new_value, afc.change_type, afc.created_at, al.user_id, u.fullname as user_name
        FROM audit_field_changes afc
        JOIN audit_logs al ON afc.audit_log_id = al.id
        LEFT JOIN users u ON al.user_id = u.id
        WHERE afc.field_name = ?
          AND afc.target_id = ?
        ORDER BY afc.created_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $field_name, $target_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $changes = [];
    while ($row = $result->fetch_assoc()) {
        $changes[] = [
            'old_value' => $row['old_value'],
            'new_value' => $row['new_value'],
            'change_type' => $row['change_type'],
            'changed_at' => $row['created_at'],
            'user_id' => $row['user_id'],
            'user_name' => $row['user_name'] ?? 'Unknown',
        ];
    }
    echo json_encode(['success' => true, 'changes' => $changes]);
} catch (Exception $e) {
    error_log('Error in get_target_field_history.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
} 