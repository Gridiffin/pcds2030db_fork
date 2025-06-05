<?php
/**
 * Unsubmit Sector Outcome
 * 
 * Sets the status of a specific sector outcome to 'draft' or marks it as unsubmitted.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php'; // Assumes db_connect.php handles $conn
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php'; // General functions, potentially outcome-related
require_once ROOT_PATH . 'app/lib/admin_functions.php'; // Admin-specific functions
require_once ROOT_PATH . 'app/lib/admins/outcomes.php'; // Contains outcome functions and record_outcome_history
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an admin
if (!is_admin()) {
    $_SESSION['error_message'] = "Access denied. You must be an administrator to perform this action.";
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Check if metric_id is provided and valid (renamed from outcome_id to match table schema)
if (!isset($_GET['metric_id']) && !isset($_GET['outcome_id'])) {
    $_SESSION['error_message'] = "Invalid request. ID is missing or invalid.";
    // Redirect back to the outcomes management page
    header('Location: manage_outcomes.php');
    exit;
}

// Support both metric_id (new) and outcome_id (legacy) parameters
$metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : intval($_GET['outcome_id']);

// Database connection should be established by db_connect.php, available as $conn
if (!$conn) {
    // This should ideally be handled more gracefully, perhaps by db_connect.php itself
    $_SESSION['error_message'] = "Database connection failed.";
    header('Location: ../metrics/manage_metrics.php');
    exit;
}

// Attempt to unsubmit the outcome
// This assumes a function unsubmit_outcome_data() exists in functions.php or admin_functions.php
// Alternatively, direct SQL can be used here if such a function is not yet implemented.

// Example using a hypothetical unsubmit_outcome_data function:
/*
if (function_exists('unsubmit_outcome_data')) {
    if (unsubmit_outcome_data($conn, $outcome_id)) {
        $_SESSION['success_message'] = "Outcome has been successfully un-submitted and marked as draft.";
    } else {
        // The function should set a more specific error message if possible
        $_SESSION['error_message'] = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : "Failed to un-submit the outcome. Please check logs or try again.";
    }
} else {
    // Fallback to direct SQL if the helper function isn't available
    // This assumes a table, e.g., 'sector_outcomes' and a status column, e.g., 'status' or 'is_draft'
    // Adjust table and column names as per your database schema
    $sql = "UPDATE sector_outcomes SET status = 'draft' WHERE outcome_id = ?"; // Or SET is_draft = 1
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('i', $outcome_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['success_message'] = "Outcome has been successfully un-submitted and marked as draft.";
            } else {
                // Check if the outcome record actually exists
                $check_sql = "SELECT outcome_id FROM sector_outcomes WHERE outcome_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param('i', $outcome_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                if ($check_result->num_rows === 0) {
                    $_SESSION['error_message'] = "No outcome found with ID {$outcome_id}. Nothing to un-submit.";
                } else {
                    $_SESSION['info_message'] = "Outcome was already in the desired state or no changes were made.";
                }
                $check_stmt->close();
            }
        } else {
            $_SESSION['error_message'] = "Failed to un-submit the outcome. Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Failed to prepare the database statement for un-submitting the outcome.";
    }
}
*/

// Using direct SQL as a placeholder until helper functions are confirmed/implemented
// Using the correct table name sector_outcomes_data and the is_draft field
// Note: The table uses metric_id as the primary identifier, not outcome_id
// First get the outcome data for the history record
$get_sql = "SELECT id, data_json FROM sector_outcomes_data WHERE metric_id = ?";
$get_stmt = $conn->prepare($get_sql);
$outcome_record_id = 0;
$data_json = '{}';

if ($get_stmt) {
    $get_stmt->bind_param('i', $metric_id);
    $get_stmt->execute();
    $get_result = $get_stmt->get_result();
    
    if ($row = $get_result->fetch_assoc()) {
        $outcome_record_id = $row['id'];
        $data_json = $row['data_json'];
    }
    $get_stmt->close();
}

$sql = "UPDATE sector_outcomes_data SET is_draft = 1 WHERE metric_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('i', $metric_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Record history for the unsubmit action
            $user_id = $_SESSION['user_id'];
            $action_type = 'unsubmit';
            $status = 'draft'; // After unsubmitting, the status becomes draft
            $description = "Outcome marked as draft (unsubmitted)";
              if ($outcome_record_id > 0) {
                record_outcome_history($outcome_record_id, $metric_id, $data_json, $action_type, $status, $user_id, $description);
            }
            
            $_SESSION['success_message'] = "Outcome (ID: {$metric_id}) has been successfully un-submitted and marked as Draft.";
            
            // Log successful outcome unsubmit
            log_audit_action(
                'admin_outcome_unsubmitted',
                "Admin unsubmitted outcome (Metric ID: {$metric_id}) - changed to draft status",
                'success',
                $_SESSION['user_id']
            );
        } else {
            // Check if the outcome record actually exists to provide a more accurate message
            $check_sql = "SELECT metric_id FROM sector_outcomes_data WHERE metric_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            if ($check_stmt) {
                $check_stmt->bind_param('i', $metric_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                if ($check_result->num_rows === 0) {
                    $_SESSION['error_message'] = "No outcome found with ID {$metric_id}. Nothing to un-submit.";
                } else {
                    // Outcome exists, but was already 'Draft' or no change was made
                    $_SESSION['info_message'] = "Outcome (ID: {$metric_id}) was already in a draft state or no changes were necessary.";
                }
                $check_stmt->close();
            } else {
                 $_SESSION['error_message'] = "Could not verify outcome status. Please check manually.";
            }
        }    } else {
        $_SESSION['error_message'] = "Failed to un-submit outcome (ID: {$metric_id}). Database error: " . $stmt->error;
        
        // Log outcome unsubmit failure
        log_audit_action(
            'admin_outcome_unsubmit_failed',
            "Admin failed to unsubmit outcome (Metric ID: {$metric_id}): " . $stmt->error,
            'failure',
            $_SESSION['user_id']
        );
    }
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Failed to prepare the database statement for un-submitting outcome. Error: " . $conn->error;
}

// Redirect back to the outcomes management page
header('Location: manage_outcomes.php'); // Correctly redirect to the outcomes management page
exit;

?>
