<?php
/**
 * Agency Outcomes Functions
 * 
 * Contains functions for managing agency outcomes
 */

require_once ROOT_PATH . 'app/lib/utilities.php';
require_once ROOT_PATH . 'app/lib/agencies/core.php';

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
 * Update all outcome fields by ID in the new outcomes table
 * @param int $id
 * @param string $code
 * @param string $type
 * @param string $title
 * @param string $description
 * @param array $data
 * @return bool
 */
function update_outcome_full($id, $code, $type, $title, $description, $data) {
    global $conn;
    $data_json = json_encode($data);
    $query = "UPDATE outcomes SET code = ?, type = ?, title = ?, description = ?, data = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Error preparing update_outcome_full: " . $conn->error);
        return false;
    }
    $stmt->bind_param("sssssi", $code, $type, $title, $description, $data_json, $id);
    $success = $stmt->execute();
    if (!$success) {
        error_log("Error executing update_outcome_full: " . $stmt->error);
    }
    $stmt->close();
    return $success;
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
 * Get agency outcomes statistics for dashboard
 * 
 * @param int $sector_id The sector ID for the agency
 * @param int|null $period_id Optional period ID to filter by
 * @return array Array containing outcomes statistics
 */
function get_agency_outcomes_statistics($sector_id, $period_id = null) {
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

    // The outcomes table does not have draft/submitted status. Set to 0 for compatibility.
    $submitted_outcomes = 0;
    $draft_outcomes = 0;

    return [
        'total_outcomes' => $total_outcomes,
        'outcomes_by_type' => $outcomes_by_type,
        'chart_outcomes' => $outcomes_by_type['chart'] ?? 0,
        'kpi_outcomes' => $outcomes_by_type['kpi'] ?? 0,
        'submitted_outcomes' => $submitted_outcomes,
        'draft_outcomes' => $draft_outcomes
    ];
}
?>
