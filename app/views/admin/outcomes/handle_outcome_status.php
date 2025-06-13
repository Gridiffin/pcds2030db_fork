<?php
header('Content-Type: application/json');
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an admin and request is AJAX
if (!is_admin() || !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    // Log unauthorized outcome status change attempt
    log_audit_action(
        'outcome_status_change_denied',
        'Unauthorized attempt to change outcome status',
        'failure'
    );
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
    // Log invalid outcome status change parameters
    log_audit_action(
        'outcome_status_change_failed',
        "Invalid parameters for outcome status change. Metric ID: {$metric_id}, Action: {$action}",
        'failure'
    );
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Invalid parameters', 'metric_id' => $metric_id]));
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Ensure session user_id is set
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User session not found or user_id is not set.");
    }    $user_id = $_SESSION['user_id'];
    $is_draft = ($action === 'unsubmit') ? 1 : 0;
    
    // Get the outcome data BEFORE updating it (for history recording)
    $outcome = get_outcome_data_for_display($metric_id);
    if (!$outcome) {
        throw new Exception("Could not retrieve outcome data");
    }
      // Update the outcome status
    $update_query = "UPDATE sector_outcomes_data 
                     SET is_draft = ?, 
                         submitted_by = ?, 
                         updated_at = NOW()
                     WHERE metric_id = ?";
    $stmt = $conn->prepare($update_query);

    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }

    // Bind parameters: is_draft, submitted_by, metric_id
    $stmt->bind_param("iii", $is_draft, $user_id, $metric_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating outcome: " . $stmt->error);
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
    
    // Log successful outcome status change
    $status_text = $is_draft ? 'unsubmitted (set to draft)' : 'submitted';
    log_audit_action(
        'outcome_status_change',
        "Successfully {$status_text} outcome for metric ID: {$metric_id}",
        'success'
    );
    
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
    
    // Log failed outcome status change
    $status_text = isset($is_draft) ? ($is_draft ? 'unsubmit' : 'submit') : 'change status of';
    log_audit_action(
        'outcome_status_change_failed',
        "Failed to {$status_text} outcome for metric ID: {$metric_id}. Error: " . $e->getMessage(),
        'failure'
    );
    
    http_response_code(500);
    $response_metric_id_on_error = isset($metric_id) ? $metric_id : (isset($_POST['metric_id']) ? intval($_POST['metric_id']) : 0);
    exit(json_encode([
        'success' => false,
        'message' => "Server error: " . $e->getMessage(),
        'metric_id' => $response_metric_id_on_error
    ]));
}
?>
