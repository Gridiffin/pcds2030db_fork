<?php
/**
 * Notifications Content Partial
 * Modern component-based notification interface
 */
?>

<div class="notifications-container">
    <!-- Loading State -->
    <div class="notifications-loading" style="display: none;">
        <div class="text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading notifications...</span>
            </div>
            <p class="text-muted">Loading notifications...</p>
        </div>
    </div>

    <!-- Error Messages -->
    <div class="notifications-error alert alert-danger" style="display: none;" role="alert">
        <!-- Error content will be populated by JavaScript -->
    </div>

    <!-- Success Messages -->
    <div class="notifications-success alert alert-success" style="display: none;" role="alert">
        <!-- Success content will be populated by JavaScript -->
    </div>

    <!-- Notifications Header -->
    <?php include 'notification_header.php'; ?>

    <!-- Notifications List -->
    <div class="notifications-list-container">
        <div class="notifications-list">
            <!-- Notification items will be populated by JavaScript -->
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <?php include 'notification_item.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Empty State -->
        <div class="notifications-empty" style="<?php echo empty($notifications) ? 'display: block;' : 'display: none;'; ?>">
            <div class="text-center py-5">
                <div class="empty-icon mb-4">
                    <i class="fas fa-bell-slash fa-4x text-muted"></i>
                </div>
                <h5 class="text-muted mb-3">No notifications found</h5>
                <p class="text-muted mb-4">
                    You don't have any notifications yet. When you receive notifications, they'll appear here.
                </p>
                <button class="btn btn-outline-primary refresh-notifications-btn">
                    <i class="fas fa-sync-alt me-2"></i>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (!empty($pagination) && $pagination['last_page'] > 1): ?>
        <?php include 'notification_pagination.php'; ?>
    <?php endif; ?>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="deleteNotificationsModal" tabindex="-1" aria-labelledby="deleteNotificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteNotificationsModalLabel">
                    <i class="fas fa-trash me-2 text-danger"></i>
                    Delete Notifications
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="alert-icon me-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">Are you sure?</h6>
                        <p class="mb-0 text-muted">You are about to delete <span class="delete-count fw-bold">0</span> notification(s). This action cannot be undone.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger confirm-delete-btn">
                    <i class="fas fa-trash me-2"></i>Delete Notifications
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mark All Read Confirmation Modal -->
<div class="modal fade" id="markAllReadModal" tabindex="-1" aria-labelledby="markAllReadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="markAllReadModalLabel">
                    <i class="fas fa-check-double me-2 text-success"></i>
                    Mark All as Read
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="alert-icon me-3">
                        <i class="fas fa-info-circle fa-2x text-info"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">Mark all notifications as read?</h6>
                        <p class="mb-0 text-muted">This will mark all your notifications as read. You can still view them later.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-success confirm-mark-all-read-btn">
                    <i class="fas fa-check-double me-2"></i>Mark All as Read
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript data for initialization -->
<script>
window.notificationsConfig = {
    currentPage: <?php echo $pagination['current_page'] ?? 1; ?>,
    perPage: <?php echo $pagination['per_page'] ?? 10; ?>,
    totalPages: <?php echo $pagination['last_page'] ?? 1; ?>,
    totalNotifications: <?php echo $stats['total'] ?? 0; ?>,
    unreadCount: <?php echo $stats['unread'] ?? 0; ?>,
    currentFilter: 'all',
    searchQuery: ''
};
</script>
