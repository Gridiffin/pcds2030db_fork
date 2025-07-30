/**
 * View Submissions Page - JavaScript Entry Point
 * Handles submission viewing functionality
 */

// Import CSS for bundle generation
import '../../../css/agency/programs/view_submissions_entry.css';
import '../../../css/agency/programs/view_submissions.css';

// Import utility functions
import { showAlert } from '../../shared/utils.js';

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ View Submissions page initialized');
    
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});