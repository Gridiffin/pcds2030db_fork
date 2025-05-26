<?php
/**
 * Resubmit Program Submission
 * 
 * Sets the is_draft flag to 0 for a specific program submission within a reporting period,
 * updates its status to 'submitted', and records the current timestamp as submission_date.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/models/Program.php';
require_once ROOT_PATH . 'app/models/AuditLog.php';

// Verify user is an admin
if (!is_admin()) {
    // Set a message for the user
    $_SESSION['error_message'] = "Access denied. You must be an administrator to perform this action.";
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Check if program_id and period_id are provided and valid
if (!isset($_GET['program_id']) || !is_numeric($_GET['program_id']) || 
    !isset($_GET['period_id']) || !is_numeric($_GET['period_id'])) {
    
    $_SESSION['error_message'] = "Invalid request. Program ID or Period ID is missing or invalid.";
    // Redirect back to programs list, try to maintain original period if possible
    $redirect_url = 'programs.php';
    if (isset($_GET['period_id']) && is_numeric($_GET['period_id'])) {
        $redirect_url .= '?period_id=' . intval($_GET['period_id']);
    } elseif (isset($_SESSION['last_viewed_period_id'])) { // Fallback to a session stored period
        $redirect_url .= '?period_id=' . $_SESSION['last_viewed_period_id'];
    }
    header('Location: ' . $redirect_url);
    exit;
}

$program_id = intval($_GET['program_id']);
$period_id = intval($_GET['period_id']);

// Prepare and execute update query to set is_draft = 0, status = 'submitted', and submission_date = NOW()
$sql = "UPDATE program_submissions 
        SET is_draft = 0, status = 'submitted', submission_date = NOW(), updated_at = NOW() 
        WHERE program_id = ? AND period_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('ii', $program_id, $period_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Program has been successfully re-submitted.";
        } else {
            // Check if a submission record actually exists for this program_id and period_id
            $check_sql = "SELECT submission_id, is_draft, status FROM program_submissions WHERE program_id = ? AND period_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param('ii', $program_id, $period_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($check_result->num_rows === 0) {
                $_SESSION['error_message'] = "No submission found for this program in the specified period. Nothing to resubmit.";
            } else {
                $submission_data = $check_result->fetch_assoc();
                if ($submission_data['is_draft'] == 0 && $submission_data['status'] == 'submitted') {
                     $_SESSION['info_message'] = "Program submission was already in the re-submitted state.";
                } else {
                    $_SESSION['error_message'] = "Failed to resubmit the program. No rows were updated, though a record exists. Current state: is_draft=" . $submission_data['is_draft'] . ", status=" . $submission_data['status'];
                }
            }
            $check_stmt->close();
        }
    } else {
        $_SESSION['error_message'] = "Failed to resubmit the program. Database error: " . $stmt->error;
    }
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Failed to prepare the database statement for resubmitting.";
}

// Construct redirect URL
$redirect_url = 'programs.php?period_id=' . $period_id;

header('Location: ' . $redirect_url);
exit;
?>
