<?php
/**
 * Core Admin Functions
 * 
 * Contains basic admin authentication and permission functions
 */

require_once dirname(__DIR__) . '/utilities.php';

/**
 * Check if current user is admin
 * @return boolean
 */
function is_admin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }
    return $_SESSION['role'] === 'admin';
}

/**
 * Check admin permission
 * @return array|null Error message if not an admin
 */
function check_admin_permission() {
    if (!is_admin()) {
        return format_error('Permission denied', 403);
    }
    return null;
}

/**
 * Generate report for a specific period
 * @param int $period_id The reporting period ID
 * @return array Report info including paths to generated files
 */

?>