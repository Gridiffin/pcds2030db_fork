<?php
/**
 * AJAX endpoint to unsubmit a finalized submission (set back to draft)
 * Only focal users can perform this action.
 */
require_once dirname(__DIR__, 4) . '/lib/db_connect.php';
require_once dirname(__DIR__, 4) . '/lib/session.php';
require_once dirname(__DIR__, 4) . '/lib/functions.php';
require_once dirname(__DIR__, 4) . '/lib/audit_log.php';

header('Content-Type: application/json');

if (!is_focal_user()) {
    echo json_encode(['success' => false, 'error' => 'Permission denied.']);
    exit;
}

$submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
if ($submission_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid submission ID.']);
    exit;
}

// Get current submission data
$stmt = $conn->prepare('SELECT * FROM program_submissions WHERE submission_id = ? AND is_deleted = 0');
$stmt->bind_param('i', $submission_id);
$stmt->execute();
$result = $stmt->get_result();
$submission = $result->fetch_assoc();
if (!$submission) {
    echo json_encode(['success' => false, 'error' => 'Submission not found.']);
    exit;
}

// Only allow unsubmit if currently finalized
if (!$submission['is_submitted'] || $submission['is_draft']) {
    echo json_encode(['success' => false, 'error' => 'Submission is not finalized.']);
    exit;
}

// Update submission: set is_draft=1, is_submitted=0
$stmt = $conn->prepare('UPDATE program_submissions SET is_draft = 1, is_submitted = 0 WHERE submission_id = ?');
$stmt->bind_param('i', $submission_id);
$stmt->execute();

// Audit log
log_audit('unsubmit_submission', ['submission_id' => $submission_id, 'program_id' => $submission['program_id']], $submission['program_id']);

// Optionally: log field changes
log_field_changes($submission, array_merge($submission, ['is_draft' => 1, 'is_submitted' => 0]), null);

echo json_encode(['success' => true]);
