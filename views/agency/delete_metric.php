<?php
/**
 * Delete Sector Metric Draft
 * 
 * Allows agency users to delete a sector metric draft by metric_id.
 */

require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Check if metric_id is provided
if (!isset($_GET['metric_id']) || !is_numeric($_GET['metric_id'])) {
    header('Location: submit_metrics.php?error=Invalid metric ID');
    exit;
}

$metric_id = (int) $_GET['metric_id'];
$sector_id = $_SESSION['sector_id'];

// Verify that the metric draft belongs to the user's sector using the new JSON-based table
$stmt = $conn->prepare("SELECT metric_id FROM sector_metrics_data WHERE metric_id = ? AND sector_id = ? AND is_draft = 1");
$stmt->bind_param("ii", $metric_id, $sector_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Metric draft not found or does not belong to user's sector
    $stmt->close();
    header('Location: submit_metrics.php?error=Metric draft not found or unauthorized');
    exit;
}
$stmt->close();

// Delete the metric draft from the new JSON-based table
$delete_stmt = $conn->prepare("DELETE FROM sector_metrics_data WHERE metric_id = ? AND sector_id = ? AND is_draft = 1");
$delete_stmt->bind_param("ii", $metric_id, $sector_id);
if ($delete_stmt->execute()) {
    $delete_stmt->close();
    header('Location: submit_metrics.php?success=Metric draft deleted successfully');
    exit;
} else {
    $delete_stmt->close();
    header('Location: submit_metrics.php?error=Failed to delete metric draft');
    exit;
}
?>
