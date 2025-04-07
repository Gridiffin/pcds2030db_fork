/**
 * Status Utilities
 * 
 * Shared functions for handling program status in the frontend.
 */

/**
 * Initialize status pill selection behavior
 */
function initStatusPills() {
    const statusPills = document.querySelectorAll('.status-pill');
    const statusInput = document.getElementById('status');
    
    if (statusPills.length && statusInput) {
        statusPills.forEach(pill => {
            pill.addEventListener('click', function() {
                // Remove active class from all pills
                statusPills.forEach(p => p.classList.remove('active'));
                
                // Add active class to clicked pill
                this.classList.add('active');
                
                // Update hidden input
                statusInput.value = this.getAttribute('data-status');
            });
        });
    }
}

/**
 * Get color class for a status value
 * @param {string} status - Status value
 * @returns {string} CSS color class
 */
function getStatusColorClass(status) {
    switch (status) {
        case 'target-achieved': return 'success';
        case 'on-track-yearly': return 'warning';
        case 'severe-delay': return 'danger';
        case 'not-started': 
        default: return 'secondary';
    }
}

/**
 * Get icon class for a status value
 * @param {string} status - Status value
 * @returns {string} FontAwesome icon class
 */
function getStatusIconClass(status) {
    switch (status) {
        case 'target-achieved': return 'fas fa-check-circle';
        case 'on-track-yearly': return 'fas fa-calendar-check';
        case 'severe-delay': return 'fas fa-exclamation-triangle';
        case 'not-started': 
        default: return 'fas fa-hourglass-start';
    }
}

/**
 * Create a status badge element
 * @param {string} status - Status value
 * @returns {HTMLElement} Badge element
 */
function createStatusBadge(status) {
    const badge = document.createElement('span');
    badge.className = `badge bg-${getStatusColorClass(status)}`;
    badge.textContent = status.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
    return badge;
}

/**
 * Create a rich status badge with icon
 * @param {string} status - Status value
 * @returns {HTMLElement} Badge element with icon
 */
function createRichStatusBadge(status) {
    const badge = document.createElement('span');
    badge.className = `badge bg-${getStatusColorClass(status)}`;
    
    const icon = document.createElement('i');
    icon.className = `${getStatusIconClass(status)} me-1`;
    
    badge.appendChild(icon);
    badge.appendChild(document.createTextNode(
        status.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())
    ));
    
    return badge;
}

// Initialize on document load if auto-init is needed
document.addEventListener('DOMContentLoaded', function() {
    // Auto-initialize status pills if data-auto-init attribute exists
    if (document.querySelector('.status-pills[data-auto-init]')) {
        initStatusPills();
    }
});
