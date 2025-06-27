<?php
// Update outcome detail items (AJAX)
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__, 3) . '/lib/db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id']) || !is_numeric($data['id']) || !isset($data['items']) || !is_array($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}
$detail_id = (int)$data['id'];
$items = $data['items'];

// Fetch the current detail_json to preserve layout_type and other fields
$stmt = $conn->prepare('SELECT detail_json FROM outcomes_details WHERE detail_id = ?');
$stmt->bind_param('i', $detail_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Detail not found.']);
    exit;
}
$row = $result->fetch_assoc();
$detail_json = json_decode($row['detail_json'], true);
if (!is_array($detail_json)) $detail_json = [];
$detail_json['items'] = $items;

// Save updated detail_json
$new_json = json_encode($detail_json, JSON_UNESCAPED_UNICODE);
$update = $conn->prepare('UPDATE outcomes_details SET detail_json = ?, updated_at = NOW() WHERE detail_id = ?');
$update->bind_param('si', $new_json, $detail_id);
if ($update->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}
