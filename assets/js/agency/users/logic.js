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
            list: document.querySelector('.notifications-list'),
            loading: document.querySelector('.notifications-loading'),
            error: document.querySelector('.notifications-error'),
            success: document.querySelector('.notifications-success'),
            empty: document.querySelector('.notifications-empty'),
            
            // Header elements
            stats: document.querySelector('.notifications-stats'),
            totalCount: document.querySelector('.total-notifications'),
            unreadCount: document.querySelector('.unread-notifications'),
            
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
     * Render notifications list
     */
    renderNotifications(notifications) {
        if (!this.elements.list) this.cacheElements();
        
        try {
            if (!notifications || notifications.length === 0) {
                this.showEmpty();
                return;
            }
            
            const html = notifications.map(notification => this.renderNotificationItem(notification)).join('');
            this.elements.list.innerHTML = html;
            
            // Update UI state
            this.hideEmpty();
            this.updateBulkActionsVisibility();
            
            // Store hash for change detection
            this.lastNotificationHash = this.hashNotifications(notifications);
            
        } catch (error) {
            console.error('Failed to render notifications:', error);
            this.showError('Failed to display notifications');
        }
    }
    
    /**
     * Render individual notification item
     */
    renderNotificationItem(notification) {
        const isUnread = notification.read_at === null;
        const timeAgo = this.formatTimeAgo(notification.created_at);
        const priorityClass = this.getPriorityClass(notification.priority);
        const typeIcon = this.getTypeIcon(notification.type);
        
        return `
            <div class="notification-item ${isUnread ? 'unread' : 'read'}" data-id="${notification.id}">
                <div class="notification-select">
                    <input type="checkbox" class="notification-checkbox" value="${notification.id}">
                </div>
                
                <div class="notification-icon ${priorityClass}">
                    <i class="${typeIcon}"></i>
                </div>
                
                <div class="notification-content">
                    <div class="notification-header">
                        <h6 class="notification-title">${this.escapeHtml(notification.title)}</h6>
                        <span class="notification-time">${timeAgo}</span>
                    </div>
                    
                    <div class="notification-body">
                        <p class="notification-message">${this.escapeHtml(notification.message)}</p>
                        
                        ${notification.action_url ? `
                            <div class="notification-actions">
                                <a href="${this.escapeHtml(notification.action_url)}" class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </div>
                        ` : ''}
                    </div>
                    
                    <div class="notification-meta">
                        <span class="notification-type badge badge-${this.getTypeBadgeColor(notification.type)}">
                            ${this.formatNotificationType(notification.type)}
                        </span>
                        
                        ${notification.priority !== 'normal' ? `
                            <span class="notification-priority badge badge-${this.getPriorityBadgeColor(notification.priority)}">
                                ${notification.priority.toUpperCase()}
                            </span>
                        ` : ''}
                    </div>
                </div>
                
                <div class="notification-actions-menu">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            ${isUnread ? `
                                <button class="dropdown-item mark-read-btn" data-id="${notification.id}">
                                    <i class="fas fa-eye"></i> Mark as Read
                                </button>
                            ` : `
                                <button class="dropdown-item mark-unread-btn" data-id="${notification.id}">
                                    <i class="fas fa-eye-slash"></i> Mark as Unread
                                </button>
                            `}
                            <button class="dropdown-item delete-notification-btn" data-id="${notification.id}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Update notifications only if they have changed
     */
    updateNotificationsIfChanged(notifications) {
        const newHash = this.hashNotifications(notifications);
        if (newHash !== this.lastNotificationHash) {
            this.renderNotifications(notifications);
        }
    }
    
    /**
     * Generate a simple hash for notifications to detect changes
     */
    hashNotifications(notifications) {
        return notifications.map(n => `${n.id}-${n.read_at}`).join('|');
    }
    
    /**
     * Update pagination UI
     */
    updatePagination(pagination) {
        if (!this.elements.pagination) return;
        
        try {
            const { current_page, last_page, total, per_page, from, to } = pagination;
            
            // Update pagination info
            if (this.elements.paginationInfo) {
                this.elements.paginationInfo.innerHTML = `
                    <div class="notifications-pagination-summary">
                        Showing ${from || 0} to ${to || 0} of ${total} notifications
                    </div>
                `;
            }
            
            // Generate pagination HTML
            const paginationHTML = this.generatePaginationHTML(current_page, last_page);
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
     * Update statistics display
     */
    updateStats(stats) {
        if (this.elements.totalCount) {
            this.elements.totalCount.textContent = stats.total || 0;
        }
        if (this.elements.unreadCount) {
            this.elements.unreadCount.textContent = stats.unread || 0;
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
