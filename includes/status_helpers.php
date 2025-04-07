<?php
/**
 * Status Helpers
 * 
 * Functions for working with program status values.
 */

/**
 * Get HTML badge for a status
 * @param string $status Status value
 * @return string HTML badge markup
 */
function get_status_badge($status) {
    // Convert legacy status if needed
    $status = convert_legacy_status($status);
    
    switch ($status) {
        case 'target-achieved':
            return '<span class="badge bg-success">Monthly Target Achieved</span>';
        case 'on-track-yearly':
            return '<span class="badge bg-warning">On Track for Year</span>';
        case 'severe-delay':
            return '<span class="badge bg-danger">Severe Delays</span>';
        case 'not-started':
        default:
            return '<span class="badge bg-secondary">Not Started</span>';
    }
}

/**
 * Get status color class
 * @param string $status Status value
 * @return string CSS class name
 */
function get_status_color_class($status) {
    $status = convert_legacy_status($status);
    
    switch ($status) {
        case 'target-achieved': return 'success';
        case 'on-track-yearly': return 'warning';
        case 'severe-delay': return 'danger';
        case 'not-started': default: return 'secondary';
    }
}

/**
 * Get status icon class
 * @param string $status Status value
 * @return string FontAwesome icon class
 */
function get_status_icon($status) {
    $status = convert_legacy_status($status);
    
    switch ($status) {
        case 'target-achieved': return 'fas fa-check-circle';
        case 'on-track-yearly': return 'fas fa-calendar-check';
        case 'severe-delay': return 'fas fa-exclamation-triangle';
        case 'not-started': default: return 'fas fa-hourglass-start';
    }
}

/**
 * Get status badge with HTML and icon
 * @param string $status Status value
 * @return string HTML badge markup with icon
 */
function get_status_badge_html($status) {
    $status = convert_legacy_status($status);
    $color_class = get_status_color_class($status);
    $icon_class = get_status_icon($status);
    $status_text = get_status_display_name($status);
    
    return sprintf(
        '<span class="badge bg-%s"><i class="%s me-1"></i> %s</span>',
        $color_class,
        $icon_class,
        $status_text
    );
}

/**
 * Get status display name
 * @param string $status Status value
 * @return string Human-readable status name
 */
function get_status_display_name($status) {
    $status = convert_legacy_status($status);
    
    switch($status) {
        case 'target-achieved': return 'Monthly Target Achieved';
        case 'on-track-yearly': return 'On Track for Year';
        case 'severe-delay': return 'Severe Delays';
        case 'not-started': default: return 'Not Started';
    }
}

/**
 * Get all valid status values as key-value pairs
 * @return array Status values and display names
 */
function get_all_status_values() {
    return [
        'target-achieved' => 'Monthly Target Achieved',
        'on-track-yearly' => 'On Track for Year',
        'severe-delay' => 'Severe Delays',
        'not-started' => 'Not Started'
    ];
}

/**
 * Check if a status value is valid
 * @param string $status Status to check
 * @return bool True if valid
 */
function is_valid_status($status) {
    $status = convert_legacy_status($status);
    return array_key_exists($status, get_all_status_values());
}

/**
 * Convert old status values to new values for backwards compatibility
 * @param string $status Old status value
 * @return string New status value
 */
function convert_legacy_status($status) {
    switch($status) {
        case 'on-track': return 'target-achieved';
        case 'delayed': return 'on-track-yearly';
        case 'completed': return 'severe-delay';
        case 'not-started': return 'not-started';
        default: return $status; // If it's already a new status, return as is
    }
}
?>
