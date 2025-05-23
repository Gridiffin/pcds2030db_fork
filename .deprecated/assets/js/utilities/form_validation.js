/**
 * Form Validation Utilities
 * 
 * Shared functions for validating forms across the application.
 */

/**
 * Validate date ranges
 * @param {HTMLElement} startDateElement - Start date input element
 * @param {HTMLElement} endDateElement - End date input element
 * @returns {boolean} Whether the dates are valid
 */
function validateDateRange(startDateElement, endDateElement) {
    if (!startDateElement || !endDateElement) return true;
    
    // Clear previous validation errors
    [startDateElement, endDateElement].forEach(element => {
        element.classList.remove('is-invalid');
        const feedback = element.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.remove();
        }
    });
    
    // Skip validation if either field is empty
    if (!startDateElement.value || !endDateElement.value) return true;
    
    const startDate = new Date(startDateElement.value);
    const endDate = new Date(endDateElement.value);
    
    if (startDate > endDate) {
        // Show validation error
        endDateElement.classList.add('is-invalid');
        
        // Create feedback message if it doesn't exist
        let feedbackElement = endDateElement.nextElementSibling;
        if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
            feedbackElement = document.createElement('div');
            feedbackElement.className = 'invalid-feedback';
            endDateElement.parentNode.appendChild(feedbackElement);
        }
        
        feedbackElement.textContent = 'End date cannot be before start date';
        return false;
    }
    
    return true;
}

/**
 * Show validation error for a field
 * @param {string} fieldId - ID of the field with error
 * @param {string} message - Error message to display
 */
function showValidationError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.add('is-invalid');
    
    // Add error message if it doesn't exist
    if (!document.getElementById(`${fieldId}-error`)) {
        const errorDiv = document.createElement('div');
        errorDiv.id = `${fieldId}-error`;
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
}

/**
 * Clear validation error for a field
 * @param {string} fieldId - ID of the field to clear error for
 */
function clearValidationError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.remove('is-invalid');
    
    // Remove error message if it exists
    const errorDiv = document.getElementById(`${fieldId}-error`);
    if (errorDiv) errorDiv.remove();
}

/**
 * Create a form error alert
 * @param {string} message - Error message HTML content
 * @param {HTMLElement} container - Container to append alert to
 * @returns {HTMLElement} The created alert element
 */
function createFormAlert(message, container) {
    // Remove any existing alerts
    const existingAlert = container.querySelector('.form-error-alert');
    if (existingAlert) existingAlert.remove();
    
    // Create new alert
    const alertElement = document.createElement('div');
    alertElement.className = 'alert alert-danger form-error-alert mt-3';
    alertElement.innerHTML = message;
    
    // Add to container
    container.appendChild(alertElement);
    
    // Scroll to error
    alertElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    return alertElement;
}

/**
 * Initialize character counter for text fields
 * @param {string} fieldId - ID of field to count characters for
 * @param {number} maxLength - Maximum allowed characters
 */
function initCharacterCounter(fieldId, maxLength) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    const counterElement = field.nextElementSibling;
    if (!counterElement || !counterElement.classList.contains('character-counter')) return;
    
    // Update counter on input
    field.addEventListener('input', function() {
        const currentLength = this.value.length;
        counterElement.textContent = `${currentLength}/${maxLength} characters`;
        
        if (currentLength > maxLength) {
            counterElement.classList.add('text-danger');
            field.classList.add('is-invalid');
        } else {
            counterElement.classList.remove('text-danger');
            field.classList.remove('is-invalid');
        }
    });
    
    // Trigger initial count
    field.dispatchEvent(new Event('input'));
}
