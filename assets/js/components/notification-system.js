/**
 * Enhanced Notification System
 * 
 * Provides real-time notification updates, toast notifications,
 * filtering, and enhanced user experience features.
 */

class NotificationSystem {
    constructor(options = {}) {
        this.options = {
            // API endpoints
            apiUrl: '/app/ajax/notifications.php',
            pollInterval: 30000, // 30 seconds
            
            // UI selectors
            badgeSelector: '.notification-badge',
            dropdownSelector: '.notification-dropdown',
            listSelector: '.notification-list',
            
            // Features
            enablePolling: true,
            enableToasts: true,
            enableSound: false,
            maxNotifications: 20,
            
            // Callbacks
            onNewNotification: null,
            onNotificationRead: null,
            onError: null,
            
            ...options
        };
        
        this.notifications = [];
        this.unreadCount = 0;
        this.isPolling = false;
        this.pollTimer = null;
        this._initialLoad = true; // <-- Add flag to track initial load
        this.init();
    }
    
    init() {
        this.createToastContainer();
        this.bindEvents();
        
        if (this.options.enablePolling) {
            this.startPolling();
        }
        
        // Load initial notifications
        this.loadNotifications();
        
        console.log('Notification system initialized');
    }
    
    createToastContainer() {
        if (!document.querySelector('.toast-container')) {
            const container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1060';
            document.body.appendChild(container);
        }
    }
    
