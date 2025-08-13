<?php
/**
 * Simple Notifications View
 * Basic notification management without complex architecture
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

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get initial data
$notificationData = get_user_notifications($_SESSION['user_id'], 1, 10);
$notifications = $notificationData['notifications'] ?? [];
$stats = get_notification_stats($_SESSION['user_id']);
$pagination = [
    'current_page' => $notificationData['current_page'] ?? 1,
    'total_pages' => $notificationData['total_pages'] ?? 1,
    'per_page' => 10,
    'total_count' => $notificationData['total_count'] ?? 0
];

// Page configuration
$pageTitle = 'All Notifications';
$pageClass = 'notifications-page';
$cssBundle = 'main';
$jsBundle = null;
$contentFile = 'partials/notifications_content_simple.php';

// Include base layout
require_once dirname(dirname(__DIR__)) . '/layouts/base.php';
?>