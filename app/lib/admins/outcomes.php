<?php
/**
 * Outcomes Management Functions
 * 
 * Contains functions for managing outcomes data
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';

/**
 * Record outcome history
 *
 * @param int $outcome_record_id The ID of the outcome record in sector_outcomes_data table
 * @param int $metric_id The metric ID of the outcome
 * @param string $data_json The JSON data for the outcome
 * @param string $action_type The action performed ('create', 'edit', 'submit', 'unsubmit')
 * @param string $status The status after the action ('draft', 'submitted')
 * @param int $user_id The ID of the user who made the change
 * @param string $description Optional description of the change
 * @return bool True on success, false on failure
 */
function record_outcome_history($outcome_record_id, $metric_id, $data_json, $action_type, $status, $user_id, $description = '') {
    global $conn;
    
    $query = "INSERT INTO outcome_history 
              (outcome_record_id, metric_id, data_json, action_type, status, changed_by, change_description) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Error preparing outcome history query: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("iisssss", $outcome_record_id, $metric_id, $data_json, $action_type, $status, $user_id, $description);
    $success = $stmt->execute();
    
    if (!$success) {
        error_log("Error recording outcome history: " . $stmt->error);
    }
    
    $stmt->close();
    return $success;
}

/**
 * Get outcome history for a specific metric
 *
 * @param int $metric_id The metric ID to retrieve history for
 * @return array Array of history records
 */
function get_outcome_history($metric_id) {
    global $conn;
    
    $query = "SELECT oh.*, u.username 
              FROM outcome_history oh 
              LEFT JOIN users u ON oh.changed_by = u.user_id 
              WHERE oh.metric_id = ? 
              ORDER BY oh.created_at DESC";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Error preparing get outcome history query: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param("i", $metric_id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $history = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
    }
    
    $stmt->close();
    return $history;
}

/**
 * Get all outcomes with JSON-based storage
 *
 * @param int|null $period_id Optional period ID to filter outcomes by
 * @param bool $include_drafts Whether to include draft (unsubmitted) outcomes
 * @return array List of outcomes
 */
function get_all_outcomes_data($period_id = null, $include_drafts = true) {
    global $conn;
    
    $query = "SELECT sod.metric_id, sod.sector_id, sod.period_id, sod.table_name, s.sector_name, 
              rp.year, rp.quarter, sod.created_at, sod.updated_at 
              FROM sector_outcomes_data sod
              LEFT JOIN sectors s ON sod.sector_id = s.sector_id
              LEFT JOIN reporting_periods rp ON sod.period_id = rp.period_id";
    
    if (!$include_drafts) {
        $query .= " WHERE sod.is_draft = 0";
    } else {
        $query .= " WHERE 1=1";
    }
    
    // Add period filter if provided
    if ($period_id) {
        $query .= " AND sod.period_id = " . intval($period_id);
    }
    
    $query .= " ORDER BY sod.metric_id DESC";
    
    $result = $conn->query($query);
    
    $outcomes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $outcomes[] = $row;
        }
    }
    
    return $outcomes;
}

/**
 * Get outcomes data for a specific metric ID
 *
 * @param int $metric_id The metric ID to retrieve
 * @return array|null The outcome data or null if not found
 */
function get_outcome_data($metric_id) {
    global $conn;
    
    // Query the sector_outcomes_data table with proper JOINs for sector and reporting period information
    $query = "SELECT sod.*, s.sector_name, rp.year, rp.quarter, rp.status as period_status,
              COALESCE(u.username, 'System') as submitted_by_username
              FROM sector_outcomes_data sod
              LEFT JOIN sectors s ON sod.sector_id = s.sector_id
              LEFT JOIN reporting_periods rp ON sod.period_id = rp.period_id
              LEFT JOIN users u ON sod.submitted_by = u.user_id
              WHERE sod.metric_id = ? AND sod.is_draft = 0
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return null;
    }
    
    $stmt->bind_param("i", $metric_id);
    
    if (!$stmt->execute()) {
        error_log("Failed to execute query: " . $stmt->error);
        return null;
    }
    
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Parse JSON data from sector outcomes
 *
 * @param string $json_data The JSON data string
 * @return array|null Parsed data or null if invalid
 */
