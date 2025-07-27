/**
 * Admin View Submissions JavaScript
 * Functionality for admin view submissions page
 */

// Import admin view submissions styles (includes shared base)
import '../../../css/admin/programs/admin-view-submissions.css';

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
    
    // Initialize submission navigation
    initializeSubmissionNavigation();
    
    console.log('Admin view submissions page initialized');
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
 * Initialize submission navigation functionality
 */
function initializeSubmissionNavigation() {
    // Handle submission period selection
    const submissionLinks = document.querySelectorAll('.submission-period-link');
    submissionLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const programId = this.dataset.programId;
            const periodId = this.dataset.periodId;
            
            if (programId && periodId) {
                window.location.href = `view_submissions.php?program_id=${programId}&period_id=${periodId}`;
            }
        });
    });
    
    // Handle back to all submissions
    const backLinks = document.querySelectorAll('.back-to-submissions');
    backLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const programId = this.dataset.programId;
            
            if (programId) {
                window.location.href = `view_submissions.php?program_id=${programId}`;
            }
        });
    });
}

/**
 * Show toast notification
 */
function showToast(title, message, type = 'info') {
    if (typeof window.showToast === 'function') {
        window.showToast(title, message, type);
    } else {
        alert(`${title}: ${message}`);
    }
}

// Export functions for global access
window.AdminViewSubmissions = {
    initializeTooltips,
    initializeSubmissionNavigation
};