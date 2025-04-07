/**
 * Program Form Functionality
 * Handles form validation and form field interactions
 */
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for description
    const descriptionField = document.getElementById('description');
    if (descriptionField) {
        const counter = document.querySelector('.character-counter');
        const maxLength = 500;
        
        descriptionField.addEventListener('input', function() {
            const currentLength = this.value.length;
            counter.textContent = `${currentLength}/${maxLength} characters`;
            
            if (currentLength > maxLength) {
                counter.classList.add('text-danger');
                descriptionField.classList.add('is-invalid');
            } else {
                counter.classList.remove('text-danger');
                descriptionField.classList.remove('is-invalid');
            }
        });
    }
    
    // Add form validation
    const form = document.querySelector('.program-form');
    if (form) {
        form.addEventListener('submit', validateProgramForm);
    }
    
    // Date validation for target and status dates
    const targetDateInput = document.getElementById('target_date');
    const statusDateInput = document.getElementById('status_date');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (targetDateInput && statusDateInput) {
        // Initially set status date to today if empty
        if (!statusDateInput.value) {
            statusDateInput.valueAsDate = new Date();
        }
        
        // Handle date validation
        [targetDateInput, statusDateInput, startDateInput, endDateInput].forEach(input => {
            if (input) {
                input.addEventListener('change', validateDates);
            }
        });
    }
});

/**
 * Validate the program form before submission
 */
function validateProgramForm(e) {
    // Get form fields
    const programName = document.getElementById('program_name');
    const target = document.getElementById('target');
    // Removed targetDate
    const status = document.getElementById('status');
    const statusDate = document.getElementById('status_date');
    const description = document.getElementById('description');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    let isValid = true;
    let errorMessage = '';
    
    // Validate program name
    if (!programName.value.trim()) {
        isValid = false;
        errorMessage += 'Program name is required.<br>';
        programName.classList.add('is-invalid');
    } else {
        programName.classList.remove('is-invalid');
    }
    
    // Validate target
    if (!target.value.trim()) {
        isValid = false;
        errorMessage += 'Target is required.<br>';
        target.classList.add('is-invalid');
    } else {
        target.classList.remove('is-invalid');
    }
    
    // Removed target date validation
    
    // Validate status selection
    if (!status.value) {
        isValid = false;
        errorMessage += 'Status is required.<br>';
        status.classList.add('is-invalid');
    } else {
        status.classList.remove('is-invalid');
    }
    
    // Validate status date
    if (!statusDate.value) {
        isValid = false;
        errorMessage += 'Status date is required.<br>';
        statusDate.classList.add('is-invalid');
    } else {
        statusDate.classList.remove('is-invalid');
    }
    
    // Validate description length
    if (description && description.value.length > 500) {
        isValid = false;
        errorMessage += 'Description exceeds maximum length of 500 characters.<br>';
        description.classList.add('is-invalid');
    }
    
    // Validate date ranges
    if (startDate && endDate && startDate.value && endDate.value) {
        if (new Date(startDate.value) > new Date(endDate.value)) {
            isValid = false;
            errorMessage += 'End date cannot be before start date.<br>';
            endDate.classList.add('is-invalid');
        } else {
            endDate.classList.remove('is-invalid');
        }
    }
    
    // If form is invalid, show error and prevent submission
    if (!isValid) {
        e.preventDefault();
        
        // Create or update error alert
        let errorAlert = document.querySelector('.form-error-alert');
        if (!errorAlert) {
            errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger form-error-alert mt-3';
            errorAlert.innerHTML = '<strong>Please fix the following errors:</strong><br>' + errorMessage;
            
            // Add error alert before the submit button
            const submitBtn = document.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.parentNode.insertBefore(errorAlert, submitBtn);
            } else {
                document.getElementById('createProgramForm').appendChild(errorAlert);
            }
        } else {
            errorAlert.innerHTML = '<strong>Please fix the following errors:</strong><br>' + errorMessage;
        }
        
        // Scroll to error
        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        return false;
    } else {
        // Remove any existing error alerts
        const errorAlert = document.querySelector('.form-error-alert');
        if (errorAlert) {
            errorAlert.remove();
        }
        
        return true;
    }
}

/**
 * Validate date ranges
 */
function validateDates() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    // Removed targetDate
    
    // Clear previous validation
    if (endDate) endDate.classList.remove('is-invalid');
    // Removed targetDate validation
    
    // Validate start/end date
    if (startDate && endDate && startDate.value && endDate.value) {
        if (new Date(startDate.value) > new Date(endDate.value)) {
            endDate.classList.add('is-invalid');
            
            // Show validation message
            let feedbackElement = endDate.nextElementSibling;
            if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                feedbackElement = document.createElement('div');
                feedbackElement.className = 'invalid-feedback';
                endDate.parentNode.appendChild(feedbackElement);
            }
            feedbackElement.textContent = 'End date cannot be before start date';
        }
    }
    
    // Removed target date against end date validation
}
