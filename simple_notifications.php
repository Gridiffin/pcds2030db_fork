<?php
/**
 * Simple Notifications Test Page
 * Direct access without routing for testing
 */

// Define PROJECT_ROOT_PATH if not already defined
if (!defined('PROJECT_ROOT_PATH')) {
    // Use absolute path resolution that works regardless of working directory
    $current_file = __FILE__;
    $project_root = dirname($current_file);
    define('PROJECT_ROOT_PATH', $project_root . DIRECTORY_SEPARATOR);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Simple Notifications</h1>
        
        <?php include PROJECT_ROOT_PATH . 'app/views/agency/users/partials/notifications_content_simple.php'; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>