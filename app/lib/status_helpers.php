<?php
/**
 * Status Helper Functions
 * 
 * Functions for working with program statuses across the application
 */

// Include rating helpers since we use their functions
require_once 'rating_helpers.php';

/**
 * Get a human-readable display name for a status value
 * 
 * @param string $status The status to get a display name for
 * @return string The formatted display name
 */
function get_status_display_name($status) {
    // First convert any legacy status values
    $status = convert_legacy_rating($status);
    
    // Map status values to their display names
    $status_map = [
        'on-track' => 'On Track',
        'on-track-yearly' => 'On Track for Year',
        'target-achieved' => 'Monthly Target Achieved', 
        'delayed' => 'Delayed',
        'severe-delay' => 'Severe Delays',
        'completed' => 'Completed',
        'not-started' => 'Not Started'
    ];
    
    // Return mapped name if exists, otherwise prettify the status string
    return $status_map[$status] ?? ucwords(str_replace('-', ' ', $status));
}

/**
 * For backward compatibility with older code
 */
if (!function_exists('get_status_label')) {
    function get_status_label($status) {
        return get_status_display_name($status);
    }
}
