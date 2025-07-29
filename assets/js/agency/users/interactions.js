// Notifications Interactions Module
// Handles user interactions, event listeners, and UI behaviors

export default class NotificationsInteractions {
    constructor(app) {
        this.app = app;
        this.searchTimeout = null;
        this.searchDelay = 500; // ms
    }
    
    /**
     * Initialize all event listeners and interactions
     */
    init() {
        this.setupEventListeners();
        this.setupKeyboardShortcuts();
        this.setupMobileOptimizations();
    }
    
    /**
     * Setup all event listeners
     */
    setupEventListeners() {
        // Filter buttons
        this.setupFilterButtons();
        
        // Search functionality
        this.setupSearch();
        
        // Bulk selection
        this.setupBulkSelection();
        
        // Action buttons
        this.setupActionButtons();
        
        // Pagination
        this.setupPagination();
        
        // Per page selection
        this.setupPerPageSelection();
        
        // Individual notification actions
        this.setupNotificationActions();
        
        // Refresh functionality
        this.setupRefresh();
        
        // Modal interactions
        this.setupModals();
    }
    
    /**
     * Setup filter button interactions
     */
    setupFilterButtons() {
        console.log('Setting up filter buttons...');
        
        // Use event delegation for better reliability
        document.addEventListener('click', (e) => {
            const filterBtn = e.target.closest('.notifications-filter-btn');
            
            if (filterBtn) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Filter button clicked:', filterBtn);
                
                const filter = filterBtn.dataset.filter;
                console.log('Filter value:', filter);
                
                if (filter) {
                    // Update active state
                    document.querySelectorAll('.notifications-filter-btn').forEach(btn => {
                        btn.classList.remove('active', 'btn-primary');
                        btn.classList.add('btn-outline-primary');
                    });
                    
                    filterBtn.classList.remove('btn-outline-primary');
                    filterBtn.classList.add('active', 'btn-primary');
                    
                    console.log('Updated button states, applying filter:', filter);
                    
                    // Apply filter
                    this.app.applyFilter(filter);
                } else {
                    console.warn('No filter value found in button dataset');
                }
            }
        });
        
        // Also handle direct button clicks for better compatibility
        document.querySelectorAll('.notifications-filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Direct filter button click:', btn);
                
                const filter = btn.dataset.filter;
                console.log('Direct filter value:', filter);
                
