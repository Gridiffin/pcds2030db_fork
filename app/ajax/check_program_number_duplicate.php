<?php
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/agencies/core.php';

header('Content-Type: application/json');

if (!is_agency()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied.']);
    exit;
}

$initiative_id = isset($_POST['initiative_id']) ? intval($_POST['initiative_id']) : 0;
$program_number = isset($_POST['program_number']) ? trim($_POST['program_number']) : '';

if (!$initiative_id || !$program_number) {
    echo json_encode(['exists' => false, 'error' => 'Missing parameters.']);
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM programs WHERE initiative_id = ? AND program_number = ? AND is_deleted = 0");
$stmt->bind_param("is", $initiative_id, $program_number);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row && $row['cnt'] > 0) {
    echo json_encode(['exists' => true]);
} else {
    echo json_encode(['exists' => false]);
} 