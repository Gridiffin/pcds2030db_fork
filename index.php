<?php
/**
 * PCDS 2030 Dashboard - Direct Entry Point
 * 
 * Redirects users either to their appropriate dashboard (if logged in) or to the login page.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admin_functions.php';

// Check if user is already logged in
if (is_logged_in()) {
    // Define default landing page URLs
    $admin_dashboard = APP_URL . '/app/views/admin/dashboard.php';
    $agency_dashboard = APP_URL . '/app/views/agency/dashboard.php';
    
    // Redirect to appropriate dashboard based on user type
    if (is_admin()) {
        // Admin - go directly to admin dashboard
        header('Location: ' . $admin_dashboard);
    } else {
        // Agency - go directly to agency dashboard
        header('Location: ' . $agency_dashboard);
    }
} else {
    // Not logged in - go directly to login page
    header('Location: ' . APP_URL . '/login.php');
}

// Ensure script execution stops after redirect
exit;
?>
