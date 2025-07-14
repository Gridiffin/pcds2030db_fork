<?php
/**
 * Program Details Data Processor
 * 
 * Handles data processing and formatting for the program details page.
 * Separates complex logic from the view file for better maintainability.
 */

/**
 * Process program details data for display
 * 
 * @param array $program Raw program data from database
 * @param array $latest_submission Latest submission data
 * @param bool $has_submissions Whether program has submissions
 * @return array Processed data ready for display
 */
function process_program_details_data($program, $latest_submission, $has_submissions) {
    $processed = [
        'basic_info' => process_basic_info($program),
        'targets' => process_targets($latest_submission, $has_submissions),
        'status' => process_status($latest_submission, $has_submissions),
        'timeline' => process_timeline($program),
        'attachments' => process_attachments($program),
        'related_programs' => process_related_programs($program),
        'submissions_history' => process_submissions_history($program),
        'performance_metrics' => process_performance_metrics($program),
        'accessibility' => process_accessibility_data($program)
    ];
    
    return $processed;
}

/**
 * Process basic program information
 */
function process_basic_info($program) {
    return [
        'program_name' => htmlspecialchars($program['program_name'] ?? ''),
        'program_number' => htmlspecialchars($program['program_number'] ?? ''),
        'description' => htmlspecialchars($program['program_description'] ?? ''),
        'agency_name' => htmlspecialchars($program['agency_name'] ?? ''),
        'initiative_name' => htmlspecialchars($program['initiative_name'] ?? ''),
        'initiative_number' => htmlspecialchars($program['initiative_number'] ?? ''),
        'sector_name' => htmlspecialchars($program['sector_name'] ?? 'Not specified'),
        'is_assigned' => isset($program['is_assigned']) && $program['is_assigned'],
        'created_at' => $program['created_at'] ?? null,
        'updated_at' => $program['updated_at'] ?? null
    ];
}

/**
 * Process targets data
 */
function process_targets($latest_submission, $has_submissions) {
    $targets = [];
    
    if ($has_submissions && isset($latest_submission['content_json']) && !empty($latest_submission['content_json'])) {
        $content = is_string($latest_submission['content_json']) 
            ? json_decode($latest_submission['content_json'], true) 
            : $latest_submission['content_json'];
        
        if (isset($content['targets']) && is_array($content['targets'])) {
            foreach ($content['targets'] as $target) {
                if (isset($target['target_text'])) {
                    $targets[] = [
                        'target_number' => $target['target_number'] ?? '',
                        'text' => htmlspecialchars($target['target_text']),
                        'status_description' => htmlspecialchars($target['status_description'] ?? ''),
                        'start_date' => $target['start_date'] ?? '',
                        'end_date' => $target['end_date'] ?? '',
                        'progress' => $target['progress'] ?? 0
                    ];
                }
            }
        } else {
            // Legacy data format
            $target_text = $content['target'] ?? $latest_submission['target'] ?? '';
            $status_description = $content['status_text'] ?? $latest_submission['status_text'] ?? '';
            
            if (strpos($target_text, ';') !== false) {
                $target_parts = array_map('trim', explode(';', $target_text));
                $status_parts = array_map('trim', explode(';', $status_description));
                
                foreach ($target_parts as $index => $target_part) {
                    if (!empty($target_part)) {
                        $targets[] = [
                            'text' => htmlspecialchars($target_part),
                            'status_description' => htmlspecialchars(isset($status_parts[$index]) ? $status_parts[$index] : ''),
                            'target_number' => '',
                            'start_date' => '',
                            'end_date' => '',
                            'progress' => 0
                        ];
                    }
                }
            } else {
                $targets[] = [
                    'text' => htmlspecialchars($target_text),
                    'status_description' => htmlspecialchars($status_description),
                    'target_number' => '',
                    'start_date' => '',
                    'end_date' => '',
                    'progress' => 0
                ];
            }
        }
    } elseif ($has_submissions && !empty($latest_submission['target'])) {
        $targets[] = [
            'text' => htmlspecialchars($latest_submission['target']),
            'status_description' => htmlspecialchars($latest_submission['status_text'] ?? ''),
            'target_number' => '',
            'start_date' => '',
            'end_date' => '',
            'progress' => 0
        ];
    }
    
    return $targets;
}

