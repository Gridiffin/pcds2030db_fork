/**
 * Submission action handlers
 * Handles submit, edit, and other submission-related actions
 */

import { canPerformAction, getBaseUrl } from './logic.js';

/**
 * Initialize submission action handlers
 */
export function initializeSubmissionActions() {
    // Handle submit submission button
    const submitButtons = document.querySelectorAll('[data-action="submit-submission"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', handleSubmitSubmission);
    });
    
    // Handle edit submission links
    const editLinks = document.querySelectorAll('[data-action="edit-submission"]');
    editLinks.forEach(link => {
        link.addEventListener('click', handleEditSubmission);
    });
    
    // Handle add new submission links
    const addLinks = document.querySelectorAll('[data-action="add-submission"]');
    addLinks.forEach(link => {
        link.addEventListener('click', handleAddSubmission);
    });
}

/**
 * Handle submission submit action
 * @param {Event} event - Click event
 */
function handleSubmitSubmission(event) {
    event.preventDefault();
    
    const button = event.currentTarget;
    const submissionId = button.dataset.submissionId || window.submissionId;
    const programId = window.programId;
    
    if (!submissionId) {
        console.error('No submission ID found for submit action');
        return;
    }
    
    // Show confirmation dialog
    const confirmMessage = 'Are you sure you want to submit this submission for review? Once submitted, you may not be able to edit it.';
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Add loading state
    button.classList.add('loading');
    button.disabled = true;
    
    // Redirect to submission handler
    const baseUrl = getBaseUrl();
    const submitUrl = `${baseUrl}/app/views/agency/programs/submit_submission.php?submission_id=${submissionId}&program_id=${programId}`;
    
    window.location.href = submitUrl;
}

/**
 * Handle edit submission action
 * @param {Event} event - Click event
 */
function handleEditSubmission(event) {
    // Allow default link behavior, but could add validation here
    const link = event.currentTarget;
    const href = link.href;
    
    if (!href || href === '#') {
        event.preventDefault();
        console.error('Edit submission link has no valid href');
        return;
    }
    
    // Could add loading state or validation here
    console.log('Navigating to edit submission:', href);
}

/**
 * Handle add new submission action
 * @param {Event} event - Click event
 */
function handleAddSubmission(event) {
    // Allow default link behavior, but could add validation here
    const link = event.currentTarget;
    const href = link.href;
    
    if (!href || href === '#') {
        event.preventDefault();
        console.error('Add submission link has no valid href');
        return;
    }
    
    console.log('Navigating to add submission:', href);
}

/**
 * Show loading state on button
 * @param {HTMLElement} button - Button element
 */
function showLoadingState(button) {
    button.classList.add('loading');
    button.disabled = true;
    
    const originalText = button.textContent;
    button.dataset.originalText = originalText;
    button.textContent = 'Loading...';
}

/**
 * Hide loading state on button
 * @param {HTMLElement} button - Button element
 */
function hideLoadingState(button) {
    button.classList.remove('loading');
    button.disabled = false;
    
    if (button.dataset.originalText) {
        button.textContent = button.dataset.originalText;
        delete button.dataset.originalText;
    }
}
