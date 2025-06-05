<?php
/**
 * Logout script
 * 
 * Destroys the session and redirects to login page.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include config for APP_URL
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log logout before destroying session
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    log_logout($_SESSION['user_id']);
}

// Unset all session variables
$_SESSION = array();

// If using session cookies, delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: " . APP_URL . "/login.php");
exit;
?>
