<?php
/**
 * Program Details Error Handler
 * 
 * Provides centralized error handling and logging for the program details page.
 */

/**
 * Handle program details errors
 * 
 * @param Exception $error The error that occurred
 * @param string $context The context where the error occurred
 * @param array $data Additional data for debugging
 * @return array Error response for the user
 */
function handle_program_details_error($error, $context = 'unknown', $data = []) {
    // Log the error
    log_program_details_error($error, $context, $data);
    
    // Determine user-friendly message
    $user_message = get_user_friendly_error_message($error, $context);
    
    // Return error response
    return [
        'success' => false,
        'error' => $user_message,
        'debug_info' => DEBUG_MODE ? $error->getMessage() : null,
        'context' => $context
    ];
}

/**
 * Log program details errors
 */
function log_program_details_error($error, $context, $data) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $error->getMessage(),
        'file' => $error->getFile(),
        'line' => $error->getLine(),
        'context' => $context,
        'user_id' => $_SESSION['user_id'] ?? 'unknown',
        'program_id' => $data['program_id'] ?? 'unknown',
        'data' => $data
    ];
    
    $log_file = ROOT_PATH . 'logs/program_details_errors.log';
    $log_dir = dirname($log_file);
    
    // Create log directory if it doesn't exist
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Write to log file
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    
    // Also log to system error log if available
    if (function_exists('error_log')) {
        error_log("Program Details Error: " . json_encode($log_entry));
    }
}

/**
 * Get user-friendly error message
 */
function get_user_friendly_error_message($error, $context) {
    $error_code = $error->getCode();
    $error_message = $error->getMessage();
    
    // Database connection errors
    if (strpos($error_message, 'Connection refused') !== false || 
        strpos($error_message, 'MySQL server has gone away') !== false) {
        return 'Database connection error. Please try again in a few moments.';
    }
    
    // Database query errors
    if (strpos($error_message, 'Unknown column') !== false) {
        return 'Data structure error. Please contact support.';
    }
    
    // Permission errors
    if (strpos($error_message, 'Access denied') !== false) {
        return 'You do not have permission to access this program.';
    }
    
    // File not found errors
    if (strpos($error_message, 'No such file') !== false) {
        return 'Program not found or has been removed.';
    }
    
    // Context-specific messages
    switch ($context) {
        case 'program_load':
            return 'Unable to load program details. Please try again.';
        case 'submissions_load':
            return 'Unable to load program submissions. Please try again.';
        case 'attachments_load':
            return 'Unable to load program attachments. Please try again.';
        case 'data_processing':
            return 'Unable to process program data. Please try again.';
        case 'permission_check':
            return 'Permission check failed. Please log in again.';
        default:
            return 'An unexpected error occurred. Please try again.';
    }
}

/**
 * Validate program access permissions
 * 
 * @param int $program_id The program ID to check
 * @param int $user_id The user ID checking access
 * @param int $agency_id The user's agency ID
 * @return array Validation result
 */
function validate_program_access($program_id, $user_id, $agency_id) {
    try {
        global $conn;
        
        // Check if program exists
        $stmt = $conn->prepare("SELECT agency_id FROM programs WHERE program_id = ? AND is_deleted = 0");
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Program not found", 404);
        }
        
        $program = $result->fetch_assoc();
        
        // Check if user has access to this program
        if ($program['agency_id'] != $agency_id) {
            // Check if user is assigned to this program
            $assign_stmt = $conn->prepare("SELECT role FROM program_user_assignments WHERE program_id = ? AND user_id = ?");
            $assign_stmt->bind_param("ii", $program_id, $user_id);
            $assign_stmt->execute();
            $assign_result = $assign_stmt->get_result();
            
            if ($assign_result->num_rows === 0) {
                throw new Exception("Access denied", 403);
            }
        }
        
        return [
            'success' => true,
            'is_owner' => $program['agency_id'] == $agency_id,
            'program_agency_id' => $program['agency_id']
        ];
        
    } catch (Exception $e) {
        return handle_program_details_error($e, 'permission_check', [
            'program_id' => $program_id,
            'user_id' => $user_id,
            'agency_id' => $agency_id
        ]);
    }
}

/**
 * Validate program data integrity
 * 
 * @param array $program The program data to validate
 * @return array Validation result
 */
