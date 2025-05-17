<?php
// submit_draft_metric.php
// Move data from draft to submitted status in the sector_metrics_data table

require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agencies/index.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Get metric_id from GET parameters
$metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 0;
$sector_id = $_SESSION['sector_id'] ?? 0;

if ($metric_id <= 0 || $sector_id <= 0) {
    $_SESSION['flash_error'] = 'Invalid outcome ID or sector ID.';
    header('Location: submit_metrics.php');
    exit;
}

// First, make sure we have a valid period ID
$current_period = get_current_reporting_period();
$period_id = $current_period['period_id'] ?? null;

if (!$period_id) {
    $_SESSION['error_message'] = 'No active reporting period found. Please contact an administrator.';
    header('Location: submit_metrics.php');
    exit;
}

global $conn;

// Start transaction
$conn->begin_transaction();

try {
    // Check if draft data exists
    $draft_query = "SELECT data_json, table_name FROM sector_metrics_data 
                   WHERE metric_id = ? AND sector_id = ? AND is_draft = 1 LIMIT 1";
    $stmt = $conn->prepare($draft_query);
    $stmt->bind_param("ii", $metric_id, $sector_id);
    $stmt->execute();
    $result = $stmt->get_result();    if ($result->num_rows === 0) {
        throw new Exception('No draft data found for the selected outcome.');
    }

    $draft_data = $result->fetch_assoc();
    $data_json = $draft_data['data_json'];
    $table_name = $draft_data['table_name'];

    // First copy the draft data into a submitted record (is_draft = 0)
    $insert_query = "INSERT INTO sector_metrics_data 
                    (metric_id, sector_id, period_id, table_name, data_json, is_draft) 
                    VALUES (?, ?, ?, ?, ?, 0)
                    ON DUPLICATE KEY UPDATE
                    table_name = VALUES(table_name),
                    data_json = VALUES(data_json),
                    updated_at = CURRENT_TIMESTAMP";
    
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iiiss", $metric_id, $sector_id, $period_id, $table_name, $data_json);
    $insert_stmt->execute();

    // Then delete the draft record
    $delete_query = "DELETE FROM sector_metrics_data WHERE metric_id = ? AND sector_id = ? AND is_draft = 1";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $metric_id, $sector_id);
    $delete_stmt->execute();

    // Commit transaction
    $conn->commit();    $_SESSION['flash_success'] = 'Outcome draft submitted successfully.';
    header('Location: submit_metrics.php');
    exit;
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['flash_error'] = 'Failed to submit outcome draft: ' . $e->getMessage();
    header('Location: submit_metrics.php');
    exit;
}
?>
