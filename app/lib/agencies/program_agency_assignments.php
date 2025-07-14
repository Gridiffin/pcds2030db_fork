<?php
/**
 * Program Agency Assignments Library
 * 
 * Handles agency-level permissions for programs including:
 * - Owner: Full access, can assign other agencies
 * - Editor: Can edit submissions and targets
 * - Viewer: Read-only access
 */

if (!defined('PROJECT_ROOT_PATH')) {
        // Check if current user can remove agency assignment (must be owner, focal, or admin)
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
}

if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once dirname(__DIR__) . '/db_connect.php';
require_once dirname(__DIR__) . '/session.php';
require_once dirname(__DIR__) . '/admins/core.php';

// Include user-level permissions (will be loaded when needed)
function load_user_assignments_if_needed() {
    static $loaded = false;
    if (!$loaded) {
        require_once __DIR__ . '/program_user_assignments.php';
        $loaded = true;
    }
}

/**
 * Get user's role for a specific program
 *
 * @param int $program_id Program ID
 * @param int $agency_id Agency ID (optional, uses session if not provided)
 * @return string|false Role ('owner', 'editor', 'viewer') or false if no access
 */
function get_user_program_role($program_id, $agency_id = null) {
    global $conn;
    
    // Add recursion protection
    static $recursion_protection = [];
    $call_key = $program_id . '_' . ($agency_id ?? 'null');
    
    if (isset($recursion_protection[$call_key])) {
        error_log("Recursion detected in get_user_program_role for program_id: $program_id, agency_id: $agency_id");
        return false; // Break recursion
    }
    
    $recursion_protection[$call_key] = true;
    
    $program_id = intval($program_id);
    if ($program_id <= 0) {
        unset($recursion_protection[$call_key]);
        return false;
    }

    // Use session agency_id if not provided
    if ($agency_id === null) {
        $agency_id = $_SESSION['agency_id'] ?? 0;
    }
    $agency_id = intval($agency_id);

    if ($agency_id <= 0) {
        unset($recursion_protection[$call_key]);
        return false;
    }

    // Check program_agency_assignments table
    $stmt = $conn->prepare("
        SELECT role 
        FROM program_agency_assignments 
        WHERE program_id = ? AND agency_id = ? AND is_active = 1
    ");
    
    if (!$stmt) {
        error_log("Failed to prepare statement in get_user_program_role: " . $conn->error);
        unset($recursion_protection[$call_key]);
        return false;
    }
    
    $stmt->bind_param("ii", $program_id, $agency_id);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Failed to execute statement in get_user_program_role: " . $stmt->error);
        unset($recursion_protection[$call_key]);
        return false;
    }
    
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $role = $row['role'];
        unset($recursion_protection[$call_key]);
        return $role;
    }

    unset($recursion_protection[$call_key]);
    return false;
}

/**
 * Check if user can edit a program at agency level only (no user restrictions)
 * This is used internally to avoid circular dependencies
 *
 * @param int $program_id Program ID
 * @param int $agency_id Agency ID (optional)
 * @return bool True if user can edit at agency level
 */
function can_edit_program_agency_level($program_id, $agency_id = null) {
    // Focal users can edit programs within their agency (check agency access first)
    if (is_focal_user()) {
        $focal_agency_id = $_SESSION['agency_id'] ?? null;
        if ($focal_agency_id) {
            $role = get_user_program_role($program_id, $focal_agency_id);
            if ($role) {
                return true; // Focal users can edit any program their agency has access to
            }
        }
        // If focal user's agency doesn't have access, follow normal rules
    }
    
    $role = get_user_program_role($program_id, $agency_id);
    return in_array($role, ['owner', 'editor']);
}

/**
 * Check if user can edit a program
 *
 * @param int $program_id Program ID
 * @param int $agency_id Agency ID (optional)
 * @return bool True if user can edit
 */
