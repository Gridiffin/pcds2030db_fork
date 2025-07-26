<?php
/**
 * Simple Get Periods - New Working Implementation
 * Returns available draft submissions for a specific program
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
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('i', $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $periods = [];
    while ($row = $result->fetch_assoc()) {
        // Construct period name from components
        $period_name = ucfirst($row['period_type']) . ' ' . $row['period_number'] . ', ' . $row['year'];
        
        $periods[] = [
            'period_id' => (int)$row['period_id'],
            'submission_id' => (int)$row['submission_id'],
            'period_name' => $period_name,
            'last_updated' => $row['updated_at'] ? date('M j, Y g:i A', strtotime($row['updated_at'])) : 'Never',
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'periods' => $periods,
        'count' => count($periods)
    ]);
    
} catch (Exception $e) {
    error_log("Error in simple_get_periods.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while loading periods',
        'error' => $e->getMessage()
    ]);
}
?>