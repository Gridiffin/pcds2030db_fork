/**
 * Admin Notifications Management JavaScript
 * 
 * Handles AJAX operations, form submissions, and real-time updates
 * for the admin notification management interface.
 */

class AdminNotificationManager {
    constructor() {
        this.init();
        this.bindEvents();
        this.startAutoRefresh();
    }

    init() {
        console.log('Admin Notification Manager initialized');
        this.refreshStats();
    }

    bindEvents() {
        // Send notification form
        document.getElementById('sendNotificationForm')?.addEventListener('submit', (e) => {
            this.handleSendNotification(e);
        });

        // Cleanup notifications form
        document.getElementById('cleanupNotificationsForm')?.addEventListener('submit', (e) => {
            this.handleCleanupNotifications(e);
        });

        // Refresh buttons
        document.getElementById('refreshStats')?.addEventListener('click', () => {
            this.refreshStats();
        });

        document.getElementById('refreshNotifications')?.addEventListener('click', () => {
            this.refreshNotificationsList();
        });

        // Auto-refresh toggle (if implemented)
        document.getElementById('autoRefreshToggle')?.addEventListener('change', (e) => {
            this.toggleAutoRefresh(e.target.checked);
        });
    }

    async handleSendNotification(event) {
        event.preventDefault();
        
        const form = event.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        formData.append('action', 'send_system_notification');

        // Validate form
        const message = formData.get('message').trim();
        if (!message) {
            this.showAlert('Message is required', 'error');
            return;
        }

        // Show loading state
        this.setButtonLoading(submitBtn, true);

        try {
            const response = await fetch(window.location.pathname, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert(result.message, 'success');
                form.reset();
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('sendNotificationModal'));
                modal?.hide();
                
                // Refresh stats and notifications list
                this.refreshStats();
                this.refreshNotificationsList();
            } else {
                this.showAlert(result.message, 'error');
            }
        } catch (error) {
            console.error('Error sending notification:', error);
            this.showAlert('An error occurred while sending the notification', 'error');
        } finally {
            this.setButtonLoading(submitBtn, false);
        }
    }

    async handleCleanupNotifications(event) {
        event.preventDefault();
        
        const form = event.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        formData.append('action', 'cleanup_notifications');

        const daysToKeep = parseInt(formData.get('days_to_keep'));
        if (isNaN(daysToKeep) || daysToKeep < 1) {
            this.showAlert('Please enter a valid number of days to keep', 'error');
            return;
        }

        // Confirm action
        if (!confirm(`Are you sure you want to delete notifications older than ${daysToKeep} days? This action cannot be undone.`)) {
            return;
        }

        // Show loading state
        this.setButtonLoading(submitBtn, true);

        try {
            const response = await fetch(window.location.pathname, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert(result.message, 'success');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('cleanupNotificationsModal'));
                modal?.hide();
                
                // Refresh stats and notifications list
                this.refreshStats();
                this.refreshNotificationsList();
            } else {
                this.showAlert(result.message, 'error');
            }
        } catch (error) {
            console.error('Error cleaning up notifications:', error);
            this.showAlert('An error occurred while cleaning up notifications', 'error');
        } finally {
            this.setButtonLoading(submitBtn, false);
        }
    }

    async refreshStats() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_notification_stats');

            const response = await fetch(window.location.pathname, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.updateStatsDisplay(result.stats, result.type_stats);
            }
        } catch (error) {
            console.error('Error refreshing stats:', error);
        }
    }

    updateStatsDisplay(stats, typeStats) {
        // Update main stat cards
        this.updateElement('totalNotifications', this.formatNumber(stats.total_notifications || 0));
        this.updateElement('unreadNotifications', this.formatNumber(stats.unread_notifications || 0));
        this.updateElement('usersWithNotifications', this.formatNumber(stats.users_with_notifications || 0));
        this.updateElement('notificationsLast24h', this.formatNumber(stats.notifications_last_24h || 0));

        // Update type statistics (if there's a chart or additional display)
        this.updateTypeStats(typeStats);
        
        // Add visual feedback
        this.addRefreshAnimation();
    }

    updateTypeStats(typeStats) {
        // This can be expanded to show a breakdown by notification type
        // For now, we'll log the data for debugging
        console.log('Notification type statistics:', typeStats);
    }

    async refreshNotificationsList() {
        // Reload the page to refresh the notifications list
        // In a more sophisticated implementation, this would be an AJAX call
        // to load only the notifications table content
        window.location.reload();
    }

    updateElement(id, content) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = content;
        }
    }

    formatNumber(num) {
        return new Intl.NumberFormat().format(num);
    }

    addRefreshAnimation() {
        const statsCards = document.querySelectorAll('.admin-stat-card');
        statsCards.forEach(card => {
            card.style.animation = 'none';
            card.offsetHeight; // Trigger reflow
            card.style.animation = 'pulse 0.5s ease-in-out';
        });
    }

    setButtonLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.classList.add('loading');
            button.setAttribute('data-original-text', button.innerHTML);
        } else {
            button.disabled = false;
            button.classList.remove('loading');
            const originalText = button.getAttribute('data-original-text');
            if (originalText) {
                button.innerHTML = originalText;
                button.removeAttribute('data-original-text');
            }
        }
    }

    showAlert(message, type = 'info') {
        // Create toast notification
        const toastContainer = this.getOrCreateToastContainer();
        
        const toastHtml = `
            <div class="toast align-items-center text-bg-${type === 'error' ? 'danger' : type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${this.getAlertIcon(type)}"></i> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: type === 'error' ? 5000 : 3000
        });
        
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    getOrCreateToastContainer() {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1060';
            document.body.appendChild(container);
        }
        return container;
    }

    getAlertIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    startAutoRefresh() {
        // Refresh stats every 5 minutes
        this.autoRefreshInterval = setInterval(() => {
            this.refreshStats();
        }, 5 * 60 * 1000);
    }

    toggleAutoRefresh(enabled) {
        if (enabled) {
            this.startAutoRefresh();
        } else {
            clearInterval(this.autoRefreshInterval);
        }
    }

    destroy() {
        clearInterval(this.autoRefreshInterval);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.adminNotificationManager = new AdminNotificationManager();
});

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    .loading-spinner {
        width: 3rem;
        height: 3rem;
        border: 0.3rem solid rgba(255, 255, 255, 0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
`;
document.head.appendChild(style);