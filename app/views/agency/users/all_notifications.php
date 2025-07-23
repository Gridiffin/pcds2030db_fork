<?php
/**
 * All Notifications - Modern Refactored View
 * Agency side notification management with modular architecture
 */

// Define PROJECT_ROOT_PATH if not already defined
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include base setup and session management in correct order
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

// Page configuration
$pageTitle = 'All Notifications';
$pageClass = 'notifications-page';
$cssBundle = 'agency-notifications'; // CSS bundle for notifications module
$jsBundle = 'agency-notifications';
$contentFile = 'partials/notifications_content.php';

// Get notification data
$notificationData = get_user_notifications($_SESSION['user_id']);
$notifications = $notificationData['notifications'] ?? [];
$stats = $notificationData['stats'] ?? [];
$pagination = $notificationData['pagination'] ?? [];

// Include base layout
require_once dirname(dirname(__DIR__)) . '/layouts/base.php';

