<?php
/**
 * Program User Assignments Library
 * 
 * Handles user-level permissions within agencies for programs:
 * - Editor: Can edit program submissions and details
 * - Viewer: Can only view program information
 * 
 * Works in conjunction with agency-level permissions
 */

if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once dirname(__DIR__) . '/db_connect.php';
require_once dirname(__DIR__) . '/session.php';
require_once dirname(__DIR__) . '/admins/core.php';
require_once dirname(__DIR__) . '/agencies/program_agency_assignments.php';

/**
 * Get user's role for a specific program (user-level)
 *
 * @param int $program_id Program ID
 * @param int $user_id User ID (optional, uses session if not provided)
 * @return string|false Role ('editor', 'viewer') or false if no specific assignment
 */
function get_user_program_user_role($program_id, $user_id = null) {
    global $conn;
    
    $program_id = intval($program_id);
    if ($program_id <= 0) {
        return false;
    }
    
    // Use session user_id if not provided
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? 0;
    }
    $user_id = intval($user_id);
    
    if ($user_id <= 0) {
        return false;
    }
    
    // Check program_user_assignments table
    $stmt = $conn->prepare("
        SELECT role 
        FROM program_user_assignments 
        WHERE program_id = ? AND user_id = ? AND is_active = 1
    ");
    $stmt->bind_param("ii", $program_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['role'];
    }
    
    return false;
}

/**
 * Check if a program has editor restrictions enabled
 *
 * @param int $program_id Program ID
 * @return bool True if restrictions are enabled
 */
function program_has_editor_restrictions($program_id) {
    global $conn;
    
    $program_id = intval($program_id);
    if ($program_id <= 0) {
        return false;
    }
    
    $stmt = $conn->prepare("SELECT restrict_editors FROM programs WHERE program_id = ?");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return (bool)$row['restrict_editors'];
    }
    
    return false;
}

/**
 * Enhanced can_edit_program function with user-level permissions
 * 
 * Permission hierarchy:
 * 1. Focal users can edit programs within their agency (if agency has access)
 * 2. Check agency-level permissions (owner/editor)
 * 3. If agency has access, check user-level restrictions
 *
 * @param int $program_id Program ID
 * @param int $user_id User ID (optional)
 * @return bool True if user can edit
 */
function can_edit_program_with_user_restrictions($program_id, $user_id = null) {
    // Focal users can edit programs within their agency (check agency access first)
    if (is_focal_user()) {
        $focal_agency_id = $_SESSION['agency_id'] ?? null;
        if ($focal_agency_id && can_edit_program($program_id)) {
            return true;
        }
        // If focal user's agency doesn't have access, follow normal rules
    }
    
    // Check agency-level permissions first
    if (!can_edit_program($program_id)) {
        return false;
    }
    
    // If program doesn't have editor restrictions, all agency users can edit
    if (!program_has_editor_restrictions($program_id)) {
        return true;
    }
    
    // Check user-level permissions
    $user_role = get_user_program_user_role($program_id, $user_id);
    return $user_role === 'editor';
}

/**
 * Enhanced can_view_program function with user-level permissions
 *
 * @param int $program_id Program ID
 * @param int $user_id User ID (optional)
 * @return bool True if user can view
 */
function can_view_program_with_user_restrictions($program_id, $user_id = null) {
    // Focal users can view programs within their agency (check agency access first)
    if (is_focal_user()) {
        $focal_agency_id = $_SESSION['agency_id'] ?? null;
        if ($focal_agency_id && can_view_program($program_id)) {
            return true;
        }
        // If focal user's agency doesn't have access, follow normal rules
    }
    
    // Check agency-level permissions first
    return can_view_program($program_id);
}

/**
 * Get all users assigned to a program with their roles
 *
 * @param int $program_id Program ID
 * @return array Array of assigned users with their roles
 */
