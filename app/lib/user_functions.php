<?php
// Ensure audit_log.php is always available for log_audit()
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
}
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';
/**
 * User Profile Functions
 * Helper functions for user profile management
 */

if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';

/**
 * Get user by ID
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @return array|null User data or null if not found
 */
function get_user_by_id($conn, $user_id) {
    if (!$conn || !is_numeric($user_id)) {
        return null;
    }
    
    $query = "SELECT user_id, username, fullname, email, agency_id, role, created_at, updated_at, is_active 
              FROM users 
              WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare get_user_by_id query: " . $conn->error);
        return null;
    }
    
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Update user profile
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param array $data Profile data to update
 * @return array Result with success status and message
 */
function update_user_profile($conn, $user_id, $data) {
    if (!$conn || !is_numeric($user_id) || empty($data)) {
        return ['success' => false, 'message' => 'Invalid parameters'];
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get current user data for audit logging
        $current_user = get_user_by_id($conn, $user_id);
        if (!$current_user) {
            throw new Exception('User not found');
        }
        
        // Build update query dynamically based on provided data
        $update_fields = [];
        $values = [];
        $types = '';
        
        // Update username if provided
        if (isset($data['username']) && !empty($data['username'])) {
            // Check username uniqueness
            if (!validate_username_unique($conn, $data['username'], $user_id)) {
                throw new Exception('Username already exists');
            }
            
            $update_fields[] = 'username = ?';
            $values[] = $data['username'];
            $types .= 's';
        }
        
        // Update email if provided
        if (isset($data['email']) && !empty($data['email'])) {
            if (!validate_email_format($data['email'])) {
                throw new Exception('Invalid email format');
            }
            
            $update_fields[] = 'email = ?';
            $values[] = $data['email'];
            $types .= 's';
        }
        
        // Update fullname if provided
        if (isset($data['fullname'])) {
            $update_fields[] = 'fullname = ?';
            $values[] = $data['fullname'];
            $types .= 's';
        }
        
        // Update password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                throw new Exception('Password must be at least 8 characters long');
            }
            
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $update_fields[] = 'pw = ?';
            $values[] = $hashed_password;
            $types .= 's';
        }
        
        if (empty($update_fields)) {
            throw new Exception('No data to update');
        }
        
        // Add updated_at field
        $update_fields[] = 'updated_at = NOW()';
        
        // Prepare and execute update query
        $query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE user_id = ?";
        $values[] = $user_id;
        $types .= 'i';
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Failed to prepare update query: ' . $conn->error);
        }
        
        $stmt->bind_param($types, ...$values);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update user profile: ' . $stmt->error);
        }
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('No changes were made to the profile');
        }
        
        // Log the profile update for audit trail
        $details = 'User updated their profile: ' . json_encode(array_keys($data));
        log_audit_action('profile_update', $details, 'success', $user_id);
        
        $conn->commit();
        
        return [
            'success' => true, 
            'message' => 'Profile updated successfully'
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Profile update failed for user $user_id: " . $e->getMessage());
        
        return [
            'success' => false, 
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Validate username uniqueness
 * @param mysqli $conn Database connection
 * @param string $username Username to validate
 * @param int $current_user_id Current user ID (to exclude from check)
 * @return bool True if username is unique
 */
function validate_username_unique($conn, $username, $current_user_id) {
    if (!$conn || empty($username)) {
        return false;
    }
    
    $query = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Failed to prepare username uniqueness query: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param('si', $username, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows === 0;
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool True if email format is valid
 */
function validate_email_format($email) {
    if (empty($email)) {
        return false;
    }
    
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate username format
 * @param string $username Username to validate
 * @return array Result with valid status and message
 */
function validate_username_format($username) {
    if (empty($username)) {
        return ['valid' => false, 'message' => 'Username is required'];
    }
    
    if (strlen($username) < 3 || strlen($username) > 50) {
        return ['valid' => false, 'message' => 'Username must be between 3 and 50 characters'];
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ['valid' => false, 'message' => 'Username can only contain letters, numbers, and underscores'];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return array Result with valid status, strength score, and message
 */
function validate_password_strength($password) {
    if (empty($password)) {
        return ['valid' => false, 'strength' => 0, 'message' => 'Password is required'];
    }
    
    $strength = 0;
    $feedback = [];
    
    // Length check
    if (strlen($password) < 8) {
        return ['valid' => false, 'strength' => 0, 'message' => 'Password must be at least 8 characters long'];
    } elseif (strlen($password) >= 12) {
        $strength += 2;
    } else {
        $strength += 1;
    }
    
    // Character variety checks
    if (preg_match('/[a-z]/', $password)) {
        $strength += 1;
    } else {
        $feedback[] = 'lowercase letter';
    }
    
    if (preg_match('/[A-Z]/', $password)) {
        $strength += 1;
    } else {
        $feedback[] = 'uppercase letter';
    }
    
    if (preg_match('/[0-9]/', $password)) {
        $strength += 1;
    } else {
        $feedback[] = 'number';
    }
    
    if (preg_match('/[^a-zA-Z0-9]/', $password)) {
        $strength += 1;
    } else {
        $feedback[] = 'special character';
    }
    
    // Determine strength level and message
    $strength_level = 'weak';
    $is_valid = true;
    
    if ($strength >= 5) {
        $strength_level = 'strong';
    } elseif ($strength >= 3) {
        $strength_level = 'medium';
    } else {
        $is_valid = false;
    }
    
    $message = '';
    if (!$is_valid) {
        $message = 'Password is too weak. Please include: ' . implode(', ', $feedback);
    } elseif ($strength_level === 'medium') {
        $message = 'Password strength is medium. Consider adding: ' . implode(', ', $feedback);
    } else {
        $message = 'Password is strong';
    }
    
    return [
        'valid' => $is_valid,
        'strength' => $strength,
        'level' => $strength_level,
        'message' => $message
    ];
}

/**
 * Get user's agency name
 * @param mysqli $conn Database connection
 * @param int $agency_id Agency ID
 * @return string Agency name or 'Unknown'
 */
function get_agency_name_by_id($conn, $agency_id) {
    if (!$conn || !is_numeric($agency_id)) {
        return 'Unknown';
    }
    
    $query = "SELECT agency_name FROM agency WHERE agency_id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Failed to prepare agency name query: " . $conn->error);
        return 'Unknown';
    }
    
    $stmt->bind_param('i', $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['agency_name'];
    }
    
    return 'Unknown';
}
