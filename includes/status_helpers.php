<?php
/**
 * Status Helpers
 * 
 * Functions for working with program status values.
 * This file avoids redundancy with utilities.php by using function_exists checks.
 */

require_once 'utilities.php';

// Only define these functions if they don't already exist

/**
 * Get HTML badge for a status
 * @param string $status Status value
 * @return string HTML badge markup
 */
if (!function_exists('get_status_badge')) {
    function get_status_badge($status) {
        switch ($status) {
            case 'on-track':
                return '<span class="badge bg-success">On Track</span>';
            case 'delayed':
                return '<span class="badge bg-warning">Delayed</span>';
            case 'completed':
                return '<span class="badge bg-info">Completed</span>';
            case 'not-started':
            default:
                return '<span class="badge bg-secondary">Not Started</span>';
        }
    }
}

/**
 * Get status color class
 * @param string $status Status value
 * @return string CSS class name
 */
if (!function_exists('get_status_color_class')) {
    function get_status_color_class($status) {
        switch ($status) {
            case 'on-track': return 'success';
            case 'delayed': return 'warning';
            case 'completed': return 'primary';
            case 'not-started': default: return 'secondary';
        }
    }
}

/**
 * Get status icon class
 * @param string $status Status value
 * @return string FontAwesome icon class
 */
if (!function_exists('get_status_icon')) {
    function get_status_icon($status) {
        switch ($status) {
            case 'on-track': return 'fas fa-check-circle';
            case 'delayed': return 'fas fa-exclamation-triangle';
            case 'completed': return 'fas fa-flag-checkered';
            case 'not-started': default: return 'fas fa-hourglass-start';
        }
    }
}

/**
 * Get status badge with HTML and icon
 * @param string $status Status value
 * @return string HTML badge markup with icon
 */
if (!function_exists('get_status_badge_html')) {
    function get_status_badge_html($status) {
        $color_class = get_status_color_class($status);
        $icon_class = get_status_icon($status);
        $status_text = ucwords(str_replace('-', ' ', $status));
        
        return sprintf(
            '<span class="badge bg-%s"><i class="%s me-1"></i> %s</span>',
            $color_class,
            $icon_class,
            $status_text
        );
    }
}

/**
 * Get status display name
 * @param string $status Status value
 * @return string Human-readable status name
 */
if (!function_exists('get_status_display_name')) {
    function get_status_display_name($status) {
        return ucfirst(str_replace('-', ' ', $status));
    }
}

/**
 * Get all valid status values as key-value pairs
 * @return array Status values and display names
 */
if (!function_exists('get_all_status_values')) {
    function get_all_status_values() {
        return [
            'on-track' => 'On Track',
            'delayed' => 'Delayed',
            'completed' => 'Completed',
            'not-started' => 'Not Started'
        ];
    }
}

/**
 * Check if a status value is valid
 * @param string $status Status to check
 * @return bool True if valid
 */
if (!function_exists('is_valid_status')) {
    function is_valid_status($status) {
        return in_array($status, ['on-track', 'delayed', 'completed', 'not-started']);
    }
}
?>
