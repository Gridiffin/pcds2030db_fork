<?php
/**
 * Program Details Data Helper
 * Handles data preparation for program details views
 */

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/programs.php';
require_once __DIR__ . '/program_attachments.php';
require_once __DIR__ . '/program_permissions.php';
require_once __DIR__ . '/../rating_helpers.php';
require_once __DIR__ . '/../program_status_helpers.php';

/**
 * Get complete program details data for viewing
 * 
 * @param int $program_id Program ID
 * @return array|null Complete program details data or null if not found/no access
 */
function get_program_details_view_data($program_id) {
    global $conn;
    
    // Get comprehensive program details
    $program = get_program_details($program_id, true);
    if (!$program) {
        return null;
    }
    
    // Check permissions
    $can_edit = can_edit_program($program_id);
    $can_view = can_view_program($program_id);
    $is_owner = is_program_owner($program_id);
    
    if (!$can_view) {
        return null;
    }
    
    // Get program submissions and process data
    $has_submissions = !empty($program['submissions']);
    $latest_submission = $has_submissions ? $program['submissions'][0] : null;
    $targets = [];
    $rating = 'not_started';
    $remarks = '';
    $is_draft = false;
    
    if ($has_submissions) {
        // Check if latest submission is draft
        $is_draft = isset($latest_submission['is_draft']) && $latest_submission['is_draft'];
        
        // Get targets from the current submission
        if (isset($latest_submission['targets']) && is_array($latest_submission['targets'])) {
            $targets = $latest_submission['targets'];
        }
        
        // Get rating and remarks from submission
        $rating = $latest_submission['rating'] ?? $latest_submission['status_indicator'] ?? 'not-started';
        $remarks = $latest_submission['remarks'] ?? $latest_submission['description'] ?? '';
        
        // Fallback to legacy content_json if targets are not found
        if (empty($targets) && isset($latest_submission['content_json']) && !empty($latest_submission['content_json'])) {
            $targets = process_legacy_targets($latest_submission['content_json'], $rating, $remarks);
        }
    }
    
    // Use program rating for consistency
    $rating = isset($program['rating']) ? $program['rating'] : 'not_started';
    
    // Get all reporting periods
    $all_periods = get_all_reporting_periods();
    $latest_by_period = $program['latest_submissions_by_period'] ?? [];
    
    // Get submission history for timeline
    $submission_history = get_program_edit_history($program_id);
    
    // Get program hold points
    $hold_points = get_program_hold_points($program_id);
    
    // Get program attachments
    $attachments = get_program_attachments($program_id);
    
    // Get related programs if linked to initiative
    $related_programs = [];
    if (!empty($program['initiative_id'])) {
        $related_programs = get_related_programs_by_initiative(
            $program['initiative_id'],
            $program_id,
            true // Always allow cross-agency viewing
        );
    }
    
    // Determine alert flags
    $alert_flags = [
        'show_draft_alert' => $is_draft && $is_owner,
        'show_no_targets_alert' => $has_submissions && empty($targets) && $is_owner,
        'show_no_submissions_alert' => !$has_submissions,
        'show_finalized_alert' => $has_submissions && !$is_draft
    ];
    
    return [
        'program' => $program,
        'has_submissions' => $has_submissions,
        'latest_submission' => $latest_submission,
        'targets' => $targets,
        'rating' => $rating,
        'remarks' => $remarks,
        'is_draft' => $is_draft,
        'all_periods' => $all_periods,
        'latest_by_period' => $latest_by_period,
        'submission_history' => $submission_history,
        'hold_points' => $hold_points,
        'attachments' => $attachments,
        'related_programs' => $related_programs,
        'alert_flags' => $alert_flags,
        'permissions' => [
            'can_edit' => $can_edit,
            'can_view' => $can_view,
            'is_owner' => $is_owner
        ]
    ];
}

/**
 * Process legacy targets from content_json
 * 
 * @param mixed $content_json Legacy content JSON
 * @param string &$rating Rating reference to update
 * @param string &$remarks Remarks reference to update
 * @return array Processed targets
 */
function process_legacy_targets($content_json, &$rating, &$remarks) {
    $targets = [];
    
    if (is_string($content_json)) {
        $content = json_decode($content_json, true) ?: [];
    } elseif (is_array($content_json)) {
        $content = $content_json;
    } else {
        return $targets;
    }
    
    // Extract targets from legacy content
    if (isset($content['targets']) && is_array($content['targets'])) {
        foreach ($content['targets'] as $target) {
            if (isset($target['target_text'])) {
                $targets[] = [
                    'target_number' => $target['target_number'] ?? '',
                    'text' => $target['target_text'],
                    'status_description' => $target['status_description'] ?? '',
                    'start_date' => $target['start_date'] ?? '',
                    'end_date' => $target['end_date'] ?? ''
                ];
            } else {
                $targets[] = $target;
            }
        }
    } elseif (isset($content['target']) && !empty($content['target'])) {
        // Legacy single target format
        $targets[] = [
            'text' => $content['target'],
            'status_description' => $content['status_text'] ?? ''
        ];
    }
    
    // Override rating and remarks from legacy content if available
    if (isset($content['rating'])) {
        $rating = $content['rating'];
    }
    if (isset($content['remarks'])) {
        $remarks = $content['remarks'];
    }
    
    return $targets;
}

/**
 * Get program hold points
 * 
 * @param int $program_id Program ID
 * @return array Array of hold points
 */
function get_program_hold_points($program_id) {
    global $conn;
    
    $hold_points = [];
    
    if (!$program_id) {
        return $hold_points;
    }
    
    $stmt = $conn->prepare('SELECT id, reason, remarks, created_at, ended_at, created_by FROM program_hold_points WHERE program_id = ? ORDER BY created_at ASC');
    $stmt->bind_param('i', $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $hold_points[] = $row;
    }
    
    $stmt->close();
    
    return $hold_points;
}


/**
 * Format file size in human readable format
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>