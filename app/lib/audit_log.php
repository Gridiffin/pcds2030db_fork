<?php
/**
 * Audit Log Functions
 * 
 * Centralized audit logging system for tracking user activities and system events.
 * This module provides secure, parameterized logging with proper error handling.
 */

/**
 * Log an audit action to the database
 * 
 * @param string $action The action being performed (e.g., 'login', 'create_program', 'delete_user')
 * @param string $details Additional details about the action (optional)
 * @param string $status The status of the action ('success' or 'failure')
 * @param int $user_id Override user ID (optional, defaults to current session user)
 * @return int|bool Audit log ID if logged successfully, false otherwise
 */
function log_audit_action($action, $details = '', $status = 'success', $user_id = null) {
    global $conn;

    // Validate inputs
    if (empty($action)) {
        error_log("Audit log error: Action cannot be empty");
        return false;
    }

    if (!in_array($status, ['success', 'failure'])) {
        error_log("Audit log error: Invalid status '$status'");
        return false;
    }

    // Get user ID from session if not provided
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? 0;
    }

    // Get client information
    $ip_address = get_client_ip();

    // Load database mappings
    $db_mappings = include __DIR__ . '/../config/db_names.php';
    $tables = $db_mappings['tables'];
    $columns = $db_mappings['columns'];
    
    $audit_logs_table = $tables['audit_logs'];
    $user_id_col = $columns['audit_logs']['user_id'];
    
    // Prepare the SQL statement
    $sql = "INSERT INTO $audit_logs_table ($user_id_col, action, details, ip_address, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";

    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Audit log error: Failed to prepare statement - " . $conn->error);
            return false;
        }

        $stmt->bind_param('issss', $user_id, $action, $details, $ip_address, $status);
        $result = $stmt->execute();

        if (!$result) {
            error_log("Audit log error: Failed to execute statement - " . $stmt->error);
        }

        $audit_log_id = $conn->insert_id;
        $stmt->close();
        return $audit_log_id;

    } catch (Exception $e) {
        error_log("Audit log error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}

/**
 * Get audit logs with filtering and pagination
 * 
 * @param array $filters Associative array of filters
 * @param int $limit Number of records to return (default: 50)
 * @param int $offset Offset for pagination (default: 0)
 * @return array Array containing logs and pagination info
 */
function get_audit_logs($filters = [], $limit = 50, $offset = 0) {
    global $conn;

    $conditions = [];
    $params = [];
    $param_types = "";

    // Build WHERE conditions based on filters
    if (!empty($filters['date_from'])) {
        $conditions[] = "al.created_at >= ?";
        $params[] = $filters['date_from'] . ' 00:00:00';
        $param_types .= 's';
    }

    if (!empty($filters['date_to'])) {
        $conditions[] = "al.created_at <= ?";
        $params[] = $filters['date_to'] . ' 23:59:59';
        $param_types .= 's';
    }

    if (!empty($filters['action_type'])) {
        $conditions[] = "al.action = ?";
        $params[] = $filters['action_type'];
        $param_types .= 's';
    }    if (!empty($filters['user'])) {
        $conditions[] = "(a.agency_name LIKE ? OR u.username LIKE ? OR u.fullname LIKE ?)";
        $search_term = '%' . $filters['user'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $param_types .= 'sss';
        error_log("Searching audit logs for user: " . $filters['user']);
    }

    if (!empty($filters['status'])) {
        $conditions[] = "al.status = ?";
        $params[] = $filters['status'];
        $param_types .= 's';
    }

    if (!empty($filters['user_id'])) {
        $conditions[] = "al.user_id = ?";
        $params[] = $filters['user_id'];
        $param_types .= 'i';
    }

    // Build the WHERE clause
    $where_clause = '';
    if (!empty($conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $conditions);
    }

    // Load database mappings for count query
    $db_mappings = include __DIR__ . '/../config/db_names.php';
    $tables = $db_mappings['tables'];
    $columns = $db_mappings['columns'];
    
    $audit_logs_table = $tables['audit_logs'];
    $users_table = $tables['users'];
    $agency_table = $tables['agency'];
    $user_id_col = $columns['audit_logs']['user_id'];
    $agency_id_col = $columns['users']['agency_id'];
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total 
                  FROM $audit_logs_table al 
                  LEFT JOIN $users_table u ON al.$user_id_col = u.user_id 
                  LEFT JOIN $agency_table a ON u.$agency_id_col = a.agency_id 
                  $where_clause";

    $total_records = 0;
    try {
        if (!empty($params)) {
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param($param_types, ...$params);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $total_records = $count_result->fetch_assoc()['total'];
            $count_stmt->close();
        } else {
            $count_result = $conn->query($count_sql);
            $total_records = $count_result->fetch_assoc()['total'];
        }
    } catch (Exception $e) {
        error_log("Error fetching total count for audit logs: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return [
            'logs' => [],
            'total' => 0,
            'error' => 'Failed to fetch audit logs'
        ];
    }

    // Get additional mapped column names for main query
    $audit_field_changes_table = $tables['audit_field_changes'];
    $agency_name_col = $columns['agency']['name'];
    $username_col = $columns['users']['username'];
    $role_col = $columns['users']['role'];
    
    // Main query with joins - enhanced to include field changes
    $sql = "SELECT 
                al.id,
                al.$user_id_col,
                al.action,
                al.details,
                al.ip_address,
                al.status,
                al.created_at,
                COALESCE(a.$agency_name_col, u.$username_col, 'System') as user_name,
                u.$role_col,
                COUNT(afc.change_id) as field_changes_count,
                GROUP_CONCAT(
                    CONCAT(afc.field_name, ':', afc.change_type, ':', 
                           COALESCE(afc.old_value, 'NULL'), '->', 
                           COALESCE(afc.new_value, 'NULL')
                    ) SEPARATOR '|'
                ) as field_changes_summary
            FROM $audit_logs_table al
            LEFT JOIN $users_table u ON al.$user_id_col = u.user_id
            LEFT JOIN $agency_table a ON u.$agency_id_col = a.agency_id
            LEFT JOIN $audit_field_changes_table afc ON al.id = afc.audit_log_id
            $where_clause
            GROUP BY al.id, al.$user_id_col, al.action, al.details, al.ip_address, al.status, al.created_at, u.$username_col, u.fullname, a.$agency_name_col
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?";

    // Add limit and offset to params
    $params[] = $limit;
    $params[] = $offset;
    $param_types .= 'ii';

    $logs = [];

    try {
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($param_types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }

        $stmt->close();

    } catch (Exception $e) {
        error_log("Error fetching audit logs: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return [
            'logs' => [],
            'total' => 0,
            'error' => 'Failed to fetch audit logs'
        ];
    }

    return [
        'logs' => $logs,
        'total' => $total_records,
        'limit' => $limit,
        'offset' => $offset
    ];
}

/**
 * Get client IP address
 * Handles various proxy scenarios
 * 
 * @return string Client IP address
 */
function get_client_ip() {
    $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ip = $_SERVER[$key];
            if (!empty($ip)) {
                // Handle comma-separated list (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Helper function to log login attempts
 * 
 * @param string $username User username
 * @param bool $success Whether login was successful
 * @param string $reason Failure reason (if applicable)
 * @param int $user_id User ID (if login successful)
 */
function log_login_attempt($username, $success = true, $reason = '', $user_id = null) {
    $action = $success ? 'login_success' : 'login_failure';
    $status = $success ? 'success' : 'failure';
    $details = "Username: $username" . ($reason ? " | Reason: $reason" : '');
    
    log_audit_action($action, $details, $status, $user_id);
}

/**
 * Helper function to log logout
 * 
 * @param int $user_id User ID
 */
function log_logout($user_id = null) {
    log_audit_action('logout', 'User logged out', 'success', $user_id);
}

/**
 * Helper function to log data operations
 * 
 * @param string $operation Operation type (create, update, delete)
 * @param string $entity Entity type (program, user, outcome, etc.)
 * @param int $entity_id ID of the affected entity
 * @param array $changes Array of changes made (optional)
 * @param int $user_id User performing the action
 */
function log_data_operation($operation, $entity, $entity_id, $changes = [], $user_id = null) {
    $action = "{$operation}_{$entity}";
    
    // Generate better details
    $entity_name = get_entity_name($entity, $entity_id);
    $entity_display = $entity_name ? $entity_name : "ID: $entity_id";
    
    switch ($operation) {
        case 'create':
            $details = "Created new $entity: $entity_display";
            break;
        case 'update':
            $details = "Updated $entity: $entity_display";
            break;
        case 'delete':
            $details = "Deleted $entity: $entity_display";
            break;
        default:
            $details = "$entity: $entity_display";
    }
    
    if (!empty($changes)) {
        $details .= " | Changes: " . json_encode($changes);
    }
    
    $audit_log_id = log_audit_action($action, $details, 'success', $user_id);
    
    // If we have field changes and a valid audit log ID, log the field changes
    if ($audit_log_id && !empty($changes) && is_array($changes)) {
        log_field_changes($audit_log_id, $changes);
    }
    
    return $audit_log_id;
}

/**
 * Enhanced function to log data operations with detailed field tracking
 * 
 * @param string $operation Operation type (create, update, delete)
 * @param string $entity Entity type (program, user, outcome, etc.)
 * @param int $entity_id ID of the affected entity
 * @param array $old_data Old data before changes (for updates)
 * @param array $new_data New data after changes
 * @param int $user_id User performing the action
 * @return int|bool Audit log ID if successful, false otherwise
 */
function log_detailed_data_operation($operation, $entity, $entity_id, $old_data = [], $new_data = [], $user_id = null) {
    global $conn;
    
    $action = "{$operation}_{$entity}";
    
    // Generate meaningful details based on operation and entity
    $details = generate_audit_details($operation, $entity, $entity_id, $old_data, $new_data);
    
    // Calculate field changes
    $field_changes = [];
    
    if ($operation === 'create') {
        // For create operations, all fields are new
        foreach ($new_data as $field => $value) {
            if ($value !== null && $value !== '') {
                $field_changes[] = [
                    'field_name' => $field,
                    'field_type' => get_field_type($value),
                    'old_value' => null,
                    'new_value' => $value,
                    'change_type' => 'added'
                ];
            }
        }
    } elseif ($operation === 'update') {
        // For update operations, compare old and new values
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
    } elseif ($operation === 'delete') {
        // For delete operations, all fields are removed
        foreach ($old_data as $field => $value) {
            $field_changes[] = [
                'field_name' => $field,
                'field_type' => get_field_type($value),
                'old_value' => $value,
                'new_value' => null,
                'change_type' => 'removed'
            ];
        }
    }
    // Only log if there are real field changes
    if (empty($field_changes)) {
        return false;
    }
    // Log the basic audit action first
    $audit_log_id = log_audit_action($action, $details, 'success', $user_id);
    if (!$audit_log_id) {
        return false;
    }
    // Log the field changes
    log_field_changes($audit_log_id, $field_changes);
    return $audit_log_id;
}

/**
 * Log field-level changes to the audit_field_changes table
 * 
 * @param int $audit_log_id The audit log ID to associate with
 * @param array $field_changes Array of field changes
 * @return bool True if successful, false otherwise
 */
function log_field_changes($audit_log_id, $field_changes) {
    global $conn;
    
    if (empty($field_changes) || !is_array($field_changes)) {
        return false;
    }
    
    // Load database mappings
    $db_mappings = include __DIR__ . '/../config/db_names.php';
    $tables = $db_mappings['tables'];
    
    $audit_field_changes_table = $tables['audit_field_changes'];
    
    $sql = "INSERT INTO $audit_field_changes_table (audit_log_id, field_name, field_type, old_value, new_value, change_type) VALUES (?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $conn->prepare($sql);
        
        foreach ($field_changes as $change) {
            $stmt->bind_param('isssss', 
                $audit_log_id,
                $change['field_name'],
                $change['field_type'],
                $change['old_value'],
                $change['new_value'],
                $change['change_type']
            );
            
            if (!$stmt->execute()) {
                error_log("Failed to log field change: " . $stmt->error);
                return false;
            }
        }
        
        $stmt->close();
        return true;
        
    } catch (Exception $e) {
        error_log("Error logging field changes: " . $e->getMessage());
        return false;
    }
}

/**
 * Determine the field type based on the value
 * 
 * @param mixed $value The value to analyze
 * @return string The field type
 */
function get_field_type($value) {
    if (is_null($value)) {
        return 'null';
    } elseif (is_bool($value)) {
        return 'boolean';
    } elseif (is_numeric($value)) {
        return is_float($value) ? 'float' : 'integer';
    } elseif (is_string($value)) {
        // Check if it's a date
        if (strtotime($value) !== false && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return 'date';
        }
        // Check if it's JSON
        if (is_string($value) && (strpos($value, '{') === 0 || strpos($value, '[') === 0)) {
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                return 'json';
            }
        }
        return 'text';
    } elseif (is_array($value)) {
        return 'json';
    } else {
        return 'text';
    }
}

/**
 * Helper function to log export operations
 * 
 * @param string $export_type Type of export (csv, pdf, excel, etc.)
 * @param string $entity Entity being exported
 * @param array $filters Applied filters
 * @param int $user_id User performing the export
 */
function log_export_operation($export_type, $entity, $filters = [], $user_id = null) {
    $action = "export_{$entity}";
    $details = "Export type: $export_type";
    
    if (!empty($filters)) {
        $details .= " | Filters: " . json_encode($filters);
    }
    
    log_audit_action($action, $details, 'success', $user_id);
}

/**
 * Clean up old audit logs (for maintenance)
 * 
 * @param int $days_to_keep Number of days to keep logs (default: 90)
 * @return int Number of records deleted
 */
function cleanup_audit_logs($days_to_keep = 90) {
    global $conn;
    
    if (!is_admin()) {
        error_log("Audit log cleanup: Unauthorized access attempt");
        return 0;
    }
    
    // Load database mappings
    $db_mappings = include __DIR__ . '/../config/db_names.php';
    $tables = $db_mappings['tables'];
    
    $audit_logs_table = $tables['audit_logs'];
    
    $sql = "DELETE FROM $audit_logs_table WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $days_to_keep);
        $stmt->execute();
        $deleted_count = $stmt->affected_rows;
        $stmt->close();
        
        // Log the cleanup operation
        log_audit_action('cleanup_audit_logs', "Deleted $deleted_count old records (older than $days_to_keep days)", 'success');
        
        return $deleted_count;
        
    } catch (Exception $e) {
        error_log("Audit log cleanup error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Log successful user deletion
 * 
 * @param int $deleted_user_id The ID of the user that was deleted
 * @param string $deleted_username The username of the user that was deleted
 * @param string $deleted_user_role The role of the user that was deleted
 * @param int $admin_user_id The ID of the admin who performed the deletion
 * @return bool True if logged successfully, false otherwise
 */
function log_user_deletion_success($deleted_user_id, $deleted_username, $deleted_user_role, $admin_user_id) {
    $details = json_encode([
        'deleted_user_id' => $deleted_user_id,
        'deleted_username' => $deleted_username,
        'deleted_user_role' => $deleted_user_role,
        'action' => 'User account deleted'
    ], JSON_UNESCAPED_SLASHES);
    
    return log_audit_action('user_deletion', $details, 'success', $admin_user_id);
}

/**
 * Log failed user deletion attempt
 * 
 * @param int $target_user_id The ID of the user that deletion was attempted on
 * @param string $error_reason The reason why deletion failed
 * @param int $admin_user_id The ID of the admin who attempted the deletion
 * @return bool True if logged successfully, false otherwise
 */
function log_user_deletion_failed($target_user_id, $error_reason, $admin_user_id) {
    $details = json_encode([
        'target_user_id' => $target_user_id,
        'error_reason' => $error_reason,
        'action' => 'User deletion attempt failed'
    ], JSON_UNESCAPED_SLASHES);

    return log_audit_action('user_deletion', $details, 'failure', $admin_user_id);
}

/**
 * Get detailed field changes for a specific audit log entry
 * 
 * @param int $audit_log_id The audit log ID
 * @return array Array of field changes
 */
function get_audit_field_changes($audit_log_id) {
    global $conn;
    
    // Load database mappings
    $db_mappings = include __DIR__ . '/../config/db_names.php';
    $tables = $db_mappings['tables'];
    
    $audit_field_changes_table = $tables['audit_field_changes'];
    
    $sql = "SELECT 
                field_name,
                field_type,
                old_value,
                new_value,
                change_type,
                created_at
            FROM $audit_field_changes_table 
            WHERE audit_log_id = ?
            ORDER BY field_name, created_at";
    
    $field_changes = [];
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $audit_log_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $field_changes[] = $row;
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Error fetching field changes: " . $e->getMessage());
    }
    
    return $field_changes;
}

/**
 * Format field change for display
 * 
 * @param array $change Field change data
 * @return string Formatted change description
 */
function format_field_change($change) {
    $field_name = ucwords(str_replace('_', ' ', $change['field_name']));
    $change_type = $change['change_type'];
    $old_value = $change['old_value'];
    $new_value = $change['new_value'];
    
    switch ($change_type) {
        case 'added':
            return "<strong>$field_name</strong> was added with value: <code>" . htmlspecialchars($new_value) . "</code>";
        case 'modified':
            return "<strong>$field_name</strong> changed from <code>" . htmlspecialchars($old_value) . "</code> to <code>" . htmlspecialchars($new_value) . "</code>";
        case 'removed':
            return "<strong>$field_name</strong> was removed (was: <code>" . htmlspecialchars($old_value) . "</code>)";
        default:
            return "<strong>$field_name</strong> was changed";
    }
}

/**
 * Generate meaningful audit details based on operation and entity
 * 
 * @param string $operation Operation type (create, update, delete)
 * @param string $entity Entity type (program, user, outcome, etc.)
 * @param int $entity_id ID of the affected entity
 * @param array $old_data Old data before changes
 * @param array $new_data New data after changes
 * @return string Formatted details string
 */
function generate_audit_details($operation, $entity, $entity_id, $old_data = [], $new_data = []) {
    $entity_name = get_entity_name($entity, $entity_id);
    $entity_display = $entity_name ? $entity_name : "ID: $entity_id";
    
    switch ($operation) {
        case 'create':
            $key_field = get_key_field($entity);
            $new_value = $new_data[$key_field] ?? 'Unknown';
            return "Created new $entity: $new_value";
            
        case 'update':
            $key_field = get_key_field($entity);
            $old_value = $old_data[$key_field] ?? 'Unknown';
            $new_value = $new_data[$key_field] ?? $old_value;
            return "Updated $entity: $new_value";
            
        case 'delete':
            $key_field = get_key_field($entity);
            $deleted_value = $old_data[$key_field] ?? 'Unknown';
            return "Deleted $entity: $deleted_value";
            
        default:
            return "$entity: $entity_display";
    }
}

/**
 * Get the key field name for an entity type using mappings
 * 
 * @param string $entity Entity type
 * @return string Key field name
 */
function get_key_field($entity) {
    // Load database mappings
    $db_mappings = include __DIR__ . '/../config/db_names.php';
    $columns = $db_mappings['columns'];
    
    // Map entity types to table names and their key fields
    $entity_key_fields = [
        'program' => 'program_name',
        'user' => 'username',
        'outcome' => 'detail_name',
        'initiative' => 'initiative_name',
        'agency' => 'agency_name',
        'period' => 'period_type'
    ];
    
    return $entity_key_fields[$entity] ?? 'id';
}

/**
 * Get entity name from database using mappings
 * 
 * @param string $entity Entity type
 * @param int $entity_id Entity ID
 * @return string|null Entity name or null if not found
 */
function get_entity_name($entity, $entity_id) {
    global $conn;
    
    if (!$entity_id) return null;
    
    // Load database mappings
    $db_mappings = include __DIR__ . '/../config/db_names.php';
    $tables = $db_mappings['tables'];
    $columns = $db_mappings['columns'];
    
    // Map entity types to table names
    $entity_table_map = [
        'program' => 'programs',
        'user' => 'users',
        'outcome' => 'outcomes_details',
        'initiative' => 'initiatives',
        'agency' => 'agency',
        'period' => 'reporting_periods'
    ];
    
    if (!isset($entity_table_map[$entity])) {
        return null;
    }
    
    $table = $entity_table_map[$entity];
    
    // Get the appropriate name field based on entity type
    $name_field_map = [
        'program' => 'program_name',
        'user' => 'username',
        'outcome' => 'detail_name',
        'initiative' => 'initiative_name',
        'agency' => 'agency_name',
        'period' => 'period_type' // reporting_periods doesn't have a name field, using period_type
    ];
    
    $name_field = $name_field_map[$entity] ?? 'id';
    $id_field = $columns[$table]['id'] ?? 'id';
    
    try {
        $sql = "SELECT $name_field FROM $table WHERE $id_field = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $entity_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row[$name_field];
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error getting entity name: " . $e->getMessage());
    }
    
    return null;
}
?>
