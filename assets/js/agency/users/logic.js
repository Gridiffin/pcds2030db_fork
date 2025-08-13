// Notifications Logic Module
// Handles DOM manipulation, UI updates, and business logic

export default class NotificationsLogic {
    constructor(app) {
        this.app = app;
        this.elements = {};
        this.lastNotificationHash = null;
    }
    
    /**
     * Cache DOM elements for efficient access
     */
    cacheElements() {
        this.elements = {
            container: document.querySelector('.notifications-container'),
            list: this.ensureNotificationsList(),
            loading: document.querySelector('.notifications-loading'),
            error: document.querySelector('.notifications-error'),
            success: document.querySelector('.notifications-success'),
            empty: document.querySelector('.notifications-empty'),
            
            // Header elements
            stats: document.querySelector('.notifications-stats'),
            totalCount: document.querySelector('.total-notifications'),
            unreadCount: document.querySelector('.unread-notifications'),
            readCount: document.querySelector('.read-notifications'),
            recentCount: document.querySelector('.recent-notifications'),
            
            // Action buttons
            markAllReadBtn: document.querySelector('.mark-all-read-btn'),
            deleteSelectedBtn: document.querySelector('.delete-selected-btn'),
            refreshBtn: document.querySelector('.refresh-notifications-btn'),
            
            // Filters and search
            filterBtns: document.querySelectorAll('.notifications-filter-btn'),
            searchInput: document.querySelector('.notifications-search-input'),
            searchBtn: document.querySelector('.notifications-search-btn'),
            clearSearchBtn: document.querySelector('.clear-search-btn'),
            
            // Bulk selection
            selectAllCheckbox: document.querySelector('.select-all-notifications'),
            selectedCount: document.querySelector('.selected-count'),
            bulkActions: document.querySelector('.bulk-actions'),
            
            // Pagination
            pagination: document.querySelector('.notifications-pagination'),
            paginationInfo: document.querySelector('.notifications-pagination-info'),
            perPageSelect: document.querySelector('.notifications-per-page select')
        };
    }
    
    /**
     * Ensure notifications list element exists
     */
    ensureNotificationsList() {
        let listElement = document.querySelector('.notifications-list');
        
        if (!listElement) {
            // Create the notifications list element
            listElement = document.createElement('div');
            listElement.className = 'notifications-list';
            
            // Insert it before the empty state element
            const emptyElement = document.querySelector('.notifications-empty');
            if (emptyElement && emptyElement.parentNode) {
                emptyElement.parentNode.insertBefore(listElement, emptyElement);
            } else {
                // Fallback: append to container
                const container = document.querySelector('.notifications-container');
                if (container) {
                    container.appendChild(listElement);
                }
            }
        }
        
        return listElement;
    }
    
    /**
     * Update individual notification item (for immediate feedback)
     */
    updateNotificationItem(notificationId, updates = {}) {
        const notificationElement = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
        
        if (!notificationElement) {
            console.warn(`Notification element with ID ${notificationId} not found`);
            return;
        }
        
        // Update read status
        if (updates.read_status !== undefined) {
            const isUnread = updates.read_status === 0;
            
            // Update CSS classes
            notificationElement.classList.toggle('unread', isUnread);
            notificationElement.classList.toggle('read', !isUnread);
            
            // Update unread indicator
            const unreadIndicator = notificationElement.querySelector('.unread-indicator');
            if (unreadIndicator) {
                unreadIndicator.style.display = isUnread ? 'inline-block' : 'none';
            }
            
            // Update action buttons inside dropdown menu
            const markReadBtn = notificationElement.querySelector('.dropdown-menu .mark-read-btn');
            const markUnreadBtn = notificationElement.querySelector('.dropdown-menu .mark-unread-btn');
            
            // Find the action button (either read or unread)
            const actionButton = markReadBtn || markUnreadBtn;
            
            if (actionButton) {
                if (isUnread) {
                    // Change to "Mark as Read" button
                    actionButton.className = 'dropdown-item mark-read-btn';
                    actionButton.innerHTML = '<i class="fas fa-eye me-2"></i>Mark as Read';
                    actionButton.dataset.id = notificationId;
                } else {
                    // Change to "Mark as Unread" button
                    actionButton.className = 'dropdown-item mark-unread-btn';
                    actionButton.innerHTML = '<i class="fas fa-eye-slash me-2"></i>Mark as Unread';
                    actionButton.dataset.id = notificationId;
                }
            }
        }
        
        // Update other properties if provided
        if (updates.message !== undefined) {
            const messageElement = notificationElement.querySelector('.notification-message');
            if (messageElement) {
                messageElement.textContent = updates.message;
            }
        }
        
        if (updates.time_ago !== undefined) {
            const timeElement = notificationElement.querySelector('.notification-time');
            if (timeElement) {
                timeElement.textContent = updates.time_ago;
            }
        }
    }
    
