/**
 * Status Utilities
 * Shared functions for handling program status display and interactions
 */

/**
 * Get the appropriate CSS class for a status
 * @param {string} status - The program status ('on-track', 'delayed', 'completed', 'not-started')
 * @param {string} prefix - CSS class prefix (default: 'bg')
 * @return {string} CSS class name
 */
function getStatusClass(status, prefix = 'bg') {
    let statusClass = 'secondary';
    
    switch (status) {
        case 'on-track': 
            statusClass = 'success'; 
            break;
        case 'delayed': 
            statusClass = 'warning'; 
            break;
        case 'completed': 
            statusClass = 'info'; 
            break;
        case 'not-started': 
            statusClass = 'secondary'; 
            break;
    }
    
    return `${prefix}-${statusClass}`;
}

/**
 * Format a status name for display (capitalize, replace hyphens)
 * @param {string} status - The program status 
 * @return {string} Formatted status name
 */
function formatStatusName(status) {
    if (!status) return 'Not Reported';
    
    // Replace hyphens with spaces and capitalize each word
    return status
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

/**
 * Initialize status pill selection behavior
 * @param {string} containerId - ID of container with status pills (optional)
 */
function initStatusPills(containerId = null) {
    const container = containerId ? document.getElementById(containerId) : document;
    if (!container) return;
    
    const statusPills = container.querySelectorAll('.status-pill');
    const statusInput = container.querySelector('input[name="status"]') || document.getElementById('status');
    
    if (!statusPills.length || !statusInput) return;
    
    statusPills.forEach(pill => {
        pill.addEventListener('click', function() {
            // Ignore if this is a read-only pill (large)
            if (this.classList.contains('large')) return;
            
            // Remove active class from all pills
            statusPills.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked pill
            this.classList.add('active');
            
            // Update hidden input value
            statusInput.value = this.getAttribute('data-status');
            
            // Trigger a custom event that other components can listen for
            const event = new CustomEvent('statusChanged', {
                detail: { status: statusInput.value }
            });
            document.dispatchEvent(event);
            
            // Validate related fields if needed (e.g., date validation)
            if (typeof validateDates === 'function') {
                validateDates();
            }
        });
    });
}

/**
 * Create a status badge element
 * @param {string} status - The status value
 * @param {boolean} usePill - Whether to use pill style (default: false)
 * @return {HTMLElement} The status badge element
 */
function createStatusBadge(status, usePill = false) {
    const statusClass = getStatusClass(status);
    const statusText = formatStatusName(status);
    
    const badge = document.createElement('span');
    badge.className = usePill ? `badge rounded-pill ${statusClass}` : `badge ${statusClass}`;
    badge.textContent = statusText;
    
    return badge;
}

// Initialize status pills when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initStatusPills();
});