function validate_program_data_integrity($program) {
    $errors = [];
    
    // Check required fields
    $required_fields = ['program_id', 'program_name', 'agency_id'];
    foreach ($required_fields as $field) {
        if (empty($program[$field])) {
            $errors[] = "Missing required field: $field";
        }
    }
    
    // Check data types
    if (!is_numeric($program['program_id'])) {
        $errors[] = "Invalid program ID format";
    }
    
    if (!is_numeric($program['agency_id'])) {
        $errors[] = "Invalid agency ID format";
    }
    
    // Check date formats if present
    if (!empty($program['start_date']) && !strtotime($program['start_date'])) {
        $errors[] = "Invalid start date format";
    }
    
    if (!empty($program['end_date']) && !strtotime($program['end_date'])) {
        $errors[] = "Invalid end date format";
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }
    
    return ['success' => true];
}

/**
 * Handle database connection errors
 */
function handle_database_connection_error() {
    try {
        global $conn;
        
        // Test connection
        if (!$conn || $conn->connect_error) {
            throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : 'No connection'));
        }
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return handle_program_details_error($e, 'database_connection');
    }
}

/**
 * Create error response for AJAX requests
 */
function create_error_response($message, $context = 'unknown', $data = []) {
    $response = [
        'success' => false,
        'message' => $message,
        'context' => $context,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if (DEBUG_MODE) {
        $response['debug_data'] = $data;
    }
    
    return $response;
}

/**
 * Create success response for AJAX requests
 */
function create_success_response($data, $message = 'Success') {
    return [
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

/**
 * Check if user has permission to perform action
 */
function check_user_permission($action, $program_id, $user_id, $agency_id) {
    try {
        global $conn;
        
        // Get user role
        $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("User not found");
        }
        
        $user = $result->fetch_assoc();
        $role = $user['role'];
        
        // Admin can do everything
        if ($role === 'admin') {
            return ['success' => true, 'permitted' => true];
        }
        
        // Check program ownership
        $stmt = $conn->prepare("SELECT agency_id FROM programs WHERE program_id = ?");
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Program not found");
        }
        
        $program = $result->fetch_assoc();
        $is_owner = $program['agency_id'] == $agency_id;
        
        // Define permissions
        $permissions = [
            'view' => ['admin', 'agency', 'focal'],
            'edit' => ['admin', 'agency'],
            'delete' => ['admin'],
            'submit' => ['admin', 'agency', 'focal'],
            'upload_attachment' => ['admin', 'agency']
        ];
        
        $permitted = in_array($role, $permissions[$action] ?? []) && 
                    ($is_owner || $role === 'admin');
        
        return [
            'success' => true,
            'permitted' => $permitted,
            'role' => $role,
            'is_owner' => $is_owner
        ];
        
    } catch (Exception $e) {
        return handle_program_details_error($e, 'permission_check', [
            'action' => $action,
            'program_id' => $program_id,
            'user_id' => $user_id
        ]);
    }
}

/**
 * Sanitize user input
 */
function sanitize_program_input($input) {
    $sanitized = [];
    
    foreach ($input as $key => $value) {
        if (is_string($value)) {
            $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        } elseif (is_array($value)) {
            $sanitized[$key] = sanitize_program_input($value);
        } else {
            $sanitized[$key] = $value;
        }
    }
    
    return $sanitized;
}

/**
 * Validate file upload for program details
 */
function validate_program_file_upload($file, $allowed_types = [], $max_size = 10485760) {
    $errors = [];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = "No file was uploaded";
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        $errors[] = "File size exceeds maximum allowed size (" . format_bytes($max_size) . ")";
    }
    
    // Check file type
    if (!empty($allowed_types)) {
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "File type not allowed. Allowed types: " . implode(', ', $allowed_types);
        }
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => "File exceeds PHP upload limit",
            UPLOAD_ERR_FORM_SIZE => "File exceeds form upload limit",
            UPLOAD_ERR_PARTIAL => "File was only partially uploaded",
            UPLOAD_ERR_NO_FILE => "No file was uploaded",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload"
        ];
        
        $errors[] = $upload_errors[$file['error']] ?? "Unknown upload error";
    }
    
    return $errors;
}

/**
 * Format bytes to human readable format
 */
function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
} 