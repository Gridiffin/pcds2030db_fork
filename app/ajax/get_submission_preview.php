<?php
/**
 * Get Submission Preview
 * Returns submission details for review before finalization
 */

require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';

// Ensure user is authenticated and is a focal user
if (!is_agency()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
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
            s.created_at,
            s.updated_at,
            s.is_draft,
            p.program_name,
            p.program_number,
            rp.year,
            rp.period_type,
            rp.period_number,
            rp.start_date,
            rp.end_date,
            u.first_name,
            u.last_name
        FROM program_submissions s
        JOIN programs p ON s.program_id = p.program_id
        JOIN reporting_periods rp ON s.period_id = rp.period_id
        LEFT JOIN users u ON s.submitted_by = u.user_id
        WHERE s.submission_id = ? 
        AND s.is_draft = 1
        AND s.is_deleted = 0
        AND p.agency_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $submission_id, $_SESSION['user_id']);
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
        'submission_id' => $submission['submission_id'],
        'program_id' => $submission['program_id'],
        'period_id' => $submission['period_id'],
        'program_name' => $submission['program_name'],
        'program_number' => $submission['program_number'],
        'period_name' => $period_name,
        'summary' => $submission['summary'],
        'is_draft' => (bool)$submission['is_draft'],
        'created_at' => $submission['created_at'] ? date('M j, Y g:i A', strtotime($submission['created_at'])) : null,
        'updated_at' => $submission['updated_at'] ? date('M j, Y g:i A', strtotime($submission['updated_at'])) : null,
        'submitted_by' => trim($submission['first_name'] . ' ' . $submission['last_name'])
    ];
    
    // Get submission metrics/outcomes (if any)
    $metrics_query = "
        SELECT 
            po.outcome_id,
            po.outcome_name,
            sm.target_value,
            sm.actual_value,
            sm.notes
        FROM submission_metrics sm
        JOIN program_outcomes po ON sm.outcome_id = po.outcome_id
        WHERE sm.submission_id = ?
        ORDER BY po.outcome_name
    ";
    
    $metrics_stmt = $conn->prepare($metrics_query);
    $metrics_stmt->bind_param('i', $submission_id);
    $metrics_stmt->execute();
    $metrics_result = $metrics_stmt->get_result();
    
    $metrics = [];
    while ($metric = $metrics_result->fetch_assoc()) {
        $metrics[] = [
            'outcome_id' => $metric['outcome_id'],
            'outcome_name' => $metric['outcome_name'],
            'target_value' => $metric['target_value'],
            'actual_value' => $metric['actual_value'],
            'notes' => $metric['notes']
        ];
    }
    
    $formatted_submission['metrics'] = $metrics;
    
    echo json_encode([
        'success' => true,
        'submission' => $formatted_submission
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_submission_preview.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while loading submission details'
    ]);
}
?>