    bindEvents() {
        // Mark as read when notification is clicked
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-item')) {
                const notificationId = e.target.closest('.notification-item').dataset.notificationId;
                if (notificationId) {
                    this.markAsRead(notificationId);
                }
            }
        });
        
        // Mark all as read button
        document.addEventListener('click', (e) => {
            if (e.target.matches('.mark-all-read-btn')) {
                e.preventDefault();
                this.markAllAsRead();
            }
        });
        
        // Clear all notifications button
        document.addEventListener('click', (e) => {
            if (e.target.matches('.clear-all-notifications-btn')) {
                e.preventDefault();
                this.clearAllNotifications();
            }
        });
        
        // Page visibility change - resume/pause polling
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                this.resumePolling();
            } else {
                this.pausePolling();
            }
        });
    }
    
    async loadNotifications() {
        try {
            const response = await fetch(this.options.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_notifications&limit=' + this.options.maxNotifications
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateNotifications(data.notifications, data.unread_count);
            } else {
                this.handleError('Failed to load notifications: ' + data.message);
            }
        } catch (error) {
            this.handleError('Network error: ' + error.message);
        }
    }
    
    updateNotifications(notifications, unreadCount) {
        const previousUnreadCount = this.unreadCount;
        this.notifications = notifications;
        this.unreadCount = unreadCount;
        
        // Update UI
        this.updateBadge();
        this.updateDropdown();
        
        // Only show toasts for new notifications after initial load
        if (!this._initialLoad && unreadCount > previousUnreadCount && previousUnreadCount >= 0) {
            const newNotifications = notifications.slice(0, unreadCount - previousUnreadCount);
            console.log('New notifications detected:', newNotifications.length, 'showing toasts for these only after initial load');
            newNotifications.forEach(notification => {
                // showToast is disabled for better UX, but you can enable it here if needed
                if (this.options.onNewNotification) {
                    this.options.onNewNotification(notification);
                }
            });
        }
        this._initialLoad = false; // <-- Set flag to false after first update
    }
    
    updateBadge() {
        const badges = document.querySelectorAll(this.options.badgeSelector);
        badges.forEach(badge => {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'inline-block';
                badge.classList.add('animate-pulse');
                setTimeout(() => badge.classList.remove('animate-pulse'), 1000);
            } else {
                badge.style.display = 'none';
            }
        });
    }
    
    updateDropdown() {
        const dropdowns = document.querySelectorAll(this.options.dropdownSelector);
        dropdowns.forEach(dropdown => {
            const list = dropdown.querySelector(this.options.listSelector);
            if (list) {
                list.innerHTML = this.renderNotificationList();
            }
        });
    }
    
    renderNotificationList() {
        if (this.notifications.length === 0) {
            return `
                <div class="notification-empty text-center py-4">
                    <i class="fas fa-bell-slash text-muted fa-2x mb-2"></i>
                    <p class="text-muted mb-0">No notifications</p>
                </div>
            `;
        }
        
        let html = '';
        
        // Add action buttons
        if (this.unreadCount > 0) {
            html += `
                <div class="notification-actions p-2 border-bottom">
                    <button class="btn btn-sm btn-outline-primary mark-all-read-btn me-2">
                        <i class="fas fa-check"></i> Mark All Read
                    </button>
                    <button class="btn btn-sm btn-outline-secondary clear-all-notifications-btn">
                        <i class="fas fa-trash"></i> Clear All
                    </button>
                </div>
            `;
        }
        
        // Add notifications
        this.notifications.forEach(notification => {
            html += this.renderNotificationItem(notification);
        });
        
        return html;
    }
    
    renderNotificationItem(notification) {
        const isUnread = notification.read_status == 0;
        const timeAgo = this.formatTimeAgo(notification.created_at);
        const icon = this.getNotificationIcon(notification.type);
        
        return `
            <div class="notification-item ${isUnread ? 'unread' : ''}" 
                 data-notification-id="${notification.notification_id}">
                <div class="notification-content p-3 border-bottom">
                    <div class="d-flex align-items-start">
                        <div class="notification-icon me-3">
                            <i class="fas fa-${icon} ${isUnread ? 'text-primary' : 'text-muted'}"></i>
                        </div>
                        <div class="notification-text flex-grow-1">
                            <div class="notification-message ${isUnread ? 'fw-bold' : ''}">
                                ${this.escapeHtml(notification.message)}
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> ${timeAgo}
                            </small>
                        </div>
                        ${isUnread ? '<div class="notification-indicator"><span class="badge bg-primary rounded-pill">&nbsp;</span></div>' : ''}
                    </div>
                    ${notification.action_url ? `
                        <div class="notification-action mt-2">
                            <a href="${notification.action_url}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> View
                            </a>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
    
    showToast(notification) {
        // Toast notifications are disabled to prevent spam and improve UX
        console.log('Toast notification blocked for better UX:', notification.message);
        return;
    }
    
    async markAsRead(notificationId) {
        try {
            const response = await fetch(this.options.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_read&notification_ids[]=${notificationId}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update local state
                const notification = this.notifications.find(n => n.notification_id == notificationId);
                if (notification && notification.read_status == 0) {
                    notification.read_status = 1;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                    this.updateBadge();
                    this.updateDropdown();
                    
                    if (this.options.onNotificationRead) {
                        this.options.onNotificationRead(notification);
                    }
                }
            }
        } catch (error) {
            this.handleError('Failed to mark notification as read: ' + error.message);
        }
    }
    
    async markAllAsRead() {
        try {
            const response = await fetch(this.options.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all_read'
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update local state
                this.notifications.forEach(notification => {
                    notification.read_status = 1;
                });
                this.unreadCount = 0;
                this.updateBadge();
                this.updateDropdown();
            }
        } catch (error) {
            this.handleError('Failed to mark all notifications as read: ' + error.message);
        }
    }
    
    async clearAllNotifications() {
        if (!confirm('Are you sure you want to clear all notifications? This action cannot be undone.')) {
            return;
        }
        
        try {
            const response = await fetch(this.options.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=clear_all'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.notifications = [];
                this.unreadCount = 0;
                this.updateBadge();
                this.updateDropdown();
            }
        } catch (error) {
            this.handleError('Failed to clear notifications: ' + error.message);
        }
    }
    
    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.pollTimer = setInterval(() => {
            this.loadNotifications();
        }, this.options.pollInterval);
        
        console.log('Notification polling started');
    }
    
    stopPolling() {
        if (!this.isPolling) return;
        
        this.isPolling = false;
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
        
        console.log('Notification polling stopped');
    }
    
    pausePolling() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
    }
    
    resumePolling() {
        if (this.isPolling && !this.pollTimer) {
            this.pollTimer = setInterval(() => {
                this.loadNotifications();
            }, this.options.pollInterval);
        }
    }
    
    getNotificationIcon(type) {
        const iconMap = {
            'program_created': 'plus-circle',
            'program_edited': 'edit',
            'program_deleted': 'trash',
            'program_assignment': 'user-plus',
            'submission_created': 'file-plus',
            'submission_edited': 'file-edit',
            'submission_finalized': 'check-circle',
            'submission_deleted': 'file-times',
            'system': 'cog',
            'announcement': 'bullhorn',
            'maintenance': 'tools',
            'update': 'sync',
            'reminder': 'clock'
        };
        
        return iconMap[type] || 'bell';
    }
    
    formatTimeAgo(timestamp) {
        const now = new Date();
        const time = new Date(timestamp);
        const diffMs = now - time;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;
        
        return time.toLocaleDateString();
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    playNotificationSound() {
        // Create a subtle notification sound
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+P2yWwgAjiR2e+8aB4Elc7zx2sTBjiS1+K8bCAAkc/wz2oSBziN2+y7aB8Emszx0GwQBAF');
            audio.volume = 0.3;
            audio.play().catch(() => {
                // Ignore errors if sound can't be played
            });
        } catch (error) {
            // Ignore audio errors
        }
    }
    
    handleError(message) {
        console.error('Notification system error:', message);
        if (this.options.onError) {
            this.options.onError(message);
        }
    }
    
    // Public API methods
    refresh() {
        this.loadNotifications();
    }
    
    enable() {
        this.startPolling();
    }
    
    disable() {
        this.stopPolling();
    }
    
    destroy() {
        this.stopPolling();
        // Remove event listeners if needed
    }
}

// Auto-initialize on DOM ready - DISABLED FOR BETTER UX
document.addEventListener('DOMContentLoaded', () => {
    // Notification system completely disabled to prevent toast spam
    console.log('Notification system auto-initialization disabled for better UX');
    
    // Only initialize if explicitly requested
    // if (document.querySelector('.notification-badge') || document.querySelector('.notification-dropdown')) {
    //     window.notificationSystem = new NotificationSystem({
    //         enableToasts: false,
    //         enablePolling: false
    //     });
    // }
});

// Add required CSS
const style = document.createElement('style');
style.textContent = `
    .notification-badge {
        position: relative;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: bold;
        min-width: 1.25rem;
        text-align: center;
        display: none;
    }
    
    .animate-pulse {
        animation: pulse 1s ease-in-out;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .notification-item {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .notification-item:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .notification-item.unread {
        background-color: rgba(0, 123, 255, 0.05);
        border-left: 3px solid #007bff;
    }
    
    .notification-indicator {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
    }
    
    .notification-empty {
        padding: 2rem 1rem;
    }
    
    .notification-actions {
        background-color: #f8f9fa;
    }
    
    .toast {
        cursor: default;
    }
    
    .toast:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
`;
document.head.appendChild(style);

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationSystem;
}