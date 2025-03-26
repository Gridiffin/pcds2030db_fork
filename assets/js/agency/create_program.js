/**
 * Create Program Form Functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createProgramForm');
    
    if (!form) return;
    
    // Handle date field validation
    const startDateField = document.getElementById('start_date');
    const endDateField = document.getElementById('end_date');
    
    if (startDateField && endDateField) {
        endDateField.addEventListener('change', function() {
            validateDates(startDateField, endDateField);
        });
        
        startDateField.addEventListener('change', function() {
            validateDates(startDateField, endDateField);
        });
    }
    
    // Set minimum date for start date to today
    if (startDateField) {
        const today = new Date().toISOString().split('T')[0];
        startDateField.setAttribute('min', today);
    }
    
    // Character counter for description
    const descriptionField = document.getElementById('description');
    if (descriptionField) {
        const maxLength = 500;
        
        // Create counter element
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end';
        counter.textContent = `0/${maxLength} characters`;
        descriptionField.parentNode.appendChild(counter);
        
        descriptionField.addEventListener('input', function() {
            const remaining = this.value.length;
            counter.textContent = `${remaining}/${maxLength} characters`;
            
            if (remaining > maxLength) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        });
    }
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        const programName = document.getElementById('program_name').value.trim();
        
        if (programName === '') {
            e.preventDefault();
            showValidationError('program_name', 'Program name is required');
            return;
        }
        
        if (startDateField && endDateField && startDateField.value && endDateField.value) {
            if (!validateDates(startDateField, endDateField)) {
                e.preventDefault();
                return;
            }
        }
    });
});

/**
 * Validate date ranges
 */
function validateDates(startField, endField) {
    if (startField.value && endField.value) {
        const startDate = new Date(startField.value);
        const endDate = new Date(endField.value);
        
        if (startDate > endDate) {
            showValidationError('end_date', 'End date cannot be before start date');
            return false;
        }
    }
    
    clearValidationError('end_date');
    return true;
}

/**
 * Show validation error
 */
function showValidationError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    // Add error class
    field.classList.add('is-invalid');
    
    // Create or update error message
    let errorElement = field.nextElementSibling;
    if (!errorElement || !errorElement.classList.contains('invalid-feedback')) {
        errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    }
    
    errorElement.textContent = message;
}

/**
 * Clear validation error
 */
function clearValidationError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.remove('is-invalid');
    
    const errorElement = field.nextElementSibling;
    if (errorElement && errorElement.classList.contains('invalid-feedback')) {
        errorElement.textContent = '';
    }
}
