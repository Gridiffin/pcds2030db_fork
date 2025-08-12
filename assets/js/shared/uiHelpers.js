/**
 * Shared UI Helpers
 * 
 * Consolidated UI utility functions used across the application.
 * This module provides safe wrappers for Bootstrap and other UI libraries.
 */

/**
 * Safe Bootstrap Modal wrapper
 * @param {HTMLElement} element - Modal element
 * @returns {Object|null} Bootstrap Modal instance or null
 */
export function getBootstrapModal(element) {
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        return bootstrap.Modal.getInstance(element) || new bootstrap.Modal(element);
    }
    return null;
}

/**
 * Safe Bootstrap Tooltip wrapper
 * @param {HTMLElement} element - Tooltip element
 * @returns {Object|null} Bootstrap Tooltip instance or null
 */
export function getBootstrapTooltip(element) {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        return new bootstrap.Tooltip(element);
    }
    return null;
}

/**
 * Safe Bootstrap Popover wrapper
 * @param {HTMLElement} element - Popover element
 * @returns {Object|null} Bootstrap Popover instance or null
 */
export function getBootstrapPopover(element) {
    if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
        return new bootstrap.Popover(element);
    }
    return null;
}

/**
 * Safe Chart.js wrapper
 * @param {HTMLCanvasElement} canvas - Canvas element
 * @param {Object} config - Chart configuration
 * @returns {Object|null} Chart instance or null
 */
export function createChart(canvas, config) {
    if (typeof Chart !== 'undefined') {
        return new Chart(canvas, config);
    }
    return null;
}

/**
 * Safe gtag wrapper for analytics
 * @param {string} action - Analytics action
 * @param {Object} parameters - Analytics parameters
 */
export function trackEvent(action, parameters = {}) {
    if (typeof gtag !== 'undefined') {
        gtag('event', action, parameters);
    }
}

/**
 * Safe showToast wrapper
 * @param {string} title - Toast title
 * @param {string} message - Toast message
 * @param {string} type - Toast type (success, error, warning, info)
 */
export function showToast(title, message, type = 'info') {
    if (typeof window.showToast === 'function') {
        window.showToast(title, message, type);
    } else {
        // Fallback toast implementation
        createFallbackToast(title, message, type);
    }
}

/**
 * Create fallback toast notification
 * @param {string} title - Toast title
 * @param {string} message - Toast message
 * @param {string} type - Toast type
 */
function createFallbackToast(title, message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <strong>${title}</strong><br>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}

/**
 * Initialize Bootstrap tooltips safely
 * @param {string} selector - CSS selector for tooltip elements
 */
export function initializeTooltips(selector = '[data-bs-toggle="tooltip"]') {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll(selector));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Initialize Bootstrap popovers safely
 * @param {string} selector - CSS selector for popover elements
 */
export function initializePopovers(selector = '[data-bs-toggle="popover"]') {
    if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
        const popoverTriggerList = [].slice.call(document.querySelectorAll(selector));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
}

/**
 * Close all Bootstrap modals safely
 */
export function closeAllModals() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        });
    }
}

/**
 * Safe jQuery wrapper (if jQuery is available)
 * @param {string|HTMLElement} selector - jQuery selector or element
 * @returns {Object|null} jQuery object or null
 */
export function $(selector) {
    if (typeof window.$ !== 'undefined') {
        return window.$(selector);
    }
    return null;
}

/**
 * Check if a global variable is defined
 * @param {string} name - Global variable name
 * @returns {boolean} Whether the variable is defined
 */
export function isGlobalDefined(name) {
    return typeof window[name] !== 'undefined';
}

/**
 * Safe module.exports wrapper for Node.js compatibility
 * @param {Object} exports - Exports object
 */
export function safeModuleExports(exports) {
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = exports;
    }
}
