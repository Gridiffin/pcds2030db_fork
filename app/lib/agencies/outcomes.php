<?php
/**
 * Agency Outcomes Functions
 * 
 * Contains functions for managing agency outcomes
 */

require_once ROOT_PATH . 'app/lib/utilities.php';
require_once ROOT_PATH . 'app/lib/agencies/core.php';

/**
 * Get agency sector outcomes - using JSON-based storage
 */
function get_agency_sector_outcomes($sector_id) {
    global $conn;

    $sector_id = intval($sector_id);
    $query = "SELECT sod.metric_id, sod.sector_id, sod.table_name, sod.data_json, sod.submitted_by, sod.is_important,
                     COALESCE(u.username, 'Unknown') AS submitted_by_username
              FROM sector_outcomes_data sod
              LEFT JOIN users u ON sod.submitted_by = u.user_id
              WHERE sod.sector_id = ? AND sod.is_draft = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $outcomes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Convert JSON data to array that matches the expected format for backward compatibility
            $outcome_base = [
                'metric_id' => $row['metric_id'],
                'sector_id' => $row['sector_id'],
                'table_name' => $row['table_name'],
                'is_submitted' => true,
                'submitted_by' => $row['submitted_by'],
                'submitted_by_username' => $row['submitted_by_username'],
                'is_important' => $row['is_important'] ?? 0
            ];
            
            // Add to outcomes array
            $outcomes[] = $outcome_base;
        }
    }

    return $outcomes;
}

/**
 * Get Draft Outcome - using JSON-based storage
*/
function get_draft_outcome($sector_id) {
    global $conn;

    $sector_id = intval($sector_id);
    $query = "SELECT metric_id, sector_id, table_name, data_json, is_important 
              FROM sector_outcomes_data 
              WHERE sector_id = ? AND is_draft = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $outcomes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Convert JSON data to array that matches the expected format for backward compatibility
            $outcome_base = [
                'metric_id' => $row['metric_id'],
                'sector_id' => $row['sector_id'],                'table_name' => $row['table_name'],
                'is_draft' => true,
                'is_important' => $row['is_important'] ?? 0
            ];
            
            // Add to outcomes array
            $outcomes[] = $outcome_base;
        }
    }

    return $outcomes;
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
    
    $sector_id = intval($sector_id);
    
    // Initialize statistics array
    $stats = [
        'total_outcomes' => 0,
        'submitted_outcomes' => 0,
        'draft_outcomes' => 0,
        'recent_outcomes' => []
    ];
    
    // Build base query
    $query = "SELECT sod.* 
              FROM sector_outcomes_data sod
              WHERE sod.sector_id = ?";
    
    $params = [$sector_id];
    $param_types = "i";
    
    // Add period filter if provided
    if ($period_id) {
        $query .= " AND sod.period_id = ?";
        $params[] = $period_id;
        $param_types .= "i";
    }
    
    $query .= " ORDER BY sod.updated_at DESC";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare agency outcomes statistics query: " . $conn->error);
        return $stats;
    }
    
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats['total_outcomes']++;
            
            if ($row['is_draft'] == 0) {
                $stats['submitted_outcomes']++;
            } else {
                $stats['draft_outcomes']++;
            }
            
            // Add to recent outcomes (limit to 5)
            if (count($stats['recent_outcomes']) < 5) {
                $stats['recent_outcomes'][] = [
                    'metric_id' => $row['metric_id'],
                    'table_name' => $row['table_name'],
                    'is_draft' => $row['is_draft'],
                    'updated_at' => $row['updated_at']
                ];
            }
        }
    }
    
    $stmt->close();
    return $stats;
}

/**
 * Get agency outcomes - using new schema with agency_id
 */
function get_agency_outcomes($agency_id) {
    global $conn;

    $agency_id = intval($agency_id);
    $query = "SELECT od.detail_id, od.detail_name, od.display_config, od.detail_json, od.is_important, od.indicator_type,
                     COALESCE(u.username, 'Unknown') AS submitted_by_username,
                     COALESCE(u.user_id, 0) AS submitted_by
              FROM outcomes_details od
              LEFT JOIN users u ON od.agency_id = u.agency_id
              WHERE od.agency_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $agency_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $outcomes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Convert to array that matches the expected format for backward compatibility
            $outcome_base = [
                'metric_id' => $row['detail_id'], // Using detail_id as metric_id for compatibility
                'table_name' => $row['detail_name'], // Using detail_name as table_name for compatibility
                'is_submitted' => true,
                'submitted_by' => $row['submitted_by'],
                'submitted_by_username' => $row['submitted_by_username'],
                'is_important' => $row['is_important'] ?? 0,
                'indicator_type' => $row['indicator_type']
            ];
            
            // Add to outcomes array
            $outcomes[] = $outcome_base;
        }
    }

    return $outcomes;
}
