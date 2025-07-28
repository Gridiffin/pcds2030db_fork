<?php
/**
 * Individual Notification Item Partial
 * Renders a single notification with all interactive elements
 */

// Helper functions for notification rendering
if (!function_exists('get_priority_class')) {
    function get_priority_class($priority) {
        $classes = [
            'low' => 'priority-low',
            'normal' => 'priority-normal',
            'high' => 'priority-high',
            'urgent' => 'priority-urgent'
        ];
        return $classes[$priority] ?? 'priority-normal';
    }
}

if (!function_exists('get_priority_badge_color')) {
    function get_priority_badge_color($priority) {
        $colors = [
            'low' => 'info',
            'normal' => 'secondary',
            'high' => 'warning',
            'urgent' => 'danger'
        ];
        return $colors[$priority] ?? 'secondary';
    }
}

if (!function_exists('get_type_icon')) {
    function get_type_icon($type) {
        $icons = [
            'system' => 'fas fa-cog',
            'update' => 'fas fa-sync',
            'reminder' => 'fas fa-bell',
            'alert' => 'fas fa-exclamation-triangle',
            'info' => 'fas fa-info-circle',
            'success' => 'fas fa-check-circle',
            'warning' => 'fas fa-exclamation-circle',
            'error' => 'fas fa-times-circle',
            'assigned_program' => 'fas fa-tasks',
            'deadline' => 'fas fa-clock',
            'feedback' => 'fas fa-comment'
        ];
        return $icons[$type] ?? 'fas fa-bell';
    }
}

if (!function_exists('get_type_badge_color')) {
    function get_type_badge_color($type) {
        $colors = [
            'system' => 'secondary',
            'update' => 'primary',
            'reminder' => 'info',
            'alert' => 'warning',
            'info' => 'info',
            'success' => 'success',
            'warning' => 'warning',
            'error' => 'danger',
            'assigned_program' => 'primary',
            'deadline' => 'warning',
            'feedback' => 'success'
        ];
        return $colors[$type] ?? 'secondary';
    }
}

$isUnread = ($notification['read_at'] ?? null) === null;
$timeAgo = format_time_ago($notification['created_at']);
$priorityClass = get_priority_class($notification['priority'] ?? 'normal');
$typeIcon = get_type_icon($notification['type'] ?? 'info');
$typeBadgeColor = get_type_badge_color($notification['type'] ?? 'info');
?>

<div class="notification-item <?php echo $isUnread ? 'unread' : 'read'; ?>" data-id="<?php echo htmlspecialchars($notification['notification_id'] ?? ''); ?>">
    <!-- Selection Checkbox -->
    <div class="notification-select">
        <input type="checkbox" class="notification-checkbox" value="<?php echo htmlspecialchars($notification['notification_id'] ?? ''); ?>">
    </div>

    <!-- Notification Icon -->
    <div class="notification-icon <?php echo $priorityClass; ?>">
        <i class="<?php echo $typeIcon; ?>"></i>
    </div>

    <!-- Notification Content -->
    <div class="notification-content">
        <!-- Header with title and time -->
        <div class="notification-header">
            <h6 class="notification-title">
                <?php echo htmlspecialchars($notification['title'] ?? 'Notification'); ?>
                <?php if ($isUnread): ?>
                    <span class="unread-indicator"></span>
                <?php endif; ?>
            </h6>
            <span class="notification-time" title="<?php echo date('F j, Y g:i A', strtotime($notification['created_at'])); ?>">
                <?php echo $timeAgo; ?>
            </span>
        </div>

        <!-- Message Body -->
        <div class="notification-body">
            <p class="notification-message">
                <?php echo htmlspecialchars($notification['message']); ?>
            </p>
            
            <?php if (!empty($notification['action_url'])): ?>
                <div class="notification-actions">
                    <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" class="btn btn-sm btn-outline-primary notification-action-link">
                        <i class="fas fa-external-link-alt me-1"></i>
                        View Details
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Meta Information -->
        <div class="notification-meta">
            <div class="notification-badges">
                <span class="notification-type badge badge-<?php echo $typeBadgeColor; ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $notification['type'] ?? 'info')); ?>
                </span>
                
                <?php if (($notification['priority'] ?? 'normal') !== 'normal'): ?>
                    <span class="notification-priority badge badge-<?php echo get_priority_badge_color($notification['priority']); ?>">
                        <?php echo strtoupper($notification['priority']); ?>
                    </span>
                <?php endif; ?>
                
                <?php if (!empty($notification['category'])): ?>
                    <span class="notification-category badge badge-secondary">
                        <?php echo htmlspecialchars($notification['category']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Actions Menu -->
    <div class="notification-actions-menu">
        <div class="dropdown">
            <button class="btn btn-sm btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <?php if ($isUnread): ?>
                    <li>
                        <button class="dropdown-item mark-read-btn" data-id="<?php echo htmlspecialchars($notification['id']); ?>">
                            <i class="fas fa-eye me-2"></i>
                            Mark as Read
                        </button>
                    </li>
                <?php else: ?>
                    <li>
                        <button class="dropdown-item mark-unread-btn" data-id="<?php echo htmlspecialchars($notification['id']); ?>">
                            <i class="fas fa-eye-slash me-2"></i>
                            Mark as Unread
                        </button>
                    </li>
                <?php endif; ?>
                
                <?php if (!empty($notification['action_url'])): ?>
                    <li>
                        <a class="dropdown-item" href="<?php echo htmlspecialchars($notification['action_url']); ?>">
                            <i class="fas fa-external-link-alt me-2"></i>
                            Open Link
                        </a>
                    </li>
                <?php endif; ?>
                
                <li><hr class="dropdown-divider"></li>
                
                <li>
                    <button class="dropdown-item text-danger delete-notification-btn" data-id="<?php echo htmlspecialchars($notification['id']); ?>">
                        <i class="fas fa-trash me-2"></i>
                        Delete
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php
?>
