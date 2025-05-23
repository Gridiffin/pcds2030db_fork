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
    $query = "SELECT metric_id, sector_id, table_name, data_json 
              FROM sector_outcomes_data 
              WHERE sector_id = ? AND is_draft = 0";
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
                'is_submitted' => true
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
    $query = "SELECT metric_id, sector_id, table_name, data_json 
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
                'sector_id' => $row['sector_id'],
                'table_name' => $row['table_name'],
                'is_draft' => true
            ];
            
            // Add to outcomes array
            $outcomes[] = $outcome_base;
        }
    }

    return $outcomes;
}
