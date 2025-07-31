<?php
/**
 * AJAX endpoint to unsubmit a finalized submission (set back to draft)
 * Only focal users can perform this action.
 */
require_once dirname(__DIR__) . '/lib/db_connect.php';
require_once dirname(__DIR__) . '/lib/session.php';
require_once dirname(__DIR__) . '/lib/functions.php';
require_once dirname(__DIR__) . '/lib/audit_log.php';

header('Content-Type: application/json');

// Debug logging
error_log("Unsubmit submission request received - User: " . ($_SESSION['username'] ?? 'unknown') . ", Submission ID: " . ($_POST['submission_id'] ?? 'none'));

if (!is_focal_user()) {
    error_log("Permission denied for unsubmit - User is not focal user");
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
    error_log("Submission not found - ID: $submission_id");
    echo json_encode(['success' => false, 'error' => 'Submission not found.']);
    exit;
}

error_log("Submission found - ID: $submission_id, is_submitted: " . $submission['is_submitted'] . ", is_draft: " . $submission['is_draft']);

// Only allow unsubmit if currently finalized
if (!$submission['is_submitted'] || $submission['is_draft']) {
    $status = [];
    if (!$submission['is_submitted']) $status[] = 'not submitted';
    if ($submission['is_draft']) $status[] = 'is draft';
    
    echo json_encode([
        'success' => false, 
        'error' => 'Submission is not finalized. Current status: ' . implode(', ', $status),
        'debug' => [
            'submission_id' => $submission_id,
            'is_submitted' => $submission['is_submitted'],
            'is_draft' => $submission['is_draft']
        ]
    ]);
    exit;
}

// Update submission: set is_draft=1, is_submitted=0
$stmt = $conn->prepare('UPDATE program_submissions SET is_draft = 1, is_submitted = 0 WHERE submission_id = ?');
$stmt->bind_param('i', $submission_id);
$result = $stmt->execute();

// Log the update result for debugging
error_log("Unsubmit update result - Submission ID: $submission_id, Rows affected: " . $stmt->affected_rows);

if (!$result) {
    error_log("Unsubmit update failed - Error: " . $stmt->error);
    echo json_encode(['success' => false, 'error' => 'Database update failed: ' . $stmt->error]);
    exit;
}

if ($stmt->affected_rows == 0) {
    error_log("Unsubmit update affected 0 rows - Submission ID: $submission_id");
    echo json_encode(['success' => false, 'error' => 'No rows were updated. Submission may have already been unsubmitted.']);
    exit;
}

// Audit log
$audit_log_id = log_audit('unsubmit_submission', ['submission_id' => $submission_id, 'program_id' => $submission['program_id']], $submission['program_id']);

// Log field changes if audit logging was successful
if ($audit_log_id && function_exists('log_field_changes')) {
    $old_data = $submission;
    $new_data = array_merge($submission, ['is_draft' => 1, 'is_submitted' => 0]);
    
    // Calculate field changes
    $field_changes = [];
    foreach (['is_draft', 'is_submitted'] as $field) {
        $old_value = $old_data[$field] ?? null;
        $new_value = $new_data[$field] ?? null;
        if ($old_value !== $new_value) {
            $field_changes[] = [
                'field_name' => $field,
                'field_type' => 'integer',
                'old_value' => $old_value,
                'new_value' => $new_value,
                'change_type' => 'modified'
            ];
        }
    }
    
    if (!empty($field_changes)) {
        log_field_changes($audit_log_id, $field_changes);
    }
}

echo json_encode(['success' => true]); 