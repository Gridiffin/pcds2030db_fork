<?php
/**
 * All Notifications - Improved Simple Implementation
 * Agency side notification management with clean, functional design
 */

// Define PROJECT_ROOT_PATH if not already defined
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/core.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/notifications.php';

// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Debug: Log the user session for troubleshooting
error_log("Notifications page - User ID: " . ($_SESSION['user_id'] ?? 'Not set') . ", Role: " . ($_SESSION['role'] ?? 'Not set'));

// Get initial data using the enhanced function with "unread" filter as default
$user_id = $_SESSION['user_id'];
$notificationData = get_user_notifications_enhanced($user_id, 1, 10, true, false, false); // Changed to unread_only = true
$notifications = $notificationData['notifications'] ?? [];
$stats = get_notification_stats($user_id);
$pagination = [
    'current_page' => $notificationData['current_page'] ?? 1,
    'total_pages' => $notificationData['total_pages'] ?? 1,
    'per_page' => 10,
    'total_count' => $notificationData['total_count'] ?? 0
];

// Debug: Log the data fetching results
error_log("Notifications page - Fetched " . count($notifications) . " notifications, Total: " . ($notificationData['total_count'] ?? 0));

// If format_time_ago function doesn't exist, create a simple one
if (!function_exists('format_time_ago')) {
    function format_time_ago($timestamp) {
        if (!$timestamp) return 'Unknown time';
        
        $time_ago = time() - strtotime($timestamp);
        
        if ($time_ago < 60) return 'Just now';
        if ($time_ago < 3600) return floor($time_ago / 60) . ' min ago';
        if ($time_ago < 86400) return floor($time_ago / 3600) . ' hrs ago';
        if ($time_ago < 604800) return floor($time_ago / 86400) . ' days ago';
        
        return date('M j, Y', strtotime($timestamp));
    }
}

// Set page configuration for layout
$pageTitle = "Notifications";
$pageClass = 'notifications-page';
$cssBundle = 'agency-notifications';
$jsBundle = 'agency-notifications';

// Set header configuration
$header_config = [
    'title' => 'Notifications',
    'subtitle' => 'Manage your system notifications',
    'breadcrumb' => [
        ['name' => 'Dashboard', 'url' => '/index.php?page=agency_dashboard'],
        ['name' => 'Notifications', 'url' => '']
    ]
];

// Create the content file path for the notifications content
$contentFile = __DIR__ . '/partials/notifications_content_simple.php';

// Include the layout which will include our content file
include PROJECT_ROOT_PATH . 'app/views/layouts/base.php';

