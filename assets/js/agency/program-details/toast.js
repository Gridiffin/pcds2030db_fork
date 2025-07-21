/**
 * Program Details Toast Notifications Module
 * 
 * Handles toast notifications for the program details page.
 */

export class ProgramDetailsToast {
    constructor(controller) {
        this.controller = controller;
        this.toastContainer = null;
        this.activeToasts = new Map();
    }

    /**
     * Initialize toast functionality
     */
    init() {
        console.log('Initializing Program Details Toast...');
        this.createToastContainer();
    }

    /**
     * Create toast container if it doesn't exist
     */
    createToastContainer() {
        // Check if container already exists
        this.toastContainer = document.getElementById('toast-container');
        
        if (!this.toastContainer) {
            this.toastContainer = document.createElement('div');
            this.toastContainer.id = 'toast-container';
            this.toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            this.toastContainer.style.zIndex = '1060'; // Above modals
            document.body.appendChild(this.toastContainer);
        }
    }

    /**
     * Show success toast
     */
    showSuccess(message, duration = 5000) {
        return this.showToast(message, 'success', duration);
    }

    /**
     * Show error toast
     */
    showError(message, duration = 8000) {
        return this.showToast(message, 'danger', duration);
    }

    /**
     * Show warning toast
     */
    showWarning(message, duration = 6000) {
        return this.showToast(message, 'warning', duration);
    }

    /**
     * Show info toast
     */
    showInfo(message, duration = 5000) {
        return this.showToast(message, 'info', duration);
    }

    /**
     * Show toast with action button
     */
    showToastWithAction(title, message, type = 'info', duration = 10000, action = null) {
        return this.showToast(message, type, duration, title, action);
    }

    /**
     * Show a toast notification
     */
    showToast(message, type = 'info', duration = 5000, title = null, action = null) {
        const toastId = this.generateToastId();
        const toast = this.createToastElement(toastId, message, type, title, action);
        
        this.toastContainer.appendChild(toast);
        
        // Initialize Bootstrap toast
        let bsToast;
        if (window.bootstrap && window.bootstrap.Toast) {
            bsToast = new bootstrap.Toast(toast, {
                autohide: duration > 0,
                delay: duration
            });
            bsToast.show();
        } else {
            // Fallback without Bootstrap
            toast.style.display = 'block';
            if (duration > 0) {
                setTimeout(() => this.hideToast(toastId), duration);
            }
        }

        // Store reference
        this.activeToasts.set(toastId, {
            element: toast,
            bsToast: bsToast,
            timer: duration > 0 ? setTimeout(() => this.removeToast(toastId), duration + 500) : null
        });

        // Auto-remove after hiding
        toast.addEventListener('hidden.bs.toast', () => {
            this.removeToast(toastId);
        });

        return toastId;
    }

    /**
     * Create toast DOM element
     */
    createToastElement(toastId, message, type, title, action) {
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        const iconMap = {
            success: 'fas fa-check-circle',
            danger: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        const icon = iconMap[type] || iconMap.info;

        let html = '<div class="d-flex">';
        
        // Toast body
        html += '<div class="toast-body d-flex align-items-center">';
        html += `<i class="${icon} me-2"></i>`;
        html += '<div class="flex-grow-1">';
        
        if (title) {
            html += `<div class="fw-bold">${this.escapeHtml(title)}</div>`;
        }
        
        html += `<div>${this.escapeHtml(message)}</div>`;
        
        if (action) {
            html += `<div class="mt-2">`;
            html += `<a href="${action.url}" class="btn btn-sm btn-outline-light">${this.escapeHtml(action.text)}</a>`;
            html += `</div>`;
        }
        
        html += '</div>';
        html += '</div>';
        
        // Close button
        html += '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>';
        
        html += '</div>';

        toast.innerHTML = html;
        return toast;
    }

    /**
     * Hide specific toast
     */
    hideToast(toastId) {
        const toastData = this.activeToasts.get(toastId);
        if (!toastData) return;

        if (toastData.bsToast) {
            toastData.bsToast.hide();
        } else {
            toastData.element.style.display = 'none';
            this.removeToast(toastId);
        }
    }

    /**
     * Remove toast from DOM and cleanup
     */
    removeToast(toastId) {
        const toastData = this.activeToasts.get(toastId);
        if (!toastData) return;

        // Clear timer
        if (toastData.timer) {
            clearTimeout(toastData.timer);
        }

        // Remove from DOM
        if (toastData.element && toastData.element.parentNode) {
            toastData.element.parentNode.removeChild(toastData.element);
        }

        // Remove from active toasts
        this.activeToasts.delete(toastId);
    }

    /**
     * Hide all active toasts
     */
    hideAllToasts() {
        this.activeToasts.forEach((toastData, toastId) => {
            this.hideToast(toastId);
        });
    }

    /**
     * Clear all toasts immediately
     */
    clearAllToasts() {
        this.activeToasts.forEach((toastData, toastId) => {
            this.removeToast(toastId);
        });
        
        if (this.toastContainer) {
            this.toastContainer.innerHTML = '';
        }
    }

    /**
     * Generate unique toast ID
     */
    generateToastId() {
        return `toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Show loading toast
     */
    showLoading(message = 'Loading...') {
        const toastId = this.generateToastId();
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = 'toast align-items-center text-bg-primary border-0';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div>${this.escapeHtml(message)}</div>
                </div>
            </div>
        `;

        this.toastContainer.appendChild(toast);

        // Initialize without auto-hide
        let bsToast;
        if (window.bootstrap && window.bootstrap.Toast) {
            bsToast = new bootstrap.Toast(toast, { autohide: false });
            bsToast.show();
        } else {
            toast.style.display = 'block';
        }

        this.activeToasts.set(toastId, {
            element: toast,
            bsToast: bsToast,
            timer: null
        });

        return toastId;
    }

    /**
     * Update loading toast to success/error
     */
    updateLoadingToast(toastId, message, type = 'success') {
        const toastData = this.activeToasts.get(toastId);
        if (!toastData) return;

        // Remove loading toast
        this.removeToast(toastId);

        // Show new toast
        return this.showToast(message, type);
    }
}

// Global toast functions for backward compatibility
window.showToast = function(title, message, type = 'info', duration = 5000) {
    if (window.programDetailsController && window.programDetailsController.toast) {
        return window.programDetailsController.toast.showToast(message, type, duration, title);
    }
    console.warn('Toast system not initialized');
};

window.showToastWithAction = function(title, message, type = 'info', duration = 10000, action = null) {
    if (window.programDetailsController && window.programDetailsController.toast) {
        return window.programDetailsController.toast.showToastWithAction(title, message, type, duration, action);
    }
    console.warn('Toast system not initialized');
};
