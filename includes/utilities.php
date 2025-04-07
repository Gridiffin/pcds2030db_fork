<?php
/**
 * Shared Utility Functions
 * 
 * This file contains utility functions used across the application.
 */

/**
 * Check if a database table has a specific column
 * @param string $table Table name
 * @param string $column Column name
 * @return boolean Whether the column exists
 */
function has_database_column($table, $column) {
    global $conn;
    static $column_cache = [];
    
    $cache_key = "{$table}.{$column}";
    
    if (!isset($column_cache[$cache_key])) {
        $column_check = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        $column_cache[$cache_key] = $column_check && $column_check->num_rows > 0;
    }
    
    return $column_cache[$cache_key];
}

/**
 * Process and standardize error responses
 * @param string $message Error message
 * @param int $code Error code (optional)
 * @return array Standardized error response
 */
function format_error($message, $code = 400) {
    return [
        'error' => $message,
        'code' => $code
    ];
}

/**
 * Process and standardize success responses
 * @param string $message Success message
 * @param array $data Additional data (optional)
 * @return array Standardized success response
 */
function format_success($message, $data = []) {
    return array_merge([
        'success' => true,
        'message' => $message
    ], $data);
}

/**
 * Generate status badge HTML
 * @param string $status Status value
 * @return string HTML for the status badge
 */
function get_status_badge_html($status) {
    $status_class = 'secondary';
    switch ($status) {
        case 'on-track': $status_class = 'success'; break;
        case 'delayed': $status_class = 'warning'; break;
        case 'completed': $status_class = 'primary'; break;
        case 'not-started': $status_class = 'secondary'; break;
    }
    
    $status_text = ucfirst(str_replace('-', ' ', $status));
    
    return "<span class=\"badge bg-{$status_class}\">{$status_text}</span>";
}

/**
 * Validate program status
 * @param string $status Status to validate
 * @return boolean Whether status is valid
 */
function is_valid_status($status) {
    return in_array($status, ['on-track', 'delayed', 'completed', 'not-started']);
}

/**
 * Sanitize and validate form input
 * @param array $data Form data
 * @param array $required Required fields
 * @return array Sanitized data or error
 */
function validate_form_input($data, $required = []) {
    global $conn;
    $sanitized = [];
    $errors = [];
    
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $sanitized[$key] = $conn->real_escape_string(trim($value));
        } else {
            $sanitized[$key] = $value;
        }
    }
    
    // Check required fields
    foreach ($required as $field) {
        if (empty($sanitized[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    if (!empty($errors)) {
        return format_error(implode(', ', $errors));
    }
    
    return $sanitized;
}

/**
 * Get status badge with HTML and icon - DEPRECATED: use from status_helpers.php instead
 * @param string $status Status value
 * @return string HTML badge markup with icon
 */
if (!function_exists('get_status_badge_html')) {
    function get_status_badge_html($status) {
        // Check if status_helpers.php has been included and the new function exists there
        if (function_exists('get_status_icon') && function_exists('get_status_color_class')) {
            $color_class = get_status_color_class($status);
            $icon_class = get_status_icon($status);
            $status_text = ucwords(str_replace('-', ' ', $status));
            
            return sprintf(
                '<span class="badge bg-%s"><i class="%s me-1"></i> %s</span>',
                $color_class,
                $icon_class,
                $status_text
            );
        } else {
            // Simplified fallback implementation
            switch ($status) {
                case 'on-track':
                    return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> On Track</span>';
                case 'delayed':
                    return '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle me-1"></i> Delayed</span>';
                case 'completed':
                    return '<span class="badge bg-info"><i class="fas fa-flag-checkered me-1"></i> Completed</span>';
                case 'not-started':
                default:
                    return '<span class="badge bg-secondary"><i class="fas fa-hourglass-start me-1"></i> Not Started</span>';
            }
        }
    }
}
?>
