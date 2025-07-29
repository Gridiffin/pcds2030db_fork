// Notifications AJAX Module
// Handles all API communication and data fetching

export default class NotificationsAjax {
    constructor(app) {
        this.app = app;
        // Use configuration baseUrl if available, otherwise detect it
        this.baseUrl = this.getBaseUrl();
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
    }
    
    /**
     * Get the base URL from configuration or detect it
     */
    getBaseUrl() {
        // Check if we have a configuration with baseUrl
        if (window.notificationsConfig && window.notificationsConfig.baseUrl) {
            const configUrl = window.notificationsConfig.baseUrl;
            console.log('Using base URL from configuration:', configUrl);
            return configUrl;
        }
        
        // Fallback to detection
        return this.detectBaseUrl();
    }
    
    /**
     * Detect the base URL for AJAX requests
     * More robust approach that handles different URL patterns
     */
    detectBaseUrl() {
        // Get the current page URL and path
        const currentUrl = window.location.href;
        const currentPath = window.location.pathname;
        const currentOrigin = window.location.origin;
        
        console.log('Current URL:', currentUrl);
        console.log('Current path:', currentPath);
        console.log('Current origin:', currentOrigin);
        
        // Method 1: Check if URL contains pcds2030_dashboard_fork
        if (currentUrl.includes('/pcds2030_dashboard_fork/') || currentPath.includes('/pcds2030_dashboard_fork/')) {
            const baseUrl = currentOrigin + '/pcds2030_dashboard_fork';
            console.log('Method 1: Detected pcds2030_dashboard_fork in URL, using base URL:', baseUrl);
            return baseUrl;
        }
        
        // Method 2: Check for index.php with pcds2030_dashboard_fork in path
        if (currentPath.includes('pcds2030_dashboard_fork')) {
            // Extract everything up to and including pcds2030_dashboard_fork
            const pathParts = currentPath.split('/');
            const projectIndex = pathParts.findIndex(part => part === 'pcds2030_dashboard_fork');
            if (projectIndex >= 0) {
                const projectPath = pathParts.slice(0, projectIndex + 1).join('/');
                const baseUrl = currentOrigin + projectPath;
                console.log('Method 2: Extracted project path, using base URL:', baseUrl);
                return baseUrl;
            }
        }
        
        // Method 3: Check script tag src for bundle path (as fallback)
        const scriptTags = document.querySelectorAll('script[src*="agency-notifications.bundle.js"]');
        if (scriptTags.length > 0) {
            const bundleSrc = scriptTags[0].src;
            console.log('Found bundle script src:', bundleSrc);
            
            if (bundleSrc.includes('/pcds2030_dashboard_fork/')) {
                const baseUrl = currentOrigin + '/pcds2030_dashboard_fork';
                console.log('Method 3: Detected from script src, using base URL:', baseUrl);
                return baseUrl;
            }
        }
        
        // Method 4: Try to detect from any link or form action
        const links = document.querySelectorAll('a[href], form[action]');
        for (const element of links) {
            const url = element.href || element.action;
            if (url && url.includes('/pcds2030_dashboard_fork/')) {
                const baseUrl = currentOrigin + '/pcds2030_dashboard_fork';
                console.log('Method 4: Detected from DOM element, using base URL:', baseUrl);
                return baseUrl;
            }
        }
        
        // Fallback: Use current origin
        console.log('Using fallback origin as base URL:', currentOrigin);
        return currentOrigin;
    }
    
