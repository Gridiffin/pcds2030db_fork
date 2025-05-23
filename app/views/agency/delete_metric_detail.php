<?php
// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Start output buffering to catch any unwanted output
ob_start();

require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';

// Clear any previous output and set JSON header
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['detail_id'])) {
    echo json_encode(['success' => false, 'message' => 'Detail ID is required']);
    exit;
}

$detail_id = (int)$_GET['detail_id'];

try {
    if (!$conn) {
        throw new Exception('Database connection error');
    }

    // First check if the detail exists and get its data
    $stmt = $conn->prepare("SELECT detail_id, detail_name, detail_json FROM metrics_details WHERE detail_id = ?");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }

    $stmt->bind_param('i', $detail_id);
    if (!$stmt->execute()) {
        throw new Exception('Database execute error: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Outcome detail not found']);
        exit;
    }
    
    // Get the detail info for logging
    $detail = $result->fetch_assoc();    // Begin transaction
    $conn->begin_transaction();

    try {
        // Delete the outcome detail
        $delete_stmt = $conn->prepare("DELETE FROM metrics_details WHERE detail_id = ?");
        if (!$delete_stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }

        $delete_stmt->bind_param('i', $detail_id);
        if (!$delete_stmt->execute()) {
            throw new Exception('Database execute error: ' . $delete_stmt->error);
        }

        // Check if any rows were affected
        if ($delete_stmt->affected_rows > 0) {
            // Log the deletion
            error_log(sprintf(
                'Metric detail deleted - ID: %d, Name: %s',
                $detail_id,
                $detail['detail_name']
            ));

            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Metric detail deleted successfully',
                'detail_id' => $detail_id
            ]);
        } else {
            throw new Exception('No records were deleted');
        }
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($delete_stmt)) $delete_stmt->close();
    }

} catch (Exception $e) {
    error_log('Delete metric detail error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'detail_id' => $detail_id
    ]);
}
?>

