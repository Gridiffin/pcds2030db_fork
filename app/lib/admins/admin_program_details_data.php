<?php
/**
 * Admin Program Details Data Helper
 * 
 * Functions to retrieve program details data for admin views
 * Based on agency program_details_data.php but adapted for admin perspective
 */

/**
 * Get comprehensive program details data for admin view
 * 
 * @param int $program_id Program ID
 * @return array|null Program data array or null if not found
 */
function get_admin_program_details_view_data($program_id) {
    global $conn;
    
    if (!$program_id) return null;
    
    // Get program basic information with agency details
    $program_query = "SELECT p.*, 
                             i.initiative_name, 
                             i.initiative_number, 
                             i.initiative_description,
                             i.start_date as initiative_start_date,
                             i.end_date as initiative_end_date,
                             a.agency_name,
                             a.agency_id,
                             p.program_description as description
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
    
    // Get latest finalized submission (admin only sees finalized)
    $latest_submission = null;
    $has_submissions = false;
    
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
                         AND ps.is_deleted = 0 
                         AND ps.is_draft = 0
                         ORDER BY ps.submission_id DESC 
                         LIMIT 1";
    
    $stmt = $conn->prepare($submission_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $latest_submission = $result->fetch_assoc();
    
    if ($latest_submission) {
        $has_submissions = true;
        
        // Add period display
        $latest_submission['period_display'] = get_period_display_name($latest_submission);
    }
    
    // Get submission information
    $submission_info = null;
    if ($latest_submission && !empty($latest_submission['submitted_by']) && !empty($latest_submission['submitted_at'])) {
        $submission_info = [
            'submitted_by' => $latest_submission['submitted_by'],
            'submitted_by_name' => $latest_submission['submitted_by_name'],
            'submitted_at' => $latest_submission['submitted_at']
        ];
    }
    
    // Get targets for latest submission
    $targets = [];
    if ($latest_submission) {
        $targets_query = "SELECT target_id, target_number, target_description, 
                                 status_indicator, status_description, remarks, 
                                 start_date, end_date 
                          FROM program_targets 
                          WHERE submission_id = ? AND is_deleted = 0 
                          ORDER BY target_id";
        
        $stmt = $conn->prepare($targets_query);
        $stmt->bind_param("i", $latest_submission['submission_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($target = $result->fetch_assoc()) {
            $targets[] = $target;
        }
    }
    
    // Get all reporting periods for this program (submission history) - excluding half-yearly and yearly
    $all_periods = [];
    $periods_query = "SELECT DISTINCT rp.period_id, rp.period_type, rp.period_number, rp.year,
                             ps.submission_id, ps.is_draft, ps.submitted_at,
                             u.fullname as submitted_by_name
                      FROM reporting_periods rp 
                      LEFT JOIN program_submissions ps ON rp.period_id = ps.period_id 
                          AND ps.program_id = ? AND ps.is_deleted = 0 AND ps.is_draft = 0
                      LEFT JOIN users u ON ps.submitted_by = u.user_id
                      WHERE rp.period_type NOT IN ('half', 'yearly')
                      ORDER BY rp.year DESC, rp.period_number DESC";
    
    $stmt = $conn->prepare($periods_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($period = $result->fetch_assoc()) {
        $period['period_display'] = get_period_display_name($period);
        $all_periods[] = $period;
    }
    
    // Get submission history (finalized only for admin) - excluding half-yearly and yearly
    $submission_history = [];
    $history_query = "SELECT ps.*, 
                             rp.period_type, rp.period_number, rp.year,
                             u.fullname as submitted_by_name
                      FROM program_submissions ps 
                      LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id 
                      LEFT JOIN users u ON ps.submitted_by = u.user_id
                      WHERE ps.program_id = ? 
                      AND ps.is_deleted = 0 
                      AND ps.is_draft = 0
                      AND rp.period_type NOT IN ('half', 'yearly')
                      ORDER BY ps.submission_id DESC";
    
    $stmt = $conn->prepare($history_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($submission = $result->fetch_assoc()) {
        $submission['period_display'] = get_period_display_name($submission);
        $submission_history[] = $submission;
    }
    
    // Get latest by period (for overview)
    $latest_by_period = [];
    foreach ($all_periods as $period) {
        if (!empty($period['submission_id'])) {
            $latest_by_period[$period['period_id']] = $period;
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
    
    // Get related programs if linked to initiative
    $related_programs = [];
    if (!empty($program['initiative_id'])) {
        $related_query = "SELECT p.program_id, p.program_name, p.program_number, 
                                 p.rating, a.agency_name,
                                 CASE 
                                    WHEN COUNT(ps.submission_id) = 0 THEN 1
                                    WHEN COUNT(CASE WHEN ps.is_draft = 0 THEN 1 END) = 0 THEN 1
                                    ELSE 0
                                 END as is_draft_only
                          FROM programs p 
                          LEFT JOIN agency a ON p.agency_id = a.agency_id
                          LEFT JOIN program_submissions ps ON p.program_id = ps.program_id 
                              AND ps.is_deleted = 0
                          WHERE p.initiative_id = ? 
                          AND p.program_id != ? 
                          AND p.is_deleted = 0
                          GROUP BY p.program_id, p.program_name, p.program_number, 
                                   p.rating, a.agency_name
                          ORDER BY a.agency_name, p.program_name";
        
        $stmt = $conn->prepare($related_query);
        $stmt->bind_param("ii", $program['initiative_id'], $program_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($related = $result->fetch_assoc()) {
            $related_programs[] = $related;
        }
    }
    
    // Get program assignees and editors (only user-level assignments exist)
    $program_assignees = [];
    
    // Get user-level assignments (only table that exists)
    $user_assignments_query = "SELECT pua.role, u.fullname, u.email, a.agency_name 
                              FROM program_user_assignments pua
                              JOIN users u ON pua.user_id = u.user_id
                              LEFT JOIN agency a ON u.agency_id = a.agency_id
                              WHERE pua.program_id = ? AND pua.is_active = 1
                              ORDER BY u.fullname";
    
    $stmt = $conn->prepare($user_assignments_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($assignment = $result->fetch_assoc()) {
        $program_assignees[] = [
            'type' => 'user',
            'name' => $assignment['fullname'],
            'email' => $assignment['email'],
            'role' => $assignment['role'],
            'agency_name' => $assignment['agency_name']
        ];
    }
    
    // Get hold points/issues (if any)
    $hold_points = [];
    // This would be implemented based on your hold points system
    
    // Set alert flags (admin perspective)
    $alert_flags = [];
    if (!$has_submissions) {
        $alert_flags[] = 'no_submissions';
    }
    
    return [
        'program' => $program,
        'agency_info' => $agency_info,
        'has_submissions' => $has_submissions,
        'latest_submission' => $latest_submission,
        'targets' => $targets,
        'rating' => $program['rating'] ?? 'not_started',
        'remarks' => $program['remarks'] ?? '',
        'all_periods' => $all_periods,
        'latest_by_period' => $latest_by_period,
        'submission_history' => $submission_history,
        'hold_points' => $hold_points,
        'attachments' => $attachments,
        'related_programs' => $related_programs,
        'submission_info' => $submission_info,
        'program_assignees' => $program_assignees,
        'alert_flags' => $alert_flags,
        'permissions' => [
            'can_edit' => true,    // Admin can edit all
            'can_view' => true,    // Admin can view all
            'is_owner' => false    // Admin is not owner but has rights
        ],
        'is_draft' => false // Admin only sees finalized
    ];
}

// Note: format_file_size() function is available from functions.php