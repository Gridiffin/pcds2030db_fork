/**
 * Business logic for view submissions page
 * Pure functions that can be easily tested
 */

/**
 * Validate submission data integrity
 * @param {number} submissionId - The submission ID to validate
 * @returns {boolean} - Whether submission data is valid
 */
export function validateSubmissionData(submissionId) {
    if (!submissionId || submissionId <= 0) {
        console.warn('Invalid submission ID provided:', submissionId);
        return false;
    }
    
    // Add more validation logic as needed
    return true;
}

/**
 * Format period display name
 * @param {Object} period - Period data object
 * @returns {string} - Formatted period display name
 */
export function formatPeriodDisplay(period) {
    if (!period || !period.year || !period.period_type || !period.period_number) {
        return 'Unknown Period';
    }
    
    const periodType = period.period_type.charAt(0).toUpperCase() + period.period_type.slice(1);
    return `${period.year} ${periodType} ${period.period_number}`;
}

/**
 * Calculate target statistics
 * @param {Array} targets - Array of target objects
 * @returns {Object} - Statistics object with counts by status
 */
export function calculateTargetStats(targets) {
    const stats = {
        total: 0,
        on_track: 0,
        at_risk: 0,
        behind: 0,
        completed: 0
    };
    
    if (!Array.isArray(targets)) {
        return stats;
    }
    
    stats.total = targets.length;
    
    targets.forEach(target => {
        const status = target.status_indicator || 'unknown';
        if (stats.hasOwnProperty(status)) {
            stats[status]++;
        }
    });
    
    return stats;
}

/**
 * Validate user permissions for actions
 * @param {string} action - The action to validate
 * @param {Object} permissions - User permissions object
 * @returns {boolean} - Whether user can perform action
 */
export function canPerformAction(action, permissions) {
    if (!permissions || typeof permissions !== 'object') {
        return false;
    }
    
    const actionMap = {
        'edit': permissions.can_edit || false,
        'submit': permissions.can_edit || false,
        'view': permissions.can_view || false,
        'delete': permissions.is_owner || false
    };
    
    return actionMap[action] || false;
}

/**
 * Format file size for display
 * @param {number} bytes - File size in bytes
 * @returns {string} - Formatted file size string
 */
export function formatFileSize(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Generate dynamic base URL for AJAX requests
 * @returns {string} - Base URL for API calls
 */
export function getBaseUrl() {
    // Use the global APP_URL if available, otherwise construct from location
    if (window.APP_URL) {
        return window.APP_URL;
    }
    
    const path = window.location.pathname;
    const parts = path.split('/');
    
    // Find the project root by looking for common directory structure
    let baseIndex = -1;
    for (let i = 0; i < parts.length; i++) {
        if (parts[i] === 'app' && parts[i + 1] === 'views') {
            baseIndex = i;
            break;
        }
    }
    
    if (baseIndex > 0) {
        return parts.slice(0, baseIndex).join('/');
    }
    
    // Fallback to current directory approach
    return window.location.origin + window.location.pathname.split('/').slice(0, -4).join('/');
}
