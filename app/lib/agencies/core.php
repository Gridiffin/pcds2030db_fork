<?php
/**
 * Agency Core Functions
 * 
 * Core functions for agency-related operations
 */

require_once dirname(__DIR__) . '/session.php';

/**
 * Check if current user is an agency
 * @return boolean True if user is an agency, false otherwise
 */
function is_agency() {
    if (!is_logged_in() || !isset($_SESSION['role'])) {
        return false;
    }
    return $_SESSION['role'] === 'agency';
}

/**
 * Redirect if user is not an agency
 * @param string $redirect_url URL to redirect to
 */
function require_agency($redirect_url = 'login.php') {
    if (!is_agency()) {
        header("Location: $redirect_url");
        exit;
    }
}

/**
 * Get current agency ID
 * @return int|null Agency ID or null if not an agency
 */
function get_agency_id() {
    return is_agency() ? $_SESSION['user_id'] : null;
}
?>
