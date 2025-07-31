<?php
/**
 * Finalize Submission
 * Changes a draft submission to finalized status
 */

// Start session FIRST before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/notifications_core.php';

// Ensure user is authenticated and is a focal user
if (!is_focal_user()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Only focal users can finalize submissions.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$submission_id = (int)($input['submission_id'] ?? 0);
$program_id = (int)($input['program_id'] ?? 0);
$period_id = (int)($input['period_id'] ?? 0);

if (!$submission_id || !$program_id || !$period_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Start transaction
    $conn->autocommit(FALSE);
    
    // Verify the submission belongs to the user's agency and is a draft
    $verify_query = "
        SELECT s.submission_id, s.is_draft
        FROM program_submissions s
        JOIN programs p ON s.program_id = p.program_id
        WHERE s.submission_id = ? 
        AND s.program_id = ? 
        AND s.period_id = ?
        AND p.agency_id = ?
        AND s.is_draft = 1
        AND s.is_deleted = 0
    ";
    
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param('iiii', $submission_id, $program_id, $period_id, $_SESSION['user_id']);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if (!$verify_result->fetch_assoc()) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Submission not found or already finalized']);
        exit;
    }
    
    // Update submission to finalized
    $finalize_query = "
        UPDATE program_submissions 
        SET 
            is_draft = 0,
            submitted_at = NOW(),
            submitted_by = ?,
            updated_at = NOW()
        WHERE submission_id = ?
    ";
    
    $finalize_stmt = $conn->prepare($finalize_query);
    $finalize_stmt->bind_param('ii', $_SESSION['user_id'], $submission_id);
    $finalize_stmt->execute();
    
    // Log the finalization action
    $log_query = "
        INSERT INTO audit_log (
            user_id, 
            action, 
            table_name, 
            record_id, 
            changes, 
            created_at
        ) VALUES (
            ?,
            'finalize_submission',
            'program_submissions',
            ?,
            ?,
            NOW()
        )
    ";
    
    $changes = json_encode([
        'action' => 'finalized_submission',
        'submission_id' => $submission_id,
        'program_id' => $program_id,
        'period_id' => $period_id
    ]);
    
    $log_stmt = $conn->prepare($log_query);
    $log_stmt->bind_param('iis', $_SESSION['user_id'], $submission_id, $changes);
    $log_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Send finalization notifications
    notify_submission_finalized($submission_id, $program_id, $_SESSION['user_id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Submission finalized successfully',
        'submission_id' => $submission_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Error in finalize_submission.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while finalizing the submission'
    ]);
}
?>