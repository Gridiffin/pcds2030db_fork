<?php
/**
 * Unsubmit Outcome
 * 
 * Sets the is_draft flag to 1 for the selected metric_id.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin_functions.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Check if metric_id is provided and valid
if (!isset($_GET['metric_id']) || !is_numeric($_GET['metric_id'])) {
    // Invalid request, redirect back
    header('Location: manage_metrics.php');
    exit;
}

$metric_id = intval($_GET['metric_id']);

// Prepare and execute update query to set is_draft = 1
$sql = "UPDATE sector_metrics_data SET is_draft = 1 WHERE metric_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('i', $metric_id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to manage_metrics.php after update
header('Location: manage_metrics.php');
exit;
?>
