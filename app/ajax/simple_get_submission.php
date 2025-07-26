<?php
/**
 * Simple Get Submission - New Working Implementation
 * Returns submission details for review before finalization
 */

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/agencies/core.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$submission_id = (int)($_GET['submission_id'] ?? 0);

if (!$submission_id) {
    echo json_encode(['success' => false, 'message' => 'Submission ID is required']);
    exit;
}

try {
    // Get submission details with program and period information
    $query = "
        SELECT 
            s.submission_id,
            s.program_id,
            s.period_id,
            s.description as summary,
            s.updated_at,
            s.is_draft,
            p.program_name,
            p.program_number,
            rp.year,
            rp.period_type,
            rp.period_number,
            rp.start_date,
            rp.end_date,
            COALESCE(u.fullname, '') as fullname
        FROM program_submissions s
        JOIN programs p ON s.program_id = p.program_id
        JOIN reporting_periods rp ON s.period_id = rp.period_id
        LEFT JOIN users u ON s.submitted_by = u.user_id
        WHERE s.submission_id = ? 
        AND s.is_draft = 1
        AND s.is_deleted = 0
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('i', $submission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submission = $result->fetch_assoc();
    
    if (!$submission) {
        echo json_encode(['success' => false, 'message' => 'Submission not found or access denied']);
        exit;
    }
    
    // Construct period name from components
    $period_name = ucfirst($submission['period_type']) . ' ' . $submission['period_number'] . ', ' . $submission['year'];
    
    // Format the submission data
    $formatted_submission = [
        'submission_id' => (int)$submission['submission_id'],
        'program_id' => (int)$submission['program_id'],
        'period_id' => (int)$submission['period_id'],
        'program_name' => $submission['program_name'],
        'program_number' => $submission['program_number'],
        'period_name' => $period_name,
        'summary' => $submission['summary'],
        'is_draft' => (bool)$submission['is_draft'],
        'updated_at' => $submission['updated_at'] ? date('M j, Y g:i A', strtotime($submission['updated_at'])) : null,
        'submitted_by' => $submission['fullname'] ?: 'Unknown'
    ];
    
    echo json_encode([
        'success' => true,
        'submission' => $formatted_submission
    ]);
    
} catch (Exception $e) {
    error_log("Error in simple_get_submission.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while loading submission details',
        'error' => $e->getMessage()
    ]);
}
?>