function get_program_assigned_users($program_id) {
    global $conn;
    
    $program_id = intval($program_id);
    if ($program_id <= 0) {
        return [];
    }
    
    $stmt = $conn->prepare("
        SELECT pua.*, u.username, u.fullname, u.agency_id, a.agency_name,
               assigned_by_user.username as assigned_by_name,
               assigned_by_user.fullname as assigned_by_fullname
        FROM program_user_assignments pua
        LEFT JOIN users u ON pua.user_id = u.user_id
        LEFT JOIN agency a ON u.agency_id = a.agency_id
        LEFT JOIN users assigned_by_user ON pua.assigned_by = assigned_by_user.user_id
        WHERE pua.program_id = ? AND pua.is_active = 1
        ORDER BY 
            CASE pua.role 
                WHEN 'editor' THEN 1 
                WHEN 'viewer' THEN 2 
                ELSE 3 
            END,
            u.fullname, u.username
    ");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    
    return $assignments;
}

/**
 * Assign a user to a program with specific role
 *
 * @param int $program_id Program ID
 * @param int $user_id User ID to assign
 * @param string $role Role to assign ('editor', 'viewer')
 * @param string $notes Optional notes
 * @return array Success/error response
 */
function assign_user_to_program($program_id, $user_id, $role = 'viewer', $notes = '') {
    global $conn;
    
    // Validate input
    $program_id = intval($program_id);
    $user_id = intval($user_id);
    $assigned_by = $_SESSION['user_id'] ?? 0;
    
    if ($program_id <= 0 || $user_id <= 0 || $assigned_by <= 0) {
        return ['error' => 'Invalid parameters'];
    }
    
    if (!in_array($role, ['editor', 'viewer'])) {
        return ['error' => 'Invalid role'];
    }
    
    // Check if current user can assign users (must be program owner, focal, or admin)
    // Focal users can only assign within programs their agency has access to
    if (!is_admin() && !is_focal_user() && !is_program_owner($program_id)) {
        return ['error' => 'Permission denied'];
    }
    
    // Additional check for focal users - they can only assign to programs their agency has access to
    if (is_focal_user()) {
        $focal_agency_id = $_SESSION['agency_id'] ?? null;
        if ($focal_agency_id) {
            $focal_role = get_user_program_role($program_id, $focal_agency_id);
            if (!$focal_role) {
                return ['error' => 'Permission denied - your agency does not have access to this program'];
            }
        }
    }
    
    // Verify target user exists and get their agency
    $user_check_stmt = $conn->prepare("SELECT agency_id FROM users WHERE user_id = ?");
    $user_check_stmt->bind_param("i", $user_id);
    $user_check_stmt->execute();
    $user_data = $user_check_stmt->get_result()->fetch_assoc();
    
    if (!$user_data) {
        return ['error' => 'User not found'];
    }
    
    // Check if user's agency has access to the program
    $agency_access = get_user_program_role($program_id, $user_data['agency_id']);
    if (!$agency_access) {
        return ['error' => 'User\'s agency does not have access to this program'];
    }
    
    // Check if assignment already exists
    $check_stmt = $conn->prepare("
        SELECT assignment_id FROM program_user_assignments 
        WHERE program_id = ? AND user_id = ?
    ");
    $check_stmt->bind_param("ii", $program_id, $user_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        // Update existing assignment
        $update_stmt = $conn->prepare("
            UPDATE program_user_assignments 
            SET role = ?, assigned_by = ?, updated_at = CURRENT_TIMESTAMP, 
                is_active = 1, notes = ?
            WHERE program_id = ? AND user_id = ?
        ");
        $update_stmt->bind_param("sisii", $role, $assigned_by, $notes, $program_id, $user_id);
        
        if ($update_stmt->execute()) {
            return ['success' => 'User assignment updated successfully'];
        } else {
            return ['error' => 'Failed to update assignment'];
        }
    } else {
        // Create new assignment
        $insert_stmt = $conn->prepare("
            INSERT INTO program_user_assignments 
            (program_id, user_id, role, assigned_by, notes) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert_stmt->bind_param("iisis", $program_id, $user_id, $role, $assigned_by, $notes);
        
        if ($insert_stmt->execute()) {
            return ['success' => 'User assigned successfully'];
        } else {
            return ['error' => 'Failed to create assignment'];
        }
    }
}

/**
 * Remove a user from a program
 *
 * @param int $program_id Program ID
 * @param int $user_id User ID to remove
 * @return array Success/error response
 */
function remove_user_from_program($program_id, $user_id) {
    global $conn;
    
    $program_id = intval($program_id);
    $user_id = intval($user_id);
    
    if ($program_id <= 0 || $user_id <= 0) {
        return ['error' => 'Invalid parameters'];
    }
    
    // Check if current user can remove users (must be program owner, focal, or admin)
    // Focal users can only remove assignments from programs their agency has access to
    if (!is_admin() && !is_focal_user() && !is_program_owner($program_id)) {
        return ['error' => 'Permission denied'];
    }
    
    // Additional check for focal users - they can only remove assignments from programs their agency has access to
    if (is_focal_user()) {
        $focal_agency_id = $_SESSION['agency_id'] ?? null;
        if ($focal_agency_id) {
            $focal_role = get_user_program_role($program_id, $focal_agency_id);
            if (!$focal_role) {
                return ['error' => 'Permission denied - your agency does not have access to this program'];
            }
        }
    }
    
    // Soft delete the assignment
    $stmt = $conn->prepare("
        UPDATE program_user_assignments 
        SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
        WHERE program_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $program_id, $user_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        return ['success' => 'User removed from program successfully'];
    } else {
        return ['error' => 'Failed to remove user or assignment not found'];
    }
}

/**
 * Set program editor restrictions
 *
 * @param int $program_id Program ID
 * @param bool $restrict Whether to restrict editors
 * @return array Success/error response
 */
function set_program_editor_restrictions($program_id, $restrict = true) {
    global $conn;
    
    $program_id = intval($program_id);
    
    if ($program_id <= 0) {
        return ['error' => 'Invalid program ID'];
    }
    
    // Check if current user can modify restrictions (must be program owner, focal, or admin)
    if (!is_admin() && !is_focal_user() && !is_program_owner($program_id)) {
        return ['error' => 'Permission denied'];
    }
    
    $restrict_value = $restrict ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE programs SET restrict_editors = ? WHERE program_id = ?");
    $stmt->bind_param("ii", $restrict_value, $program_id);
    
    if ($stmt->execute()) {
        $action = $restrict ? 'enabled' : 'disabled';
        return ['success' => "Editor restrictions {$action} successfully"];
    } else {
        return ['error' => 'Failed to update restrictions'];
    }
}

/**
 * Get users in agency who can be assigned to a program
 *
 * @param int $program_id Program ID
 * @param int $agency_id Agency ID (optional, gets from program if not provided)
 * @return array Array of available users
 */
function get_assignable_users_for_program($program_id, $agency_id = null) {
    global $conn;
    
    $program_id = intval($program_id);
    
    // Get agency ID from program if not provided
    if ($agency_id === null) {
        $program_stmt = $conn->prepare("
            SELECT DISTINCT paa.agency_id 
            FROM program_agency_assignments paa 
            WHERE paa.program_id = ? AND paa.is_active = 1
        ");
        $program_stmt->bind_param("i", $program_id);
        $program_stmt->execute();
        $agencies = $program_stmt->get_result();
        
        $assignable_users = [];
        while ($agency_row = $agencies->fetch_assoc()) {
            $users = get_assignable_users_for_program($program_id, $agency_row['agency_id']);
            $assignable_users = array_merge($assignable_users, $users);
        }
        
        return $assignable_users;
    }
    
    $agency_id = intval($agency_id);
    
    $stmt = $conn->prepare("
        SELECT u.user_id, u.username, u.fullname, u.agency_id,
               pua.role as current_role,
               CASE WHEN pua.assignment_id IS NOT NULL THEN 1 ELSE 0 END as is_assigned
        FROM users u
        LEFT JOIN program_user_assignments pua ON u.user_id = pua.user_id 
            AND pua.program_id = ? AND pua.is_active = 1
        WHERE u.agency_id = ? 
            AND u.role IN ('agency', 'focal')
            AND u.is_active = 1
        ORDER BY u.fullname, u.username
    ");
    $stmt->bind_param("ii", $program_id, $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    return $users;
}
?>
