<?php
/**
 * Simple Notifications Content
 * Basic notification display with simple filtering
 */
?>

<div class="notifications-simple-container">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Total</h5>
                    <h3 class="text-primary" id="total-count"><?php echo $stats['total'] ?? 0; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Unread</h5>
                    <h3 class="text-warning" id="unread-count"><?php echo $stats['unread'] ?? 0; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Read</h5>
                    <h3 class="text-success" id="read-count"><?php echo ($stats['total'] ?? 0) - ($stats['unread'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Recent</h5>
                    <h3 class="text-info" id="recent-count"><?php echo $stats['recent'] ?? 0; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" data-filter="all">All</button>
                        <button type="button" class="btn btn-outline-primary active" data-filter="unread">Unread</button>
                        <button type="button" class="btn btn-outline-primary" data-filter="read">Read</button>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success" id="mark-all-read">Mark All Read</button>
                    <button class="btn btn-primary" id="refresh-notifications">Refresh</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div id="loading" class="text-center py-4" style="display: none;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Notifications List -->
    <div id="notifications-list">
        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">No notifications found.</div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="card mb-3 notification-item" 
                     data-id="<?php echo $notification['notification_id']; ?>" 
                     data-read="<?php echo $notification['read_status']; ?>">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="d-flex align-items-start">
                                    <?php if ($notification['read_status'] == 0): ?>
                                        <span class="badge bg-warning me-2">New</span>
                                    <?php endif; ?>
                                    <div>
                                        <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <small class="text-muted">
                                            <?php echo format_time_ago($notification['created_at']); ?>
                                            • Type: <?php echo ucfirst($notification['type']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <?php if ($notification['action_url']): ?>
                                    <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" 
                                       class="btn btn-sm btn-outline-primary me-2">View</a>
                                <?php endif; ?>
                                
                                <?php if ($notification['read_status'] == 0): ?>
                                    <button class="btn btn-sm btn-success mark-read" 
                                            data-id="<?php echo $notification['notification_id']; ?>">
                                        Mark Read
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-sm btn-danger delete-notification" 
                                        data-id="<?php echo $notification['notification_id']; ?>">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
        <nav aria-label="Notifications pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($pagination['current_page'] ?? 1) <= 1 ? 'disabled' : ''; ?>">
                    <button class="page-link" data-page="<?php echo ($pagination['current_page'] ?? 1) - 1; ?>">Previous</button>
                </li>
                
                <?php for ($i = 1; $i <= ($pagination['total_pages'] ?? 1); $i++): ?>
                    <li class="page-item <?php echo $i == ($pagination['current_page'] ?? 1) ? 'active' : ''; ?>">
                        <button class="page-link" data-page="<?php echo $i; ?>"><?php echo $i; ?></button>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo ($pagination['current_page'] ?? 1) >= ($pagination['total_pages'] ?? 1) ? 'disabled' : ''; ?>">
                    <button class="page-link" data-page="<?php echo ($pagination['current_page'] ?? 1) + 1; ?>">Next</button>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
// Simple notifications JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = window.location.origin + '/pcds2030_dashboard_fork';
    let currentFilter = 'unread'; // Changed default from 'read' to 'unread'
    let currentPage = 1;

    // Filter buttons
    document.querySelectorAll('[data-filter]').forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active button
            document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            currentFilter = this.dataset.filter;
            loadNotifications();
        });
    });

    // Mark all read
    document.getElementById('mark-all-read')?.addEventListener('click', function() {
        if (confirm('Mark all notifications as read?')) {
            markAllRead();
        }
    });

    // Refresh
    document.getElementById('refresh-notifications')?.addEventListener('click', function() {
        loadNotifications();
    });

    // Mark single as read
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('mark-read')) {
            const id = e.target.dataset.id;
            markAsRead([id]);
        }
    });

    // Delete notification
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-notification')) {
            const id = e.target.dataset.id;
            if (confirm('Delete this notification?')) {
                deleteNotification(id);
            }
        }
    });

    // Pagination
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('page-link') && e.target.dataset.page) {
            currentPage = parseInt(e.target.dataset.page);
            loadNotifications();
        }
    });

    // Load notifications
    function loadNotifications() {
        showLoading(true);
        
        const params = new URLSearchParams({
            page: currentPage,
            per_page: 10,
            filter: currentFilter
        });

        fetch(`${baseUrl}/app/ajax/get_user_notifications.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStats(data.stats);
                    updateNotificationsList(data.notifications);
                    updatePagination(data.pagination);
                } else {
                    alert('Error loading notifications: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load notifications');
            })
            .finally(() => {
                showLoading(false);
            });
    }

    // Mark as read
    function markAsRead(ids) {
        const formData = new FormData();
        formData.append('action', 'mark_read');
        formData.append('notification_ids', JSON.stringify(ids));

        fetch(`${baseUrl}/app/ajax/notifications.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            } else {
                alert('Error: ' + (data.message || 'Failed to mark as read'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to mark notifications as read');
        });
    }

    // Mark all read
    function markAllRead() {
        const formData = new FormData();
        formData.append('action', 'mark_all_read');

        fetch(`${baseUrl}/app/ajax/notifications.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            } else {
                alert('Error: ' + (data.message || 'Failed to mark all as read'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to mark all notifications as read');
        });
    }

    // Delete notification
    function deleteNotification(id) {
        const formData = new FormData();
        formData.append('action', 'delete_notification');
        formData.append('notification_id', id);

        fetch(`${baseUrl}/app/ajax/notifications.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete notification'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete notification');
        });
    }

    // Update stats
    function updateStats(stats) {
        document.getElementById('total-count').textContent = stats.total_count || 0;
        document.getElementById('unread-count').textContent = stats.unread_count || 0;
        document.getElementById('read-count').textContent = stats.read_count || 0;
        document.getElementById('recent-count').textContent = stats.recent || 0;
    }

    // Update notifications list
    function updateNotificationsList(notifications) {
        const container = document.getElementById('notifications-list');
        
        if (!notifications || notifications.length === 0) {
            container.innerHTML = '<div class="alert alert-info">No notifications found.</div>';
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            const isUnread = notification.read_status == 0;
            html += `
                <div class="card mb-3 notification-item" data-id="${notification.notification_id}" data-read="${notification.read_status}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="d-flex align-items-start">
                                    ${isUnread ? '<span class="badge bg-warning me-2">New</span>' : ''}
                                    <div>
                                        <p class="mb-1">${escapeHtml(notification.message)}</p>
                                        <small class="text-muted">
                                            ${notification.time_ago || new Date(notification.created_at).toLocaleDateString()}
                                            • Type: ${notification.type.charAt(0).toUpperCase() + notification.type.slice(1)}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                ${notification.action_url ? `<a href="${escapeHtml(notification.action_url)}" class="btn btn-sm btn-outline-primary me-2">View</a>` : ''}
                                
                                ${isUnread ? `<button class="btn btn-sm btn-success mark-read" data-id="${notification.notification_id}">Mark Read</button>` : ''}
                                
                                <button class="btn btn-sm btn-danger delete-notification" data-id="${notification.notification_id}">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    // Update pagination
    function updatePagination(pagination) {
        // Simple pagination update - could be enhanced
        currentPage = pagination.current_page || 1;
    }

    // Show/hide loading
    function showLoading(show) {
        const loading = document.getElementById('loading');
        const list = document.getElementById('notifications-list');
        
        if (loading) loading.style.display = show ? 'block' : 'none';
        if (list) list.style.display = show ? 'none' : 'block';
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>