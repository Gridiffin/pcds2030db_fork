<?php
/**
 * Admin Program Management Functions
 * 
 * Functions for admin users to manage programs across all agencies
 */

// Include audit logging functions
require_once __DIR__ . '/../audit_log.php';

/**
 * Get all assignable users from all agencies for admin program management
 * 
 * @param int $program_id Program ID
 * @return array Array of assignable users with agency information
 */
function get_all_assignable_users_for_program($program_id) {
    global $conn;
    
    $program_id = intval($program_id);
    
    // Get all users from all agencies with their current assignment status
    $stmt = $conn->prepare("
        SELECT u.user_id, u.username, u.fullname, u.agency_id, u.role as user_role, 
               a.agency_name,
               pua.role as current_role,
               CASE WHEN pua.assignment_id IS NOT NULL THEN 1 ELSE 0 END as is_assigned
        FROM users u
        LEFT JOIN agency a ON u.agency_id = a.agency_id
        LEFT JOIN program_user_assignments pua ON u.user_id = pua.user_id 
            AND pua.program_id = ? AND pua.is_active = 1
        WHERE u.role IN ('agency', 'focal')
            AND u.is_active = 1
        ORDER BY a.agency_name, u.fullname, u.username
    ");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    return $users;
}

/**
 * Get agency information by agency ID
 * 
 * @param int $agency_id Agency ID
 * @return array|null Agency information or null if not found
 */
function get_agency_info($agency_id) {
    global $conn;
    
    if (!$agency_id) {
        return null;
    }
    
    $agency_id = intval($agency_id);
    
    $stmt = $conn->prepare("
        SELECT agency_id, agency_name
        FROM agency 
        WHERE agency_id = ?
    ");
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row;
    }
    
    return null;
}

/**
 * Update program with admin privileges (can update across agencies)
 * 
 * @param array $program_data Program data to update
 * @return array Result with success/error information
 */
function update_admin_program($program_data) {
    global $conn;
    
    $program_id = intval($program_data['program_id']);
    
    if ($program_id <= 0) {
        return ['success' => false, 'error' => 'Invalid program ID'];
    }
    
    // Validate required fields
    if (empty($program_data['program_name'])) {
        return ['success' => false, 'error' => 'Program name is required'];
    }
    
    // Prepare data for update
    $program_name = trim($program_data['program_name']);
    $program_number = trim($program_data['program_number'] ?? '');
    $brief_description = trim($program_data['brief_description'] ?? '');
    $start_date = !empty($program_data['start_date']) ? $program_data['start_date'] : null;
    $end_date = !empty($program_data['end_date']) ? $program_data['end_date'] : null;
    $initiative_id = !empty($program_data['initiative_id']) ? intval($program_data['initiative_id']) : null;
    $rating = $program_data['rating'] ?? 'not_started';
    
    // Get current program data for audit logging
    $current_stmt = $conn->prepare("SELECT * FROM programs WHERE program_id = ?");
    $current_stmt->bind_param("i", $program_id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();
    $old_data = $current_result->fetch_assoc();
    
    if (!$old_data) {
        return ['success' => false, 'error' => 'Program not found'];
    }
    
    // Handle program number auto-generation if empty but initiative is selected
    if (empty($program_number) && !empty($initiative_id)) {
        $program_number = generate_next_program_number($initiative_id);
    }
    
    // Update program
    $stmt = $conn->prepare("
        UPDATE programs 
        SET program_name = ?, 
            program_number = ?,
            program_description = ?, 
            start_date = ?, 
            end_date = ?, 
            initiative_id = ?,
            rating = ?,
            updated_at = NOW()
        WHERE program_id = ?
    ");
    
    $stmt->bind_param("sssssssi", 
        $program_name, 
        $program_number,
        $brief_description, 
        $start_date, 
        $end_date, 
        $initiative_id,
        $rating,
        $program_id
    );
    
    if ($stmt->execute()) {
        // Log the update for audit
        $audit_details = [
            'program_id' => $program_id,
            'program_name' => $program_name,
            'updated_by_admin' => true,
            'admin_user_id' => $_SESSION['user_id'] ?? null
        ];
        
        // Log audit entry
        if (function_exists('log_audit')) {
            $audit_log_id = log_audit('update_program', $audit_details, $program_id);
            
            // Log field changes if audit logging is available
            if (function_exists('log_field_changes') && $audit_log_id) {
                $new_data = [
                    'program_name' => $program_name,
                    'program_number' => $program_number,
                    'program_description' => $brief_description,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'initiative_id' => $initiative_id,
                    'rating' => $rating
                ];
                
                // Create field changes array by comparing old and new data
                $field_changes = [];
                foreach ($new_data as $field => $new_value) {
                    $old_value = $old_data[$field] ?? null;
                    // Check if the value actually changed
                    if ($old_value !== $new_value) {
                        $field_changes[] = [
                            'field_name' => $field,
                            'field_type' => get_field_type($new_value),
                            'old_value' => $old_value,
                            'new_value' => $new_value,
                            'change_type' => $old_value === null ? 'added' : 'modified'
                        ];
                    }
                }
                
                // Check for removed fields
                foreach ($old_data as $field => $old_value) {
                    if (!array_key_exists($field, $new_data) && $old_value !== null) {
                        $field_changes[] = [
                            'field_name' => $field,
                            'field_type' => get_field_type($old_value),
                            'old_value' => $old_value,
                            'new_value' => null,
                            'change_type' => 'removed'
                        ];
                    }
                }
                
                // Only log if there are actual changes
                if (!empty($field_changes)) {
                    log_field_changes($audit_log_id, $field_changes);
                }
            }
        }
        
        return [
            'success' => true, 
            'message' => 'Program updated successfully',
            'program_id' => $program_id
        ];
    } else {
        return [
            'success' => false, 
            'error' => 'Failed to update program: ' . $conn->error
        ];
    }
}
?>
