<?php
/**
 * Delete Metric Detail
 * 
 * Allows agency users to delete a metric detail by detail_id.
 */

require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if detail_id is provided and valid
if (!isset($_GET['detail_id']) || !is_numeric($_GET['detail_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid detail ID']);
    exit;
}

$detail_id = (int) $_GET['detail_id'];

// Delete the metric detail from the metrics_details table
$stmt = $conn->prepare("DELETE FROM metrics_details WHERE detail_id = ?");
$stmt->bind_param("i", $detail_id);

if ($stmt->execute()) {
    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Metric detail deleted successfully']);
    exit;
} else {
    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to delete metric detail']);
    exit;
}
?>