/**
 * Process status information
 */
function process_status($latest_submission, $has_submissions) {
    $status_map = [
        'on-track' => ['label' => 'On Track', 'class' => 'warning', 'icon' => 'fas fa-chart-line'],
        'on-track-yearly' => ['label' => 'On Track for Year', 'class' => 'warning', 'icon' => 'fas fa-calendar-check'],
        'target-achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success', 'icon' => 'fas fa-check-circle'],
        'delayed' => ['label' => 'Delayed', 'class' => 'danger', 'icon' => 'fas fa-exclamation-circle'],
        'severe-delay' => ['label' => 'Severe Delays', 'class' => 'danger', 'icon' => 'fas fa-exclamation-triangle'],
        'completed' => ['label' => 'Completed', 'class' => 'primary', 'icon' => 'fas fa-flag-checkered'],
        'not-started' => ['label' => 'Not Started', 'class' => 'secondary', 'icon' => 'fas fa-hourglass-start']
    ];
    
    if ($has_submissions) {
        $rating = $latest_submission['status'] ?? 'not-started';
        $status = convert_program_legacy_status($rating);
        if (!isset($status_map[$status])) {
            $status = 'not-started';
        }
    } else {
        $status = 'not-started';
    }
    
    return [
        'current' => $status,
        'display' => $status_map[$status],
        'remarks' => htmlspecialchars($latest_submission['remarks'] ?? ''),
        'submission_date' => $latest_submission['submitted_at'] ?? null,
        'is_draft' => isset($latest_submission['is_draft']) && $latest_submission['is_draft']
    ];
}

/**
 * Process timeline information
 */
function process_timeline($program) {
    return [
        'start_date' => $program['start_date'] ?? null,
        'end_date' => $program['end_date'] ?? null,
        'initiative_start_date' => $program['initiative_start_date'] ?? null,
        'initiative_end_date' => $program['initiative_end_date'] ?? null,
        'created_at' => $program['created_at'] ?? null,
        'updated_at' => $program['updated_at'] ?? null
    ];
}

/**
 * Process attachments data
 */
function process_attachments($program) {
    $attachments = get_program_attachments($program['program_id']);
    
    $processed = [];
    foreach ($attachments as $attachment) {
        $processed[] = [
            'id' => $attachment['attachment_id'],
            'filename' => htmlspecialchars($attachment['original_filename']),
            'size' => $attachment['file_size_formatted'],
            'type' => $attachment['mime_type'],
            'upload_date' => $attachment['upload_date'],
            'uploaded_by' => htmlspecialchars($attachment['uploaded_by'] ?? 'Unknown'),
            'description' => htmlspecialchars($attachment['description'] ?? ''),
            'download_url' => APP_URL . '/app/ajax/download_program_attachment.php?id=' . $attachment['attachment_id']
        ];
    }
    
    return $processed;
}

/**
 * Process related programs
 */
function process_related_programs($program) {
    $related_programs = [];
    
    if (!empty($program['initiative_id'])) {
        $related_programs = get_related_programs_by_initiative(
            $program['initiative_id'], 
            $program['program_id'], 
            true // Allow cross-agency viewing
        );
    }
    
    return $related_programs;
}

/**
 * Process submissions history
 */
function process_submissions_history($program) {
    $submissions = $program['submissions'] ?? [];
    $history = [];
    
    foreach ($submissions as $submission) {
        $history[] = [
            'id' => $submission['submission_id'],
            'period' => format_period_name($submission),
            'submission_date' => $submission['submitted_at'],
            'status' => $submission['status'] ?? 'not-started',
            'is_draft' => isset($submission['is_draft']) && $submission['is_draft'],
            'submitted_by' => $submission['submitted_by_name'] ?? 'Unknown'
        ];
    }
    
    return $history;
}

/**
 * Process performance metrics
 */