    /**
     * Get notifications with filters and pagination
     */
    async getNotifications(params = {}) {
        try {
            const url = this.buildUrl('/app/ajax/get_user_notifications.php', params);
            console.log('Fetching notifications from:', url); // Debug log
            
            const response = await fetch(url, {
                method: 'GET',
                headers: this.defaultHeaders,
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                return {
                    success: true,
                    data: data.notifications,
                    pagination: data.pagination,
                    stats: data.stats
                };
            } else {
                return data;
            }
            
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
            return {
                success: false,
                message: 'Failed to fetch notifications. Please check your connection.',
                error: error.message
            };
        }
    }
    
    /**
     * Mark specific notifications as read
     */
    async markNotificationsRead(notificationIds) {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('notification_ids', JSON.stringify(notificationIds));
            
            const response = await fetch(this.baseUrl + '/app/ajax/notifications.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return this.handleResponse(data);
            
        } catch (error) {
            console.error('Failed to mark notifications as read:', error);
            return {
                success: false,
                message: 'Failed to mark notifications as read. Please try again.',
                error: error.message
            };
        }
    }
    
    /**
     * Mark specific notifications as unread
     */
    async markNotificationsUnread(notificationIds) {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_unread');
            formData.append('notification_ids', JSON.stringify(notificationIds));
            
            const response = await fetch(this.baseUrl + '/app/ajax/notifications.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return this.handleResponse(data);
            
        } catch (error) {
            console.error('Failed to mark notifications as unread:', error);
            return {
                success: false,
                message: 'Failed to mark notifications as unread. Please try again.',
                error: error.message
            };
        }
    }
    
    /**
     * Mark all notifications as read
     */
    async markAllNotificationsRead() {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_all_read');
            
            const response = await fetch(this.baseUrl + '/app/ajax/notifications.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return this.handleResponse(data);
            
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
            return {
                success: false,
                message: 'Failed to mark all notifications as read. Please try again.',
                error: error.message
            };
        }
    }
    
    /**
     * Delete specific notifications
     */
    async deleteNotifications(notificationIds) {
        try {
            const formData = new FormData();
            formData.append('action', 'delete_notification');
            formData.append('notification_id', notificationIds[0]); // Single notification for now
            
            const response = await fetch(this.baseUrl + '/app/ajax/notifications.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return this.handleResponse(data);
            
        } catch (error) {
            console.error('Failed to delete notifications:', error);
            return {
                success: false,
                message: 'Failed to delete notifications. Please try again.',
                error: error.message
            };
        }
    }
    
    /**
     * Get notification statistics
     */
    async getNotificationStats() {
        try {
            const response = await fetch(this.baseUrl + '/app/ajax/get_notification_stats.php', {
                method: 'GET',
                headers: this.defaultHeaders,
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return this.handleResponse(data);
            
        } catch (error) {
            console.error('Failed to fetch notification stats:', error);
            return {
                success: false,
                message: 'Failed to fetch notification statistics.',
                error: error.message
            };
        }
    }
    
    /**
     * Create a new notification (admin function)
     */
    async createNotification(notificationData) {
        try {
            const response = await fetch(this.baseUrl + '/app/ajax/create_notification.php', {
                method: 'POST',
                headers: this.defaultHeaders,
                credentials: 'same-origin',
                body: JSON.stringify(notificationData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return this.handleResponse(data);
            
        } catch (error) {
            console.error('Failed to create notification:', error);
            return {
                success: false,
                message: 'Failed to create notification. Please try again.',
                error: error.message
            };
        }
    }
    
    /**
     * Update notification preferences
     */
    async updateNotificationPreferences(preferences) {
        try {
            const response = await fetch(this.baseUrl + '/app/ajax/update_notification_preferences.php', {
                method: 'POST',
                headers: this.defaultHeaders,
                credentials: 'same-origin',
                body: JSON.stringify({
                    preferences: preferences
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return this.handleResponse(data);
            
        } catch (error) {
            console.error('Failed to update notification preferences:', error);
            return {
                success: false,
                message: 'Failed to update notification preferences. Please try again.',
                error: error.message
            };
        }
    }
    
    /**
     * Export notifications data
     */
    async exportNotifications(params = {}) {
        try {
            const url = this.buildUrl('/app/ajax/export_notifications.php', params);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Handle file download
            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = `notifications_export_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(downloadUrl);
            
            return {
                success: true,
                message: 'Notifications exported successfully'
            };
            
        } catch (error) {
            console.error('Failed to export notifications:', error);
            return {
                success: false,
                message: 'Failed to export notifications. Please try again.',
                error: error.message
            };
        }
    }
    
    /**
     * Send test notification (admin function)
     */
    async sendTestNotification(testData) {
        try {
            const response = await fetch(this.baseUrl + '/app/ajax/send_test_notification.php', {
                method: 'POST',
                headers: this.defaultHeaders,
                credentials: 'same-origin',
                body: JSON.stringify(testData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return this.handleResponse(data);
            
        } catch (error) {
            console.error('Failed to send test notification:', error);
            return {
                success: false,
                message: 'Failed to send test notification. Please try again.',
                error: error.message
            };
        }
    }
    
    /**
     * Build URL with query parameters
     */
    buildUrl(endpoint, params = {}) {
        // Remove leading slash from endpoint to make it relative
        const relativeEndpoint = endpoint.startsWith('/') ? endpoint.slice(1) : endpoint;
        
        // Construct the full URL properly
        const fullUrl = `${this.baseUrl}/${relativeEndpoint}`;
        const url = new URL(fullUrl);
        
        console.log('buildUrl - endpoint:', endpoint, 'baseUrl:', this.baseUrl, 'fullUrl:', fullUrl);
        
        Object.keys(params).forEach(key => {
            if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
                url.searchParams.append(key, params[key]);
            }
        });
        
        const finalUrl = url.toString();
        console.log('buildUrl - final URL:', finalUrl);
        return finalUrl;
    }
    
    /**
     * Handle API response consistently
     */
    handleResponse(data) {
        // Ensure response has expected structure
        if (typeof data !== 'object' || data === null) {
            return {
                success: false,
                message: 'Invalid response format',
                data: null
            };
        }
        
        // Handle different response formats
        if (data.hasOwnProperty('success')) {
            return data;
        }
        
        // Assume success if no explicit success field but has data
        if (data.hasOwnProperty('data') || data.hasOwnProperty('notifications')) {
            return {
                success: true,
                data: data.data || data.notifications || data,
                pagination: data.pagination || null,
                stats: data.stats || null,
                message: data.message || null
            };
        }
        
        // Handle error responses
        if (data.hasOwnProperty('error')) {
            return {
                success: false,
                message: data.error || 'An error occurred',
                data: null
            };
        }
        
        // Default successful response
        return {
            success: true,
            data: data,
            message: null
        };
    }
    
    /**
     * Handle network errors and timeouts
     */
    async fetchWithTimeout(url, options = {}, timeout = 30000) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeout);
        
        try {
            const response = await fetch(url, {
                ...options,
                signal: controller.signal
            });
            clearTimeout(timeoutId);
            return response;
        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                throw new Error('Request timed out. Please check your connection.');
            }
            
            throw error;
        }
    }
    
    /**
     * Retry failed requests with exponential backoff
     */
    async retryRequest(requestFn, maxRetries = 3, baseDelay = 1000) {
        let lastError;
        
        for (let attempt = 1; attempt <= maxRetries; attempt++) {
            try {
                return await requestFn();
            } catch (error) {
                lastError = error;
                
                if (attempt === maxRetries) {
                    break;
                }
                
                // Exponential backoff: 1s, 2s, 4s
                const delay = baseDelay * Math.pow(2, attempt - 1);
                await this.delay(delay);
                
                console.warn(`Request failed (attempt ${attempt}/${maxRetries}), retrying in ${delay}ms...`, error);
            }
        }
        
        throw lastError;
    }
    
    /**
     * Utility function for delays
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}
