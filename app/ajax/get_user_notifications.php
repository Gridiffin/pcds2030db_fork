<?php
/**
 * Get User Notifications Endpoint
 * 
 * Handles GET requests for fetching user notifications with pagination and filtering
 * This endpoint is called by the frontend JavaScript and expects GET parameters
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

// Set JSON header
header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get parameters from GET request (as expected by JavaScript)
    $page = intval($_GET['page'] ?? 1);
    $per_page = intval($_GET['per_page'] ?? 10);
    $filter = $_GET['filter'] ?? 'all';
    
    // Debug logging
    error_log("get_user_notifications.php - Received parameters: page=$page, per_page=$per_page, filter=$filter");
    
    // Limit the maximum number of notifications per request
    $per_page = min($per_page, 50);
    $page = max($page, 1);
    
    // Handle different filter types
    $unread_only = false;
    $read_only = false;
    $today_only = false;
    
    switch ($filter) {
        case 'unread':
            $unread_only = true;
            error_log("Filter: unread_only = true");
            break;
        case 'read':
            $read_only = true;
            error_log("Filter: read_only = true");
            break;
        case 'today':
            $today_only = true;
            error_log("Filter: today_only = true");
            break;
        case 'all':
        default:
            error_log("Filter: all (no additional filters)");
            // No additional filters
            break;
    }
    
    // Get notifications using the existing function with enhanced filtering
    $result = get_user_notifications_enhanced($user_id, $page, $per_page, $unread_only, $read_only, $today_only);
    
    if ($result === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch notifications']);
        exit;
    }
    
    // Format notifications for frontend (matching the expected structure)
    $formatted_notifications = [];
    foreach ($result['notifications'] as $notification) {
        $formatted_notifications[] = [
            'notification_id' => $notification['notification_id'],
            'message' => $notification['message'],
            'type' => $notification['type'],
            'read_status' => intval($notification['read_status']),
            'action_url' => $notification['action_url'],
            'created_at' => $notification['created_at'],
            'time_ago' => format_time_ago($notification['created_at'])
        ];
    }
    
    // Get statistics for stat cards
    $stats = get_notification_stats($user_id);
    
    // Calculate read count from total and unread
    $read_count = ($stats['total'] ?? 0) - ($stats['unread'] ?? 0);
    
    // Return data in expected format
    echo json_encode([
        'success' => true,
        'notifications' => $formatted_notifications,
        'pagination' => [
            'current_page' => $result['current_page'],
            'total_pages' => $result['total_pages'],
            'per_page' => $per_page,
            'total_count' => $result['total_count']
        ],
        'stats' => [
            'unread_count' => $stats['unread'] ?? 0,
            'total_count' => $stats['total'] ?? 0,
            'read_count' => $read_count
        ]
    ]);

} catch (Exception $e) {
    error_log("Get user notifications error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>