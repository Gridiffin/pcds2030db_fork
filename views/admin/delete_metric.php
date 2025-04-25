<?php
// views/admin/delete_metric.php

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

// Check if metric_id is provided
if (!isset($_GET['metric_id']) || !is_numeric($_GET['metric_id'])) {
    // Invalid metric_id, redirect back with error
    $_SESSION['error_message'] = 'Invalid metric ID.';
    header('Location: manage_metrics.php');
    exit;
}

$metric_id = (int) $_GET['metric_id'];

// Prepare and execute delete query
$delete_query = "DELETE FROM sector_metrics_submitted WHERE metric_id = ?";
$stmt = $conn->prepare($delete_query);

if (!$stmt) {
    $_SESSION['error_message'] = 'Failed to prepare delete statement.';
    header('Location: manage_metrics.php');
    exit;
}

$stmt->bind_param("i", $metric_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Metric ID $metric_id deleted successfully.";
} else {
    $_SESSION['error_message'] = "Failed to delete metric ID $metric_id: " . $stmt->error;
}

$stmt->close();

// Redirect back to manage_metrics.php
header('Location: manage_metrics.php');
exit;
?>
