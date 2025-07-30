// Main Notifications Module Entry Point
import NotificationsLogic from './logic.js';
import NotificationsAjax from './ajax.js';
import NotificationsInteractions from './interactions.js';

// Import CSS for Vite bundling
import '../../../css/agency/users/notifications.css';

/**
 * Main Notifications Application Class
 * Coordinates all notification functionality and manages page state
 */
class NotificationsApp {
    constructor() {
        // Initialize from configuration if available
        const config = window.notificationsConfig || {};
        
        this.currentPage = config.currentPage || 1;
        this.perPage = config.perPage || 10;
        this.currentFilter = config.currentFilter || 'all';
        this.searchQuery = '';
        this.selectedNotifications = new Set();
        this.isLoading = false;
        
        console.log('NotificationsApp initialized with config:', config);
        
        // Initialize modules
        this.logic = new NotificationsLogic(this);
        this.ajax = new NotificationsAjax(this);
        this.interactions = new NotificationsInteractions(this);
        
        this.init();
    }
    
    /**
     * Initialize the notifications application
     */
    init() {
        try {
            
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setup());
            } else {
                this.setup();
            }
        } catch (error) {
            console.error('Failed to initialize Notifications App:', error);
            this.showError('Failed to initialize notifications. Please refresh the page.');
        }
    }
    
    /**
     * Setup the application after DOM is ready
     */
    setup() {
        try {
            // Initialize UI interactions
            this.interactions.init();
            
            // Load initial notifications (JavaScript will handle empty state)
            this.loadNotifications();
            
            // Setup periodic refresh for new notifications
            this.setupAutoRefresh();
            
        } catch (error) {
            console.error('Failed to setup Notifications App:', error);
            this.showError('Failed to setup notifications interface.');
        }
    }
    
    /**
     * Load notifications with current filters and pagination
     */
    async loadNotifications() {
        if (this.isLoading) return;
        
        try {
            this.setLoading(true);
            
            const params = {
                page: this.currentPage,
                per_page: this.perPage,
                filter: this.currentFilter,
                search: this.searchQuery
            };
            
            console.log('loadNotifications called with params:', params);
            
            const response = await this.ajax.getNotifications(params);
            
            console.log('loadNotifications response:', response);
            
            if (response.success) {
                this.logic.renderNotifications(response.data);
                this.logic.updatePagination(response.pagination);
                this.logic.updateStats(response.stats);
                this.clearError();
            } else {
                throw new Error(response.message || 'Failed to load notifications');
            }
        } catch (error) {
            console.error('Failed to load notifications:', error);
            this.showError('Failed to load notifications. Please try again.');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Setup automatic refresh for new notifications
     */
    setupAutoRefresh() {
        // Refresh every 30 seconds
        setInterval(() => {
            if (!this.isLoading && document.visibilityState === 'visible') {
                this.refreshNotifications();
            }
        }, 30000);
        
        // Refresh when page becomes visible
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                this.refreshNotifications();
            }
        });
    }
    
    /**
     * Refresh notifications without showing loading state
     */
    async refreshNotifications() {
        try {
            const params = {
                page: this.currentPage,
                per_page: this.perPage,
                filter: this.currentFilter,
                search: this.searchQuery
            };
            
            const response = await this.ajax.getNotifications(params);
            
            if (response.success) {
                this.logic.updateNotificationsIfChanged(response.data);
                this.logic.updateStats(response.stats);
            }
        } catch (error) {
            console.warn('Failed to refresh notifications:', error);
            // Don't show error for background refresh
        }
    }
    
    /**
     * Change current page
     */
    changePage(page) {
        if (page !== this.currentPage && page > 0) {
            this.currentPage = page;
            this.loadNotifications();
        }
    }
    
    /**
     * Change items per page
     */
    changePerPage(perPage) {
        if (perPage !== this.perPage) {
            this.perPage = perPage;
            this.currentPage = 1; // Reset to first page
            this.loadNotifications();
        }
    }
    
    /**
     * Apply filter
     */
    applyFilter(filter) {
        console.log('applyFilter called with filter:', filter, 'currentFilter:', this.currentFilter);
        
        if (filter !== this.currentFilter) {
            console.log('Filter changed from', this.currentFilter, 'to', filter);
            this.currentFilter = filter;
            this.currentPage = 1; // Reset to first page
            this.selectedNotifications.clear();
            this.loadNotifications();
        } else {
            console.log('Filter unchanged, no action taken');
        }
    }
    
    /**
     * Apply search
     */
    applySearch(query) {
        if (query !== this.searchQuery) {
            this.searchQuery = query;
            this.currentPage = 1; // Reset to first page
            this.loadNotifications();
        }
    }
    
    /**
     * Mark notifications as unread
     */
    async markAsUnread(notificationIds) {
        try {
            this.setLoading(true);
            
            const response = await this.ajax.markNotificationsUnread(notificationIds);
            
            if (response.success) {
                // Update UI immediately for better user experience
                notificationIds.forEach(id => {
                    this.logic.markNotificationAsUnread(id);
                });
                
                // Update stats from response if available
                if (response.unread_count !== undefined) {
                    this.logic.updateStats({
                        unread: response.unread_count,
                        total: response.total_count || this.logic.elements.totalCount?.textContent
                    });
                }
                
                // Check if we need to remove notifications from the list due to filtering
                if (this.currentFilter === 'read') {
                    // If we're on "read only" filter, remove the unread notifications from the list
                    notificationIds.forEach(id => {
                        this.logic.removeNotificationFromList(id);
                    });
                }
                
                this.showSuccess(`${notificationIds.length} notification(s) marked as unread`);
            } else {
                throw new Error(response.message || 'Failed to mark notifications as unread');
            }
        } catch (error) {
            console.error('Failed to mark notifications as unread:', error);
            this.showError('Failed to mark notifications as unread. Please try again.');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Mark notifications as read
     */
    async markAsRead(notificationIds) {
        try {
            this.setLoading(true);
            
            const response = await this.ajax.markNotificationsRead(notificationIds);
            
            if (response.success) {
                // Update UI immediately for better user experience
                notificationIds.forEach(id => {
                    this.logic.markNotificationAsRead(id);
                });
                
                // Update stats from response if available
                if (response.unread_count !== undefined) {
                    this.logic.updateStats({
                        unread: response.unread_count,
                        total: response.total_count || this.logic.elements.totalCount?.textContent
                    });
                }
                
                // Check if we need to remove notifications from the list due to filtering
                if (this.currentFilter === 'unread') {
                    // If we're on "unread only" filter, remove the read notifications from the list
                    notificationIds.forEach(id => {
                        this.logic.removeNotificationFromList(id);
                    });
                }
                
                this.showSuccess(`${notificationIds.length} notification(s) marked as read`);
            } else {
                throw new Error(response.message || 'Failed to mark notifications as read');
            }
        } catch (error) {
            console.error('Failed to mark notifications as read:', error);
            this.showError('Failed to mark notifications as read. Please try again.');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Delete notifications
     */
    async deleteNotifications(notificationIds) {
        try {
            this.setLoading(true);
            
            const response = await this.ajax.deleteNotifications(notificationIds);
            
            if (response.success) {
                this.selectedNotifications.clear();
                this.loadNotifications(); // Refresh to show updated list
                this.showSuccess(`${notificationIds.length} notification(s) deleted`);
            } else {
                throw new Error(response.message || 'Failed to delete notifications');
            }
        } catch (error) {
            console.error('Failed to delete notifications:', error);
            this.showError('Failed to delete notifications. Please try again.');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        try {
            this.setLoading(true);
            
            const response = await this.ajax.markAllNotificationsRead();
            
            if (response.success) {
                this.selectedNotifications.clear();
                this.loadNotifications(); // Refresh to show updated status
                this.showSuccess('All notifications marked as read');
            } else {
                throw new Error(response.message || 'Failed to mark all notifications as read');
            }
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
            this.showError('Failed to mark all notifications as read. Please try again.');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Set loading state
     */
    setLoading(loading) {
        this.isLoading = loading;
        this.logic.setLoadingState(loading);
    }
    
    /**
     * Show error message
     */
    showError(message) {
        this.logic.showMessage(message, 'error');
    }
    
    /**
     * Show success message
     */
    showSuccess(message) {
        this.logic.showMessage(message, 'success');
    }
    
    /**
     * Clear error messages
     */
    clearError() {
        this.logic.clearMessages();
    }
    
    /**
     * Get current state for debugging
     */
    getState() {
        return {
            currentPage: this.currentPage,
            perPage: this.perPage,
            currentFilter: this.currentFilter,
            searchQuery: this.searchQuery,
            selectedNotifications: Array.from(this.selectedNotifications),
            isLoading: this.isLoading
        };
    }
}

// Initialize the app based on the current page
document.addEventListener('DOMContentLoaded', () => {
    // Check if we're on a notifications page
    const notificationsContainer = document.querySelector('.notifications-container');
    
    // Additional check to ensure we're actually on a notifications page
    const isNotificationPage = notificationsContainer && 
        (window.location.pathname.includes('/notifications') || 
         window.location.pathname.includes('/all_notifications') ||
         document.body.classList.contains('notifications-page'));
    
    if (isNotificationPage) {
        console.log('Initializing NotificationsApp on notifications page');
        window.notificationsApp = new NotificationsApp();
    } else if (notificationsContainer) {
        console.log('Notifications container found but not on notifications page - skipping initialization');
    }
});

// Export for module usage
export default NotificationsApp;
