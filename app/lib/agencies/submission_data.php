<?php
/**
 * Submission Data Helper
 * Handles data preparation for submission views
 */

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/programs.php';
require_once __DIR__ . '/program_attachments.php';
require_once __DIR__ . '/program_permissions.php';
require_once __DIR__ . '/../rating_helpers.php';

/**
 * Get complete submission data for viewing
 * 
 * @param int $program_id Program ID
 * @param int $period_id Period ID
 * @return array|null Complete submission data or null if not found/no access
 */
function get_submission_view_data($program_id, $period_id) {
    global $conn;
    
    // Get program details
    $program = get_program_details($program_id, true);
    if (!$program) {
        return null;
    }
    
    // Check user permissions
    $can_edit = can_edit_program($program_id);
    $can_view = can_view_program($program_id);
    
    if (!$can_view) {
        return null;
    }
    
    // Get reporting period details
    $period_query = "SELECT * FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($period_query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $period = $stmt->get_result()->fetch_assoc();
    
    if (!$period) {
        return null;
    }
    
    // Get submission details
    $submission_query = "SELECT ps.*, rp.year, rp.period_type, rp.period_number, rp.start_date, rp.end_date, rp.status as period_status,
                                CASE 
                                   WHEN rp.period_type = 'quarter' THEN CONCAT('Q', rp.period_number, ' ', rp.year)
                                   WHEN rp.period_type = 'half' THEN CONCAT('H', rp.period_number, ' ', rp.year)
                                   WHEN rp.period_type = 'yearly' THEN CONCAT('Y', rp.period_number, ' ', rp.year)
                                   ELSE CONCAT(rp.period_type, ' ', rp.period_number, ' ', rp.year)
                               END as period_name,
                                CONCAT(
                                   CASE 
                                       WHEN rp.period_type = 'quarter' THEN CONCAT('Q', rp.period_number, ' ', rp.year)
                                       WHEN rp.period_type = 'half' THEN CONCAT('H', rp.period_number, ' ', rp.year)
                                       WHEN rp.period_type = 'yearly' THEN CONCAT('Y', rp.period_number, ' ', rp.year)
                                       ELSE CONCAT(rp.period_type, ' ', rp.period_number, ' ', rp.year)
                                   END,
                                   ' (', DATE_FORMAT(rp.start_date, '%b %Y'), ' - ', 
                                   DATE_FORMAT(rp.end_date, '%b %Y'), ')'
                                ) as period_display
                         FROM program_submissions ps
                         JOIN reporting_periods rp ON ps.period_id = rp.period_id
                         WHERE ps.program_id = ? AND ps.period_id = ? AND ps.is_deleted = 0";
    
    $stmt = $conn->prepare($submission_query);
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();
    $submission = $stmt->get_result()->fetch_assoc();
    
    if (!$submission) {
        return null;
    }
    
    // Get targets for this submission
    $targets = get_submission_targets($submission['submission_id']);
    
    // Get attachments for this submission
    $attachments = get_submission_attachments($program_id, $submission['submission_id']);
    
    // Get program rating information
    $program_rating = $program['rating'] ?? 'not_started';
    $rating_info = get_rating_info($program_rating);
    
    return [
        'program' => $program,
        'period' => $period,
        'submission' => $submission,
        'targets' => $targets,
        'attachments' => $attachments,
        'rating_info' => $rating_info,
        'permissions' => [
            'can_edit' => $can_edit,
            'can_view' => $can_view
        ]
    ];
}

/**
 * Get targets for a submission
 * 
 * @param int $submission_id Submission ID
 * @return array Array of targets
 */
function get_submission_targets($submission_id) {
    global $conn;
    
    $targets_query = "SELECT * FROM program_targets 
                      WHERE submission_id = ? AND is_deleted = 0 
                      ORDER BY target_number ASC, target_id ASC";
    
    $stmt = $conn->prepare($targets_query);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get attachments for a submission
 * 
 * @param int $program_id Program ID
 * @param int $submission_id Submission ID
 * @return array Array of attachments
 */
function get_submission_attachments($program_id, $submission_id) {
    global $conn;
    
    // Note: program_attachments table only has submission_id, not program_id
    // The program_id parameter is kept for API compatibility but not used in query
    $attachments_query = "SELECT pa.*, u.username as uploaded_by_name
                          FROM program_attachments pa
                          LEFT JOIN users u ON pa.uploaded_by = u.user_id
                          WHERE pa.submission_id = ? AND pa.is_deleted = 0 
                          ORDER BY pa.uploaded_at DESC";
    
    $stmt = $conn->prepare($attachments_query);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $attachments = [];
    while ($row = $result->fetch_assoc()) {
        $attachments[] = [
            'attachment_id' => $row['attachment_id'],
            'filename' => $row['file_name'], // Provide both for compatibility
            'file_name' => $row['file_name'],
            'original_filename' => $row['file_name'],
            'file_path' => $row['file_path'],
            'file_size' => $row['file_size'],
            'file_type' => $row['file_type'],
            'uploaded_at' => $row['uploaded_at'],
            'upload_date' => $row['uploaded_at'],
            'uploaded_by' => $row['uploaded_by_name'],
            'file_size_formatted' => format_file_size($row['file_size'] ?? 0),
            'submission_id' => $row['submission_id']
        ];
    }
    
    return $attachments;
}


?>