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
