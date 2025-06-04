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

// Update the draft outcome to submitted
$query = "UPDATE sector_outcomes_data SET is_draft = 0 WHERE id = ? AND sector_id = ? AND is_draft = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $outcome_id, $sector_id);
if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Draft outcome submitted successfully.';
} else {
    $_SESSION['error_message'] = 'Failed to submit draft outcome.';
}

header('Location: submit_outcomes.php');
exit;
?>
