<?php
/**
 * Rating Helper Functions
 * 
 * Functions for working with program ratings across the application
 */

/**
 * Convert legacy rating values to new rating values
 * 
 * @param string $rating The rating to convert
 * @return string The converted rating
 */
function convert_legacy_rating($rating) {
    if (!$rating) return 'not-started';
    
    $rating = strtolower(trim($rating));
    
    // Map of old rating values to new ones
    $rating_map = [
        'on track' => 'on-track',
        'on-track' => 'on-track',
        'on track for year' => 'on-track-yearly',
        'on-track-yearly' => 'on-track-yearly',
        'target achieved' => 'target-achieved',
        'monthly target achieved' => 'target-achieved',
        'target-achieved' => 'target-achieved',
        'delayed' => 'delayed',
        'severe delays' => 'severe-delay',
        'severe delay' => 'severe-delay',
        'severe-delay' => 'severe-delay',
        'completed' => 'completed',
        'not started' => 'not-started',
        'not-started' => 'not-started'
    ];
    
    return $rating_map[$rating] ?? 'not-started';
}

/**
 * Get appropriate badge HTML for a rating
 * 
 * @param string $rating The rating to get a badge for
 * @return string HTML for the rating badge
 */
function get_rating_badge($rating) {
    $rating = convert_legacy_rating($rating);
    
    $badges = [
        'on-track' => ['class' => 'warning', 'icon' => 'calendar-check', 'label' => 'On Track'],
        'on-track-yearly' => ['class' => 'warning', 'icon' => 'calendar-check', 'label' => 'On Track for Year'],
        'target-achieved' => ['class' => 'success', 'icon' => 'check-circle', 'label' => 'Monthly Target Achieved'],
        'completed' => ['class' => 'success', 'icon' => 'check-circle', 'label' => 'Monthly Target Achieved'],
        'delayed' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'label' => 'Delayed'],
        'severe-delay' => ['class' => 'danger', 'icon' => 'exclamation-circle', 'label' => 'Severe Delay'],
        'not-started' => ['class' => 'secondary', 'icon' => 'clock', 'label' => 'Not Started']
    ];
    
    // Set default if rating is not in our map
    if (!isset($badges[$rating])) {
        $rating = 'not-started';
    }
    
    $badge = $badges[$rating];
    
    return '<span class="badge bg-' . $badge['class'] . '"><i class="fas fa-' . $badge['icon'] . ' me-1"></i> ' . $badge['label'] . '</span>';
}

/**
 * Get all valid rating values
 * 
 * @return array Array of valid rating values
 */
function get_valid_ratings() {
    return [
        'on-track',
        'on-track-yearly',
        'target-achieved', 
        'delayed',
        'severe-delay',
        'completed',
        'not-started'
    ];
}

/**
 * Check if a rating value is valid
 * 
 * @param string $rating The rating to check
 * @return bool True if rating is valid
 */
function is_valid_rating($rating) {
    return in_array(convert_legacy_rating($rating), get_valid_ratings());
}

/**
 * Legacy function names for backward compatibility
 */
if (!function_exists('convert_legacy_status')) {
    function convert_legacy_status($status) {
        return convert_legacy_rating($status);
    }
}

/**
 * Additional legacy function names for backward compatibility
 */
if (!function_exists('get_status_badge')) {
    function get_status_badge($status) {
        return get_rating_badge($status);
    }
}

if (!function_exists('get_valid_statuses')) {
    function get_valid_statuses() {
        return get_valid_ratings();
    }
}

if (!function_exists('is_valid_status')) {
    function is_valid_status($status) {
        return is_valid_rating($status);
    }
}
?>
