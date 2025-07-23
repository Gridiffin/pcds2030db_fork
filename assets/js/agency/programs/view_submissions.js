/**
 * View Submissions Page - JavaScript Entry Point
 * Handles submission viewing functionality
 */

// Import CSS for bundle generation
import '../../../css/agency/programs/programs.css';

// Import utility functions
import { showAlert } from '../../shared/utils.js';

/**
 * Submit submission for review
 */
window.submitSubmission = function(submissionId) {
    if (!confirm('Are you sure you want to submit this submission for review? This action cannot be undone.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('submission_id', submissionId);
    formData.append('action', 'submit');
    
    // Use dynamic base path for API calls
    const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -3).join('/');
    const apiUrl = `${baseUrl}/app/api/agency/submit_submission.php`;
    
    fetch(apiUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showAlert(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to submit submission', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while submitting the submission', 'danger');
    });
};

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