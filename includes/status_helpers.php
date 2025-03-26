<?php
/**
 * Status Helpers
 * Shared functions for handling program status display
 */

/**
 * Get status badge HTML
 * 
 * @param string $status The program status
 * @param bool $useSpan Whether to use span (true) or div (false)
 * @return string HTML for the status badge
 */
function get_status_badge($status, $useSpan = true) {
    $status_class = 'secondary';
    
    switch($status) {
        case 'on-track': $status_class = 'success'; break;
        case 'delayed': $status_class = 'warning'; break;
        case 'completed': $status_class = 'info'; break;
        case 'not-started': $status_class = 'secondary'; break;
    }
    
    $element = $useSpan ? 'span' : 'div';
    $formatted_status = ucfirst(str_replace('-', ' ', $status));
    
    return "<{$element} class=\"badge bg-{$status_class}\">{$formatted_status}</{$element}>";
}

/**
 * Get status badge class
 * 
 * @param string $status The program status
 * @param string $prefix CSS class prefix (default: 'bg')
 * @return string CSS class
 */
function get_status_class($status, $prefix = 'bg') {
    $status_class = 'secondary';
    
    switch($status) {
        case 'on-track': $status_class = 'success'; break;
        case 'delayed': $status_class = 'warning'; break;
        case 'completed': $status_class = 'info'; break;
        case 'not-started': $status_class = 'secondary'; break;
    }
    
    return "{$prefix}-{$status_class}";
}