    /**
     * Remove notification from list (for filtering purposes)
     */
    removeNotificationFromList(notificationId) {
        const notificationElement = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
        
        if (notificationElement) {
            // Add fade-out animation
            notificationElement.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
            notificationElement.style.opacity = '0';
            notificationElement.style.transform = 'translateX(-100%)';
            
            // Remove from DOM after animation
            setTimeout(() => {
                notificationElement.remove();
                
                // Check if list is now empty
                const remainingNotifications = document.querySelectorAll('.notification-item');
                if (remainingNotifications.length === 0) {
                    this.showEmpty();
                }
            }, 300);
        }
    }
    
    /**
     * Mark notification as read with immediate UI update
     */
    markNotificationAsRead(notificationId) {
        this.updateNotificationItem(notificationId, { read_status: 1 });
        
        // Update stats if available - try to find the elements directly
        const unreadCountElement = document.querySelector('.unread-notifications');
        const readCountElement = document.querySelector('.read-notifications');
        const totalCountElement = document.querySelector('.total-notifications');
        
        if (unreadCountElement && readCountElement && totalCountElement) {
            const currentUnread = parseInt(unreadCountElement.textContent) || 0;
            const currentTotal = parseInt(totalCountElement.textContent) || 0;
            
            if (currentUnread > 0) {
                unreadCountElement.textContent = currentUnread - 1;
                readCountElement.textContent = (currentTotal - (currentUnread - 1));
            }
        }
    }
    
    /**
     * Mark notification as unread with immediate UI update
     */
    markNotificationAsUnread(notificationId) {
        this.updateNotificationItem(notificationId, { read_status: 0 });
        
        // Update stats if available - try to find the elements directly
        const unreadCountElement = document.querySelector('.unread-notifications');
        const readCountElement = document.querySelector('.read-notifications');
        const totalCountElement = document.querySelector('.total-notifications');
        
        if (unreadCountElement && readCountElement && totalCountElement) {
            const currentUnread = parseInt(unreadCountElement.textContent) || 0;
            const currentTotal = parseInt(totalCountElement.textContent) || 0;
            
            unreadCountElement.textContent = currentUnread + 1;
            readCountElement.textContent = (currentTotal - (currentUnread + 1));
        }
    }

    /**
     * Render notifications list (JavaScript-only implementation)
     */
    renderNotifications(notifications) {
        console.log('renderNotifications called with:', notifications);
        
        // Ensure DOM elements are available
        if (!this.elements.list) {
            this.cacheElements();
        }
        
        if (!this.elements.list) {
            console.error('Cannot render notifications: list element not found');
            return;
        }
        
        try {
            // Clear existing notifications
            this.clearNotificationsList();
            
            // Handle empty state
            if (!notifications || notifications.length === 0) {
                console.log('No notifications to display');
                this.showEmpty();
                return;
            }
            
            console.log(`Rendering ${notifications.length} notifications`);
            
            // Create document fragment for efficient DOM manipulation
            const fragment = document.createDocumentFragment();
            
            // Render each notification
            notifications.forEach(notification => {
                const element = this.createNotificationElement(notification);
                if (element) {
                    fragment.appendChild(element);
                }
            });
            
            // Add all notifications to DOM at once
            this.elements.list.appendChild(fragment);
            
            // Update UI state
            this.hideEmpty();
            this.updateBulkActionsVisibility();
            
            // Store hash for change detection
            this.lastNotificationHash = this.generateNotificationHash(notifications);
            
            console.log('Notifications rendered successfully');
            
        } catch (error) {
            console.error('Failed to render notifications:', error);
            this.showError('Failed to display notifications');
            this.showEmpty();
        }
    }
    
    /**
     * Clear notifications list
     */
    clearNotificationsList() {
        if (this.elements.list) {
            this.elements.list.innerHTML = '';
        }
    }
    
