<?php
/**
 * Rating Helper Functions V2 - Clean Implementation
 * 
 * Simple functions for working with program ratings.
 * No legacy conversion - works directly with database values.
 */

// Database ENUM values
define('RATING_NOT_STARTED', 'not_started');
define('RATING_ON_TRACK', 'on_track_for_year');
define('RATING_TARGET_ACHIEVED', 'monthly_target_achieved');
define('RATING_SEVERE_DELAY', 'severe_delay');

/**
 * Get all valid rating values
 * 
 * @return array Array of valid rating values
 */
function get_valid_ratings() {
    return [
        RATING_NOT_STARTED,
        RATING_ON_TRACK,
        RATING_TARGET_ACHIEVED,
        RATING_SEVERE_DELAY
    ];
}

/**
 * Check if a rating value is valid
 * 
 * @param string $rating The rating to check
 * @return bool True if rating is valid
 */
function is_valid_rating($rating) {
    return in_array($rating, get_valid_ratings());
}

/**
 * Get rating display label
 * 
 * @param string $rating The rating value
 * @return string Display label
 */
function get_rating_label($rating) {
    $labels = [
        RATING_NOT_STARTED => 'Not Started',
        RATING_ON_TRACK => 'On Track',
        RATING_TARGET_ACHIEVED => 'Monthly Target Achieved',
        RATING_SEVERE_DELAY => 'Severe Delays'
    ];
    
    return $labels[$rating] ?? 'Unknown';
}

/**
 * Get rating badge HTML
 * 
 * @param string $rating The rating value
 * @return string HTML for the rating badge
 */
function get_rating_badge($rating) {
    if (!is_valid_rating($rating)) {
        $rating = RATING_NOT_STARTED;
    }
    
    $badges = [
        RATING_NOT_STARTED => ['class' => 'secondary', 'icon' => 'clock', 'label' => 'Not Started'],
        RATING_ON_TRACK => ['class' => 'warning', 'icon' => 'calendar-check', 'label' => 'On Track'],
        RATING_TARGET_ACHIEVED => ['class' => 'success', 'icon' => 'check-circle', 'label' => 'Monthly Target Achieved'],
        RATING_SEVERE_DELAY => ['class' => 'danger', 'icon' => 'exclamation-circle', 'label' => 'Severe Delay']
    ];
    
    $badge = $badges[$rating];
    
    return '<span class="badge bg-' . $badge['class'] . '"><i class="fas fa-' . $badge['icon'] . ' me-1"></i> ' . $badge['label'] . '</span>';
}

/**
 * Get rating color for reports
 * 
 * @param string $rating The rating value
 * @return string Color code
 */
function get_rating_color($rating) {
    if (!is_valid_rating($rating)) {
        $rating = RATING_NOT_STARTED;
    }
    
    $colors = [
        RATING_NOT_STARTED => 'grey',
        RATING_ON_TRACK => 'yellow',
        RATING_TARGET_ACHIEVED => 'green',
        RATING_SEVERE_DELAY => 'red'
    ];
    
    return $colors[$rating];
}

/**
 * Get rating options for select dropdown
 * 
 * @return array Array of options for select dropdown
 */
function get_rating_options() {
    return [
        RATING_NOT_STARTED => 'Not Started',
        RATING_ON_TRACK => 'On Track',
        RATING_TARGET_ACHIEVED => 'Monthly Target Achieved',
        RATING_SEVERE_DELAY => 'Severe Delays'
    ];
}
?> 