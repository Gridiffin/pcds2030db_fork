<?php
/**
 * Submit Draft Outcome
 *
 * Handles submission of a draft outcome (agency user).
 */

// Fix require_once paths for config and libraries
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__, 3) . '/lib/db_connect.php';
require_once dirname(__DIR__, 3) . '/lib/session.php';
require_once dirname(__DIR__, 3) . '/lib/functions.php';
require_once dirname(__DIR__, 3) . '/lib/agencies/index.php';
require_once dirname(__DIR__, 3) . '/lib/audit_log.php';

if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$sector_id = $_SESSION['sector_id'] ?? 0;

if (!isset($_GET['outcome_id']) || !is_numeric($_GET['outcome_id'])) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: submit_outcomes.php');
    exit;
}

$outcome_id = (int) $_GET['outcome_id'];

// Get outcome details for logging
$outcome_query = "SELECT table_name FROM sector_outcomes_data WHERE metric_id = ? AND sector_id = ? AND is_draft = 1";
$outcome_stmt = $conn->prepare($outcome_query);
$outcome_stmt->bind_param("ii", $outcome_id, $sector_id);
$outcome_stmt->execute();
$outcome_result = $outcome_stmt->get_result();
$outcome_data = $outcome_result->fetch_assoc();

// Update the draft outcome to submitted
$query = "UPDATE sector_outcomes_data SET is_draft = 0 WHERE metric_id = ? AND sector_id = ? AND is_draft = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $outcome_id, $sector_id);
if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Draft outcome submitted successfully.';
    
    // Log successful outcome submission
    log_audit_action(
        'outcome_submitted',
        "Outcome '" . ($outcome_data['table_name'] ?? 'Unknown') . "' (Metric ID: {$outcome_id}) submitted for sector {$sector_id}",
        'success',
        $_SESSION['user_id']
    );
} else {
    $_SESSION['error_message'] = 'Failed to submit draft outcome.';
    
    // Log outcome submission failure
    log_audit_action(
        'outcome_submission_failed',
        "Failed to submit outcome (Metric ID: {$outcome_id}) for sector {$sector_id}: " . $conn->error,
        'failure',
        $_SESSION['user_id']
    );
}

header('Location: submit_outcomes.php');
exit;
?>