    /**
     * Generate hash for notification change detection
     */
    generateNotificationHash(notifications) {
        if (!notifications || notifications.length === 0) return '';
        return notifications.map(n => `${n.notification_id}-${n.read_status}`).join('|');
    }
    
    /**
     * Create notification DOM element (JavaScript-only implementation)
     */
    createNotificationElement(notification) {
        if (!notification || !notification.notification_id) {
            console.warn('Invalid notification data:', notification);
            return null;
        }
        
        try {
            const isUnread = notification.read_status === 0;
            const timeAgo = notification.time_ago || this.formatTimeAgo(notification.created_at);
            const iconType = this.getNotificationIcon(notification.type);
            
            // Create main notification container
            const item = this.createElement('div', {
                className: `notification-item ${isUnread ? 'unread' : 'read'}`,
                'data-id': notification.notification_id
            });
            
            // Create notification icon
            const iconContainer = this.createElement('div', { className: 'notification-icon' });
            const icon = this.createElement('i', { className: `fas fa-${iconType}` });
            iconContainer.appendChild(icon);
            
            // Create notification content
            const content = this.createNotificationContent(notification, isUnread, timeAgo);
            
            // Create actions menu
            const actionsMenu = this.createNotificationActions(notification, isUnread);
            
            // Assemble notification
            item.appendChild(iconContainer);
            item.appendChild(content);
            item.appendChild(actionsMenu);
            
            return item;
            
        } catch (error) {
            console.error('Failed to create notification element:', error);
            return null;
        }
    }
    
    /**
     * Create DOM element with attributes
     */
    createElement(tag, attributes = {}) {
        const element = document.createElement(tag);
        Object.entries(attributes).forEach(([key, value]) => {
            if (key === 'className') {
                element.className = value;
            } else {
                element.setAttribute(key, value);
            }
        });
        return element;
    }
    
    /**
     * Create notification content section
     */
    createNotificationContent(notification, isUnread, timeAgo) {
        const content = this.createElement('div', { className: 'notification-content' });
        const header = this.createElement('div', { className: 'notification-header' });
        
        // Create title
        const title = this.createElement('h6', { className: 'notification-title' });
        title.textContent = notification.message;
        
        // Add unread indicator
        if (isUnread) {
            const indicator = this.createElement('span', { className: 'unread-indicator' });
            title.appendChild(indicator);
        }
        
        // Create time
        const time = this.createElement('span', { className: 'notification-time' });
        time.textContent = timeAgo;
        
        header.appendChild(title);
        header.appendChild(time);
        content.appendChild(header);
        
        return content;
    }
    
    /**
     * Create notification actions menu
     */
    createNotificationActions(notification, isUnread) {
        const actionsMenu = this.createElement('div', { className: 'notification-actions-menu' });
        const dropdown = this.createElement('div', { className: 'dropdown' });
        
        // Dropdown toggle
        const toggle = this.createElement('button', {
            className: 'btn btn-sm btn-link dropdown-toggle',
            type: 'button',
            'data-bs-toggle': 'dropdown'
        });
        const ellipsis = this.createElement('i', { className: 'fas fa-ellipsis-v' });
        toggle.appendChild(ellipsis);
        
        // Dropdown menu
        const menu = this.createElement('ul', { className: 'dropdown-menu dropdown-menu-end' });
        
        // Read/Unread action
        const readAction = this.createActionItem(
            isUnread ? 'mark-read-btn' : 'mark-unread-btn',
            `fas fa-${isUnread ? 'eye' : 'eye-slash'} me-2`,
            `Mark as ${isUnread ? 'Read' : 'Unread'}`,
            notification.notification_id
        );
        menu.appendChild(readAction);
        
        // Action URL if present
        if (notification.action_url) {
            const linkAction = this.createLinkItem(
                'fas fa-external-link-alt me-2',
                'Open Link',
                notification.action_url
            );
            menu.appendChild(linkAction);
        }
        
        // Divider
        const divider = this.createElement('li');
        const hr = this.createElement('hr', { className: 'dropdown-divider' });
        divider.appendChild(hr);
        menu.appendChild(divider);
        
        // Delete action
        const deleteAction = this.createActionItem(
            'dropdown-item text-danger delete-notification-btn',
            'fas fa-trash me-2',
            'Delete',
            notification.notification_id
        );
        menu.appendChild(deleteAction);
        
        dropdown.appendChild(toggle);
        dropdown.appendChild(menu);
        actionsMenu.appendChild(dropdown);
        
        return actionsMenu;
    }
    
