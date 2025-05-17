<?php
/**
 * Agency Metrics Functions
 * 
 * Contains functions for managing agency metrics
 * @deprecated Use outcomes.php instead
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';
require_once 'outcomes.php';

/**
 * Get agency sector metrics - using JSON-based storage
 * @deprecated Use get_agency_sector_outcomes instead
 */
function get_agency_sector_metrics($sector_id){
    return get_agency_sector_outcomes($sector_id);
}

/**
 * Get Draft Metric - using JSON-based storage
 * @deprecated Use get_draft_outcome instead
 */
function get_draft_metric($sector_id){
    return get_draft_outcome($sector_id);
}

/**
 * Get all metrics for a sector with JSON-based storage
 *
 * @param int $sector_id The sector ID
 * @param int|null $period_id Optional period ID to filter by
 * @return array List of metrics
 * @deprecated Use get_agency_outcomes_data instead
 */
function get_agency_metrics_data($sector_id, $period_id = null) {
    return get_agency_outcomes_data($sector_id, $period_id);
}

/**
 * Get metric data for a specific metric ID (agency version)
 *
 * @param int $metric_id The metric ID to retrieve
 * @param int $sector_id The sector ID (for security check)
 * @return array|null The metric data or null if not found
 * @deprecated Use get_agency_outcome_data instead
 */
function get_agency_metric_data($metric_id, $sector_id) {
    return get_agency_outcome_data($metric_id, $sector_id);
}
?>