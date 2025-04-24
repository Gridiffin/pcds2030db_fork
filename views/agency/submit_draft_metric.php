<?php
// submit_draft_metric.php
// Move data tied to selected metric_id from sector_metrics_draft to sector_metrics_submitted

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

// Get metric_id from GET parameters
$metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 0;

if ($metric_id <= 0) {
    $_SESSION['flash_error'] = 'Invalid metric ID.';
    header('Location: submit_metrics.php');
    exit;
}

global $conn;

// Start transaction
$conn->begin_transaction();

try {
    // Select all rows from sector_metrics_draft for this metric_id
    $select_query = "SELECT * FROM sector_metrics_draft WHERE metric_id = ?";
    $stmt = $conn->prepare($select_query);
    $stmt->bind_param("i", $metric_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('No draft data found for the selected metric.');
    }

    // Prepare insert statement for sector_metrics_submitted
    $insert_query = "INSERT INTO sector_metrics_submitted (metric_id, sector_id, table_name, column_title, table_content, month) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);

    // Insert each row into sector_metrics_submitted
    while ($row = $result->fetch_assoc()) {
        $insert_stmt->bind_param(
            "iissss",
            $row['metric_id'],
            $row['sector_id'],
            $row['table_name'],
            $row['column_title'],
            $row['table_content'],
            $row['month']
        );
        $insert_stmt->execute();
    }

    // Delete rows from sector_metrics_draft for this metric_id
    $delete_query = "DELETE FROM sector_metrics_draft WHERE metric_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $metric_id);
    $delete_stmt->execute();

    // Commit transaction
    $conn->commit();

    $_SESSION['flash_success'] = 'Metric draft submitted successfully.';
    header('Location: submit_metrics.php');
    exit;
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['flash_error'] = 'Failed to submit metric draft: ' . $e->getMessage();
    header('Location: submit_metrics.php');
    exit;
}
?>
