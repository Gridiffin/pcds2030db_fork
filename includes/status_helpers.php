<?php
/**
 * Status Helper Functions
 * 
 * Functions for working with program statuses across the application
 */

/**
 * Convert legacy status values to new status values
 * 
 * @param string $status The status to convert
 * @return string The converted status
 */
function convert_legacy_status($status) {
    if (!$status) return 'not-started';
    
    $status = strtolower(trim($status));
    
    // Map of old status values to new ones
    $status_map = [
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
    
    return $status_map[$status] ?? 'not-started';
}

/**
 * Get appropriate badge HTML for a status
 * 
 * @param string $status The status to get a badge for
 * @return string HTML for the status badge
 */
function get_status_badge($status) {
    $status = convert_legacy_status($status);
    
    $badges = [
        'on-track' => ['class' => 'warning', 'icon' => 'check-circle', 'label' => 'On Track'],
        'on-track-yearly' => ['class' => 'warning', 'icon' => 'calendar-check', 'label' => 'On Track for Year'],
        'target-achieved' => ['class' => 'success', 'icon' => 'trophy', 'label' => 'Target Achieved'],
        'delayed' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'label' => 'Delayed'],
        'severe-delay' => ['class' => 'danger', 'icon' => 'exclamation-circle', 'label' => 'Severe Delay'],
        'completed' => ['class' => 'primary', 'icon' => 'flag-checkered', 'label' => 'Completed'],
        'not-started' => ['class' => 'secondary', 'icon' => 'clock', 'label' => 'Not Started']
    ];
    
    $badge = $badges[$status] ?? $badges['not-started'];
    
    return '<span class="badge bg-' . $badge['class'] . '"><i class="fas fa-' . $badge['icon'] . ' me-1"></i> ' . $badge['label'] . '</span>';
}

/**
 * Get all valid status values
 * 
 * @return array Array of valid status values
 */
function get_valid_statuses() {
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
 * Check if a status value is valid
 * 
 * @param string $status The status to check
 * @return bool True if status is valid
 */
function is_valid_status($status) {
    return in_array(convert_legacy_status($status), get_valid_statuses());
}
?>