function process_performance_metrics($program) {
    $submissions = $program['submissions'] ?? [];
    $metrics = [
        'total_submissions' => count($submissions),
        'completed_targets' => 0,
        'on_track_targets' => 0,
        'delayed_targets' => 0,
        'overall_progress' => 0
    ];
    
    if (!empty($submissions)) {
        $latest = $submissions[0];
        if (isset($latest['content_json'])) {
            $content = is_string($latest['content_json']) 
                ? json_decode($latest['content_json'], true) 
                : $latest['content_json'];
            
            if (isset($content['targets']) && is_array($content['targets'])) {
                foreach ($content['targets'] as $target) {
                    $status = $target['status'] ?? 'not-started';
                    switch ($status) {
                        case 'completed':
                            $metrics['completed_targets']++;
                            break;
                        case 'on-track':
                        case 'target-achieved':
                            $metrics['on_track_targets']++;
                            break;
                        case 'delayed':
                        case 'severe-delay':
                            $metrics['delayed_targets']++;
                            break;
                    }
                }
                
                $total_targets = count($content['targets']);
                if ($total_targets > 0) {
                    $metrics['overall_progress'] = round(($metrics['completed_targets'] / $total_targets) * 100);
                }
            }
        }
    }
    
    return $metrics;
}

/**
 * Process accessibility data
 */
function process_accessibility_data($program) {
    return [
        'aria_labels' => [
            'program_name' => 'Program name: ' . ($program['program_name'] ?? ''),
            'program_number' => 'Program number: ' . ($program['program_number'] ?? ''),
            'agency_name' => 'Agency: ' . ($program['agency_name'] ?? ''),
            'initiative_name' => 'Initiative: ' . ($program['initiative_name'] ?? ''),
            'status' => 'Current status: ' . ($program['status'] ?? 'not-started')
        ],
        'live_regions' => [
            'status_updates' => 'status-updates',
            'target_progress' => 'target-progress',
            'attachment_updates' => 'attachment-updates'
        ]
    ];
}

/**
 * Format period name for display
 */
function format_period_name($submission) {
    if (isset($submission['year']) && isset($submission['period_type']) && isset($submission['period_number'])) {
        if ($submission['period_type'] === 'quarter') {
            return "Q{$submission['period_number']}-{$submission['year']}";
        } elseif ($submission['period_type'] === 'half') {
            return "H{$submission['period_number']}-{$submission['year']}";
        } elseif ($submission['period_type'] === 'yearly') {
            return "Y{$submission['period_number']}-{$submission['year']}";
        } else {
            return "{$submission['period_type']} {$submission['period_number']}-{$submission['year']}";
        }
    }
    
    return 'Unknown period';
}

/**
 * Convert legacy status to new format for program details
 */
function convert_program_legacy_status($status) {
    $status_map = [
        'on-track' => 'on-track',
        'on-track-yearly' => 'on-track-yearly',
        'target-achieved' => 'target-achieved',
        'delayed' => 'delayed',
        'severe-delay' => 'severe-delay',
        'completed' => 'completed',
        'not-started' => 'not-started'
    ];
    
    return $status_map[$status] ?? 'not-started';
}

/**
 * Get file icon based on MIME type for program details
 */
function get_program_file_icon($mime_type) {
    $icon_map = [
        'application/pdf' => 'fa-file-pdf',
        'application/msword' => 'fa-file-word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
        'application/vnd.ms-excel' => 'fa-file-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa-file-excel',
        'application/vnd.ms-powerpoint' => 'fa-file-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'fa-file-powerpoint',
        'image/jpeg' => 'fa-file-image',
        'image/png' => 'fa-file-image',
        'image/gif' => 'fa-file-image',
        'text/plain' => 'fa-file-text',
        'text/csv' => 'fa-file-csv'
    ];
    
    return $icon_map[$mime_type] ?? 'fa-file';
}

/**
 * Validate and sanitize program data
 */
function validate_program_data($data) {
    $errors = [];
    
    if (empty($data['program_name'])) {
        $errors[] = 'Program name is required';
    }
    
    if (empty($data['agency_id'])) {
        $errors[] = 'Agency is required';
    }
    
    if (!empty($data['start_date']) && !empty($data['end_date'])) {
        if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
            $errors[] = 'Start date cannot be after end date';
        }
    }
    
    return $errors;
}

/**
 * Format date for display
 */
function format_date_for_display($date, $format = 'M j, Y') {
    if (empty($date)) {
        return 'Not specified';
    }
    
    return date($format, strtotime($date));
}

/**
 * Format date and time for display
 */
function format_datetime_for_display($datetime, $format = 'M j, Y g:i A') {
    if (empty($datetime)) {
        return 'Not available';
    }
    
    return date($format, strtotime($datetime));
} 