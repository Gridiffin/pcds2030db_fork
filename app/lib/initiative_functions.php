<?php
/**
 * Initiative Functions
 * 
 * Helper functions for managing initiatives in the admin interface
 */

// Include numbering helpers for hierarchical program numbering
require_once __DIR__ . '/numbering_helpers.php';

/**
 * Get all initiatives with optional filtering
 */
function get_all_initiatives($filters = []) {
    global $conn;
    
    $where_conditions = ['1=1'];
    $params = [];
    $param_types = '';
      // Add filter conditions
    if (isset($filters['is_active'])) {
        $where_conditions[] = 'i.is_active = ?';
        $params[] = $filters['is_active'];
        $param_types .= 'i';
    }
    
    if (isset($filters['search']) && !empty($filters['search'])) {
        $where_conditions[] = '(i.initiative_name LIKE ? OR i.initiative_number LIKE ? OR i.initiative_description LIKE ?)';
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $param_types .= 'sss';
    }
    
    // Build query
    $sql = "SELECT i.*, u.username as created_by_username,
                   COUNT(p.program_id) as program_count
            FROM initiatives i
            LEFT JOIN users u ON i.created_by = u.user_id
            LEFT JOIN programs p ON i.initiative_id = p.initiative_id
            WHERE " . implode(' AND ', $where_conditions) . "
            GROUP BY i.initiative_id
            ORDER BY i.initiative_name ASC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $initiatives = [];
    while ($row = $result->fetch_assoc()) {
        $initiatives[] = $row;
    }
    
    return $initiatives;
}

/**
 * Get single initiative by ID
 */
function get_initiative_by_id($initiative_id) {
    global $conn;
    
    $sql = "SELECT i.*, u.username as created_by_username,
                   COUNT(p.program_id) as program_count
            FROM initiatives i
            LEFT JOIN users u ON i.created_by = u.user_id
            LEFT JOIN programs p ON i.initiative_id = p.initiative_id
            WHERE i.initiative_id = ?
            GROUP BY i.initiative_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Create new initiative
 */
function create_initiative($data) {
    global $conn;
    
    // Validate required fields
    $required_fields = ['initiative_name'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return ['error' => "Field '{$field}' is required"];
        }
    }
    
    // Check for duplicate initiative name or number
    $check_sql = "SELECT initiative_id FROM initiatives WHERE initiative_name = ?";
    $params = [$data['initiative_name']];
    $param_types = 's';
    
    if (!empty($data['initiative_number'])) {
        $check_sql .= " OR initiative_number = ?";
        $params[] = $data['initiative_number'];
        $param_types .= 's';
    }
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param($param_types, ...$params);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        return ['error' => 'Initiative with this name or number already exists'];
    }    // Insert new initiative
    $sql = "INSERT INTO initiatives (initiative_name, initiative_number, initiative_description, 
                                   start_date, end_date, is_active, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    // Prepare variables for bind_param (must be actual variables, not expressions)
    $initiative_name = $data['initiative_name'];
    $initiative_number = $data['initiative_number'] ?? null;
    $initiative_description = $data['initiative_description'] ?? null;
    $start_date = $data['start_date'] ?? null;
    $end_date = $data['end_date'] ?? null;
    $is_active = $data['is_active'] ?? 1;
    $created_by = $_SESSION['user_id'] ?? 1;
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssii', 
        $initiative_name,
        $initiative_number,
        $initiative_description,
        $start_date,
        $end_date,
        $is_active,
        $created_by
    );
    
    if ($stmt->execute()) {
        $initiative_id = $conn->insert_id;
        return ['success' => true, 'initiative_id' => $initiative_id];
    } else {
        return ['error' => 'Failed to create initiative: ' . $conn->error];
    }
}

/**
 * Update existing initiative
 */
