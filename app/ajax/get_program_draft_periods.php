<?php
/**
 * Get Draft Reporting Periods for a Program
 * Returns available draft submissions for a specific program
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

$program_id = (int)($_GET['program_id'] ?? 0);

if (!$program_id) {
    echo json_encode(['success' => false, 'message' => 'Program ID is required']);
    exit;
}

try {
    // Get draft submissions for this program
    $query = "
        SELECT 
            s.submission_id,
            s.period_id,
            s.updated_at,
            rp.year,
            rp.period_type,
            rp.period_number,
            rp.start_date,
            rp.end_date
        FROM program_submissions s
        JOIN reporting_periods rp ON s.period_id = rp.period_id
        WHERE s.program_id = ? 
        AND s.is_draft = 1
        AND s.is_deleted = 0
        ORDER BY rp.start_date DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $periods = [];
    while ($row = $result->fetch_assoc()) {
        // Construct period name from components
        $period_name = ucfirst($row['period_type']) . ' ' . $row['period_number'] . ', ' . $row['year'];
        
        $periods[] = [
            'period_id' => $row['period_id'],
            'submission_id' => $row['submission_id'],
            'period_name' => $period_name,
            'last_updated' => $row['updated_at'] ? date('M j, Y g:i A', strtotime($row['updated_at'])) : 'Never',
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'periods' => $periods
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_program_draft_periods.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while loading periods'
    ]);
}
?>