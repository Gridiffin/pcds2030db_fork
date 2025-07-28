<?php
/**
 * Manage Notifications Page
 * 
 * Admin interface for managing system notifications.
 * Following modern admin styling with base.php layout and Vite bundles.
 */

// Define the project root path correctly by navigating up from the current file's directory.
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(dirname(__DIR__))))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include the main config file which defines global constants like APP_URL.
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Include necessary libraries
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/notifications.php';
require_once PROJECT_ROOT_PATH . 'app/lib/notifications_core.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Page configuration for base.php layout
$pageTitle = 'Manage Notifications';
$pageClass = 'admin-notifications-page';
$cssBundle = 'admin-notifications'; // CSS bundle for admin notifications
$jsBundle = 'admin-notifications';   // JS bundle for admin notifications
$contentFile = 'partials/manage_notifications_content.php';

// Process form submissions
$message = '';
$message_type = '';

// Check if there's a message in the session and use it
if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    
    // If show_toast_only is set, we'll only show the toast notification
    $show_toast_only = isset($_SESSION['show_toast_only']) && $_SESSION['show_toast_only'];
    
    // Clear the message from session after using it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    if (isset($_SESSION['show_toast_only'])) {
        unset($_SESSION['show_toast_only']);
    }
}

// Handle AJAX requests for notification management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $response = ['success' => false, 'message' => 'Unknown action'];
    
    switch ($action) {
        case 'send_system_notification':
            $message_text = trim($_POST['message'] ?? '');
            $notification_type = $_POST['type'] ?? 'system';
            $action_url = trim($_POST['action_url'] ?? '') ?: null;
            
            if (empty($message_text)) {
                $response = ['success' => false, 'message' => 'Message text is required'];
                break;
            }
            
            if (notify_system_wide($message_text, $notification_type, $action_url, $_SESSION['user_id'])) {
                $response = ['success' => true, 'message' => 'System notification sent successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to send system notification'];
            }
            break;
            
        case 'cleanup_notifications':
            $days_to_keep = intval($_POST['days_to_keep'] ?? 30);
            if ($days_to_keep < 1) $days_to_keep = 30;
            
            $deleted_count = cleanup_old_notifications($days_to_keep);
            $response = [
                'success' => true, 
                'message' => "Cleaned up $deleted_count old notifications (older than $days_to_keep days)"
            ];
            break;
            
        case 'get_notification_stats':
            // Get system-wide notification statistics
            $stats_query = "SELECT 
                              COUNT(*) as total_notifications,
                              SUM(CASE WHEN read_status = 0 THEN 1 ELSE 0 END) as unread_notifications,
                              COUNT(DISTINCT user_id) as users_with_notifications,
                              SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as notifications_last_24h,
                              SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as notifications_last_7d
                           FROM notifications";
            $stmt = $conn->prepare($stats_query);
            $stmt->execute();
            $stats = $stmt->get_result()->fetch_assoc();
            
            // Get notifications by type
            $type_stats_query = "SELECT type, COUNT(*) as count 
                                FROM notifications 
                                GROUP BY type 
                                ORDER BY count DESC";
            $stmt = $conn->prepare($type_stats_query);
            $stmt->execute();
            $type_stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            $response = [
                'success' => true,
                'stats' => $stats,
                'type_stats' => $type_stats
            ];
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
            break;
    }
    
    echo json_encode($response);
    exit;
}

// Get recent notifications for display (admin view)
$recent_notifications_query = "SELECT n.*, u.username, u.fullname, a.agency_name
                              FROM notifications n
                              LEFT JOIN users u ON n.user_id = u.user_id
                              LEFT JOIN agency a ON u.agency_id = a.agency_id
                              ORDER BY n.created_at DESC
                              LIMIT 50";
$stmt = $conn->prepare($recent_notifications_query);
$stmt->execute();
$recent_notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get notification statistics for dashboard
$notification_stats_query = "SELECT 
                               COUNT(*) as total_notifications,
                               SUM(CASE WHEN read_status = 0 THEN 1 ELSE 0 END) as unread_notifications,
                               COUNT(DISTINCT user_id) as users_with_notifications,
                               SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as notifications_last_24h
                            FROM notifications";
$stmt = $conn->prepare($notification_stats_query);
$stmt->execute();
$notification_stats = $stmt->get_result()->fetch_assoc();

// Include base layout
require_once dirname(dirname(__DIR__)) . '/layouts/base_admin.php';
?>