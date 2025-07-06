<?php
/**
 * Agency Initiative Functions
 * 
 * Functions for agency users to view initiatives they're involved with
 */

// Include database names helper for centralized table/column mapping
require_once dirname(__DIR__) . '/db_names_helper.php';

/**
 * Get initiatives that have programs assigned to the current agency
 */
function get_agency_initiatives($agency_id = null, $filters = []) {
    global $conn;
    
    // Use current session user if no agency_id provided
    if ($agency_id === null) {
        $agency_id = $_SESSION['user_id'];
    }
    
    // Build query using db_names helper
    $initiatives_table = get_table_name('initiatives');
    $programs_table = get_table_name('programs');
    
    $initiative_id_col = get_column_name('initiatives', 'id');
    $initiative_name_col = get_column_name('initiatives', 'name');
    $initiative_number_col = get_column_name('initiatives', 'number');
    $initiative_description_col = get_column_name('initiatives', 'description');
    $is_active_col = get_column_name('initiatives', 'is_active');
    $program_id_col = get_column_name('programs', 'id');
    $program_initiative_id_col = get_column_name('programs', 'initiative_id');
    $program_users_assigned_col = get_column_name('programs', 'users_assigned');
    
    $where_conditions = ["p.{$program_users_assigned_col} = ?"];
    $params = [$agency_id];
    $param_types = 'i';
    
    // Add filter conditions
    if (isset($filters['is_active'])) {
        $where_conditions[] = "i.{$is_active_col} = ?";
        $params[] = $filters['is_active'];
        $param_types .= 'i';
    }
    
    if (isset($filters['search']) && !empty($filters['search'])) {
        $where_conditions[] = "(i.{$initiative_name_col} LIKE ? OR i.{$initiative_number_col} LIKE ? OR i.{$initiative_description_col} LIKE ?)";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $param_types .= 'sss';
    }
    
    // Build query to get initiatives where agency has programs
    $sql = "SELECT DISTINCT i.*, 
                   COUNT(DISTINCT p.{$program_id_col}) as agency_program_count,
                   COUNT(DISTINCT all_p.{$program_id_col}) as total_program_count
            FROM {$initiatives_table} i
            INNER JOIN {$programs_table} p ON i.{$initiative_id_col} = p.{$program_initiative_id_col}
            LEFT JOIN {$programs_table} all_p ON i.{$initiative_id_col} = all_p.{$program_initiative_id_col}
            WHERE " . implode(' AND ', $where_conditions) . "
            GROUP BY i.{$initiative_id_col}
            ORDER BY i.{$initiative_name_col} ASC";
    
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
    
    // Build query using db_names helper
    $initiatives_table = get_table_name('initiatives');
    $programs_table = get_table_name('programs');
    
    $initiative_id_col = get_column_name('initiatives', 'id');
    $program_id_col = get_column_name('programs', 'id');
    $program_initiative_id_col = get_column_name('programs', 'initiative_id');
    $program_users_assigned_col = get_column_name('programs', 'users_assigned');
    
    // First check if agency has programs in this initiative
    $check_sql = "SELECT COUNT(*) as count FROM {$programs_table} WHERE {$program_initiative_id_col} = ? AND {$program_users_assigned_col} = ?";
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
                   COUNT(DISTINCT p.{$program_id_col}) as total_program_count,
                   COUNT(DISTINCT agency_p.{$program_id_col}) as agency_program_count
            FROM {$initiatives_table} i
            LEFT JOIN {$programs_table} p ON i.{$initiative_id_col} = p.{$program_initiative_id_col}
            LEFT JOIN {$programs_table} agency_p ON i.{$initiative_id_col} = agency_p.{$program_initiative_id_col} AND agency_p.{$program_users_assigned_col} = ?
            WHERE i.{$initiative_id_col} = ?
            GROUP BY i.{$initiative_id_col}";
    
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
    
    // Build query using db_names helper
    $programs_table = get_table_name('programs');
    $users_table = get_table_name('users');
    $agency_table = get_table_name('agency');
    $program_submissions_table = get_table_name('program_submissions');
    
    $program_id_col = get_column_name('programs', 'id');
    $program_name_col = get_column_name('programs', 'name');
    $program_initiative_id_col = get_column_name('programs', 'initiative_id');
    $program_users_assigned_col = get_column_name('programs', 'users_assigned');
    $user_id_col = get_column_name('users', 'id');
    $user_agency_id_col = get_column_name('users', 'agency_id');
    $agency_id_col = get_column_name('agency', 'id');
    $agency_name_col = get_column_name('agency', 'name');
    $submission_id_col = get_column_name('program_submissions', 'id');
    $submission_program_id_col = get_column_name('program_submissions', 'program_id');
    $submission_is_draft_col = get_column_name('program_submissions', 'is_draft');
    $submission_content_json_col = get_column_name('program_submissions', 'content_json');
    
    $sql = "SELECT p.*, a.{$agency_name_col},
                   COALESCE(latest_sub.{$submission_is_draft_col}, 1) as is_draft,
                   COALESCE(JSON_UNQUOTE(JSON_EXTRACT(latest_sub.{$submission_content_json_col}, '$.rating')), 'not-started') as rating,
                   (p.{$program_users_assigned_col} = ?) as is_owned_by_agency
            FROM {$programs_table} p
            LEFT JOIN {$users_table} u ON p.{$program_users_assigned_col} = u.{$user_id_col}
            LEFT JOIN {$agency_table} a ON u.{$user_agency_id_col} = a.{$agency_id_col}
            LEFT JOIN (
                SELECT ps1.*
                FROM {$program_submissions_table} ps1
                INNER JOIN (
                    SELECT {$submission_program_id_col}, MAX({$submission_id_col}) as max_submission_id
                    FROM {$program_submissions_table}
                    GROUP BY {$submission_program_id_col}
                ) ps2 ON ps1.{$submission_program_id_col} = ps2.{$submission_program_id_col} AND ps1.{$submission_id_col} = ps2.max_submission_id
            ) latest_sub ON p.{$program_id_col} = latest_sub.{$submission_program_id_col}
            WHERE p.{$program_initiative_id_col} = ?
            ORDER BY is_owned_by_agency DESC, p.{$program_name_col} ASC";
    
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
