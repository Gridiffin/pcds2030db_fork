<?php
/**
 * Get Program Submissions List (AJAX)
 * 
 * Returns draft submissions for a specific program to be used in submission selection modal.
 * Only returns submissions that are drafts (not finalized) so they can be submitted.
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/agencies/programs.php';
require_once '../lib/agencies/program_permissions.php';

// Set JSON header
header('Content-Type: application/json');

// Verify user is an agency
if (!is_agency()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Agency login required.']);
    exit;
}

// Get program ID from request
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

if (!$program_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Program ID is required.']);
    exit;
}

// Verify user has access to this program
if (!can_edit_program($program_id)) {
    http_response_code(403);
    echo json_encode(['error' => 'You do not have permission to access this program.']);
    exit;
}

try {
    // Get draft submissions for this program
    $submissions_query = "SELECT ps.submission_id, ps.period_id, ps.is_draft, ps.submitted_at, ps.description,
                                rp.year, rp.period_type, rp.period_number, rp.status as period_status,
                                CONCAT(rp.year, ' ', 
                                       CASE 
                                           WHEN rp.period_type = 'quarter' THEN CONCAT('Q', rp.period_number)
                                           WHEN rp.period_type = 'half' THEN CONCAT('H', rp.period_number)
                                           WHEN rp.period_type = 'yearly' THEN 'Yearly'
                                           ELSE CONCAT(UPPER(LEFT(rp.period_type, 1)), SUBSTRING(rp.period_type, 2), ' ', rp.period_number)
                                       END
                                ) as period_display
                         FROM program_submissions ps
                         JOIN reporting_periods rp ON ps.period_id = rp.period_id
                         WHERE ps.program_id = ? 
                         AND ps.is_draft = 1 
                         AND ps.is_deleted = 0
                         ORDER BY rp.year DESC, rp.period_number DESC";

    $stmt = $conn->prepare($submissions_query);
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        // Format the submission data
        $submission = [
            'submission_id' => $row['submission_id'],
            'period_id' => $row['period_id'],
            'period_display' => $row['period_display'],
            'period_status' => $row['period_status'],
            'is_draft' => (bool)$row['is_draft'],
            'submitted_at' => $row['submitted_at'] ? date('M j, Y g:i A', strtotime($row['submitted_at'])) : null,
            'description' => $row['description'] ?: 'No description',
            'year' => $row['year'],
            'period_type' => $row['period_type'],
            'period_number' => $row['period_number']
        ];
        
        $submissions[] = $submission;
    }
    
    $stmt->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'submissions' => $submissions,
        'count' => count($submissions)
    ]);

} catch (Exception $e) {
    error_log("Error in get_program_submissions_list.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while fetching submissions.',
        'message' => $e->getMessage()
    ]);
}
?> 