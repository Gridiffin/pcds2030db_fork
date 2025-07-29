<?php
/**
 * Agency Notifications Management Functions
 * 
 * Functions for managing notifications in the agency context
 */

// Define PROJECT_ROOT_PATH if not already defined
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
require_once PROJECT_ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'db_connect.php';
require_once PROJECT_ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'functions.php';
require_once PROJECT_ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'audit_log.php';

/**
 * Get user notifications
 * @param int $user_id User ID
 * @param int $page Page number (1-based)
 * @param int $limit Items per page
 * @param bool $unread_only Whether to fetch only unread notifications
 * @return array Array with notifications, pagination info, and counts
 */
function get_user_notifications($user_id, $page = 1, $limit = 20, $unread_only = false) {
    global $conn;
    
    if (!$user_id) {
        return [
            'notifications' => [],
            'total_count' => 0,
            'unread_count' => 0,
            'total_pages' => 0,
            'current_page' => 1
        ];
    }
    
    $offset = ($page - 1) * $limit;
    
    // Base query conditions
    $where_conditions = ["user_id = ?"];
    $params = [$user_id];
    $param_types = 'i';
    
    if ($unread_only) {
        $where_conditions[] = "read_status = 0";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM notifications WHERE $where_clause";
    $stmt = $conn->prepare($count_query);
    if (!$stmt) {
        error_log("Failed to prepare notification count query: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $total_count = $stmt->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_count / $limit);
    
    // Get unread count
    $unread_count_query = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND read_status = 0";
    $stmt = $conn->prepare($unread_count_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $unread_count = $stmt->get_result()->fetch_assoc()['unread'];
    
    // Get notifications with pagination
    $notifications_query = "SELECT * FROM notifications 
                           WHERE $where_clause 
                           ORDER BY created_at DESC 
                           LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($notifications_query);
    if (!$stmt) {
        error_log("Failed to prepare notifications query: " . $conn->error);
        return [];
    }
    
    // Add limit and offset parameters
    $params[] = $limit;
    $params[] = $offset;
    $param_types .= 'ii';
    
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    return [
        'notifications' => $notifications,
        'total_count' => $total_count,
        'unread_count' => $unread_count,
        'total_pages' => $total_pages,
        'current_page' => $page
    ];
}

/**
 * Get user notifications with enhanced filtering options
 * @param int $user_id User ID
 * @param int $page Page number (1-based)
 * @param int $limit Items per page
 * @param bool $unread_only Whether to fetch only unread notifications
 * @param bool $read_only Whether to fetch only read notifications
 * @param bool $today_only Whether to fetch only today's notifications
 * @return array Array with notifications, pagination info, and counts
 */
function get_user_notifications_enhanced($user_id, $page = 1, $limit = 20, $unread_only = false, $read_only = false, $today_only = false) {
    global $conn;
    
    if (!$user_id) {
        return [
            'notifications' => [],
            'total_count' => 0,
            'unread_count' => 0,
            'total_pages' => 0,
            'current_page' => 1
        ];
    }
    
    $offset = ($page - 1) * $limit;
    
    // Base query conditions
    $where_conditions = ["user_id = ?"];
    $params = [$user_id];
    $param_types = 'i';
    
    // Handle read status filters
    if ($unread_only) {
        $where_conditions[] = "read_status = 0";
    } elseif ($read_only) {
        $where_conditions[] = "read_status = 1";
    }
    
    // Handle date filters
    if ($today_only) {
        $where_conditions[] = "DATE(created_at) = CURDATE()";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM notifications WHERE $where_clause";
    $stmt = $conn->prepare($count_query);
    if (!$stmt) {
        error_log("Failed to prepare notification count query: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $total_count = $stmt->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_count / $limit);
    
    // Get unread count (always for stats)
    $unread_count_query = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND read_status = 0";
    $stmt = $conn->prepare($unread_count_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $unread_count = $stmt->get_result()->fetch_assoc()['unread'];
    
    // Get notifications with pagination
    $notifications_query = "SELECT * FROM notifications 
                           WHERE $where_clause 
                           ORDER BY created_at DESC 
                           LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($notifications_query);
    if (!$stmt) {
        error_log("Failed to prepare notifications query: " . $conn->error);
        return false;
    }
    
    // Add limit and offset parameters
    $params[] = $limit;
    $params[] = $offset;
    $param_types .= 'ii';
    
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    return [
        'notifications' => $notifications,
        'total_count' => $total_count,
        'unread_count' => $unread_count,
        'total_pages' => $total_pages,
        'current_page' => $page
    ];
}

/**
 * Mark notifications as unread
 * @param int $user_id User ID
 * @param array|null $notification_ids Specific notification IDs (null for all)
 * @return bool Success status
 */
function mark_notifications_unread($user_id, $notification_ids = null) {
    global $conn;
    
    if (!$user_id) {
        return false;
    }
    
    if ($notification_ids === null) {
        // Mark all notifications as unread
        $query = "UPDATE notifications SET read_status = 0 WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare mark all unread query: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param('i', $user_id);
        $success = $stmt->execute();
        
        if ($success) {
            $affected_count = $stmt->affected_rows;
            log_audit_action("mark_all_notifications_unread", "Marked $affected_count notifications as unread");
        }
        
        return $success;
    }
    
    // Mark specific notifications as unread
    if (!is_array($notification_ids) || empty($notification_ids)) {
        return false;
    }
    
    $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
    $query = "UPDATE notifications SET read_status = 0 WHERE user_id = ? AND notification_id IN ($placeholders)";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare mark unread query: " . $conn->error);
        return false;
    }
    
    // Build parameter types and values
    $types = 'i' . str_repeat('i', count($notification_ids));
    $params = array_merge([$user_id], $notification_ids);
    
    $stmt->bind_param($types, ...$params);
    $success = $stmt->execute();
    
    if ($success) {
        $affected_count = $stmt->affected_rows;
        $ids_str = implode(',', $notification_ids);
        log_audit_action("mark_notifications_unread", "Marked $affected_count notifications as unread: $ids_str");
    }
    
    return $success;
}

/**
 * Mark notifications as read
 * @param int $user_id User ID
 * @param array|null $notification_ids Specific notification IDs (null for all)
 * @return bool Success status
 */
function mark_notifications_read($user_id, $notification_ids = null) {
    global $conn;
    
    if (!$user_id) {
        return false;
    }
    
    if ($notification_ids === null) {
        // Mark all notifications as read
        $query = "UPDATE notifications SET read_status = 1 WHERE user_id = ? AND read_status = 0";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare mark all read query: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param('i', $user_id);
        $success = $stmt->execute();
        
        if ($success) {
            // Log the action
            $affected_count = $stmt->affected_rows;
            log_audit_action("mark_all_notifications_read", "Marked $affected_count notifications as read");
        }
        
        return $success;
    } else {
        // Mark specific notifications as read
        if (empty($notification_ids)) {
            return true; // No notifications to mark
        }
        
        $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
        $query = "UPDATE notifications SET read_status = 1 
                  WHERE user_id = ? AND notification_id IN ($placeholders) AND read_status = 0";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare mark specific read query: " . $conn->error);
            return false;
        }
        
        $params = array_merge([$user_id], $notification_ids);
        $param_types = 'i' . str_repeat('i', count($notification_ids));
        
        $stmt->bind_param($param_types, ...$params);
        $success = $stmt->execute();
        
        if ($success) {
            $affected_count = $stmt->affected_rows;
            log_audit_action("mark_notifications_read", "Marked $affected_count specific notifications as read");
        }
        
        return $success;
    }
}

/**
 * Delete notifications
 * @param int $user_id User ID
 * @param array|null $notification_ids Specific notification IDs (null for all)
 * @return bool Success status
 */
function delete_notifications($user_id, $notification_ids = null) {
    global $conn;
    
    if (!$user_id) {
        return false;
    }
    
    if ($notification_ids === null) {
        // Delete all notifications for user
        $query = "DELETE FROM notifications WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare delete all query: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param('i', $user_id);
        $success = $stmt->execute();
        
        if ($success) {
            $affected_count = $stmt->affected_rows;
            log_audit_action("delete_all_notifications", "Deleted $affected_count notifications");
        }
        
        return $success;
    } else {
        // Delete specific notifications
        if (empty($notification_ids)) {
            return true; // No notifications to delete
        }
        
        $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
        $query = "DELETE FROM notifications WHERE user_id = ? AND notification_id IN ($placeholders)";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare delete specific query: " . $conn->error);
            return false;
        }
        
        $params = array_merge([$user_id], $notification_ids);
        $param_types = 'i' . str_repeat('i', count($notification_ids));
        
        $stmt->bind_param($param_types, ...$params);
        $success = $stmt->execute();
        
        if ($success) {
            $affected_count = $stmt->affected_rows;
            log_audit_action("delete_notifications", "Deleted $affected_count specific notifications");
        }
        
        return $success;
    }
}

/**
 * Get notification icon based on type
 * @param string $type Notification type
 * @return string Font Awesome icon class
 */
function get_notification_icon($type) {
    $icon_map = [
        'assigned_program' => 'tasks',
        'deadline' => 'clock',
        'update' => 'bell',
        'feedback' => 'comment',
        'program_assignment' => 'tasks',
        'program_reopened' => 'unlock',
        'system' => 'cog',
        'reminder' => 'bell'
    ];
    
    return $icon_map[$type] ?? 'info-circle';
}

/**
 * Format time ago for notifications
 * @param string $timestamp Database timestamp
 * @return string Formatted time ago string
 */
function format_time_ago($timestamp) {
    if (!$timestamp) {
        return 'Unknown time';
    }
    
    $time_ago = time() - strtotime($timestamp);
    
    if ($time_ago < 60) {
        return 'Just now';
    } elseif ($time_ago < 3600) {
        $minutes = floor($time_ago / 60);
        return $minutes . ' min' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time_ago < 86400) {
        $hours = floor($time_ago / 3600);
        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time_ago < 604800) {
        $days = floor($time_ago / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', strtotime($timestamp));
    }
}

/**
 * Get notification badge class based on type
 * @param string $type Notification type
 * @return string Bootstrap badge class
 */
function get_notification_badge_class($type) {
    $badge_map = [
        'assigned_program' => 'bg-primary',
        'deadline' => 'bg-warning',
        'update' => 'bg-info',
        'feedback' => 'bg-success',
        'program_assignment' => 'bg-primary',
        'program_reopened' => 'bg-secondary',
        'system' => 'bg-dark',
        'reminder' => 'bg-warning'
    ];
    
    return $badge_map[$type] ?? 'bg-secondary';
}

/**
 * Create a new notification
 * @param int $user_id User ID to notify
 * @param string $message Notification message
 * @param string $type Notification type
 * @param string|null $action_url Optional URL for action button
 * @return bool Success status
 */
function create_notification($user_id, $message, $type = 'system', $action_url = null) {
    global $conn;
    
    if (!$user_id || !$message) {
        return false;
    }
    
    $query = "INSERT INTO notifications (user_id, message, type, action_url, read_status, created_at) 
              VALUES (?, ?, ?, ?, 0, NOW())";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare create notification query: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param('isss', $user_id, $message, $type, $action_url);
    $success = $stmt->execute();
    
    if ($success) {
        log_audit_action("create_notification", "Created notification for user $user_id: $message");
    }
    
    return $success;
}

/**
 * Get notification statistics for a user
 * @param int $user_id User ID
 * @return array Notification statistics
 */
function get_notification_stats($user_id) {
    global $conn;
    
    if (!$user_id) {
        return [
            'total' => 0,
            'unread' => 0,
            'by_type' => [],
            'recent' => 0
        ];
    }
    
    // Get basic counts
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN read_status = 0 THEN 1 ELSE 0 END) as unread,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as recent
              FROM notifications 
              WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare notification stats query: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    
    // Get counts by type
    $type_query = "SELECT type, COUNT(*) as count 
                   FROM notifications 
                   WHERE user_id = ? 
                   GROUP BY type";
    
    $stmt = $conn->prepare($type_query);
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $by_type = [];
        while ($row = $result->fetch_assoc()) {
            $by_type[$row['type']] = $row['count'];
        }
        
        $stats['by_type'] = $by_type;
    } else {
        $stats['by_type'] = [];
    }
    
    return $stats;
}
