/**
 * Admin Generate Reports JavaScript
 * 
 * Handles the UI interactions for the report generation page.
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initializePeriodFiltering();
    initializeReportGeneration();
    setupTooltips();
});

/**
 * Initialize period filtering functionality
 */
function initializePeriodFiltering() {
    const periodSelect = document.getElementById('period_id');
    
    // Add event listener for period selection changes
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            // If a new period is selected, redirect to update the report list
            if (this.value) {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('period_id', this.value);
                window.location.href = currentUrl.toString();
            }
        });
    }
}

/**
 * Initialize report generation functionality with error handling
 */
function initializeReportGeneration() {
    const generateBtn = document.getElementById('generateReportBtn');
    const refreshBtn = document.getElementById('refreshReportList');
    
    // Handle generate button errors gracefully
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            try {
                // The main functionality is already in the inline script
                // This is just additional error handling
                console.log('Report generation initiated');
            } catch (error) {
                console.error('Error in report generation:', error);
                showAlert('danger', 'An unexpected error occurred: ' + error.message);
            }
        });
    }
    
    // Enhance refresh button with loading state
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
            // The redirect happens in the inline script
        });
    }
}

/**
 * Initialize Bootstrap tooltips
 */
function setupTooltips() {
    // Initialize any tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(function(tooltip) {
        new bootstrap.Tooltip(tooltip);
    });
}

/**
 * Show alert messages
 * @param {string} type - The alert type (success, danger, warning, info)
 * @param {string} message - The message to display
 */
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        alert.classList.remove('show');
        setTimeout(function() {
            if (alert.parentNode === alertContainer) {
                alertContainer.removeChild(alert);
            }
        }, 150);
    }, 5000);
}

// Additional utility functions
/**
 * Format date for display
 * @param {string} dateString - The date string to format
 * @return {string} Formatted date string
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}