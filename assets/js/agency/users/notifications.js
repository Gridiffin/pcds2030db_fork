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
        this.currentPage = 1;
        this.perPage = 10;
        this.currentFilter = 'all';
        this.searchQuery = '';
        this.selectedNotifications = new Set();
        this.isLoading = false;
        
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
            console.log('Initializing Notifications App...');
            
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
            
            // Load initial notifications
            this.loadNotifications();
            
            // Setup periodic refresh for new notifications
            this.setupAutoRefresh();
            
            console.log('Notifications App initialized successfully');
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
            
            const response = await this.ajax.getNotifications(params);
            
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
        if (filter !== this.currentFilter) {
            this.currentFilter = filter;
            this.currentPage = 1; // Reset to first page
            this.selectedNotifications.clear();
            this.loadNotifications();
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
     * Mark notifications as read
     */
    async markAsRead(notificationIds) {
        try {
            this.setLoading(true);
            
            const response = await this.ajax.markNotificationsRead(notificationIds);
            
            if (response.success) {
                this.loadNotifications(); // Refresh to show updated status
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
    
    if (notificationsContainer) {
        window.notificationsApp = new NotificationsApp();
    }
});

// Export for module usage
export default NotificationsApp;
