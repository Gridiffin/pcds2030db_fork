<?php
/**
 * Notifications AJAX Endpoint
 * 
 * Handles AJAX requests for notification management including:
 * - Getting notifications
 * - Marking as read
 * - Clearing notifications
 * - Real-time updates
 */

// Define PROJECT_ROOT_PATH
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/notifications.php';
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Validate action
if (empty($action)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action is required']);
    exit;
}

try {
    switch ($action) {
        case 'get_notifications':
            handle_get_notifications($user_id);
            break;
            
        case 'mark_read':
            handle_mark_read($user_id);
            break;
            
        case 'mark_unread':
            handle_mark_unread($user_id);
            break;
            
        case 'mark_all_read':
            handle_mark_all_read($user_id);
            break;
            
        case 'clear_all':
            handle_clear_all($user_id);
            break;
            
        case 'get_unread_count':
            handle_get_unread_count($user_id);
            break;
            
        case 'delete_notification':
            handle_delete_notification($user_id);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Notifications AJAX error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

/**
 * Get notifications for the user
 */
function handle_get_notifications($user_id) {
    $page = intval($_POST['page'] ?? 1);
    $limit = intval($_POST['limit'] ?? 20);
    $unread_only = isset($_POST['unread_only']) && $_POST['unread_only'] === 'true';
    
    // Limit the maximum number of notifications per request
    $limit = min($limit, 50);
    
    $result = get_user_notifications($user_id, $page, $limit, $unread_only);
    
    if ($result === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch notifications']);
        return;
    }
    
    // Format notifications for frontend
    $formatted_notifications = [];
    foreach ($result['notifications'] as $notification) {
        $formatted_notifications[] = [
            'notification_id' => $notification['notification_id'],
            'message' => $notification['message'],
            'type' => $notification['type'],
            'read_status' => intval($notification['read_status']),
            'action_url' => $notification['action_url'],
            'created_at' => $notification['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'notifications' => $formatted_notifications,
        'unread_count' => $result['unread_count'],
        'total_count' => $result['total_count'],
        'total_pages' => $result['total_pages'],
        'current_page' => $result['current_page']
    ]);
}

/**
 * Mark specific notifications as read
 */
function handle_mark_read($user_id) {
    $notification_ids_raw = $_POST['notification_ids'] ?? '';
    
    if (empty($notification_ids_raw)) {
        echo json_encode(['success' => false, 'message' => 'No notification IDs provided']);
        return;
    }
    
    // Decode JSON string to array
    $notification_ids = json_decode($notification_ids_raw, true);
    
    if (!is_array($notification_ids)) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification IDs format']);
        return;
    }
    
    if (empty($notification_ids)) {
        echo json_encode(['success' => false, 'message' => 'No notification IDs provided']);
        return;
    }
    
    // Ensure all IDs are integers
    $notification_ids = array_map('intval', $notification_ids);
    $notification_ids = array_filter($notification_ids, function($id) { return $id > 0; });
    
    if (empty($notification_ids)) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification IDs']);
        return;
    }
    
    $success = mark_notifications_read($user_id, $notification_ids);
    
    if ($success) {
        // Get updated unread count
        $stats = get_notification_stats($user_id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Notifications marked as read',
            'unread_count' => $stats['unread']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read']);
    }
}

/**
 * Mark all notifications as read
 */
function handle_mark_all_read($user_id) {
    $success = mark_notifications_read($user_id, null); // null means all notifications
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'All notifications marked as read',
            'unread_count' => 0
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark all notifications as read']);
    }
}

/**
 * Clear all notifications
 */
function handle_clear_all($user_id) {
    $success = delete_notifications($user_id, null); // null means all notifications
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'All notifications cleared',
            'unread_count' => 0,
            'total_count' => 0
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to clear notifications']);
    }
}

/**
 * Get only the unread count (for lightweight polling)
 */
function handle_get_unread_count($user_id) {
    $stats = get_notification_stats($user_id);
    
    echo json_encode([
        'success' => true,
        'unread_count' => $stats['unread'],
        'total_count' => $stats['total']
    ]);
}

/**
 * Delete specific notification
 */
function handle_delete_notification($user_id) {
    $notification_id = intval($_POST['notification_id'] ?? 0);
    
    if ($notification_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
        return;
    }
    
    $success = delete_notifications($user_id, [$notification_id]);
    
    if ($success) {
        // Get updated counts
        $stats = get_notification_stats($user_id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Notification deleted',
            'unread_count' => $stats['unread'],
            'total_count' => $stats['total']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
    }
}

/**
 * Mark notifications as unread
 */
function handle_mark_unread($user_id) {
    $notification_ids_raw = $_POST['notification_ids'] ?? '';
    
    if (empty($notification_ids_raw)) {
        echo json_encode(['success' => false, 'message' => 'No notification IDs provided']);
        return;
    }
    
    // Decode JSON string to array
    $notification_ids = json_decode($notification_ids_raw, true);
    
    if (!is_array($notification_ids)) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification IDs format']);
        return;
    }
    
    if (empty($notification_ids)) {
        echo json_encode(['success' => false, 'message' => 'No notification IDs provided']);
        return;
    }
    
    // Ensure all IDs are integers
    $notification_ids = array_map('intval', $notification_ids);
    $notification_ids = array_filter($notification_ids, function($id) { return $id > 0; });
    
    if (empty($notification_ids)) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification IDs']);
        return;
    }
    
    $success = mark_notifications_unread($user_id, $notification_ids);
    
    if ($success) {
        // Get updated unread count
        $stats = get_notification_stats($user_id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Notifications marked as unread',
            'unread_count' => $stats['unread']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as unread']);
    }
}
?>