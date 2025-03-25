<?php
/**
 * Session management
 * 
 * Handles user sessions and authentication state.
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return boolean True if logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user role
 * @return string|null User role or null if not logged in
 */
function get_user_role() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

/**
 * Redirect if not logged in
 * @param string $redirect_url URL to redirect to
 */
function require_login($redirect_url = 'login.php') {
    if (!is_logged_in()) {
        header("Location: $redirect_url");
        exit;
    }
}
?>
