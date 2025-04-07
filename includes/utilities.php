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
?>
