<?php
/**
 * Admin Submission Data Helper
 * 
 * Functions to retrieve submission data for admin views
 */

/**
 * Get comprehensive submission data for admin view
 * 
 * @param int $program_id Program ID
 * @param int|null $period_id Optional specific period ID
 * @return array|null Submission data array or null if not found
 */
function get_admin_submission_view_data($program_id, $period_id = null) {
    global $conn;
    
    if (!$program_id) return null;
    
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
    
    // Get all submissions for this program (admin sees only finalized)
    $all_submissions = [];
    $submissions_query = "SELECT ps.*, 
                                 rp.period_type, 
                                 rp.period_number, 
                                 rp.year,
                                 u.fullname as submitted_by_name,
                                 ps.submitted_at
                          FROM program_submissions ps 
                          LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id 
                          LEFT JOIN users u ON ps.submitted_by = u.user_id
                          WHERE ps.program_id = ? 
                          AND ps.is_deleted = 0 
                          AND ps.is_draft = 0
                          ORDER BY rp.year DESC, rp.period_number DESC, ps.submission_id DESC";
    
    $stmt = $conn->prepare($submissions_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($sub = $result->fetch_assoc()) {
        $sub['period_display'] = get_period_display_name($sub);
        $all_submissions[] = $sub;
    }
    
    // Get specific submission if period_id provided
    $submission = null;
    if ($period_id) {
        $submission_query = "SELECT ps.*, 
                                    rp.period_type, 
                                    rp.period_number, 
                                    rp.year,
                                    u.fullname as submitted_by_name,
                                    ps.submitted_at
                             FROM program_submissions ps 
                             LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id 
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
        
        if ($submission) {
            $submission['period_display'] = get_period_display_name($submission);
        }
    } else {
        // Get latest submission if no specific period
        if (!empty($all_submissions)) {
            $submission = $all_submissions[0];
        }
    }
    
    // Get targets for the specific submission
    $targets = [];
    if ($submission) {
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
    
    // Get rating information
    $rating_info = [
        'rating' => $program['rating'] ?? 'not_started',
        'label' => get_rating_label($program['rating'] ?? 'not_started'),
        'class' => get_rating_class($program['rating'] ?? 'not_started')
    ];
    
    return [
        'program' => $program,
        'agency_info' => $agency_info,
        'submission' => $submission,
        'targets' => $targets,
        'attachments' => $attachments,
        'rating_info' => $rating_info,
        'all_submissions' => $all_submissions,
        'permissions' => [
            'can_edit' => true,    // Admin can edit all
            'can_view' => true     // Admin can view all
        ]
    ];
}

// Note: get_rating_label() function is available from rating_helpers.php

/**
 * Helper function to get rating CSS class
 */
function get_rating_class($rating) {
    $class_map = [
        'not_started' => 'secondary',
        'on_track_for_year' => 'warning',
        'monthly_target_achieved' => 'success',
        'severe_delay' => 'danger'
    ];
    
    return $class_map[$rating] ?? 'secondary';
}

// Note: format_file_size() function is available from functions.php