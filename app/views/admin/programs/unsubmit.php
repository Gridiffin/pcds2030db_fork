<?php
/**
 * Unsubmit Program Submission
 * 
 * Sets the is_draft flag to 1 for a specific program submission within a reporting period.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/admins/statistics.php'; // Contains program functions
require_once ROOT_PATH . 'app/lib/audit_log.php';

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

// Use the model function instead of direct SQL
$success = unsubmit_program($program_id, $period_id);

// Get program information for logging
$program_info = get_program_submission($program_id, $period_id);
$program_name = $program_info['program_name'] ?? 'Unknown Program';

if ($success) {
    $_SESSION['success_message'] = "Program submission has been successfully un-submitted and marked as draft.";
    
    // Log successful program unsubmit
    log_audit_action('admin_unsubmit_program', "Program: $program_name | Program ID: $program_id | Period ID: $period_id", 'success', $_SESSION['user_id']);
} else {
    // Check if a submission record actually exists for this program_id and period_id
    $submission = get_program_submission($program_id, $period_id);
    
    if (!$submission) {
        $_SESSION['error_message'] = "No submission found for this program in the specified period. Nothing to un-submit.";
        
        // Log failed program unsubmit
        log_audit_action('admin_unsubmit_program_failed', "Program: $program_name | Program ID: $program_id | Period ID: $period_id | Error: No submission found", 'failure', $_SESSION['user_id']);
    } else {
        // Record existed, but maybe no change was needed (e.g., already draft and not-started)
        $_SESSION['info_message'] = "Program submission was already in the desired state or no changes were made.";
    }
}

// Log the action
if (function_exists('log_action')) {
    log_action('unsubmit_program', "Program ID: $program_id, Period ID: $period_id", $success);
}

// Construct redirect URL to go back to the programs page, maintaining the period context
// And potentially other filters if they were passed or stored in session
$redirect_url = 'programs.php?period_id=' . $period_id;

// You could enhance this by re-adding other filters if your programs.php supports them in GET
// For example: if (isset($_GET['rating'])) $redirect_url .= '&rating='.urlencode($_GET['rating']);
// For simplicity, just redirecting with period_id for now.

header('Location: ' . $redirect_url);
exit;
?>