function parse_outcome_json_data($json_data) {
    if (empty($json_data)) {
        return null;
    }
    
    $data = json_decode($json_data, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON parsing error: " . json_last_error_msg());
        return null;
    }
    
    return $data;
}

/**
 * Get outcome data with parsed JSON for display
 *
 * @param int $metric_id The metric ID to retrieve
 * @return array|null The outcome data with parsed JSON or null if not found
 */
function get_outcome_data_for_display($metric_id) {
    $outcome_data = get_outcome_data($metric_id);
    
    if (!$outcome_data) {
        return null;
    }
    
    // Parse the JSON data if it exists
    if (!empty($outcome_data['data_json'])) {
        $parsed_data = parse_outcome_json_data($outcome_data['data_json']);
        $outcome_data['parsed_data'] = $parsed_data;
    }
    
    return $outcome_data;
}

/**
 * Check if a metric ID exists in the outcomes table
 *
 * @param int $metric_id The metric ID to check
 * @return bool True if exists, false otherwise
 */
function outcome_exists($metric_id) {
    global $conn;
    
    $query = "SELECT COUNT(*) as count FROM sector_outcomes_data WHERE metric_id = ? AND is_draft = 0";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("i", $metric_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    return false;
}

/**
 * Update an existing outcome with new data
 *
 * @param int $metric_id The metric ID to update
 * @param array $data The updated outcome data
 * @return bool True if successful, false otherwise
 */
function update_outcome_data($metric_id, $data) {
    global $conn;
    
    if (empty($metric_id) || !is_numeric($metric_id) || empty($data)) {
        return false;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if the outcome exists
        if (!outcome_exists($metric_id)) {
            error_log("Outcome with ID {$metric_id} not found for updating.");
            $conn->rollback();
            return false;
        }
        
        // Prepare JSON data if needed
        if (isset($data['data_json']) && is_array($data['data_json'])) {
            $data['data_json'] = json_encode($data['data_json']);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON encoding error: " . json_last_error_msg());
                $conn->rollback();
                return false;
            }
        }
        
        // Build the update query dynamically based on provided fields
        $update_fields = [];
        $types = '';
        $values = [];
        
        foreach ($data as $field => $value) {
            // Skip metric_id as it's our WHERE condition
            if ($field === 'metric_id') {
                continue;
            }
            
            $update_fields[] = "{$field} = ?";
            
            // Add to parameter binding
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } elseif (is_bool($value)) {
                $types .= 'i';
                $value = $value ? 1 : 0;
            } else {
                $types .= 's';
            }
            
            $values[] = $value;
        }
        
        if (empty($update_fields)) {
            $conn->rollback();
            return false;
        }
        
        // Add metric_id to the values array and parameter binding
        $types .= 'i';
        $values[] = $metric_id;
        
        $query = "UPDATE sector_outcomes_data SET " . implode(', ', $update_fields) . 
                 " WHERE metric_id = ?";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare update statement: " . $conn->error);
            $conn->rollback();
            return false;
        }
          // Use a different approach to bind parameters dynamically
        $stmt->bind_param($types, ...$values);
        
        if (!$stmt->execute()) {
            error_log("Failed to execute update query: " . $stmt->error);
            $conn->rollback();
            return false;
        }
        
        // Commit transaction
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        error_log("Exception in update_outcome_data: " . $e->getMessage());
        $conn->rollback();
        return false;
    }
}

/**
 * Create a new outcome record
 *
 * @param array $data The outcome data to insert
 * @return int|false The new metric ID if successful, false otherwise
 */
