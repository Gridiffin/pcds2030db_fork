<?php
/**
 * Metrics Management Functions
 * 
 * Contains functions for managing metrics data
 * @deprecated Use outcomes.php instead
 */

require_once dirname(__DIR__) . '/utilities.php';
require_once 'core.php';
require_once 'outcomes.php';

/**
 * Get all metrics with JSON-based storage
 *
 * @param int|null $period_id Optional period ID to filter metrics by
 * @return array List of metrics
 * @deprecated Use get_all_outcomes_data instead
 */
function get_all_metrics_data($period_id = null) {
    return get_all_outcomes_data($period_id);
}

/**
 * Get metrics data for a specific metric ID
 *
 * @param int $metric_id The metric ID to retrieve
 * @return array|null The metric data or null if not found
 * @deprecated Use get_outcome_data instead
 */
function get_metric_data($metric_id) {
    return get_outcome_data($metric_id);
}
?>