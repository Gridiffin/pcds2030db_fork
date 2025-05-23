/**
 * Form Utilities
 * 
 * Reusable functions for form handling across the application
 */

/**
 * Validate a form with custom rules
 * @param {HTMLFormElement} form - The form to validate
 * @param {Object} rules - Validation rules for fields
 * @returns {boolean} Whether the form is valid
 */
function validateForm(form, rules = {}) {
    if (!form) return false;
    
    let isValid = true;
    
    // Reset previous validation
    form.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });
    
    form.querySelectorAll('.invalid-feedback').forEach(element => {
        if (element.parentNode) {
            element.parentNode.removeChild(element);
        }
    });
    
    // Check required fields
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            showValidationError(field.id || field.name, 'This field is required');
            field.classList.add('is-invalid');
        }
    });
    
    // Apply custom rules
    for (const [fieldId, fieldRules] of Object.entries(rules)) {
        const field = document.getElementById(fieldId);
        if (!field) continue;
        
        for (const [rule, params] of Object.entries(fieldRules)) {
            if (rule === 'minLength' && field.value.length < params.value) {
                isValid = false;
                showValidationError(fieldId, params.message || `Minimum length is ${params.value} characters`);
            }
            else if (rule === 'maxLength' && field.value.length > params.value) {
                isValid = false;
                showValidationError(fieldId, params.message || `Maximum length is ${params.value} characters`);
            }
            else if (rule === 'pattern' && !new RegExp(params.value).test(field.value)) {
                isValid = false;
                showValidationError(fieldId, params.message || 'Invalid format');
            }
        }
    }
    
    return isValid;
}

/**
 * Show validation error message for a field
 * @param {string} fieldId - The ID of the field with error
 * @param {string} message - Error message to display
 */
function showValidationError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.add('is-invalid');
    
    // Check if error message already exists
    let errorDiv = field.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
        errorDiv.textContent = message;
    } else {
        // Create new error message
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
}

/**
 * Clear validation error for a field
 * @param {string} fieldId - The ID of the field to clear error
 */
function clearValidationError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.remove('is-invalid');
    
    const errorDiv = field.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
        errorDiv.remove();
    }
}

/**
 * Create an alert in a form
 * @param {string} message - Alert message (HTML supported)
 * @param {HTMLElement} container - Container to add alert to 
 * @param {string} type - Alert type (success, danger, warning, info)
 */
function createFormAlert(message, container, type = 'danger') {
    if (!container) return;
    
    // Remove existing alerts
    container.querySelectorAll('.alert').forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to form
    container.prepend(alertDiv);
}

/**
 * Validate date range between two date fields
 * @param {HTMLInputElement} startDateField - Start date field
 * @param {HTMLInputElement} endDateField - End date field
 * @returns {boolean} Whether the date range is valid
 */
function validateDateRange(startDateField, endDateField) {
    if (!startDateField || !endDateField) return true;
    
    const startDate = new Date(startDateField.value);
    const endDate = new Date(endDateField.value);
    
    return !startDateField.value || !endDateField.value || startDate <= endDate;
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
 * Add loading state to a submit button
 * @param {HTMLElement} button - The submit button
 * @param {string} loadingText - Text to display during loading (optional)
 * @returns {Function} Function to reset the button
 */
function setButtonLoading(button, loadingText) {
    if (!button) return () => {};
    
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