function create_outcome_data($data) {
    global $conn;
    
    if (empty($data)) {
        return false;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Required fields
        if (!isset($data['sector_id']) || !isset($data['period_id']) || !isset($data['table_name'])) {
            error_log("Missing required fields for outcome creation");
            $conn->rollback();
            return false;
        }
        
        // Prepare JSON data if needed
        if (isset($data['data_json']) && is_array($data['data_json'])) {
            $data['data_json'] = json_encode($data['data_json']);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON encoding error: " . json_last_error_msg());
                $conn->rollback();
                return false;
            }
        }
        
        // Build the insert query dynamically
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $query = "INSERT INTO sector_outcomes_data (" . implode(', ', $fields) . 
                 ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare insert statement: " . $conn->error);
            $conn->rollback();
            return false;
        }
        
        // Determine parameter types
        $types = '';
        $values = [];
        
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } elseif (is_bool($value)) {
                $types .= 'i';
                $value = $value ? 1 : 0;
            } else {
                $types .= 's';
            }
            
            $values[] = $value;
        }
        
        // Bind parameters
        $stmt->bind_param($types, ...$values);
        
        if (!$stmt->execute()) {
            error_log("Failed to execute insert query: " . $stmt->error);
            $conn->rollback();
            return false;
        }
        
        $new_id = $stmt->insert_id;
        
        // Commit transaction
        $conn->commit();
        return $new_id;
        
    } catch (Exception $e) {
        error_log("Exception in create_outcome_data: " . $e->getMessage());
        $conn->rollback();
        return false;
    }
}

/**
 * Get outcomes statistics for admin dashboard
 *
 * @param int|null $period_id Optional period ID to filter by
 * @return array Array containing outcomes statistics
 */
function get_outcomes_statistics($period_id = null) {
    global $conn;
    
    $stats = [
        'total_outcomes' => 0,
        'submitted_outcomes' => 0,
        'draft_outcomes' => 0,
        'recent_outcomes' => [],
        'sectors_with_outcomes' => 0
    ];
    
    try {
        // Build base query
        $where_clause = "WHERE 1=1";
        $params = [];
        $param_types = "";
        
        if ($period_id) {
            $where_clause .= " AND sod.period_id = ?";
            $params[] = $period_id;
            $param_types .= "i";
        }
        
        // Get total counts
        $query = "SELECT 
                    COUNT(*) as total_outcomes,
                    SUM(CASE WHEN is_draft = 0 THEN 1 ELSE 0 END) as submitted_outcomes,
                    SUM(CASE WHEN is_draft = 1 THEN 1 ELSE 0 END) as draft_outcomes,
                    COUNT(DISTINCT sector_id) as sectors_with_outcomes
                  FROM sector_outcomes_data sod 
                  $where_clause";
        
        if (!empty($params)) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param($param_types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($query);
        }
        
        if ($result && $row = $result->fetch_assoc()) {
            $stats['total_outcomes'] = (int)$row['total_outcomes'];
            $stats['submitted_outcomes'] = (int)$row['submitted_outcomes'];
            $stats['draft_outcomes'] = (int)$row['draft_outcomes'];
            $stats['sectors_with_outcomes'] = (int)$row['sectors_with_outcomes'];
        }
        
        // Get recent outcomes (last 5)
        $recent_query = "SELECT sod.metric_id, sod.table_name, sod.is_draft, 
                                sod.created_at, sod.updated_at,
                                s.sector_name, rp.year, rp.quarter,
                                COALESCE(u.username, 'System') as submitted_by_username
                         FROM sector_outcomes_data sod
                         LEFT JOIN sectors s ON sod.sector_id = s.sector_id
                         LEFT JOIN reporting_periods rp ON sod.period_id = rp.period_id
                         LEFT JOIN users u ON sod.submitted_by = u.user_id
                         $where_clause
                         ORDER BY sod.updated_at DESC 
                         LIMIT 5";
        
        if (!empty($params)) {
            $stmt = $conn->prepare($recent_query);
            $stmt->bind_param($param_types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($recent_query);
        }
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $stats['recent_outcomes'][] = $row;
            }
        }
        
    } catch (Exception $e) {
        error_log("Error in get_outcomes_statistics: " . $e->getMessage());
    }
    
    return $stats;
}
?>
