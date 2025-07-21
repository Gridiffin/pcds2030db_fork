/**
 * Shared Utility Functions
 * Common utility functions used across modules
 */

/**
 * Initialize Bootstrap tooltips
 */
export function initializeTooltips() {
    // Check if Bootstrap is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Show alert message
 */
export function showAlert(message, type = 'info', duration = 5000) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-toast');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-toast`;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
    `;
    
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${getAlertIcon(type)} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(alertDiv);
    
    // Auto remove after duration
    if (duration > 0) {
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, duration);
    }
    
    return alertDiv;
}

/**
 * Get appropriate icon for alert type
 */
function getAlertIcon(type) {
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'danger': 'fa-exclamation-triangle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle',
        'primary': 'fa-info-circle',
        'secondary': 'fa-info-circle'
    };
    return icons[type] || 'fa-info-circle';
}

/**
 * Format date for display
 */
export function formatDate(date, options = {}) {
    if (!date) return '';
    
    const dateObj = date instanceof Date ? date : new Date(date);
    
    const defaultOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    };
    
    const formatOptions = { ...defaultOptions, ...options };
    
    try {
        return dateObj.toLocaleDateString('en-US', formatOptions);
    } catch (error) {
        console.error('Error formatting date:', error);
        return dateObj.toLocaleDateString();
    }
}

/**
 * Format number for display
 */
export function formatNumber(value, options = {}) {
    if (value === null || value === undefined || value === '') {
        return '';
    }
    
    const num = parseFloat(value);
    if (isNaN(num)) {
        return value;
    }
    
    const defaultOptions = {
        maximumFractionDigits: 2,
        minimumFractionDigits: 0
    };
    
    const formatOptions = { ...defaultOptions, ...options };
    
    try {
        return num.toLocaleString('en-US', formatOptions);
    } catch (error) {
        console.error('Error formatting number:', error);
        return value.toString();
    }
}

/**
 * Debounce function calls
 */
export function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

/**
 * Throttle function calls
 */
export function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Get base URL for AJAX requests
 */
export function getBaseUrl() {
    const path = window.location.pathname;
    const segments = path.split('/');
    
    // Find the project root (look for common indicators)
    let baseUrl = window.location.origin;
    
    if (segments.includes('app')) {
        const appIndex = segments.indexOf('app');
        const projectPath = segments.slice(0, appIndex).join('/');
        baseUrl += projectPath;
    } else {
        // Fallback: assume we're in a subdirectory
        baseUrl += '/' + segments[1];
    }
    
    return baseUrl.replace(/\/+$/, ''); // Remove trailing slashes
}

/**
 * Make AJAX request with common error handling
 */
export async function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    try {
        const response = await fetch(url, finalOptions);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return await response.json();
        } else {
            return await response.text();
        }
    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}

/**
 * Escape HTML characters
 */
export function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

/**
 * Generate unique ID
 */
export function generateId(prefix = 'id') {
    return prefix + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

/**
 * Check if element is in viewport
 */
export function isInViewport(element) {
    if (!element) return false;
    
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

/**
 * Smooth scroll to element
 */
export function scrollToElement(element, options = {}) {
    if (!element) return;
    
    const defaultOptions = {
        behavior: 'smooth',
        block: 'start',
        inline: 'nearest'
    };
    
    const scrollOptions = { ...defaultOptions, ...options };
    element.scrollIntoView(scrollOptions);
}

/**
 * Copy text to clipboard
 */
export async function copyToClipboard(text) {
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            document.execCommand('copy');
            textArea.remove();
        }
        return true;
    } catch (error) {
        console.error('Failed to copy text:', error);
        return false;
    }
}
