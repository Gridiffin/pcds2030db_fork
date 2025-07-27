<?php
/**
 * Admin Edit Submission Data Helper
 * 
 * Functions to retrieve edit submission data for admin views
 */

/**
 * Get comprehensive edit submission data for admin view
 * 
 * @param int $program_id Program ID
 * @param int $period_id Period ID
 * @return array|null Edit submission data array or null if not found
 */
function get_admin_edit_submission_data($program_id, $period_id) {
    global $conn;
    
    if (!$program_id || !$period_id) return null;
    
    // Get program basic information with agency details
    $program_query = "SELECT p.*, 
                             i.initiative_name, 
                             i.initiative_number,
                             a.agency_name,
                             a.agency_id
                      FROM programs p 
                      LEFT JOIN initiatives i ON p.initiative_id = i.initiative_id 
                      LEFT JOIN agency a ON p.agency_id = a.agency_id
                      WHERE p.program_id = ? AND p.is_deleted = 0";
    
    $stmt = $conn->prepare($program_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $program = $result->fetch_assoc();
    
    if (!$program) return null;
    
    // Get agency information
    $agency_info = [
        'agency_id' => $program['agency_id'],
        'agency_name' => $program['agency_name'],
        'agency_acronym' => null // Not available in current schema
    ];
    
    // Get period information
    $period_query = "SELECT period_id, period_type, period_number, year, status
                     FROM reporting_periods 
                     WHERE period_id = ?";
    
    $stmt = $conn->prepare($period_query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $period = $result->fetch_assoc();
    
    if (!$period) return null;
    
    // Add period display
    $period['period_display'] = get_period_display_name($period);
    
    // Get existing submission for this program and period (finalized only for admin editing)
    $submission = null;
    $is_new_submission = false;
    
    $submission_query = "SELECT ps.*, 
                                u.fullname as submitted_by_name
                         FROM program_submissions ps 
                         LEFT JOIN users u ON ps.submitted_by = u.user_id
                         WHERE ps.program_id = ? 
                         AND ps.period_id = ?
                         AND ps.is_deleted = 0 
                         AND ps.is_draft = 0
                         ORDER BY ps.submission_id DESC 
                         LIMIT 1";
    
    $stmt = $conn->prepare($submission_query);
    $stmt->bind_param("ii", $program_id, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $submission = $result->fetch_assoc();
    
    // If no submission found, this will be a new submission
    if (!$submission) {
        $is_new_submission = true;
        $submission = [
            'submission_id' => null,
            'program_id' => $program_id,
            'period_id' => $period_id,
            'description' => '',
            'is_draft' => 0, // Admin creates finalized submissions
            'is_submitted' => 1,
            'submitted_at' => null,
            'submitted_by' => null
        ];
    }
    
    // Get targets for this submission
    $targets = [];
    if (!$is_new_submission && $submission['submission_id']) {
        $targets_query = "SELECT target_id, target_number, target_description, 
                                 status_indicator, status_description, remarks, 
                                 start_date, end_date 
                          FROM program_targets 
                          WHERE submission_id = ? AND is_deleted = 0 
                          ORDER BY target_id";
        
        $stmt = $conn->prepare($targets_query);
        $stmt->bind_param("i", $submission['submission_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($target = $result->fetch_assoc()) {
            $targets[] = $target;
        }
    }
    
    // Get program attachments (through submissions)
    $attachments = [];
    $attachments_query = "SELECT pa.*, u.fullname as uploaded_by_name
                          FROM program_attachments pa 
                          LEFT JOIN users u ON pa.uploaded_by = u.user_id
                          LEFT JOIN program_submissions ps ON pa.submission_id = ps.submission_id
                          WHERE ps.program_id = ? AND pa.is_deleted = 0 AND ps.is_deleted = 0
                          ORDER BY pa.uploaded_at DESC";
    
    $stmt = $conn->prepare($attachments_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($attachment = $result->fetch_assoc()) {
        // Format file size
        $attachment['file_size_formatted'] = format_file_size($attachment['file_size'] ?? 0);
        $attachments[] = $attachment;
    }
    
    return [
        'program' => $program,
        'agency_info' => $agency_info,
        'submission' => $submission,
        'period' => $period,
        'targets' => $targets,
        'attachments' => $attachments,
        'is_new_submission' => $is_new_submission,
        'permissions' => [
            'can_edit' => true,    // Admin can edit all
            'can_view' => true     // Admin can view all
        ]
    ];
}

// Note: format_file_size() function is available from functions.php