<?php
/**
 * Agency Metrics Functions
 * 
 * Contains functions for managing agency metrics
 */

require_once ROOT_PATH . 'app/lib/agencies/outcomes.php';

/**
 * Get agency sector metrics - using JSON-based storage
 * @deprecated since 2.0.0 Use get_agency_sector_outcomes() instead
 * @see get_agency_sector_outcomes()
 */
function get_agency_sector_metrics($sector_id) {
    trigger_error('get_agency_sector_metrics() is deprecated. Use get_agency_sector_outcomes() instead.', E_USER_DEPRECATED);
    return get_agency_sector_outcomes($sector_id);
}

/**
 * Get Draft Metric - using JSON-based storage
 * @deprecated since 2.0.0 Use get_draft_outcome() instead
 * @see get_draft_outcome()
 */
function get_draft_metric($sector_id) {
    trigger_error('get_draft_metric() is deprecated. Use get_draft_outcome() instead.', E_USER_DEPRECATED);
    return get_draft_outcome($sector_id);
}
