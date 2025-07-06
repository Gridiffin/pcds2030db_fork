<?php
/**
 * Agency Initiative Functions
 * 
 * Functions for agency users to view initiatives they're involved with
 */

/**
 * Get initiatives that have programs assigned to the current agency
 */
function get_agency_initiatives($agency_id = null, $filters = []) {
    global $conn;
    
    // Use current session user if no agency_id provided
    if ($agency_id === null) {
        $agency_id = $_SESSION['user_id'];
    }
    
    $where_conditions = ['p.users_assigned = ?'];
    $params = [$agency_id];
    $param_types = 'i';
    
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
    
    // Build query to get initiatives where agency has programs
    $sql = "SELECT DISTINCT i.*, 
                   COUNT(DISTINCT p.program_id) as agency_program_count,
                   COUNT(DISTINCT all_p.program_id) as total_program_count
            FROM initiatives i
            INNER JOIN programs p ON i.initiative_id = p.initiative_id
            LEFT JOIN programs all_p ON i.initiative_id = all_p.initiative_id
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
 * Get detailed initiative information for agency view
 */
function get_agency_initiative_details($initiative_id, $agency_id = null) {
    global $conn;
    
    // Use current session user if no agency_id provided
    if ($agency_id === null) {
        $agency_id = $_SESSION['user_id'];
    }
    
    // First check if agency has programs in this initiative
    $check_sql = "SELECT COUNT(*) as count FROM programs WHERE initiative_id = ? AND users_assigned = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('ii', $initiative_id, $agency_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $access_check = $check_result->fetch_assoc();
    
    if ($access_check['count'] == 0) {
        return null; // Agency doesn't have access to this initiative
    }
    
    // Get initiative details
    $sql = "SELECT i.*, 
                   COUNT(DISTINCT p.program_id) as total_program_count,
                   COUNT(DISTINCT agency_p.program_id) as agency_program_count
            FROM initiatives i
            LEFT JOIN programs p ON i.initiative_id = p.initiative_id
            LEFT JOIN programs agency_p ON i.initiative_id = agency_p.initiative_id AND agency_p.users_assigned = ?
            WHERE i.initiative_id = ?
            GROUP BY i.initiative_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $agency_id, $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Get programs under an initiative with agency context
 */
function get_initiative_programs_for_agency($initiative_id, $agency_id = null) {
    global $conn;
    
    // Use current session user if no agency_id provided
    if ($agency_id === null) {
        $agency_id = $_SESSION['user_id'];
    }
    
    $sql = "SELECT p.*, a.agency_name,
                   COALESCE(latest_sub.is_draft, 1) as is_draft,
                   COALESCE(JSON_UNQUOTE(JSON_EXTRACT(latest_sub.content_json, '$.rating')), 'not-started') as rating,
                   (p.users_assigned = ?) as is_owned_by_agency
            FROM programs p
            LEFT JOIN users u ON p.users_assigned = u.user_id
            LEFT JOIN agency a ON u.agency_id = a.agency_id
            LEFT JOIN (
                SELECT ps1.*
                FROM program_submissions ps1
                INNER JOIN (
                    SELECT program_id, MAX(submission_id) as max_submission_id
                    FROM program_submissions
                    GROUP BY program_id
                ) ps2 ON ps1.program_id = ps2.program_id AND ps1.submission_id = ps2.max_submission_id
            ) latest_sub ON p.program_id = latest_sub.program_id
            WHERE p.initiative_id = ?
            ORDER BY is_owned_by_agency DESC, p.program_name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $agency_id, $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    return $programs;
}
?>
