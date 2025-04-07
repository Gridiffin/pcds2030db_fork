/**
 * Program Form Functionality
 * Handles form validation and form field interactions
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize character counter for description
    initCharacterCounter('description', 500);
    
    // Add form validation
    const form = document.querySelector('.program-form');
    if (form) {
        form.addEventListener('submit', validateProgramForm);
    }
    
    // Set up date validation
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput && endDateInput) {
        // Handle date validation
        [startDateInput, endDateInput].forEach(input => {
            if (input) {
                input.addEventListener('change', function() {
                    validateDateRange(startDateInput, endDateInput);
                });
            }
        });
    }
    
    // Set today's date on status date if empty
    const statusDateInput = document.getElementById('status_date');
    if (statusDateInput && !statusDateInput.value) {
        statusDateInput.valueAsDate = new Date();
    }
});

/**
 * Validate the program form before submission
 */
function validateProgramForm(e) {
    // Get form fields
    const programName = document.getElementById('program_name');
    const target = document.getElementById('target');
    const status = document.getElementById('status');
    const statusDate = document.getElementById('status_date');
    const description = document.getElementById('description');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    let isValid = true;
    let errorMessage = '';
    
    // Clear previous errors
    [programName, target, status, statusDate].forEach(field => {
        if (field) clearValidationError(field.id);
    });
    
    // Validate program name
    if (!programName.value.trim()) {
        isValid = false;
        errorMessage += 'Program name is required.<br>';
        showValidationError('program_name', 'Program name is required');
    }
    
    // Validate target
    if (!target.value.trim()) {
        isValid = false;
        errorMessage += 'Target is required.<br>';
        showValidationError('target', 'Target is required');
    }
    
    // Validate status selection
    if (!status.value) {
        isValid = false;
        errorMessage += 'Status is required.<br>';
        showValidationError('status', 'Status is required');
    }
    
    // Validate status date
    if (!statusDate.value) {
        isValid = false;
        errorMessage += 'Status date is required.<br>';
        showValidationError('status_date', 'Status date is required');
    }
    
    // Validate description length
    if (description && description.value.length > 500) {
        isValid = false;
        errorMessage += 'Description exceeds maximum length of 500 characters.<br>';
        showValidationError('description', 'Description exceeds maximum length');
    }
    
    // Validate date ranges
    if (startDate && endDate && !validateDateRange(startDate, endDate)) {
        isValid = false;
        errorMessage += 'End date cannot be before start date.<br>';
    }
    
    // If form is invalid, show error and prevent submission
    if (!isValid) {
        e.preventDefault();
        createFormAlert('<strong>Please fix the following errors:</strong><br>' + errorMessage, form);
        return false;
    }
    
    return true;
}
