<?php
/**
 * Status Helpers
 * 
 * Functions for working with program status values.
 */

require_once 'utilities.php';

/**
 * Get HTML badge for a status
 * @param string $status Status value
 * @return string HTML badge markup
 */
function get_status_badge($status) {
    return get_status_badge_html($status);
}

/**
 * Get status color class
 * @param string $status Status value
 * @return string CSS class name
 */
function get_status_color_class($status) {
    switch ($status) {
        case 'on-track': return 'success';
        case 'delayed': return 'warning';
        case 'completed': return 'primary';
        case 'not-started': default: return 'secondary';
    }
}

/**
 * Get status icon class
 * @param string $status Status value
 * @return string FontAwesome icon class
 */
function get_status_icon($status) {
    switch ($status) {
        case 'on-track': return 'fas fa-check-circle';
        case 'delayed': return 'fas fa-exclamation-triangle';
        case 'completed': return 'fas fa-flag-checkered';
        case 'not-started': default: return 'fas fa-hourglass-start';
    }
}

/**
 * Get status display name
 * @param string $status Status value
 * @return string Human-readable status name
 */
function get_status_display_name($status) {
    return ucfirst(str_replace('-', ' ', $status));
}

/**
 * Get all valid status values as key-value pairs
 * @return array Status values and display names
 */
function get_all_status_values() {
    return [
        'on-track' => 'On Track',
        'delayed' => 'Delayed',
        'completed' => 'Completed',
        'not-started' => 'Not Started'
    ];
}
?>
