<?php
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['detail_id'])) {
    echo json_encode(['success' => false, 'message' => 'Detail ID is required']);
    exit;
}

$detail_id = (int)$_GET['detail_id'];

try {
    // First check if the detail exists
    $stmt = $conn->prepare("SELECT detail_id FROM metrics_details WHERE detail_id = ?");
    $stmt->bind_param('i', $detail_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Metric detail not found']);
        exit;
    }
    
    // Delete the metric detail
    $stmt = $conn->prepare("DELETE FROM metrics_details WHERE detail_id = ?");
    $stmt->bind_param('i', $detail_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Metric detail deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete metric detail']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>