<?php
/**
 * Admin Notifications Content Partial
 * 
 * Main content area for admin notification management using modern admin styling.
 */
?>

<div class="admin-notifications-container">
    <!-- Page Header -->
    <div class="admin-page-header">
        <div class="admin-page-title">
            <h1><i class="fas fa-bell"></i> Manage Notifications</h1>
            <p class="admin-page-subtitle">Manage system notifications and monitor notification activity</p>
        </div>
        
        <div class="admin-page-actions">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                <i class="fas fa-plus"></i> Send System Notification
            </button>
            <button type="button" class="btn btn-outline-secondary" id="refreshStats">
                <i class="fas fa-sync-alt"></i> Refresh Stats
            </button>
        </div>
    </div>

    <!-- Notification Statistics Dashboard -->
    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-header">
                <h3>Total Notifications</h3>
                <i class="fas fa-bell stat-icon"></i>
            </div>
            <div class="admin-stat-value" id="totalNotifications">
                <?php echo number_format($notification_stats['total_notifications'] ?? 0); ?>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-header">
                <h3>Unread Notifications</h3>
                <i class="fas fa-bell-slash stat-icon"></i>
            </div>
            <div class="admin-stat-value" id="unreadNotifications">
                <?php echo number_format($notification_stats['unread_notifications'] ?? 0); ?>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-header">
                <h3>Users with Notifications</h3>
                <i class="fas fa-users stat-icon"></i>
            </div>
            <div class="admin-stat-value" id="usersWithNotifications">
                <?php echo number_format($notification_stats['users_with_notifications'] ?? 0); ?>
            </div>
        </div>
        
        <div class="admin-stat-card">
            <div class="admin-stat-header">
                <h3>Last 24 Hours</h3>
                <i class="fas fa-clock stat-icon"></i>
            </div>
            <div class="admin-stat-value" id="notificationsLast24h">
                <?php echo number_format($notification_stats['notifications_last_24h'] ?? 0); ?>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="admin-content-wrapper">
        <!-- Notification Management Tools -->
        <div class="admin-modern-box">
            <div class="admin-modern-box-header">
                <h3><i class="fas fa-tools"></i> Notification Management Tools</h3>
            </div>
            <div class="admin-modern-box-content">
                <div class="row">
                    <div class="col-md-6">
                        <div class="tool-card">
                            <h4><i class="fas fa-broadcast-tower"></i> System Notifications</h4>
                            <p>Send notifications to all users in the system</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                                Send Notification
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="tool-card">
                            <h4><i class="fas fa-trash-alt"></i> Cleanup Old Notifications</h4>
                            <p>Remove old notifications to maintain system performance</p>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#cleanupNotificationsModal">
                                Cleanup Notifications
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="admin-modern-box">
            <div class="admin-modern-box-header">
                <h3><i class="fas fa-history"></i> Recent Notifications</h3>
                <div class="admin-modern-box-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="refreshNotifications">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="admin-modern-box-content">
                <div class="table-responsive">
                    <table class="table table-hover admin-table" id="recentNotificationsTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Message</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_notifications)): ?>
                                <?php foreach ($recent_notifications as $notification): ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <strong><?php echo htmlspecialchars($notification['fullname'] ?? $notification['username'] ?? 'Unknown User'); ?></strong>
                                                <?php if (!empty($notification['agency_name'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($notification['agency_name']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="notification-message">
                                                <?php echo htmlspecialchars(substr($notification['message'], 0, 100)); ?>
                                                <?php if (strlen($notification['message']) > 100): ?>...<?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo get_notification_badge_class($notification['type']); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $notification['type'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($notification['read_status']): ?>
                                                <span class="badge bg-success">Read</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Unread</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo format_time_ago($notification['created_at']); ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($notification['action_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" 
                                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No recent notifications found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send System Notification Modal -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1" aria-labelledby="sendNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendNotificationModalLabel">
                    <i class="fas fa-broadcast-tower"></i> Send System Notification
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="sendNotificationForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="notificationMessage" class="form-label">Message *</label>
                        <textarea class="form-control" id="notificationMessage" name="message" rows="3" 
                                  placeholder="Enter notification message..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notificationType" class="form-label">Type</label>
                        <select class="form-select" id="notificationType" name="type">
                            <option value="system">System</option>
                            <option value="announcement">Announcement</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="update">Update</option>
                            <option value="reminder">Reminder</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="actionUrl" class="form-label">Action URL (Optional)</label>
                        <input type="url" class="form-control" id="actionUrl" name="action_url" 
                               placeholder="https://example.com/action">
                        <div class="form-text">Optional URL for users to take action on this notification</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Notification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cleanup Notifications Modal -->
<div class="modal fade" id="cleanupNotificationsModal" tabindex="-1" aria-labelledby="cleanupNotificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cleanupNotificationsModalLabel">
                    <i class="fas fa-trash-alt"></i> Cleanup Old Notifications
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cleanupNotificationsForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action will permanently delete old notifications. This cannot be undone.
                    </div>
                    
                    <div class="mb-3">
                        <label for="daysToKeep" class="form-label">Days to Keep</label>
                        <input type="number" class="form-control" id="daysToKeep" name="days_to_keep" 
                               value="30" min="1" max="365" required>
                        <div class="form-text">Notifications older than this many days will be deleted</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-trash-alt"></i> Cleanup Notifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>