<?php
/**
 * Delete Sector Outcome
 * 
 * Allows admin users to delete a sector outcome by metric_id.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Check if metric_id is provided
if (!isset($_GET['metric_id']) || !is_numeric($_GET['metric_id'])) {
    header('Location: metrics/manage_metrics.php?error=Invalid outcome ID');
    exit;
}

$metric_id = (int) $_GET['metric_id'];

// Verify that the metric exists and get its sector information
$query = "SELECT sector_id, table_name FROM sector_metrics_data WHERE metric_id = ? AND is_draft = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $metric_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: metrics/manage_metrics.php?error=Metric not found');
    exit;
}

$metric_data = $result->fetch_assoc();
$table_name = $metric_data['table_name'];
$sector_id = $metric_data['sector_id'];

// Add a confirmation step
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    // Redirect back to manage metrics page with confirmation dialog
    echo "<script>
        if (confirm('Are you sure you want to delete the metric \"" . htmlspecialchars($table_name) . "\"? This action cannot be undone.')) {
            window.location.href = 'delete_metric.php?metric_id=$metric_id&confirm=yes';
        } else {
            window.location.href = 'metrics/manage_metrics.php';
        }
    </script>";
    exit;
}

// Proceed with deletion
$delete_query = "DELETE FROM sector_metrics_data WHERE metric_id = ? AND is_draft = 0";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("i", $metric_id);

if ($stmt->execute()) {
    // Success message
    header('Location: metrics/manage_metrics.php?success=Metric "' . urlencode($table_name) . '" deleted successfully');
} else {
    // Error message
    header('Location: metrics/manage_metrics.php?error=Failed to delete metric: ' . $conn->error);
}
exit;
?>


