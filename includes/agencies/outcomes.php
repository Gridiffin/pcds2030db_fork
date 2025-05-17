<?php
/**
 * Agency Outcomes Functions
 * 
 * Contains functions for managing agency outcomes
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';

/**
 * Get agency sector outcomes - using JSON-based storage
 */
function get_agency_sector_outcomes($sector_id){
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
function get_draft_outcome($sector_id){
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

/**
 * Get all outcomes for a sector with JSON-based storage
 *
 * @param int $sector_id The sector ID
 * @param int|null $period_id Optional period ID to filter by
 * @return array List of outcomes
 */
function get_agency_outcomes_data($sector_id, $period_id = null) {
    global $conn;
    
    $query = "SELECT sod.metric_id, sod.sector_id, sod.period_id, sod.table_name, 
              rp.year, rp.quarter, sod.created_at, sod.updated_at 
              FROM sector_outcomes_data sod
              LEFT JOIN reporting_periods rp ON sod.period_id = rp.period_id
              WHERE sod.sector_id = ? AND sod.is_draft = 0";
    
    // Add period filter if provided
    if ($period_id) {
        $query .= " AND sod.period_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $sector_id, $period_id);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $sector_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $outcomes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $outcomes[] = $row;
        }
    }
    
    return $outcomes;
}

/**
 * Get outcome data for a specific metric ID (agency version)
 *
 * @param int $metric_id The metric ID to retrieve
 * @param int $sector_id The sector ID (for security check)
 * @return array|null The outcome data or null if not found
 */
function get_agency_outcome_data($metric_id, $sector_id) {
    global $conn;
    
    $query = "SELECT sod.*, rp.year, rp.quarter 
              FROM sector_outcomes_data sod
              LEFT JOIN reporting_periods rp ON sod.period_id = rp.period_id
              WHERE sod.metric_id = ? AND sod.sector_id = ? AND sod.is_draft = 0";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $metric_id, $sector_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}
?>
