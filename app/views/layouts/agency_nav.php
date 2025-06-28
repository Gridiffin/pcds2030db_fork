<?php
/**
 * Agency Navigation
 * 
 * Main navigation menu for agency users.
 */

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// Query to fetch notifications
$notifications_query = "SELECT n.* FROM notifications n 
                       WHERE n.user_id = ? AND n.read_status = 0 
                       ORDER BY n.created_at DESC LIMIT 5";
$stmt = $conn->prepare($notifications_query);

// Initialize empty notifications array
$notifications = [];
$unread_count = 0;

// Check if prepare statement was successful
if ($stmt) {
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    // Count unread notifications
    $unread_count = count($notifications);
} else {
    // Log the error
    error_log("Failed to prepare notifications query: " . $conn->error);
}

// Format notifications for display
if (!function_exists('get_notification_icon')) {
    function get_notification_icon($type) {
        switch ($type) {
            case 'assigned_program': return 'tasks';
            case 'deadline': return 'clock';
            case 'update': return 'bell';
            case 'feedback': return 'comment';
            default: return 'info-circle';
        }
    }
}

if (!function_exists('format_time_ago')) {
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
}
?>

<!-- Main Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid px-4">        <a class="navbar-brand" href="<?php echo APP_URL; ?>/app/views/agency/dashboard/dashboard.php" 
           data-full-text="<?php echo APP_NAME; ?>" 
           data-short-text="PCDS 2030 Dashboard"
           data-ultra-short-text="PCDS Dashboard">
            <span class="brand-text"><?php echo APP_NAME; ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'dashboard.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/agency/dashboard/dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'view_programs.php' || $current_page == 'create_program.php' || $current_page == 'update_program.php' || $current_page == 'program_details.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/agency/programs/view_programs.php">
                        <i class="fas fa-project-diagram me-1"></i> My Programs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'view_initiatives.php' || $current_page == 'view_initiative.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/agency/initiatives/view_initiatives.php">
                        <i class="fas fa-lightbulb me-1"></i> Initiatives
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if (in_array($current_page, ['submit_outcomes.php', 'create_outcome_flexible.php', 'create_outcome.php', 'view_outcome.php', 'view_outcome_flexible.php', 'edit_outcomes.php', 'create_outcomes_detail.php', 'update_metric_detail.php'])) echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/agency/outcomes/submit_outcomes.php">
                        <i class="fas fa-chart-line me-1"></i> Outcomes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'view_all_sectors.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/agency/sectors/view_all_sectors.php">
                        <i class="fas fa-globe me-1"></i> All Sectors
                    </a>
                </li>
            </ul>            <div class="d-flex align-items-center ms-auto">
                <!-- Public Reports Icon -->
                <div class="me-3">
                    <a href="<?php echo APP_URL; ?>/app/views/agency/reports/public_reports.php" 
                       class="btn btn-link nav-link p-0 <?php if ($current_page == 'public_reports.php') echo 'active'; ?>" 
                       title="Public Reports">
                        <i class="fas fa-file-download"></i>
                    </a>
                </div>
                
                <!-- Notifications Dropdown -->
                <div class="dropdown me-3">
                    <button class="btn btn-link nav-link position-relative dropdown-toggle p-0 <?php if ($current_page == 'all_notifications.php') echo 'active notification-active'; ?>" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                <?php echo $unread_count; ?>
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown notification-dropdown-mobile p-0" aria-labelledby="notificationsDropdown">
                        <div class="notification-header p-3">
                            <h6 class="m-0 d-flex justify-content-between align-items-center">
                                <span>Notifications</span>
                                <?php if ($unread_count > 0): ?>
                                    <span class="badge bg-danger ms-3"><?php echo $unread_count; ?> new</span>
                                <?php endif; ?>
                            </h6>
                        </div>
                        <div class="notification-body" style="max-height: 300px; overflow-y: auto;">
                            <?php if (empty($notifications)): ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-bell-slash text-muted mb-2"></i>
                                    <p class="mb-0 small">No new notifications</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($notifications as $notification): ?>
                                        <?php 
                                        // Simplify notification messages for compact display
                                        $message = $notification['message'];
                                        if (strlen($message) > 60) {
                                            $message = substr($message, 0, 57) . '...';
                                        }
                                        ?>
                                        <a href="<?php echo $notification['action_url'] ?? '#'; ?>" class="list-group-item list-group-item-action notification-item <?php echo !$notification['read_status'] ? 'unread' : ''; ?>" data-id="<?php echo $notification['notification_id']; ?>">
                                            <div class="d-flex w-100 align-items-center">                                                <div class="notification-icon-sm me-3">
                                                    <i class="fas fa-<?php echo get_notification_icon($notification['type']); ?>"></i>
                                                </div>
                                                <div class="flex-grow-1 min-width-0">
                                                    <div class="notification-message"><?php echo $message; ?></div>
                                                    <div class="notification-time small text-muted"><?php echo format_time_ago($notification['created_at']); ?></div>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>                <div class="notification-footer border-top p-2 text-center">
                            <a href="<?php echo APP_URL; ?>/app/views/agency/users/all_notifications.php" class="small">View all notifications</a>
                        </div>
                    </div>
                </div>
                
                <div class="user-info me-3 text-dark">
                    <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['agency_name']); ?>
                </div>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline-danger btn-sm logout-btn">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>            </div>
        </div>
    </div>
</nav>
