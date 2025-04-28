<?php
/**
 * Agency Metrics Functions
 * 
 * Contains functions for managing agency metrics
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';

/**
 * Get agency sector metrics - using JSON-based storage
 */
function get_agency_sector_metrics($sector_id){
    global $conn;

    $sector_id = intval($sector_id);
    $query = "SELECT metric_id, sector_id, table_name, data_json 
              FROM sector_metrics_data 
              WHERE sector_id = ? AND is_draft = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $metrics = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Convert JSON data to array that matches the expected format for backward compatibility
            $metric_base = [
                'metric_id' => $row['metric_id'],
                'sector_id' => $row['sector_id'],
                'table_name' => $row['table_name'],
                'is_submitted' => true
            ];
            
            // Add to metrics array
            $metrics[] = $metric_base;
        }
    }

    return $metrics;
}

/**
 * Get Draft Metric - using JSON-based storage
*/
function get_draft_metric($sector_id){
    global $conn;

    $sector_id = intval($sector_id);
    $query = "SELECT metric_id, sector_id, table_name, data_json 
              FROM sector_metrics_data 
              WHERE sector_id = ? AND is_draft = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sector_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $metrics = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Convert JSON data to array that matches the expected format for backward compatibility
            $metric_base = [
                'metric_id' => $row['metric_id'],
                'sector_id' => $row['sector_id'],
                'table_name' => $row['table_name'],
                'is_draft' => true
            ];
            
            // Add to metrics array
            $metrics[] = $metric_base;
        }
    }

    return $metrics;
}

/**
 * Get all metrics for a sector with JSON-based storage
 *
 * @param int $sector_id The sector ID
 * @param int|null $period_id Optional period ID to filter by
 * @return array List of metrics
 */
function get_agency_metrics_data($sector_id, $period_id = null) {
    global $conn;
    
    $query = "SELECT smd.metric_id, smd.sector_id, smd.period_id, smd.table_name, 
              rp.year, rp.quarter, smd.created_at, smd.updated_at 
              FROM sector_metrics_data smd
              LEFT JOIN reporting_periods rp ON smd.period_id = rp.period_id
              WHERE smd.sector_id = ? AND smd.is_draft = 0";
    
    // Add period filter if provided
    if ($period_id) {
        $query .= " AND smd.period_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $sector_id, $period_id);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $sector_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $metrics = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $metrics[] = $row;
        }
    }
    
    return $metrics;
}

/**
 * Get metric data for a specific metric ID (agency version)
 *
 * @param int $metric_id The metric ID to retrieve
 * @param int $sector_id The sector ID (for security check)
 * @return array|null The metric data or null if not found
 */
function get_agency_metric_data($metric_id, $sector_id) {
    global $conn;
    
    $query = "SELECT smd.*, rp.year, rp.quarter 
              FROM sector_metrics_data smd
              LEFT JOIN reporting_periods rp ON smd.period_id = rp.period_id
              WHERE smd.metric_id = ? AND smd.sector_id = ? AND smd.is_draft = 0";
    
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