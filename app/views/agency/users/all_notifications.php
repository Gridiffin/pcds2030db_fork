<?php
/**
 * View All Notifications
 * 
 * Shows all notifications for the logged-in agency user
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/index.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'All Notifications';

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20; // Notifications per page
$offset = ($page - 1) * $limit;

// Get total notification count for pagination
$count_query = "SELECT COUNT(*) as total FROM notifications WHERE user_id = ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$total_count = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_count / $limit);

// Get unread notification count
$unread_count_query = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND read_status = 0";
$stmt = $conn->prepare($unread_count_query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['unread'];

// Query to fetch all notifications with pagination
$notifications_query = "SELECT * FROM notifications 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($notifications_query);
$stmt->bind_param('iii', $_SESSION['user_id'], $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Mark all unread notifications as read
if (isset($_GET['mark_all_read']) && $_GET['mark_all_read'] == 1) {
    // First mark all as read
    $mark_read_query = "UPDATE notifications SET read_status = 1 WHERE user_id = ? AND read_status = 0";
    $stmt = $conn->prepare($mark_read_query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    
    // Then delete all notifications (optional - remove this if you want to keep read notifications)
    $delete_query = "DELETE FROM notifications WHERE user_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    
    // Redirect to remove the query parameter
    header('Location: all_notifications.php');
    exit;
}

// Helper function to get notification icon
function get_notification_icon($type) {
    switch ($type) {
        case 'assigned_program': return 'tasks';
        case 'deadline': return 'clock';
        case 'update': return 'bell';
        case 'feedback': return 'comment';
        default: return 'info-circle';
    }
}

// Helper function to format time ago
function format_time_ago($timestamp) {
    $time_ago = time() - strtotime($timestamp);
    
    if ($time_ago < 60) {
        return 'Just now';
    } elseif ($time_ago < 3600) {
        return floor($time_ago / 60) . ' min ago';
    } elseif ($time_ago < 86400) {
        return floor($time_ago / 3600) . ' hrs ago';
    } else {
        return floor($time_ago / 86400) . ' days ago';
    }
}

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'All Notifications',
    'subtitle' => 'View and manage all your notifications',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'all_notifications.php?mark_all_read=1',
            'text' => 'Mark All as Read',
            'icon' => 'fas fa-check-double',
            'class' => 'btn-primary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<!-- Notifications Content -->
<section class="section">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-white d-flex justify-content-between align-items-center">
                            <span>All Notifications</span>
                            <div>
                                <?php if ($unread_count > 0): ?>
                                    <span class="badge bg-danger me-2"><?php echo $unread_count; ?> unread</span>
                                <?php endif; ?>
                                <?php if ($total_count > 0): ?>
                                    <span class="badge bg-secondary text-white"><?php echo $total_count; ?> total</span>
                                <?php endif; ?>
                            </div>
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                <p>No notifications found.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group notifications-list">
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="list-group-item notification-item <?php echo !$notification['read_status'] ? 'unread' : ''; ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="notification-icon-sm me-3">
                                                <i class="fas fa-<?php echo get_notification_icon($notification['type']); ?>"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="notification-message mb-1"><?php echo $notification['message']; ?></div>
                                                    <div class="notification-time text-muted small ms-3">
                                                        <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="notification-meta small text-muted">
                                                        <span class="badge bg-<?php 
                                                        switch ($notification['type']) {
                                                            case 'assigned_program': echo 'primary'; break;
                                                            case 'deadline': echo 'warning'; break;
                                                            case 'update': echo 'info'; break;
                                                            case 'feedback': echo 'success'; break;
                                                            default: echo 'secondary';
                                                        }
                                                        ?> me-2">
                                                            <?php echo ucfirst(str_replace('_', ' ', $notification['type'])); ?>
                                                        </span>
                                                        <span><?php echo format_time_ago($notification['created_at']); ?></span>
                                                    </div>
                                                    <?php if (isset($notification['action_url']) && $notification['action_url']): ?>
                                                        <a href="<?php echo $notification['action_url']; ?>" class="btn btn-sm btn-outline-primary">
                                                            View <i class="fas fa-arrow-right ms-1"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Notification pagination" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php 
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $start_page + 4);
                                        if ($end_page - $start_page < 4 && $start_page > 1) {
                                            $start_page = max(1, $end_page - 4);
                                        }
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++): 
                                        ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional Styles -->
<style>
    .notifications-list .notification-item {
        padding: 1rem;
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }
    
    .notifications-list .notification-item.unread {
        background-color: rgba(13, 110, 253, 0.05);
        border-left-color: var(--primary);
    }
    
    .notification-icon-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: rgba(0, 123, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
    }
    
    .notification-message {
        font-weight: 500;
    }
</style>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>

