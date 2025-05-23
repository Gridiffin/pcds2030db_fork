<?php
/**
 * Unsubmit Outcome
 * 
 * Sets the is_draft flag to 1 for the selected metric_id.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admin_functions.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
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