function can_edit_program($program_id, $agency_id = null) {
    // Check agency-level permissions first
    $can_edit_agency_level = can_edit_program_agency_level($program_id, $agency_id);
    
    // If no agency-level access, return false
    if (!$can_edit_agency_level) {
        return false;
    }
    
    // Check if user-level restrictions exist
    load_user_assignments_if_needed();
    if (function_exists('can_edit_program_with_user_restrictions')) {
        return can_edit_program_with_user_restrictions($program_id);
    }
    
    // Fallback to agency-level permissions
    return $can_edit_agency_level;
}

/**
 * Check if user can view a program
 *
 * @param int $program_id Program ID
 * @param int $agency_id Agency ID (optional)
 * @return bool True if user can view
 */
function can_view_program($program_id, $agency_id = null) {
    // Focal users can view programs within their agency (check agency access first)
    if (is_focal_user()) {
        $focal_agency_id = $_SESSION['agency_id'] ?? null;
        if ($focal_agency_id) {
            $role = get_user_program_role($program_id, $focal_agency_id);
            if ($role) {
                return true; // Focal users can view any program their agency has access to
            }
        }
        // If focal user's agency doesn't have access, follow normal rules
    }
    
    $role = get_user_program_role($program_id, $agency_id);
    return in_array($role, ['owner', 'editor', 'viewer']);
}

/**
 * Check if user is program owner
 *
 * @param int $program_id Program ID
 * @param int $agency_id Agency ID (optional)
 * @return bool True if user is owner
 */
function is_program_owner($program_id, $agency_id = null) {
    // Focal users are treated as owners for programs within their agency
    if (is_focal_user()) {
        $focal_agency_id = $_SESSION['agency_id'] ?? null;
        if ($focal_agency_id) {
            $role = get_user_program_role($program_id, $focal_agency_id);
            if ($role) {
                return true; // Focal users are owners of any program their agency has access to
            }
        }
        // If focal user's agency doesn't have access, follow normal rules
    }
    
    $role = get_user_program_role($program_id, $agency_id);
    return $role === 'owner';
}

/**
 * Get all agencies assigned to a program
 *
 * @param int $program_id Program ID
 * @return array Array of assigned agencies with their roles
 */
