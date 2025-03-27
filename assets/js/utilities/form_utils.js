/**
 * Form Utilities
 * Shared functions for form validation and handling
 */

/**
 * Validate a date range between two date fields
 * @param {HTMLElement} startField - The start date field
 * @param {HTMLElement} endField - The end date field
 * @param {string} errorMessage - Custom error message (optional)
 * @returns {boolean} True if valid, false otherwise
 */
function validateDateRange(startField, endField, errorMessage) {
    if (!startField || !endField || !startField.value || !endField.value) {
        return true; // Nothing to validate if fields are empty
    }
    
    const startDate = new Date(startField.value);
    const endDate = new Date(endField.value);
    
    if (startDate > endDate) {
        const message = errorMessage || 'End date cannot be before start date';
        
        // Mark fields as invalid
        startField.classList.add('is-invalid');
        endField.classList.add('is-invalid');
        
        // Show error message
        const errorId = 'date-range-error';
        if (!document.getElementById(errorId)) {
            const errorDiv = document.createElement('div');
            errorDiv.id = errorId;
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            endField.parentNode.appendChild(errorDiv);
        }
        
        return false;
    }
    
    // Clear validation errors
    startField.classList.remove('is-invalid');
    endField.classList.remove('is-invalid');
    
    // Remove error message if exists
    const errorDiv = document.getElementById('date-range-error');
    if (errorDiv) errorDiv.remove();
    
    return true;
}

/**
 * Add character counter to a text field
 * @param {HTMLElement} field - The text field
 * @param {number} maxLength - Maximum character length
 */
function addCharacterCounter(field, maxLength) {
    if (!field) return;
    
    // Create counter element
    const counter = document.createElement('small');
    counter.className = 'text-muted float-end character-counter';
    counter.textContent = `${field.value.length}/${maxLength} characters`;
    field.parentNode.appendChild(counter);
    
    // Update counter on input
    field.addEventListener('input', function() {
        const currentLength = this.value.length;
        counter.textContent = `${currentLength}/${maxLength} characters`;
        
        if (currentLength > maxLength) {
            counter.classList.add('text-danger');
            field.classList.add('is-invalid');
        } else {
            counter.classList.remove('text-danger');
            field.classList.remove('is-invalid');
        }
    });
}

/**
 * Show validation error message for a field
 * @param {string} fieldId - ID of the field
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
 * @param {string} fieldId - ID of the field
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
 * Add loading state to a submit button
 * @param {HTMLElement} button - The submit button
 * @param {string} loadingText - Text to display during loading (optional)
 */
function setButtonLoading(button, loadingText) {
    if (!button) return;
    
    const originalText = button.innerHTML;
    const text = loadingText || 'Processing...';
    
    button.disabled = true;
    button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${text}`;
    
    // Return a function to reset the button
    return function() {
        button.disabled = false;
        button.innerHTML = originalText;
    };
}

/**
 * Initialize a password toggle button
 * @param {HTMLElement} toggleButton - The toggle button element
 */
function initPasswordToggle(toggleButton) {
    if (!toggleButton) return;
    
    toggleButton.addEventListener('click', function() {
        const passwordInput = this.closest('.input-group').querySelector('input');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.className = 'far fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            icon.className = 'far fa-eye';
        }
    });
}
