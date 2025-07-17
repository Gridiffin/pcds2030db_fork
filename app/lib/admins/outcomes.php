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

// --- NEW OUTCOMES TABLE FUNCTIONS ---
/**
 * Get all fixed outcomes from the new outcomes table
 * @return array
 */
function get_all_outcomes() {
    global $conn;
    $query = "SELECT * FROM outcomes ORDER BY id ASC";
    $result = $conn->query($query);
    $outcomes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['data'] = json_decode($row['data'], true);
            $outcomes[] = $row;
        }
    }
    return $outcomes;
}

/**
 * Get outcome by code from the new outcomes table
 * @param string $code
 * @return array|null
 */
function get_outcome_by_code($code) {
    global $conn;
    $query = "SELECT * FROM outcomes WHERE code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $outcome = $result->fetch_assoc();
        $outcome['data'] = json_decode($outcome['data'], true);
        return $outcome;
    }
    return null;
}

/**
 * Get outcome by ID from the new outcomes table
 * @param int $id
 * @return array|null
 */
function get_outcome_by_id($id) {
    global $conn;
    $query = "SELECT * FROM outcomes WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $outcome = $result->fetch_assoc();
        $outcome['data'] = json_decode($outcome['data'], true);
        return $outcome;
    }
    return null;
}

/**
 * Update outcome data by code in the new outcomes table
 * @param string $code
 * @param array $data
 * @return bool
 */
function update_outcome_data_by_code($code, $data) {
    global $conn;
    $data_json = json_encode($data);
    $query = "UPDATE outcomes SET data = ?, updated_at = NOW() WHERE code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $data_json, $code);
    return $stmt->execute();
}

/**
 * Parse outcome JSON data
 * @param string $json_data
 * @return array|null
 */
function parse_outcome_json($json_data) {
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
 * Get outcomes statistics for dashboard
 * 
 * @param int|null $period_id Optional period ID to filter outcomes by
 * @return array Array containing outcomes statistics
 */
function get_outcomes_statistics($period_id = null) {
    global $conn;
    
    // Get total outcomes count
    $total_query = "SELECT COUNT(*) as total FROM outcomes";
    $total_result = $conn->query($total_query);
    $total_outcomes = $total_result ? $total_result->fetch_assoc()['total'] : 0;
    
    // Get outcomes by type
    $type_query = "SELECT type, COUNT(*) as count FROM outcomes GROUP BY type";
    $type_result = $conn->query($type_query);
    $outcomes_by_type = [];
    if ($type_result) {
        while ($row = $type_result->fetch_assoc()) {
            $outcomes_by_type[$row['type']] = $row['count'];
        }
    }
    
    return [
        'total_outcomes' => $total_outcomes,
        'outcomes_by_type' => $outcomes_by_type,
        'chart_outcomes' => $outcomes_by_type['chart'] ?? 0,
        'kpi_outcomes' => $outcomes_by_type['kpi'] ?? 0,
        'submitted_outcomes' => 0, // For compatibility, as in agency version
        'draft_outcomes' => 0,     // For compatibility, as in agency version
        'sectors_with_outcomes' => 0 // For compatibility, as in agency version
    ];
}
?>