function get_program_assigned_agencies($program_id) {
    global $conn;
    
    $program_id = intval($program_id);
    if ($program_id <= 0) {
        return [];
    }
    
    $stmt = $conn->prepare("
        SELECT paa.*, a.agency_name, u.username as assigned_by_name, u.fullname as assigned_by_fullname
        FROM program_agency_assignments paa
        LEFT JOIN agency a ON paa.agency_id = a.agency_id
        LEFT JOIN users u ON paa.assigned_by = u.user_id
        WHERE paa.program_id = ? AND paa.is_active = 1
        ORDER BY 
            CASE paa.role 
                WHEN 'owner' THEN 1 
                WHEN 'editor' THEN 2 
                WHEN 'viewer' THEN 3 
                ELSE 4 
            END,
            a.agency_name
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
 * Assign an agency to a program
 *
 * @param int $program_id Program ID
 * @param int $agency_id Agency ID to assign
 * @param string $role Role to assign ('owner', 'editor', 'viewer')
 * @param string $notes Optional notes
 * @return array Success/error response
 */
function assign_agency_to_program($program_id, $agency_id, $role = 'viewer', $notes = '') {
    global $conn;
    
    // Validate input
    $program_id = intval($program_id);
    $agency_id = intval($agency_id);
    $assigned_by = $_SESSION['user_id'] ?? 0;
    
    if ($program_id <= 0 || $agency_id <= 0 || $assigned_by <= 0) {
        return ['error' => 'Invalid parameters'];
    }
    
    if (!in_array($role, ['owner', 'editor', 'viewer'])) {
        return ['error' => 'Invalid role'];
    }
    
    // Check if current user can assign agencies (must be owner, focal, or admin)
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
    
    // Check if assignment already exists
    $check_stmt = $conn->prepare("
        SELECT assignment_id FROM program_agency_assignments 
        WHERE program_id = ? AND agency_id = ?
    ");
    $check_stmt->bind_param("ii", $program_id, $agency_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        // Update existing assignment
        $update_stmt = $conn->prepare("
            UPDATE program_agency_assignments 
            SET role = ?, assigned_by = ?, updated_at = CURRENT_TIMESTAMP, 
                is_active = 1, notes = ?
            WHERE program_id = ? AND agency_id = ?
        ");
        $update_stmt->bind_param("sisii", $role, $assigned_by, $notes, $program_id, $agency_id);
        
        if ($update_stmt->execute()) {
            return ['success' => 'Agency assignment updated successfully'];
        } else {
            return ['error' => 'Failed to update assignment'];
        }
    } else {
        // Create new assignment
        $insert_stmt = $conn->prepare("
            INSERT INTO program_agency_assignments 
            (program_id, agency_id, role, assigned_by, notes) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert_stmt->bind_param("iisis", $program_id, $agency_id, $role, $assigned_by, $notes);
        
        if ($insert_stmt->execute()) {
            return ['success' => 'Agency assigned successfully'];
        } else {
            return ['error' => 'Failed to assign agency'];
        }
    }
}

/**
 * Remove agency assignment from program
 *
 * @param int $program_id Program ID
 * @param int $agency_id Agency ID to remove
 * @return array Success/error response
 */
function remove_agency_from_program($program_id, $agency_id) {
    global $conn;
    
    $program_id = intval($program_id);
    $agency_id = intval($agency_id);
    
    if ($program_id <= 0 || $agency_id <= 0) {
        return ['error' => 'Invalid parameters'];
    }
    
    // Check if current user can remove agencies (must be owner, focal, or admin)
    if (!is_admin() && !is_focal_user() && !is_program_owner($program_id)) {
        return ['error' => 'Permission denied'];
    }
    
    // Don't allow removing the last owner
    $owner_count_stmt = $conn->prepare("
        SELECT COUNT(*) as owner_count 
        FROM program_agency_assignments 
        WHERE program_id = ? AND role = 'owner' AND is_active = 1
    ");
    $owner_count_stmt->bind_param("i", $program_id);
    $owner_count_stmt->execute();
    $owner_count = $owner_count_stmt->get_result()->fetch_assoc()['owner_count'];
    
    $current_role = get_user_program_role($program_id, $agency_id);
    if ($current_role === 'owner' && $owner_count <= 1) {
        return ['error' => 'Cannot remove the last owner of the program'];
    }
    
    // Deactivate assignment instead of deleting for audit trail
    $stmt = $conn->prepare("
        UPDATE program_agency_assignments 
        SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
        WHERE program_id = ? AND agency_id = ?
    ");
    $stmt->bind_param("ii", $program_id, $agency_id);
    
    if ($stmt->execute()) {
        return ['success' => 'Agency access removed successfully'];
    } else {
        return ['error' => 'Failed to remove agency access'];
    }
}

/**
 * Get programs that an agency has access to
 *
 * @param int $agency_id Agency ID (optional, uses session if not provided)
 * @param string $role_filter Filter by role ('owner', 'editor', 'viewer')
 * @return array Array of programs with access details
 */
function get_agency_programs($agency_id = null, $role_filter = null) {
    global $conn;
    
    if ($agency_id === null) {
        $agency_id = $_SESSION['agency_id'] ?? 0;
    }
    $agency_id = intval($agency_id);
    
    if ($agency_id <= 0) {
        return [];
    }
    
    $role_condition = '';
    $params = [$agency_id];
    $param_types = 'i';
    
    if ($role_filter && in_array($role_filter, ['owner', 'editor', 'viewer'])) {
        $role_condition = 'AND paa.role = ?';
        $params[] = $role_filter;
        $param_types .= 's';
    }
    
    $stmt = $conn->prepare("
        SELECT p.*, paa.role, paa.assigned_at, a.agency_name as owner_agency_name
        FROM programs p
        INNER JOIN program_agency_assignments paa ON p.program_id = paa.program_id
        LEFT JOIN agency a ON p.agency_id = a.agency_id
        WHERE paa.agency_id = ? AND paa.is_active = 1 AND p.is_deleted = 0
        $role_condition
        ORDER BY paa.role, p.program_name
    ");
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    return $programs;
}
?>
