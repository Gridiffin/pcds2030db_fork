<?php
/**
 * Initiative Functions
 * 
 * Helper functions for managing initiatives in the admin interface
 */

// Include numbering helpers for hierarchical program numbering
require_once __DIR__ . '/numbering_helpers.php';

// Load database configuration directly
$config = include __DIR__ . '/../config/db_names.php';
if (!$config || !isset($config['tables']['initiatives'])) {
    die('Config not loaded or missing initiatives table definition.');
}

// Extract table and column names
$initiativesTable = $config['tables']['initiatives'];
$programsTable = $config['tables']['programs'];
$usersTable = $config['tables']['users'];
$agencyTable = $config['tables']['agency'];

// Initiative columns
$initiativeIdCol = $config['columns']['initiatives']['id'];
$initiativeNameCol = $config['columns']['initiatives']['name'];
$initiativeNumberCol = $config['columns']['initiatives']['number'];
$initiativeDescriptionCol = $config['columns']['initiatives']['description'];
$initiativeStartDateCol = $config['columns']['initiatives']['start_date'];
$initiativeEndDateCol = $config['columns']['initiatives']['end_date'];
$initiativeIsActiveCol = $config['columns']['initiatives']['is_active'];
$initiativeCreatedByCol = $config['columns']['initiatives']['created_by'];
$initiativeCreatedAtCol = $config['columns']['initiatives']['created_at'];
$initiativeUpdatedAtCol = $config['columns']['initiatives']['updated_at'];

// Program columns
$programIdCol = $config['columns']['programs']['id'];
$programNameCol = $config['columns']['programs']['name'];
$programNumberCol = $config['columns']['programs']['number'];
$programInitiativeIdCol = $config['columns']['programs']['initiative_id'];
$programUsersAssignedCol = $config['columns']['programs']['users_assigned'];

// User columns
$userIdCol = $config['columns']['users']['id'];
$userUsernameCol = $config['columns']['users']['username'];
$userAgencyIdCol = $config['columns']['users']['agency_id'];

// Agency columns
$agencyIdCol = $config['columns']['agency']['id'];
$agencyNameCol = $config['columns']['agency']['name'];

/**
 * Get all initiatives with optional filtering
 */
