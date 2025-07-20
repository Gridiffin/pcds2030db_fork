// Notifications AJAX Module
// Handles all API communication and data fetching

export default class NotificationsAjax {
    constructor(app) {
        this.app = app;
        this.baseUrl = window.location.origin;
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
    }
    
    /**
     * Get notifications with filters and pagination
     */
    async getNotifications(params = {}) {
        try {
            const url = this.buildUrl('/app/ajax/get_user_notifications.php', params);
            
            const response = await fetch(url, {
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
            const response = await fetch('/app/ajax/mark_notifications_read.php', {
                method: 'POST',
                headers: this.defaultHeaders,
                credentials: 'same-origin',
                body: JSON.stringify({
                    notification_ids: notificationIds
                })
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
     * Mark all notifications as read
     */
    async markAllNotificationsRead() {
        try {
            const response = await fetch('/app/ajax/mark_all_notifications_read.php', {
                method: 'POST',
                headers: this.defaultHeaders,
                credentials: 'same-origin',
                body: JSON.stringify({})
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
            const response = await fetch('/app/ajax/delete_notifications.php', {
                method: 'POST',
                headers: this.defaultHeaders,
                credentials: 'same-origin',
                body: JSON.stringify({
                    notification_ids: notificationIds
                })
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
            const response = await fetch('/app/ajax/get_notification_stats.php', {
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
            const response = await fetch('/app/ajax/create_notification.php', {
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
            const response = await fetch('/app/ajax/update_notification_preferences.php', {
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
            const response = await fetch('/app/ajax/send_test_notification.php', {
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
        const url = new URL(endpoint, this.baseUrl);
        
        Object.keys(params).forEach(key => {
            if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
                url.searchParams.append(key, params[key]);
            }
        });
        
        return url.toString();
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
