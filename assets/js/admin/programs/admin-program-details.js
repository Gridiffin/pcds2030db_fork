/**
 * Admin Program Details JavaScript
 * Functionality for admin program details page
 */

// Import admin program details styles (includes shared base)
import '../../../css/admin/programs/admin-program-details.css';

// Import essential utilities
import '../../utilities/initialization.js';
import '../../utilities/dropdown_init.js';

// Import main utilities including showToast
import '../../main.js';

// Import Bootstrap modal fix
import '../bootstrap_modal_fix.js';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize modals if any
    initializeModals();
    
    // Initialize copy functionality
    initializeCopyFunctionality();
});

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Initialize modals
 */
function initializeModals() {
    // Initialize any confirmation modals or action modals
    const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            const target = this.getAttribute('data-bs-target');
            const modal = document.querySelector(target);
            if (modal && typeof bootstrap !== 'undefined') {
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
            }
        });
    });
}

/**
 * Initialize copy functionality for program details
 */
function initializeCopyFunctionality() {
    // Add copy buttons for program numbers, IDs, etc.
    const copyableElements = document.querySelectorAll('.copyable');
    copyableElements.forEach(element => {
        element.style.cursor = 'pointer';
        element.title = 'Click to copy';
        
        element.addEventListener('click', function() {
            const textToCopy = this.textContent.trim();
            copyToClipboard(textToCopy);
        });
    });
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showCopySuccess();
        }).catch(err => {
            console.error('Failed to copy: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

/**
 * Fallback copy method for older browsers
 */
function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess();
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }
    
    document.body.removeChild(textArea);
}

/**
 * Show copy success message
 */
function showCopySuccess() {
    if (typeof showToast === 'function') {
        showToast('Success', 'Copied to clipboard!', 'success');
    } else {
        // Create a temporary notification
        const notification = document.createElement('div');
        notification.textContent = 'Copied to clipboard!';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            z-index: 9999;
            font-size: 14px;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 2000);
    }
}

/**
 * Handle download links with loading states
 */
function handleDownload(link) {
    const originalText = link.innerHTML;
    link.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Downloading...';
    link.style.pointerEvents = 'none';
    
    // Reset after a delay (actual download should handle this)
    setTimeout(() => {
        link.innerHTML = originalText;
        link.style.pointerEvents = 'auto';
    }, 2000);
}

/**
 * Confirm action with custom message
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    if (!dateString) return 'Not available';
    
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    return date.toLocaleDateString('en-US', options);
}

/**
 * Update relative timestamps
 */
function updateRelativeTimestamps() {
    const timeElements = document.querySelectorAll('[data-date]');
    timeElements.forEach(element => {
        const date = new Date(element.dataset.date);
        const now = new Date();
        const diffInDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
        
        let relativeText = '';
        if (diffInDays === 0) {
            relativeText = 'Today';
        } else if (diffInDays === 1) {
            relativeText = 'Yesterday';
        } else if (diffInDays < 7) {
            relativeText = `${diffInDays} days ago`;
        } else {
            relativeText = formatDate(element.dataset.date);
        }
        
        element.title = formatDate(element.dataset.date);
        if (element.textContent !== relativeText) {
            element.textContent = relativeText;
        }
    });
}

// Update timestamps every minute
setInterval(updateRelativeTimestamps, 60000);

// Make functions globally available
window.copyToClipboard = copyToClipboard;
window.handleDownload = handleDownload;
window.confirmAction = confirmAction;
window.formatDate = formatDate;