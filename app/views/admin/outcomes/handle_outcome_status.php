<?php
header('Content-Type: application/json');
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';

// Verify user is an admin and request is AJAX
if (!is_admin() || !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Get parameters
$metric_id = isset($_POST['metric_id']) ? intval($_POST['metric_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Basic check for database connection object
if (!isset($conn) || !$conn instanceof mysqli || $conn->connect_error) {
    http_response_code(500);
    // Ensure metric_id is available for the JSON response, even if it's 0 from initial POST check
    $response_metric_id = isset($_POST['metric_id']) ? intval($_POST['metric_id']) : 0;
    exit(json_encode([
        'success' => false, 
        'message' => 'Database connection error. Please check server logs. Specific error: '
            . (isset($conn) && $conn instanceof mysqli
               ? $conn->connect_error
               : 'mysqli object not available or connection failed before object instantiation.'),
        'metric_id' => $response_metric_id
    ]));
}

if (!$metric_id || !in_array($action, ['submit', 'unsubmit'])) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Invalid parameters', 'metric_id' => $metric_id]));
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Ensure session user_id is set
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User session not found or user_id is not set.");
    }
    $user_id = $_SESSION['user_id'];
    $is_draft = ($action === 'unsubmit') ? 1 : 0;
    
    // Update the outcome status
    $update_query = "UPDATE sector_outcomes_data 
                     SET is_draft = ?, 
                         submitted_by = ?, 
                         updated_at = NOW(),
                         submitted_at = IF(? = 1, NULL, submitted_at)
                     WHERE metric_id = ?";
    $stmt = $conn->prepare($update_query);

    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }

    // Bind with an extra slot for is_draft to handle the IF condition
    $stmt->bind_param("iiii", $is_draft, $user_id, $is_draft, $metric_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating outcome: " . $stmt->error);
    }
    
    // Get the outcome data for history recording
    $outcome = get_outcome_data_for_display($metric_id);
    if (!$outcome) {
        throw new Exception("Could not retrieve outcome data");
    }
    
    // Record in history
    if (!record_outcome_history(
        $outcome['id'],
        $metric_id,
        $outcome['data_json'],
        $action,
        $is_draft ? 'draft' : 'submitted',
        $user_id,
        'Outcome ' . ($is_draft ? 'unsubmitted' : 'submitted') . ' by admin'
    )) {
        throw new Exception("Failed to record outcome history");
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response with new draft status
    exit(json_encode([
        'success' => true,
        'message' => 'Outcome ' . ($is_draft ? 'unsubmitted' : 'submitted') . ' successfully.',
        'metric_id' => $metric_id,
        'is_draft' => $is_draft // 0 for submitted, 1 for draft
    ]));
    
} catch (Throwable $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) {
        @$conn->rollback();
    }
    http_response_code(500);
    $response_metric_id_on_error = isset($metric_id) ? $metric_id : (isset($_POST['metric_id']) ? intval($_POST['metric_id']) : 0);
    exit(json_encode([
        'success' => false,
        'message' => "Server error: " . $e->getMessage(),
        'metric_id' => $response_metric_id_on_error
    ]));
}
?>
