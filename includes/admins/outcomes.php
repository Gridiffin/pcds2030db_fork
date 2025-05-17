<?php
/**
 * Outcomes Management Functions
 * 
 * Contains functions for managing outcomes data
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';

/**
 * Get all outcomes with JSON-based storage
 *
 * @param int|null $period_id Optional period ID to filter outcomes by
 * @return array List of outcomes
 */
function get_all_outcomes_data($period_id = null) {
    global $conn;
    
    $query = "SELECT sod.metric_id, sod.sector_id, sod.period_id, sod.table_name, s.sector_name, 
              rp.year, rp.quarter, sod.created_at, sod.updated_at 
              FROM sector_outcomes_data sod
              LEFT JOIN sectors s ON sod.sector_id = s.sector_id
              LEFT JOIN reporting_periods rp ON sod.period_id = rp.period_id
              WHERE sod.is_draft = 0";
    
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
    
    $query = "SELECT sod.*, s.sector_name, rp.year, rp.quarter 
              FROM sector_outcomes_data sod
              LEFT JOIN sectors s ON sod.sector_id = s.sector_id
              LEFT JOIN reporting_periods rp ON sod.period_id = rp.period_id
              WHERE sod.metric_id = ? AND sod.is_draft = 0";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $metric_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}
?>
