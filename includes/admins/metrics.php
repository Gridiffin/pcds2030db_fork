<?php
/**
 * Metrics Management Functions
 * 
 * Contains functions for managing metrics data
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';

/**
 * Get all metrics with JSON-based storage
 *
 * @param int|null $period_id Optional period ID to filter metrics by
 * @return array List of metrics
 */
function get_all_metrics_data($period_id = null) {
    global $conn;
    
    $query = "SELECT smd.metric_id, smd.sector_id, smd.period_id, smd.table_name, s.sector_name, 
              rp.year, rp.quarter, smd.created_at, smd.updated_at 
              FROM sector_metrics_data smd
              LEFT JOIN sectors s ON smd.sector_id = s.sector_id
              LEFT JOIN reporting_periods rp ON smd.period_id = rp.period_id
              WHERE smd.is_draft = 0";
    
    // Add period filter if provided
    if ($period_id) {
        $query .= " AND smd.period_id = " . intval($period_id);
    }
    
    $query .= " ORDER BY smd.metric_id DESC";
    
    $result = $conn->query($query);
    
    $metrics = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $metrics[] = $row;
        }
    }
    
    return $metrics;
}

/**
 * Get metrics data for a specific metric ID
 *
 * @param int $metric_id The metric ID to retrieve
 * @return array|null The metric data or null if not found
 */
function get_metric_data($metric_id) {
    global $conn;
    
    $query = "SELECT smd.*, s.sector_name, rp.year, rp.quarter 
              FROM sector_metrics_data smd
              LEFT JOIN sectors s ON smd.sector_id = s.sector_id
              LEFT JOIN reporting_periods rp ON smd.period_id = rp.period_id
              WHERE smd.metric_id = ? AND smd.is_draft = 0";
    
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