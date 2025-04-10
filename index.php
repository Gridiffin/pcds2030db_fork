<?php
/**
 * PCDS 2030 Dashboard - Direct Entry Point
 * 
 * Redirects users either to their appropriate dashboard (if logged in) or to the login page.
 */

// Include necessary files
require_once 'config/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
require_once 'includes/admin_functions.php';

// Check if user is already logged in
if (is_logged_in()) {
    // Redirect to appropriate dashboard based on user type
    if (is_admin()) {
        // Admin - go directly to admin dashboard
        header('Location: ' . APP_URL . '/views/admin/dashboard.php');
    } else {
        // Agency - go directly to agency dashboard
        header('Location: ' . APP_URL . '/views/agency/dashboard.php');
    }
} else {
    // Not logged in - go directly to login page
    header('Location: ' . APP_URL . '/login.php');
}

// Ensure script execution stops after redirect
exit;
?>
