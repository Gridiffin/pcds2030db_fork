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
