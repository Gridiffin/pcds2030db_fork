<?php
/**
 * Core Agency Functions
 * 
 * Contains basic agency authentication and utility functions
 */

require_once dirname(__DIR__) . '/utilities.php';

/**
 * Check if current user is an agency
 * @return boolean
 */
function is_agency() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }
    return $_SESSION['role'] === 'agency';
}

/**
 * Check if the schema has shifted to using content_json
 * @return boolean
 */
function has_content_json_schema() {
    return has_database_column('program_submissions', 'content_json');
}

/**
 * Check if programs table has is_assigned column
 * @return boolean
 */
function has_is_assigned_column() {
    return has_database_column('programs', 'is_assigned');
}

/**
 * Process row data with content_json if needed
 * @param array $row Row data from database
 * @return array Updated row data
 */
function process_content_json($row) {
    if (has_content_json_schema() && isset($row['content_json'])) {
        $content = json_decode($row['content_json'], true);
        if ($content) {
            // Extract all content fields into the row
            $row['current_target'] = $content['target'] ?? null;
            $row['status_date'] = $content['status_date'] ?? null;
            $row['status_text'] = $content['status_text'] ?? null;
            $row['achievement'] = $content['achievement'] ?? null;
            $row['achievement_date'] = $content['achievement_date'] ?? null;
            $row['remarks'] = $content['remarks'] ?? null;
        }
        unset($row['content_json']); // Remove JSON from result
    }
    return $row;
}
?>