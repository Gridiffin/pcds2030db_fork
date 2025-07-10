<?php
/**
 * Get Submission by Period
 * 
 * AJAX endpoint to fetch submission data for a specific program and period.
 * Returns submission data if exists, or empty response if no submission found.
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

// Set JSON header
header('Content-Type: application/json');

// Verify user is an agency
if (!is_agency()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Agency login required.']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

// Get and validate input parameters
$program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
$period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : 0;

if (!$program_id || !$period_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Program ID and Period ID are required.']);
    exit;
}

try {
    // Verify program exists and user has access
    $program = get_program_details($program_id);
    if (!$program) {
        http_response_code(404);
        echo json_encode(['error' => 'Program not found or access denied.']);
        exit;
    }

    // Check if submission exists for this program and period
    $submission_query = "SELECT ps.*, rp.year, rp.period_type, rp.period_number, rp.status as period_status
                        FROM program_submissions ps
                        JOIN reporting_periods rp ON ps.period_id = rp.period_id
                        WHERE ps.program_id = ? AND ps.period_id = ? AND ps.is_deleted = 0
                        ORDER BY ps.updated_at DESC
                        LIMIT 1";
    
    $stmt = $conn->prepare($submission_query);
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $submission = $result->fetch_assoc();

    if (!$submission) {
        // No submission found for this period
        echo json_encode([
            'success' => true,
            'has_submission' => false,
            'message' => 'No submission found for this period.',
            'period_info' => [
                'period_id' => $period_id,
                'program_id' => $program_id
            ]
        ]);
        exit;
    }

    // Get targets from program_targets table
    $targets_query = "SELECT pt.* 
                     FROM program_targets pt
                     WHERE pt.submission_id = ? AND pt.is_deleted = 0
                     ORDER BY pt.target_id ASC";
    
    $stmt = $conn->prepare($targets_query);
    $stmt->bind_param("i", $submission['submission_id']);
    $stmt->execute();
    $targets_result = $stmt->get_result();
    $targets = $targets_result->fetch_all(MYSQLI_ASSOC);
    
    // Format targets for frontend
    $formatted_targets = [];
    foreach ($targets as $target) {
        $formatted_targets[] = [
            'target_number' => $target['target_number'],
            'target_text' => $target['target_description'],
            'target_status' => $target['status_indicator'],
            'status_description' => $target['status_description'],
            'start_date' => $target['start_date'],
            'end_date' => $target['end_date'],
            'remarks' => $target['remarks']
        ];
    }

    // Get submission attachments
    $attachments_query = "SELECT pa.*, u.fullname as uploaded_by_name
                         FROM program_attachments pa
                         LEFT JOIN users u ON pa.uploaded_by = u.user_id
                         WHERE pa.submission_id = ? AND pa.is_deleted = 0
                         ORDER BY pa.uploaded_at DESC";
    
    $stmt = $conn->prepare($attachments_query);
    $stmt->bind_param("i", $submission['submission_id']);
    $stmt->execute();
    $attachments_result = $stmt->get_result();
    $attachments = $attachments_result->fetch_all(MYSQLI_ASSOC);

    // Format file sizes
    foreach ($attachments as &$attachment) {
        $attachment['file_size_formatted'] = formatFileSize($attachment['file_size']);
    }

    // Prepare response
    $response = [
        'success' => true,
        'has_submission' => true,
        'submission' => [
            'submission_id' => $submission['submission_id'],
            'program_id' => $submission['program_id'],
            'period_id' => $submission['period_id'],
            'is_draft' => (bool)$submission['is_draft'],
            'is_submitted' => (bool)$submission['is_submitted'],
            'description' => $submission['description'] ?? '',
            'targets' => $formatted_targets,
            'rating' => 'not-started', // Rating is not stored in current schema
            'remarks' => '', // Remarks are stored per target in current schema
            'updated_at' => $submission['updated_at'],
            'submitted_at' => $submission['submitted_at']
        ],
        'period_info' => [
            'period_id' => $submission['period_id'],
            'year' => $submission['year'],
            'period_type' => $submission['period_type'],
            'period_number' => $submission['period_number'],
            'status' => $submission['period_status'],
            'display_name' => $submission['year'] . ' ' . ucfirst($submission['period_type']) . ' ' . $submission['period_number']
        ],
        'attachments' => $attachments
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_submission_by_period.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while fetching submission data.']);
    exit;
}

/**
 * Format file size in human readable format
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?> 