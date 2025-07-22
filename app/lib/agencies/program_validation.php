<?php
/**
 * Program Validation Helper Functions
 * Provides validation functions for program data
 */

/**
 * Validates program name
 * @param string $name The program name to validate
 * @return array Validation result with success and message
 */
function validate_program_name($name) {
    $name = trim($name);
    
    if (empty($name)) {
        return [
            'success' => false,
            'message' => 'Program name is required'
        ];
    }
    
    if (strlen($name) > 255) {
        return [
            'success' => false,
            'message' => 'Program name is too long (max 255 characters)'
        ];
    }
    
    return [
        'success' => true,
        'message' => ''
    ];
}

/**
 * Validates program number format
 * @param string $number The program number to validate
 * @param string $initiative_number The parent initiative number
 * @return array Validation result with success and message
 */
function validate_program_number($number, $initiative_number) {
    $number = trim($number);
    
    if (empty($number)) {
        return [
            'success' => true, // Optional if no initiative selected
            'message' => ''
        ];
    }
    
    if (!preg_match('/^[a-zA-Z0-9.]+$/', $number)) {
        return [
            'success' => false,
            'message' => 'Invalid format. Use only letters, numbers, and dots.'
        ];
    }
    
    if (!str_starts_with($number, $initiative_number . '.')) {
        return [
            'success' => false,
            'message' => "Program number must start with \"$initiative_number.\""
        ];
    }
    
    $pattern = '/^' . preg_quote($initiative_number, '/') . '\.[a-zA-Z0-9]+$/';
    if (!preg_match($pattern, $number)) {
        return [
            'success' => false,
            'message' => 'Please add a suffix after the initiative number (e.g., 1, A, 2B)'
        ];
    }
    
    if (strlen($number) > 20) {
        return [
            'success' => false,
            'message' => 'Program number is too long (max 20 characters)'
        ];
    }
    
    return [
        'success' => true,
        'message' => ''
    ];
}

/**
 * Validates program dates
 * @param string|null $start_date Program start date
 * @param string|null $end_date Program end date
 * @return array Validation result with success and message
 */
function validate_program_dates($start_date, $end_date) {
    // Both dates are optional
    if (empty($start_date) && empty($end_date)) {
        return [
            'success' => true,
            'message' => ''
        ];
    }
    
    // Validate date format
    $date_regex = '/^\d{4}-\d{2}-\d{2}$/';
    if (!empty($start_date) && !preg_match($date_regex, $start_date)) {
        return [
            'success' => false,
            'message' => 'Invalid start date format. Use YYYY-MM-DD'
        ];
    }
    
    if (!empty($end_date) && !preg_match($date_regex, $end_date)) {
        return [
            'success' => false,
            'message' => 'Invalid end date format. Use YYYY-MM-DD'
        ];
    }
    
    // If both dates are provided, validate range
    if (!empty($start_date) && !empty($end_date)) {
        $start = strtotime($start_date);
        $end = strtotime($end_date);
        
        if ($start === false || $end === false) {
            return [
                'success' => false,
                'message' => 'Invalid date values'
            ];
        }
        
        if ($start > $end) {
            return [
                'success' => false,
                'message' => 'End date must be after start date'
            ];
        }
    }
    
    return [
        'success' => true,
        'message' => ''
    ];
}

/**
 * Validates user assignments when editor restrictions are enabled
 * @param bool $restrict_editors Whether editor restrictions are enabled
 * @param array $assigned_editors Array of assigned editor user IDs
 * @return array Validation result with success and message
 */
function validate_user_assignments($restrict_editors, $assigned_editors) {
    if (!$restrict_editors) {
        return [
            'success' => true,
            'message' => ''
        ];
    }
    
    if (empty($assigned_editors)) {
        return [
            'success' => false,
            'message' => 'Please select at least one user when restricting editors'
        ];
    }
    
    // Validate that all assigned users exist and belong to the agency
    global $conn;
    $agency_id = $_SESSION['agency_id'] ?? null;
    
    if (!$agency_id) {
        return [
            'success' => false,
            'message' => 'Agency ID not found in session'
        ];
    }
    
    $user_ids = array_map('intval', $assigned_editors);
    $user_ids_str = implode(',', $user_ids);
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM users 
        WHERE user_id IN ($user_ids_str) 
        AND agency_id = ? 
        AND role = 'agency' 
        AND is_active = 1
    ");
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] !== count($user_ids)) {
        return [
            'success' => false,
            'message' => 'One or more selected users are invalid'
        ];
    }
    
    return [
        'success' => true,
        'message' => ''
    ];
}

/**
 * Validates complete program data
 * @param array $data Program data to validate
 * @return array Validation result with success, message, and errors
 */
function validate_program_data($data) {
    $errors = [];
    
    // Validate program name
    $name_validation = validate_program_name($data['program_name'] ?? '');
    if (!$name_validation['success']) {
        $errors['program_name'] = $name_validation['message'];
    }
    
    // Validate program number if initiative is selected
    if (!empty($data['initiative_id'])) {
        $initiative_number = get_initiative_number($data['initiative_id']);
        if ($initiative_number) {
            $number_validation = validate_program_number($data['program_number'] ?? '', $initiative_number);
            if (!$number_validation['success']) {
                $errors['program_number'] = $number_validation['message'];
            }
        }
    }
    
    // Validate dates
    $date_validation = validate_program_dates($data['start_date'] ?? null, $data['end_date'] ?? null);
    if (!$date_validation['success']) {
        $errors['dates'] = $date_validation['message'];
    }
    
    // Validate user assignments
    $user_validation = validate_user_assignments(
        !empty($data['restrict_editors']),
        $data['assigned_editors'] ?? []
    );
    if (!$user_validation['success']) {
        $errors['users'] = $user_validation['message'];
    }
    
    return [
        'success' => empty($errors),
        'message' => empty($errors) ? 'Validation successful' : 'Validation failed',
        'errors' => $errors
    ];
} 