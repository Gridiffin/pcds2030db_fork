<?php
/**
 * Delete Outcome (Agency)
 * 
 * Agency page to delete an outcome. Only allows deletion of outcomes belonging to the agency's sector.
 */

require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/agency_functions.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an agency user
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get outcome ID from URL
$outcome_id = isset($_GET['outcome_id']) ? intval($_GET['outcome_id']) : 0;
$sector_id = $_SESSION['sector_id'] ?? 0;

if ($outcome_id === 0 || $sector_id === 0) {
    $_SESSION['error_message'] = 'Invalid outcome ID or sector.';
    header('Location: submit_outcomes.php');
    exit;
}

// Check that the outcome belongs to this sector
$query = "SELECT * FROM sector_outcomes_data WHERE metric_id = ? AND sector_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $outcome_id, $sector_id);
$stmt->execute();
$result = $stmt->get_result();
$outcome = $result->fetch_assoc();

if (!$outcome) {
    $_SESSION['error_message'] = 'Outcome not found or does not belong to your sector.';
    header('Location: submit_outcomes.php');
    exit;
}

// Delete the outcome
$delete_query = "DELETE FROM sector_outcomes_data WHERE metric_id = ? AND sector_id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("ii", $outcome_id, $sector_id);
$delete_stmt->execute();

if ($delete_stmt->affected_rows > 0) {
    $_SESSION['success_message'] = 'Outcome deleted successfully.';
    // Log successful outcome deletion
    log_audit_action(
        'agency_outcome_deleted',
        "Agency deleted outcome (Metric ID: {$outcome_id}) for sector {$sector_id}",
        'success',
        $_SESSION['user_id']
    );
} else {
    $_SESSION['error_message'] = 'Failed to delete outcome.';
    // Log failure
    log_audit_action(
        'agency_outcome_deletion_failed',
        "Agency failed to delete outcome (Metric ID: {$outcome_id}) for sector {$sector_id}",
        'failure',
        $_SESSION['user_id']
    );
}

header('Location: submit_outcomes.php');
exit;
