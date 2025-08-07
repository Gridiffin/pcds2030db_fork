<?php
// Start session FIRST before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Simple Finalize - New Working Implementation
 * Changes a draft submission to finalized status
 */

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/agencies/core.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in and is a focal user
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if (!is_focal_user()) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Only focal users can finalize submissions.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$submission_id = (int)($input['submission_id'] ?? 0);
$program_id = (int)($input['program_id'] ?? 0);
$period_id = (int)($input['period_id'] ?? 0);
$program_rating = isset($input['program_rating']) ? trim($input['program_rating']) : '';

if (!$submission_id || !$program_id || !$period_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate program rating if provided
$valid_ratings = ['monthly_target_achieved', 'on_track_for_year', 'severe_delay', 'not_started'];
if ($program_rating && !in_array($program_rating, $valid_ratings)) {
    echo json_encode(['success' => false, 'message' => 'Invalid program rating provided']);
    exit;
}

try {
    // Start transaction
    $conn->autocommit(FALSE);
    
    // Verify the submission belongs to the user and is a draft
    $verify_query = "
        SELECT s.submission_id, s.is_draft
        FROM program_submissions s
        JOIN programs p ON s.program_id = p.program_id
        WHERE s.submission_id = ? 
        AND s.program_id = ? 
        AND s.period_id = ?
        AND s.is_draft = 1
        AND s.is_deleted = 0
    ";
    
    $verify_stmt = $conn->prepare($verify_query);
    if (!$verify_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $verify_stmt->bind_param('iii', $submission_id, $program_id, $period_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    $submission_data = $verify_result->fetch_assoc();
    if (!$submission_data) {
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'Submission not found, already finalized, or access denied',
            'debug' => [
                'submission_id' => $submission_id,
                'program_id' => $program_id,
                'period_id' => $period_id,
                'user_id' => $_SESSION['user_id'] ?? 'not_set'
            ]
        ]);
        exit;
    }
    
    // Update program rating if provided
    if ($program_rating) {
        $rating_query = "
            UPDATE programs 
            SET 
                rating = ?,
                updated_at = NOW()
            WHERE program_id = ?
        ";
        
        $rating_stmt = $conn->prepare($rating_query);
        if (!$rating_stmt) {
            throw new Exception("Rating update prepare failed: " . $conn->error);
        }
        
        $rating_stmt->bind_param('si', $program_rating, $program_id);
        $rating_stmt->execute();
        
        if ($rating_stmt->affected_rows === 0) {
            // Program might not exist or rating is the same
            error_log("Warning: Program rating update affected 0 rows for program_id: $program_id");
        }
    }
    
    // Update submission to finalized
    $finalize_query = "
        UPDATE program_submissions 
        SET 
            is_draft = 0,
            is_submitted = 1,
            submitted_at = NOW(),
            submitted_by = ?,
            updated_at = NOW()
        WHERE submission_id = ?
    ";
    
    $finalize_stmt = $conn->prepare($finalize_query);
    if (!$finalize_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $finalize_stmt->bind_param('ii', $_SESSION['user_id'], $submission_id);
    $finalize_stmt->execute();
    
    if ($finalize_stmt->affected_rows === 0) {
        throw new Exception("No rows updated - submission may not exist");
    }
    
    // Log the finalization action (optional - only if audit_log table exists)
    try {
        $log_query = "
            INSERT INTO audit_logs (
                user_id, 
                action, 
                details, 
                ip_address,
                status,
                created_at
            ) VALUES (
                ?,
                'finalize_submission',
                ?,
                ?,
                'success',
                NOW()
            )
        ";
        
        $details = "Finalized submission ID: $submission_id for program ID: $program_id, period ID: $period_id";
        if ($program_rating) {
            $details .= " and updated program rating to: $program_rating";
        }
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $log_stmt = $conn->prepare($log_query);
        if ($log_stmt) {
            $log_stmt->bind_param('iss', $_SESSION['user_id'], $details, $ip_address);
            $log_stmt->execute();
        }
    } catch (Exception $audit_error) {
        // Audit logging failed, but don't fail the main operation
        error_log("Audit log failed (non-critical): " . $audit_error->getMessage());
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Submission finalized successfully',
        'submission_id' => $submission_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Error in simple_finalize.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage(),
        'debug' => [
            'submission_id' => $submission_id,
            'program_id' => $program_id,
            'period_id' => $period_id,
            'user_id' => $_SESSION['user_id'] ?? 'not_set'
        ]
    ]);
} finally {
    // Restore autocommit
    $conn->autocommit(TRUE);
}
?>