    /**
     * Create dropdown action item
     */
    createActionItem(className, iconClass, text, notificationId) {
        const li = this.createElement('li');
        const button = this.createElement('button', {
            className: className,
            'data-id': notificationId
        });
        
        const icon = this.createElement('i', { className: iconClass });
        button.appendChild(icon);
        button.appendChild(document.createTextNode(text));
        
        li.appendChild(button);
        return li;
    }
    
    /**
     * Create dropdown link item
     */
    createLinkItem(iconClass, text, href) {
        const li = this.createElement('li');
        const link = this.createElement('a', {
            className: 'dropdown-item',
            href: href
        });
        
        const icon = this.createElement('i', { className: iconClass });
        link.appendChild(icon);
        link.appendChild(document.createTextNode(text));
        
        li.appendChild(link);
        return li;
    }
    
    /**
     * Get notification icon type
     */
    getNotificationIcon(type) {
        return type === 'update' ? 'info-circle' : 'bell';
    }
    
    /**
     * Render individual notification item (deprecated - kept for compatibility)
     */
    renderNotificationItem(notification) {
        // This method is now deprecated but kept for compatibility
        // Use createNotificationElement instead
        return this.createNotificationElement(notification);
    }
    
    /**
     * Update notifications only if they have changed
     */
    updateNotificationsIfChanged(notifications) {
        const newHash = this.generateNotificationHash(notifications);
        if (newHash !== this.lastNotificationHash) {
            console.log('Notifications changed, re-rendering');
            this.renderNotifications(notifications);
        } else {
            console.log('Notifications unchanged, skipping render');
        }
    }
    
    /**
     * Update pagination UI
     */
    updatePagination(pagination) {
        if (!this.elements.pagination) return;
        
        try {
            const { current_page, total_pages, total_count, per_page } = pagination;
            const from = ((current_page - 1) * per_page) + 1;
            const to = Math.min(current_page * per_page, total_count);
            
            // Update pagination info
            if (this.elements.paginationInfo) {
                this.elements.paginationInfo.innerHTML = `
                    <div class="notifications-pagination-summary">
                        Showing ${from || 0} to ${to || 0} of ${total_count || 0} notifications
                    </div>
                `;
            }
            
            // Generate pagination HTML
            const paginationHTML = this.generatePaginationHTML(current_page, total_pages);
            const paginationNav = this.elements.pagination.querySelector('.notifications-pagination-nav');
            if (paginationNav) {
                paginationNav.innerHTML = paginationHTML;
            }
            
            // Update per page select
            if (this.elements.perPageSelect) {
                this.elements.perPageSelect.value = per_page;
            }
            
        } catch (error) {
            console.error('Failed to update pagination:', error);
        }
    }
    
