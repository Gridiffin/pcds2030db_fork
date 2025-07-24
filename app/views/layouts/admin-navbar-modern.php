<?php
/**
 * Modern Admin Navigation Component
 * Performance-focused design with forest theme
 * Based on modern navbar but with admin-specific links
 */

// Ensure database connection is available
if (!isset($conn)) {
    global $conn;
    if (!$conn && defined('PROJECT_ROOT_PATH')) {
        require_once PROJECT_ROOT_PATH . 'app/config/database.php';
    }
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Initialize notifications (from original code)
$notifications_query = "SELECT n.* FROM notifications n 
                       WHERE n.user_id = ? AND n.read_status = 0 
                       ORDER BY n.created_at DESC LIMIT 5";
$stmt = $conn ? $conn->prepare($notifications_query) : null;

$notifications = [];
$unread_count = 0;

if ($stmt && isset($_SESSION['user_id'])) {
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    $unread_count = count($notifications);
} else {
    error_log("Failed to prepare notifications query or user_id not set");
}

// Helper functions
if (!function_exists('get_notification_icon')) {
    function get_notification_icon($type) {
        switch ($type) {
            case 'assigned_program': return 'fas fa-tasks';
            case 'deadline': return 'fas fa-clock';
            case 'update': return 'fas fa-bell';
            case 'feedback': return 'fas fa-comment';
            default: return 'fas fa-info-circle';
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

// Admin navigation items
$nav_items = [
    [
        'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php',
        'label' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'active_pages' => ['dashboard.php']
    ],
    [
        'url' => APP_URL . '/app/views/admin/programs/programs.php',
        'label' => 'Programs',
        'icon' => 'fas fa-project-diagram',
        'active_pages' => ['programs.php', 'create_program.php', 'edit_program.php', 'program_details.php']
    ],
    [
        'url' => APP_URL . '/app/views/admin/users/users.php',
        'label' => 'Users',
        'icon' => 'fas fa-users',
        'active_pages' => ['users.php', 'create_user.php', 'edit_user.php', 'user_details.php']
    ],
    [
        'url' => APP_URL . '/app/views/admin/reports/reports.php',
        'label' => 'Reports',
        'icon' => 'fas fa-chart-bar',
        'active_pages' => ['reports.php', 'generate_report.php', 'view_report.php']
    ],
    [
        'url' => APP_URL . '/app/views/admin/settings/settings.php',
        'label' => 'Settings',
        'icon' => 'fas fa-cog',
        'active_pages' => ['settings.php', 'system_settings.php', 'email_settings.php']
    ]
];

// Get user initials for avatar
$user_initials = '';
if (isset($_SESSION['fullname'])) {
    $names = explode(' ', $_SESSION['fullname']);
    $user_initials = substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : '');
} else {
    $user_initials = substr($_SESSION['username'], 0, 2);
}
$user_initials = strtoupper($user_initials);
?>

<nav class="navbar-modern fixed-top" role="navigation" aria-label="Admin navigation">
    <div class="navbar-container">
        <!-- Brand Section -->
        <a href="<?php echo APP_URL; ?>/app/views/admin/dashboard/dashboard.php" 
           class="navbar-brand-modern"
           aria-label="<?php echo APP_NAME; ?> Admin">
            <i class="fas fa-leaf brand-icon" aria-hidden="true"></i>
            <span class="brand-text" 
                  data-full-text="<?php echo APP_NAME; ?> Admin"
                  data-short-text="PCDS Admin"
                  data-ultra-short-text="Admin"><?php echo APP_NAME; ?> Admin</span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggle-modern" 
                type="button" 
                aria-label="Toggle navigation menu"
                aria-expanded="false"
                data-toggle="mobile-nav">
            <span class="toggle-line"></span>
            <span class="toggle-line"></span>
            <span class="toggle-line"></span>
        </button>

        <!-- Navigation Menu -->
        <ul class="navbar-nav-modern" role="menubar">
            <?php foreach ($nav_items as $item): ?>
                <?php $is_active = in_array($current_page, $item['active_pages']); ?>
                <li class="nav-item-modern" role="none">
                    <a href="<?php echo $item['url']; ?>" 
                       class="nav-link-modern <?php echo $is_active ? 'active' : ''; ?>"
                       role="menuitem"
                       <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
                        <i class="<?php echo $item['icon']; ?> nav-icon" aria-hidden="true"></i>
                        <?php echo $item['label']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Actions Section -->
        <div class="navbar-actions-modern">
            <!-- Search -->
            <div class="navbar-search-modern">
                <i class="fas fa-search navbar-search-icon" aria-hidden="true"></i>
                <input type="search" 
                       class="navbar-search-input" 
                       placeholder="Search admin..."
                       aria-label="Search the admin dashboard">
            </div>

            <!-- Notifications -->
            <div class="navbar-notifications">
                <button class="notifications-toggle" 
                        type="button"
                        aria-label="Notifications"
                        aria-expanded="false"
                        data-toggle="notifications-dropdown">
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="notifications-badge" aria-label="<?php echo $unread_count; ?> unread notifications">
                            <?php echo $unread_count; ?>
                        </span>
                    <?php endif; ?>
                </button>

                <div class="notifications-dropdown" role="menu" aria-labelledby="notifications-toggle">
                    <div class="notifications-header">
                        <h3 class="notifications-title">
                            Notifications
                            <?php if ($unread_count > 0): ?>
                                <span class="notifications-badge"><?php echo $unread_count; ?> new</span>
                            <?php endif; ?>
                        </h3>
                    </div>

                    <div class="notifications-list">
                        <?php if (empty($notifications)): ?>
                            <div class="notification-item">
                                <div class="notification-icon">
                                    <i class="fas fa-bell-slash" aria-hidden="true"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-message">No new notifications</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <?php 
                                $message = strlen($notification['message']) > 60 
                                    ? substr($notification['message'], 0, 57) . '...' 
                                    : $notification['message'];
                                ?>
                                <a href="<?php echo $notification['action_url'] ?? '#'; ?>" 
                                   class="notification-item <?php echo !$notification['read_status'] ? 'unread' : ''; ?>"
                                   role="menuitem"
                                   data-notification-id="<?php echo $notification['notification_id']; ?>">
                                    <div class="notification-icon">
                                        <i class="<?php echo get_notification_icon($notification['type']); ?>" aria-hidden="true"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-message"><?php echo htmlspecialchars($message); ?></div>
                                        <div class="notification-time"><?php echo format_time_ago($notification['created_at']); ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="notifications-footer">
                        <a href="<?php echo APP_URL; ?>/app/views/admin/notifications/all_notifications.php">
                            View all notifications
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="navbar-user">
                <button class="user-toggle" 
                        type="button"
                        aria-label="User menu"
                        aria-expanded="false"
                        data-toggle="user-dropdown">
                    <div class="user-avatar" aria-hidden="true"><?php echo $user_initials; ?></div>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']); ?></span>
                    <i class="fas fa-chevron-down user-chevron" aria-hidden="true"></i>
                </button>

                <div class="user-dropdown" role="menu" aria-labelledby="user-toggle">
                    <a href="#" class="user-dropdown-item" role="menuitem">
                        <i class="fas fa-user item-icon" aria-hidden="true"></i>
                        Profile
                    </a>
                    <a href="<?php echo APP_URL; ?>/app/views/admin/settings/settings.php" class="user-dropdown-item" role="menuitem">
                        <i class="fas fa-cog item-icon" aria-hidden="true"></i>
                        Admin Settings
                    </a>
                    <a href="<?php echo APP_URL; ?>/app/views/admin/users/users.php" class="user-dropdown-item" role="menuitem">
                        <i class="fas fa-users item-icon" aria-hidden="true"></i>
                        Manage Users
                    </a>
                    <div class="user-dropdown-divider" role="separator"></div>
                    <a href="<?php echo APP_URL; ?>/logout.php" class="user-dropdown-item" role="menuitem">
                        <i class="fas fa-sign-out-alt item-icon" aria-hidden="true"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Modern Navbar JavaScript (same as agency navbar) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const mobileToggle = document.querySelector('[data-toggle="mobile-nav"]');
    const navMenu = document.querySelector('.navbar-nav-modern');
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            this.setAttribute('aria-expanded', !isExpanded);
            this.classList.toggle('active');
            navMenu.classList.toggle('show');
        });
    }
    
    // Notifications dropdown
    const notificationsToggle = document.querySelector('[data-toggle="notifications-dropdown"]');
    const notificationsDropdown = document.querySelector('.notifications-dropdown');
    
    if (notificationsToggle && notificationsDropdown) {
        notificationsToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Close user dropdown if open
            closeUserDropdown();
            
            this.setAttribute('aria-expanded', !isExpanded);
            notificationsDropdown.classList.toggle('show');
        });
    }
    
    // User dropdown
    const userToggle = document.querySelector('[data-toggle="user-dropdown"]');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if (userToggle && userDropdown) {
        userToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Close notifications dropdown if open
            closeNotificationsDropdown();
            
            this.setAttribute('aria-expanded', !isExpanded);
            this.classList.toggle('active');
            userDropdown.classList.toggle('show');
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        closeNotificationsDropdown();
        closeUserDropdown();
    });
    
    // Helper functions
    function closeNotificationsDropdown() {
        if (notificationsToggle && notificationsDropdown) {
            notificationsToggle.setAttribute('aria-expanded', 'false');
            notificationsDropdown.classList.remove('show');
        }
    }
    
    function closeUserDropdown() {
        if (userToggle && userDropdown) {
            userToggle.setAttribute('aria-expanded', 'false');
            userToggle.classList.remove('active');
            userDropdown.classList.remove('show');
        }
    }
    
    // Handle search functionality
    const searchInput = document.querySelector('.navbar-search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                // Implement admin search functionality here
                console.log('Admin search for:', this.value);
            }
        });
    }
    
    // Mark notifications as read when clicked
    const notificationItems = document.querySelectorAll('[data-notification-id]');
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            // Implement mark as read functionality here
            console.log('Mark notification as read:', notificationId);
        });
    });
    
    // Handle responsive brand text
    function updateBrandText() {
        const brandText = document.querySelector('.brand-text');
        if (!brandText) return;
        
        const screenWidth = window.innerWidth;
        
        if (screenWidth <= 480 && brandText.dataset.ultraShortText) {
            brandText.textContent = brandText.dataset.ultraShortText;
        } else if (screenWidth <= 1200 && brandText.dataset.shortText) {
            brandText.textContent = brandText.dataset.shortText;
        } else {
            brandText.textContent = brandText.dataset.fullText;
        }
    }
    
    updateBrandText();
    window.addEventListener('resize', updateBrandText);
});
</script>