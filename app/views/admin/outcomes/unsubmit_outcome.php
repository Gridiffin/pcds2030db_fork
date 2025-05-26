<?php
/**
 * Unsubmit Sector Outcome
 * 
 * Sets the status of a specific sector outcome to 'draft' or marks it as unsubmitted.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php'; // Assumes db_connect.php handles $conn
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php'; // General functions, potentially outcome-related
require_once PROJECT_ROOT_PATH . 'app/lib/admin_functions.php'; // Admin-specific functions

// Verify user is an admin
if (!is_admin()) {
    $_SESSION['error_message'] = "Access denied. You must be an administrator to perform this action.";
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Check if outcome_id is provided and valid
if (!isset($_GET['outcome_id']) || !is_numeric($_GET['outcome_id'])) {
    $_SESSION['error_message'] = "Invalid request. Outcome ID is missing or invalid.";
    // Redirect back to the outcomes management page
    header('Location: ../metrics/manage_metrics.php'); // This page now manages outcomes
    exit;
}

$outcome_id = intval($_GET['outcome_id']);

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
// Adjust 'sector_outcomes' and 'status' / 'is_draft' as necessary.
// Assuming 'status' field and setting it to 'Draft'.
// If you use 'is_draft' (boolean/tinyint), it would be 'SET is_draft = 1'
$sql = "UPDATE sector_outcomes SET status = 'Draft' WHERE outcome_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('i', $outcome_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Outcome (ID: {$outcome_id}) has been successfully un-submitted and marked as Draft.";
        } else {
            // Check if the outcome record actually exists to provide a more accurate message
            $check_sql = "SELECT outcome_id FROM sector_outcomes WHERE outcome_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            if ($check_stmt) {
                $check_stmt->bind_param('i', $outcome_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                if ($check_result->num_rows === 0) {
                    $_SESSION['error_message'] = "No outcome found with ID {$outcome_id}. Nothing to un-submit.";
                } else {
                    // Outcome exists, but was already 'Draft' or no change was made
                    $_SESSION['info_message'] = "Outcome (ID: {$outcome_id}) was already in a draft state or no changes were necessary.";
                }
                $check_stmt->close();
            } else {
                 $_SESSION['error_message'] = "Could not verify outcome status. Please check manually.";
            }
        }
    } else {
        $_SESSION['error_message'] = "Failed to un-submit outcome (ID: {$outcome_id}). Database error: " . $stmt->error;
    }
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Failed to prepare the database statement for un-submitting outcome. Error: " . $conn->error;
}

// Redirect back to the outcomes management page
header('Location: ../metrics/manage_metrics.php'); // This page now manages outcomes
exit;

?>
