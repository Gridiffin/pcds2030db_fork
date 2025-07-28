<?php
/**
 * Status Helper Functions
 * 
 * Functions for working with program statuses across the application
 */

// Include rating helpers since we use their functions
require_once 'rating_helpers.php';

/**
 * Get full status info (label, class, icon) for program status
 *
 * @param string $status The status value
 * @return array [label, class, icon]
 */
function get_program_status_info($status) {
    $map = [
        'active' => [
            'label' => 'Active',
            'class' => 'success',
            'icon' => 'fas fa-play-circle',
        ],
        'on_hold' => [
            'label' => 'On Hold',
            'class' => 'warning',
            'icon' => 'fas fa-pause-circle',
        ],
        'completed' => [
            'label' => 'Completed',
            'class' => 'primary',
            'icon' => 'fas fa-check-circle',
        ],
        'delayed' => [
            'label' => 'Delayed',
            'class' => 'danger',
            'icon' => 'fas fa-exclamation-triangle',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'class' => 'secondary',
            'icon' => 'fas fa-times-circle',
        ],
    ];
    // Default fallback
    if (!isset($map[$status])) {
        return [
            'label' => ucwords(str_replace(['_', '-'], ' ', $status)),
            'class' => 'secondary',
            'icon' => 'fas fa-question-circle',
        ];
    }
    return $map[$status];
}

/**
 * Get a human-readable display name for a status value
 * 
 * @param string $status The status to get a display name for
 * @return string The formatted display name
 */
function get_status_display_name($status) {
    $info = get_program_status_info($status);
    return $info['label'];
}

/**
 * For backward compatibility with older code
 */
if (!function_exists('get_status_label')) {
    function get_status_label($status) {
        return get_status_display_name($status);
    }
}