                if (filter) {
                    // Update active state
                    document.querySelectorAll('.notifications-filter-btn').forEach(b => {
                        b.classList.remove('active', 'btn-primary');
                        b.classList.add('btn-outline-primary');
                    });
                    
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('active', 'btn-primary');
                    
                    console.log('Updated button states (direct), applying filter:', filter);
                    
                    // Apply filter
                    this.app.applyFilter(filter);
                }
            });
        });
        
        console.log('Filter buttons setup complete');
    }
    
    /**
     * Setup search functionality
     */
    setupSearch() {
        // Search input with debouncing
        document.addEventListener('input', (e) => {
            if (e.target.matches('.notifications-search-input')) {
                clearTimeout(this.searchTimeout);
                
                const query = e.target.value.trim();
                
                this.searchTimeout = setTimeout(() => {
                    this.app.applySearch(query);
                }, this.searchDelay);
            }
        });
        
        // Search button
        document.addEventListener('click', (e) => {
            if (e.target.matches('.notifications-search-btn')) {
                e.preventDefault();
                
                const searchInput = document.querySelector('.notifications-search-input');
                if (searchInput) {
                    const query = searchInput.value.trim();
                    this.app.applySearch(query);
                }
            }
        });
        
        // Clear search button
        document.addEventListener('click', (e) => {
            if (e.target.matches('.clear-search-btn')) {
                e.preventDefault();
                
                const searchInput = document.querySelector('.notifications-search-input');
                if (searchInput) {
                    searchInput.value = '';
                    this.app.applySearch('');
                }
            }
        });
        
        // Enter key in search
        document.addEventListener('keydown', (e) => {
            if (e.target.matches('.notifications-search-input') && e.key === 'Enter') {
                e.preventDefault();
                
                const query = e.target.value.trim();
                this.app.applySearch(query);
            }
        });
    }
    
    /**
     * Setup bulk selection functionality
     */
    setupBulkSelection() {
        // Select all checkbox
        document.addEventListener('change', (e) => {
            if (e.target.matches('.select-all-notifications')) {
                const isChecked = e.target.checked;
                const checkboxes = document.querySelectorAll('.notification-checkbox');
                
                checkboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    
                    if (isChecked) {
                        this.app.selectedNotifications.add(checkbox.value);
                    } else {
                        this.app.selectedNotifications.delete(checkbox.value);
                    }
                });
                
                this.updateBulkSelectionUI();
            }
        });
        
        // Individual notification checkboxes
        document.addEventListener('change', (e) => {
            if (e.target.matches('.notification-checkbox')) {
                const notificationId = e.target.value;
                
                if (e.target.checked) {
                    this.app.selectedNotifications.add(notificationId);
                } else {
                    this.app.selectedNotifications.delete(notificationId);
                }
                
                this.updateBulkSelectionUI();
            }
        });
    }
    
    /**
     * Update bulk selection UI state
     */
    updateBulkSelectionUI() {
        const totalCheckboxes = document.querySelectorAll('.notification-checkbox').length;
        const selectedCount = this.app.selectedNotifications.size;
        const selectAllCheckbox = document.querySelector('.select-all-notifications');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = selectedCount > 0 && selectedCount === totalCheckboxes;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalCheckboxes;
        }
        
        // Update logic module for UI changes
        this.app.logic.updateBulkActionsVisibility();
    }
    
    /**
     * Setup action buttons
     */
    setupActionButtons() {
        // Mark all as read
        document.addEventListener('click', (e) => {
            if (e.target.matches('.mark-all-read-btn')) {
                e.preventDefault();
                this.handleMarkAllAsRead();
            }
        });
        
        // Delete selected
        document.addEventListener('click', (e) => {
            if (e.target.matches('.delete-selected-btn')) {
                e.preventDefault();
                this.handleDeleteSelected();
            }
        });
        
        // Mark selected as read
        document.addEventListener('click', (e) => {
            if (e.target.matches('.mark-selected-read-btn')) {
                e.preventDefault();
                this.handleMarkSelectedAsRead();
            }
        });
        
        // Refresh notifications
        document.addEventListener('click', (e) => {
            if (e.target.matches('.refresh-notifications-btn')) {
                e.preventDefault();
                this.app.loadNotifications();
            }
        });
    }
    
    /**
     * Setup pagination interactions
     */
    setupPagination() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.pagination-btn')) {
                e.preventDefault();
                
                const page = parseInt(e.target.dataset.page);
                if (page && page > 0) {
                    this.app.changePage(page);
                }
            }
        });
    }
    
    /**
     * Setup per page selection
     */
    setupPerPageSelection() {
        document.addEventListener('change', (e) => {
            if (e.target.matches('.notifications-per-page select')) {
                const perPage = parseInt(e.target.value);
                if (perPage && perPage > 0) {
                    this.app.changePerPage(perPage);
                }
            }
        });
    }
    
    /**
     * Setup individual notification actions
     */
    setupNotificationActions() {
        // Mark single notification as read/unread
        document.addEventListener('click', (e) => {
            if (e.target.matches('.mark-read-btn')) {
                e.preventDefault();
                const notificationId = e.target.dataset.id;
                if (notificationId) {
                    this.app.markAsRead([notificationId]);
                }
            }
            
            if (e.target.matches('.mark-unread-btn')) {
                e.preventDefault();
                const notificationId = e.target.dataset.id;
                if (notificationId) {
                    this.app.markAsUnread([notificationId]);
                }
            }
        });
        
        // Delete single notification
        document.addEventListener('click', (e) => {
            if (e.target.matches('.delete-notification-btn')) {
                e.preventDefault();
                const notificationId = e.target.dataset.id;
                if (notificationId) {
                    this.handleDeleteNotification([notificationId]);
                }
            }
        });
        
        // Notification click to mark as read and navigate
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-item.unread') && !e.target.closest('.notification-select, .notification-actions-menu, .dropdown')) {
                const notificationItem = e.target.closest('.notification-item');
                const notificationId = notificationItem.dataset.id;
                const actionUrl = notificationItem.querySelector('.notification-actions a')?.href;
                
                if (notificationId) {
                    // Mark as read
                    this.app.markAsRead([notificationId]);
                    
                    // Navigate to action URL if available
                    if (actionUrl && !e.ctrlKey && !e.metaKey) {
                        setTimeout(() => {
                            window.location.href = actionUrl;
                        }, 500); // Small delay to ensure mark as read completes
                    }
                }
            }
        });
    }
    
    /**
     * Setup refresh functionality
     */
    setupRefresh() {
        // Auto-refresh when page regains focus
        window.addEventListener('focus', () => {
            this.app.refreshNotifications();
        });
        
        // Pull-to-refresh for mobile (basic implementation)
        let startY = 0;
        let pullDistance = 0;
        const pullThreshold = 100;
        
        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0) {
                startY = e.touches[0].clientY;
            }
        });
        
        document.addEventListener('touchmove', (e) => {
            if (window.scrollY === 0 && startY) {
                pullDistance = e.touches[0].clientY - startY;
                
                if (pullDistance > 0 && pullDistance < pullThreshold) {
                    e.preventDefault();
                    // Add visual feedback here if desired
                }
            }
        });
        
        document.addEventListener('touchend', () => {
            if (pullDistance > pullThreshold) {
                this.app.loadNotifications();
            }
            startY = 0;
            pullDistance = 0;
        });
    }
    
    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Only handle shortcuts when not in input fields
            if (e.target.matches('input, textarea, select')) return;
            
            switch (e.key) {
                case 'r':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        this.app.loadNotifications();
                    }
                    break;
                    
                case 'a':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        this.selectAllNotifications();
                    }
                    break;
                    
                case 'Escape':
                    this.clearSelection();
                    break;
                    
                case 'Delete':
                case 'Backspace':
                    if (this.app.selectedNotifications.size > 0) {
                        e.preventDefault();
                        this.handleDeleteSelected();
                    }
                    break;
            }
        });
    }
    
    /**
     * Setup mobile-specific optimizations
     */
    setupMobileOptimizations() {
        // Improved touch interactions for mobile
        if ('ontouchstart' in window) {
            // Add touch-friendly classes
            document.body.classList.add('touch-device');
            
            // Longer press for context menus on mobile
            let pressTimer;
            
            document.addEventListener('touchstart', (e) => {
                if (e.target.closest('.notification-item')) {
                    pressTimer = setTimeout(() => {
                        // Show context menu or selection mode
                        const notificationItem = e.target.closest('.notification-item');
                        const checkbox = notificationItem.querySelector('.notification-checkbox');
                        if (checkbox) {
                            checkbox.checked = !checkbox.checked;
                            checkbox.dispatchEvent(new Event('change'));
                        }
                    }, 500);
                }
            });
            
            document.addEventListener('touchend', () => {
                clearTimeout(pressTimer);
            });
            
            document.addEventListener('touchmove', () => {
                clearTimeout(pressTimer);
            });
        }
    }
    
    /**
     * Setup modal interactions
     */
    setupModals() {
        // Confirmation modals for destructive actions
        document.addEventListener('show.bs.modal', (e) => {
            if (e.target.matches('.delete-confirmation-modal')) {
                const selectedCount = this.app.selectedNotifications.size;
                const messageElement = e.target.querySelector('.delete-count');
                if (messageElement) {
                    messageElement.textContent = selectedCount;
                }
            }
        });
        
        // Modal action confirmations
        document.addEventListener('click', (e) => {
            if (e.target.matches('.confirm-delete-btn')) {
                e.preventDefault();
                const modal = e.target.closest('.modal');
                if (modal) {
                    const bootstrap = window.bootstrap;
                    if (bootstrap) {
                        bootstrap.Modal.getInstance(modal).hide();
                    }
                }
                
                // Proceed with deletion
                const selectedIds = Array.from(this.app.selectedNotifications);
                if (selectedIds.length > 0) {
                    this.app.deleteNotifications(selectedIds);
                }
            }
        });
    }
    
    /**
     * Handle mark all as read action
     */
    async handleMarkAllAsRead() {
        if (confirm('Mark all notifications as read?')) {
            await this.app.markAllAsRead();
        }
    }
    
    /**
     * Handle delete selected notifications
     */
    async handleDeleteSelected() {
        const selectedIds = Array.from(this.app.selectedNotifications);
        
        if (selectedIds.length === 0) {
            
            return;
        }
        
        if (confirm(`Delete ${selectedIds.length} selected notification(s)?`)) {
            await this.app.deleteNotifications(selectedIds);
        }
    }
    
    /**
     * Handle mark selected as read
     */
    async handleMarkSelectedAsRead() {
        const selectedIds = Array.from(this.app.selectedNotifications);
        
        if (selectedIds.length === 0) {
            
            return;
        }
        
        await this.app.markAsRead(selectedIds);
    }
    
    /**
     * Handle mark as unread (future feature)
     */
    async handleMarkAsUnread(notificationIds) {
        // This would require a new endpoint
        
        this.app.showError('Mark as unread functionality coming soon.');
    }
    
    /**
     * Handle delete single notification
     */
    async handleDeleteNotification(notificationIds) {
        if (confirm('Delete this notification?')) {
            await this.app.deleteNotifications(notificationIds);
        }
    }
    
    /**
     * Select all notifications
     */
    selectAllNotifications() {
        const selectAllCheckbox = document.querySelector('.select-all-notifications');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.dispatchEvent(new Event('change'));
        }
    }
    
    /**
     * Clear all selections
     */
    clearSelection() {
        this.app.selectedNotifications.clear();
        
        const checkboxes = document.querySelectorAll('.notification-checkbox, .select-all-notifications');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        this.updateBulkSelectionUI();
    }
    
    /**
     * Show notification preview (future feature)
     */
    showNotificationPreview(notificationId) {
        // This could open a modal with full notification details
        
    }
    
    /**
     * Handle notification settings (future feature)
     */
    openNotificationSettings() {
        // This could open notification preferences modal
        
    }
}