function get_all_initiatives($filters = []) {
    global $conn, $initiativesTable, $usersTable, $programsTable;
    global $initiativeIdCol, $initiativeNameCol, $initiativeIsActiveCol, $initiativeDescriptionCol, $initiativeNumberCol, $initiativeCreatedByCol;
    global $userUsernameCol, $userIdCol, $programInitiativeIdCol;
    
    $where_conditions = ['1=1'];
    $params = [];
    $param_types = '';
    
    // Add filter conditions
    if (isset($filters['is_active'])) {
        $where_conditions[] = "i.{$initiativeIsActiveCol} = ?";
        $params[] = $filters['is_active'];
        $param_types .= 'i';
    }
    
    if (isset($filters['search']) && !empty($filters['search'])) {
        $where_conditions[] = "(i.{$initiativeNameCol} LIKE ? OR i.{$initiativeNumberCol} LIKE ? OR i.{$initiativeDescriptionCol} LIKE ?)";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $param_types .= 'sss';
    }
    
    $sql = "SELECT i.*, u.{$userUsernameCol} as created_by_username,
                   (SELECT COUNT(*) FROM {$programsTable} WHERE {$programInitiativeIdCol} = i.{$initiativeIdCol}) as program_count
            FROM {$initiativesTable} i
            LEFT JOIN {$usersTable} u ON i.{$initiativeCreatedByCol} = u.{$userIdCol}
            WHERE " . implode(' AND ', $where_conditions) . "
            ORDER BY i.{$initiativeNameCol} ASC";
    
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
    global $conn, $initiativesTable, $usersTable, $programsTable;
    global $initiativeIdCol, $initiativeCreatedByCol, $userIdCol, $userUsernameCol, $programInitiativeIdCol;
    
    $sql = "SELECT i.*, u.{$userUsernameCol} as created_by_username,
                   (SELECT COUNT(*) FROM {$programsTable} WHERE {$programInitiativeIdCol} = i.{$initiativeIdCol}) as program_count
            FROM {$initiativesTable} i
            LEFT JOIN {$usersTable} u ON i.{$initiativeCreatedByCol} = u.{$userIdCol}
            WHERE i.{$initiativeIdCol} = ?";
    
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
    global $conn, $initiativesTable, $initiativeNameCol, $initiativeNumberCol, $initiativeDescriptionCol, $initiativeStartDateCol, $initiativeEndDateCol, $initiativeIsActiveCol, $initiativeCreatedByCol;
    global $initiativeIdCol;

    // Validate required fields
    $required_fields = ['initiative_name'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return ['error' => "Field '{$field}' is required"];
        }
    }

    // Check for duplicate initiative name or number
    $check_sql = "SELECT {$initiativeIdCol} FROM {$initiativesTable} WHERE {$initiativeNameCol} = ?";
    $params = [$data['initiative_name']];
    $param_types = 's';

    if (!empty($data['initiative_number'])) {
        $check_sql .= " OR {$initiativeNumberCol} = ?";
        $params[] = $data['initiative_number'];
        $param_types .= 's';
    }

    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param($param_types, ...$params);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        return ['error' => 'Initiative with this name or number already exists'];
    }

    $sql = "INSERT INTO {$initiativesTable} ({$initiativeNameCol}, {$initiativeNumberCol}, {$initiativeDescriptionCol}, {$initiativeStartDateCol}, {$initiativeEndDateCol}, {$initiativeIsActiveCol}, {$initiativeCreatedByCol}) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $initiative_name = $data['initiative_name'];
    $initiative_number = $data['initiative_number'] ?? null;
    $initiative_description = $data['initiative_description'] ?? null;
    $start_date = $data['start_date'] ?? null;
    $end_date = $data['end_date'] ?? null;
    $is_active = $data['is_active'] ?? 1;
    $created_by = $_SESSION['user_id'] ?? 1;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssii', $initiative_name, $initiative_number, $initiative_description, $start_date, $end_date, $is_active, $created_by);

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
    global $conn, $initiativesTable, $initiativeIdCol, $initiativeNameCol, $initiativeNumberCol, $initiativeDescriptionCol, $initiativeStartDateCol, $initiativeEndDateCol, $initiativeIsActiveCol, $initiativeUpdatedAtCol;

    // Validate required fields
    $required_fields = ['initiative_name'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return ['error' => "Field '{$field}' is required"];
        }
    }

    // Check if initiative exists
    $check_sql = "SELECT {$initiativeIdCol} FROM {$initiativesTable} WHERE {$initiativeIdCol} = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $initiative_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        return ['error' => 'Initiative not found'];
    }

    // Check for duplicate name/number (excluding current initiative)
    $dup_sql = "SELECT {$initiativeIdCol} FROM {$initiativesTable} WHERE ({$initiativeNameCol} = ?";
    $dup_params = [$data['initiative_name']];
    $dup_types = 's';

    if (!empty($data['initiative_number'])) {
        $dup_sql .= " OR {$initiativeNumberCol} = ?";
        $dup_params[] = $data['initiative_number'];
        $dup_types .= 's';
    }

    $dup_sql .= ") AND {$initiativeIdCol} != ?";
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
    $current_query = "SELECT {$initiativeNumberCol} FROM {$initiativesTable} WHERE {$initiativeIdCol} = ?";
    $current_stmt = $conn->prepare($current_query);
    $current_stmt->bind_param('i', $initiative_id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();
    $current_initiative = $current_result->fetch_assoc();
    $current_number = $current_initiative[$initiativeNumberCol];

    // Update initiative
    $sql = "UPDATE {$initiativesTable} SET {$initiativeNameCol} = ?, {$initiativeNumberCol} = ?, {$initiativeDescriptionCol} = ?, {$initiativeStartDateCol} = ?, {$initiativeEndDateCol} = ?, {$initiativeIsActiveCol} = ?, {$initiativeUpdatedAtCol} = CURRENT_TIMESTAMP WHERE {$initiativeIdCol} = ?";

    $initiative_name = $data['initiative_name'];
    $initiative_number = $data['initiative_number'] ?? null;
    $initiative_description = $data['initiative_description'] ?? null;
    $start_date = $data['start_date'] ?? null;
    $end_date = $data['end_date'] ?? null;
    $is_active = $data['is_active'] ?? 1;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssii', $initiative_name, $initiative_number, $initiative_description, $start_date, $end_date, $is_active, $initiative_id);

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
    global $conn, $initiativesTable, $initiativeIdCol, $initiativeIsActiveCol, $initiativeUpdatedAtCol;

    $sql = "SELECT {$initiativeIsActiveCol} FROM {$initiativesTable} WHERE {$initiativeIdCol} = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['error' => 'Initiative not found'];
    }

    $current_status = $result->fetch_assoc()[$initiativeIsActiveCol];
    $new_status = $current_status ? 0 : 1;

    $update_sql = "UPDATE {$initiativesTable} SET {$initiativeIsActiveCol} = ?, {$initiativeUpdatedAtCol} = CURRENT_TIMESTAMP WHERE {$initiativeIdCol} = ?";
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
    global $conn, $programsTable, $usersTable, $agencyTable;
    global $programIdCol, $programNameCol, $programNumberCol, $programInitiativeIdCol, $programUsersAssignedCol;
    global $userIdCol, $userAgencyIdCol, $agencyIdCol, $agencyNameCol;

    $sql = "SELECT p.{$programIdCol}, p.{$programNameCol}, p.{$programNumberCol}, a.{$agencyNameCol}
            FROM {$programsTable} p
            LEFT JOIN {$usersTable} u ON p.{$programUsersAssignedCol} = u.{$userIdCol}
            LEFT JOIN {$agencyTable} a ON u.{$userAgencyIdCol} = a.{$agencyIdCol}
            WHERE p.{$programInitiativeIdCol} = ?
            ORDER BY p.{$programNameCol}";

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
    global $conn, $initiativesTable, $initiativeIdCol, $initiativeNameCol, $initiativeNumberCol, $initiativeIsActiveCol;

    $sql = "SELECT {$initiativeIdCol}, {$initiativeNameCol}, {$initiativeNumberCol} FROM {$initiativesTable}";
    if ($active_only) {
        $sql .= " WHERE {$initiativeIsActiveCol} = 1";
    }
    $sql .= " ORDER BY {$initiativeNameCol} ASC";

    $result = $conn->query($sql);
    $initiatives = [];
    while ($row = $result->fetch_assoc()) {
        $initiatives[] = $row;
    }
    return $initiatives;
}
?>
