<?php
/**
 * Activity Helper Functions
 * Helper functions for formatting activity descriptions and icons
 */

/**
 * Format activity description for display
 * @param string $action Activity action type
 * @param string $details Activity details
 * @return string Formatted description
 */
function formatActivityDescription($action, $details) {
    switch ($action) {
        case 'program_submitted':
            return 'Program submission completed';
        case 'program_draft_saved':
            return 'Program draft saved';
        case 'update_program':
            return 'Program information updated';
        case 'outcome_updated':
            // Extract outcome name from details if available
            if (preg_match("/Updated.*?outcome '([^']+)'/", $details, $matches)) {
                return 'Outcome updated: ' . $matches[1];
            }
            return 'Program outcome updated';
        case 'outcome_submitted':
            return 'Program outcome submitted';
        case 'admin_program_edited':
            return 'Program edited by administrator';
        case 'program_finalized':
            return 'Program finalized';
        case 'resubmit_program':
            return 'Program resubmitted';
        default:
            return ucwords(str_replace('_', ' ', $action));
    }
}

/**
 * Get activity icon and color class
 * @param string $action Activity action type
 * @return array Icon and color information
 */
function getActivityIcon($action) {
    switch ($action) {
        case 'program_submitted':
        case 'outcome_submitted':
            return ['icon' => 'fas fa-check-circle', 'color' => 'text-success'];
        case 'program_draft_saved':
            return ['icon' => 'fas fa-save', 'color' => 'text-warning'];
        case 'update_program':
        case 'outcome_updated':
            return ['icon' => 'fas fa-edit', 'color' => 'text-primary'];
        case 'admin_program_edited':
            return ['icon' => 'fas fa-user-shield', 'color' => 'text-info'];
        case 'program_finalized':
            return ['icon' => 'fas fa-lock', 'color' => 'text-success'];
        case 'resubmit_program':
            return ['icon' => 'fas fa-redo', 'color' => 'text-secondary'];
        default:
            return ['icon' => 'fas fa-file-alt', 'color' => 'text-muted'];
    }
}