    /**
     * Generate pagination HTML
     */
    generatePaginationHTML(currentPage, lastPage) {
        if (lastPage <= 1) return '';
        
        let html = '<ul class="pagination">';
        
        // Previous button
        html += `
            <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                <button class="page-link pagination-btn" data-page="${currentPage - 1}" aria-label="Previous">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </li>
        `;
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(lastPage, currentPage + 2);
        
        if (startPage > 1) {
            html += `
                <li class="page-item">
                    <button class="page-link pagination-btn" data-page="1">1</button>
                </li>
            `;
            if (startPage > 2) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <button class="page-link pagination-btn" data-page="${i}">${i}</button>
                </li>
            `;
        }
        
        if (endPage < lastPage) {
            if (endPage < lastPage - 1) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            html += `
                <li class="page-item">
                    <button class="page-link pagination-btn" data-page="${lastPage}">${lastPage}</button>
                </li>
            `;
        }
        
        // Next button
        html += `
            <li class="page-item ${currentPage >= lastPage ? 'disabled' : ''}">
                <button class="page-link pagination-btn" data-page="${currentPage + 1}" aria-label="Next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </li>
        `;
        
        html += '</ul>';
        return html;
    }
    
    /**
     * Update stats display
     */
    updateStats(stats) {
        console.log('updateStats called with:', stats);
        
        // Handle both old and new field name formats
        const totalCount = stats.total_count || stats.total || 0;
        const unreadCount = parseInt(stats.unread_count || stats.unread || 0);
        const readCount = stats.read_count || (totalCount - unreadCount) || 0;
        const recentCount = stats.recent_count || stats.recent || 0;
        
        // Update total count
        if (this.elements.totalCount) {
            console.log('Updating total count from', this.elements.totalCount.textContent, 'to', totalCount);
            this.elements.totalCount.textContent = totalCount;
        }
        
        // Update unread count
        if (this.elements.unreadCount) {
            console.log('Updating unread count from', this.elements.unreadCount.textContent, 'to', unreadCount);
            this.elements.unreadCount.textContent = unreadCount;
        }
        
        // Update read count
        if (this.elements.readCount) {
            console.log('Updating read count from', this.elements.readCount.textContent, 'to', readCount);
            this.elements.readCount.textContent = readCount;
        }
        
        // Update recent count (today's notifications)
        if (this.elements.recentCount) {
            console.log('Updating recent count from', this.elements.recentCount.textContent, 'to', recentCount);
            this.elements.recentCount.textContent = recentCount;
        }
    }
    
    /**
     * Update bulk actions visibility based on selected notifications
     */
    updateBulkActionsVisibility() {
        const selectedCount = this.app.selectedNotifications.size;
        
        if (this.elements.selectedCount) {
            this.elements.selectedCount.textContent = selectedCount;
        }
        
        if (this.elements.bulkActions) {
            this.elements.bulkActions.style.display = selectedCount > 0 ? 'flex' : 'none';
        }
        
        if (this.elements.deleteSelectedBtn) {
            this.elements.deleteSelectedBtn.disabled = selectedCount === 0;
        }
    }
    
    /**
     * Set loading state
     */
    setLoadingState(loading) {
        if (this.elements.loading) {
            this.elements.loading.style.display = loading ? 'block' : 'none';
        }
        
        if (this.elements.container) {
            this.elements.container.classList.toggle('loading', loading);
        }
        
        // Disable/enable action buttons
        const actionButtons = document.querySelectorAll('.notifications-action-btn');
        actionButtons.forEach(btn => btn.disabled = loading);
    }
    
    /**
     * Show empty state
     */
    showEmpty() {
        if (this.elements.empty) {
            this.elements.empty.style.display = 'block';
        }
        if (this.elements.list) {
            this.elements.list.style.display = 'none';
        }
    }
    
    /**
     * Hide empty state
     */
    hideEmpty() {
        if (this.elements.empty) {
            this.elements.empty.style.display = 'none';
        }
        if (this.elements.list) {
            this.elements.list.style.display = 'block';
        }
    }
    
    /**
     * Show message (error or success)
     */
    showMessage(message, type = 'info') {
        this.clearMessages();
        
        const element = this.elements[type];
        if (element) {
            element.textContent = message;
            element.style.display = 'block';
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => this.clearMessages(), 5000);
            }
        }
    }
    
    /**
     * Clear all messages
     */
    clearMessages() {
        ['error', 'success'].forEach(type => {
            const element = this.elements[type];
            if (element) {
                element.style.display = 'none';
                element.textContent = '';
            }
        });
    }
    
    // Utility methods
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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
    
    getPriorityClass(priority) {
        const classes = {
            low: 'priority-low',
            normal: 'priority-normal',
            high: 'priority-high',
            urgent: 'priority-urgent'
        };
        return classes[priority] || 'priority-normal';
    }
    
    getPriorityBadgeColor(priority) {
        const colors = {
            low: 'info',
            normal: 'secondary',
            high: 'warning',
            urgent: 'danger'
        };
        return colors[priority] || 'secondary';
    }
    
    getTypeIcon(type) {
        const icons = {
            system: 'fas fa-cog',
            update: 'fas fa-sync',
            reminder: 'fas fa-bell',
            alert: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle',
            success: 'fas fa-check-circle',
            warning: 'fas fa-exclamation-circle',
            error: 'fas fa-times-circle'
        };
        return icons[type] || 'fas fa-bell';
    }
    
    getTypeBadgeColor(type) {
        const colors = {
            system: 'secondary',
            update: 'primary',
            reminder: 'info',
            alert: 'warning',
            info: 'info',
            success: 'success',
            warning: 'warning',
            error: 'danger'
        };
        return colors[type] || 'secondary';
    }
    
    formatNotificationType(type) {
        return type.charAt(0).toUpperCase() + type.slice(1);
    }
}
