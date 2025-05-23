<?php
/**
 * Agency Metrics Functions
 * 
 * Contains functions for managing agency metrics
 */

require_once ROOT_PATH . 'app/lib/agencies/outcomes.php';

/**
 * Get agency sector metrics - using JSON-based storage
 * @deprecated Use get_agency_sector_outcomes instead
 */
function get_agency_sector_metrics($sector_id) {
    return get_agency_sector_outcomes($sector_id);
}

/**
 * Get Draft Metric - using JSON-based storage
 * @deprecated Use get_draft_outcome instead
 */
function get_draft_metric($sector_id) {
    return get_draft_outcome($sector_id);
}