function update_initiative($initiative_id, $data) {
    global $conn;
    
    // Validate required fields
    $required_fields = ['initiative_name'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return ['error' => "Field '{$field}' is required"];
        }
    }
    
    // Check if initiative exists
    $check_sql = "SELECT initiative_id FROM initiatives WHERE initiative_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $initiative_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        return ['error' => 'Initiative not found'];
    }
    
    // Check for duplicate name/number (excluding current initiative)
    $dup_sql = "SELECT initiative_id FROM initiatives WHERE (initiative_name = ?";
    $dup_params = [$data['initiative_name']];
    $dup_types = 's';
    
    if (!empty($data['initiative_number'])) {
        $dup_sql .= " OR initiative_number = ?";
        $dup_params[] = $data['initiative_number'];
        $dup_types .= 's';
    }
    
    $dup_sql .= ") AND initiative_id != ?";
    $dup_params[] = $initiative_id;
    $dup_types .= 'i';
    
    $dup_stmt = $conn->prepare($dup_sql);
    $dup_stmt->bind_param($dup_types, ...$dup_params);
    $dup_stmt->execute();
    $dup_result = $dup_stmt->get_result();
      if ($dup_result->num_rows > 0) {
        return ['error' => 'Another initiative with this name or number already exists'];
    }
    
    // Get current initiative number to check if it's changing
    $current_query = "SELECT initiative_number FROM initiatives WHERE initiative_id = ?";
    $current_stmt = $conn->prepare($current_query);
    $current_stmt->bind_param('i', $initiative_id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();
    $current_initiative = $current_result->fetch_assoc();
    $current_number = $current_initiative['initiative_number'];
    
    // Update initiative
    $sql = "UPDATE initiatives            SET initiative_name = ?, initiative_number = ?, initiative_description = ?, 
                start_date = ?, end_date = ?, is_active = ?, 
                updated_at = CURRENT_TIMESTAMP 
            WHERE initiative_id = ?";
    
    // Prepare variables for bind_param (must be actual variables, not expressions)
    $initiative_name = $data['initiative_name'];
    $initiative_number = $data['initiative_number'] ?? null;
    $initiative_description = $data['initiative_description'] ?? null;
    $start_date = $data['start_date'] ?? null;
    $end_date = $data['end_date'] ?? null;
    $is_active = $data['is_active'] ?? 1;
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssii', 
        $initiative_name,
        $initiative_number,
        $initiative_description,
        $start_date,
        $end_date,
        $is_active,
        $initiative_id
    );
    
    if ($stmt->execute()) {
        // If initiative number changed and there are programs, update their numbers
        if ($current_number !== $initiative_number && $initiative_number) {
            $update_result = update_initiative_program_numbers($initiative_id, $initiative_number);
            if ($update_result['success']) {
                return [
                    'success' => true,
                    'cascade_update' => true,
                    'programs_updated' => $update_result['updated_count'],
                    'message' => $update_result['message']
                ];
            } else {
                // Initiative was updated but program numbers failed
                return [
                    'success' => true,
                    'cascade_error' => true,
                    'message' => 'Initiative updated but failed to update program numbers: ' . $update_result['error']
                ];
            }
        }
        
        return ['success' => true];
    } else {
        return ['error' => 'Failed to update initiative: ' . $conn->error];
    }
}

/**
 * Toggle initiative active status
 */
function toggle_initiative_status($initiative_id) {
    global $conn;
    
    // Get current status
    $sql = "SELECT is_active FROM initiatives WHERE initiative_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['error' => 'Initiative not found'];
    }
    
    $current_status = $result->fetch_assoc()['is_active'];
    $new_status = $current_status ? 0 : 1;
    
    // Update status
    $update_sql = "UPDATE initiatives SET is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE initiative_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ii', $new_status, $initiative_id);
    
    if ($update_stmt->execute()) {
        return ['success' => true, 'new_status' => $new_status];
    } else {
        return ['error' => 'Failed to update initiative status'];
    }
}

/**
 * Get programs associated with an initiative
 */
function get_initiative_programs($initiative_id) {
    global $conn;
    
    $sql = "SELECT p.program_id, p.program_name, p.program_number,
                   a.agency_name
            FROM programs p
            LEFT JOIN users u ON p.users_assigned = u.user_id
            LEFT JOIN agency a ON u.agency_id = a.agency_id
            WHERE p.initiative_id = ?
            ORDER BY p.program_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    return $programs;
}

/**
 * Get initiatives for dropdown/select options
 */
function get_initiatives_for_select($active_only = true) {
    global $conn;
    
    $sql = "SELECT initiative_id, initiative_name, initiative_number 
            FROM initiatives";
    
    if ($active_only) {
        $sql .= " WHERE is_active = 1";
    }
    
    $sql .= " ORDER BY initiative_name ASC";
    
    $result = $conn->query($sql);
    
    $initiatives = [];
    while ($row = $result->fetch_assoc()) {
        $initiatives[] = $row;
    }
    
    return $initiatives;
}
?>
