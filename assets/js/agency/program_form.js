/**
 * Program Form Functionality
 * Handles enhancements and validation for program forms
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form enhancements
    initFormValidation();
    initCharacterCounter();
    initStatusPills();
});

/**
 * Initialize character counter for text areas
 */
function initCharacterCounter() {
    const textAreas = document.querySelectorAll('textarea[maxlength]');
    
    textAreas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength');
        
        // Create counter element
        const counterContainer = document.createElement('div');
        counterContainer.className = 'char-counter text-muted small mt-1';
        counterContainer.innerHTML = `<span class="current-count">${textarea.value.length}</span>/${maxLength} characters`;
        
        // Insert after textarea
        textarea.parentNode.insertBefore(counterContainer, textarea.nextSibling);
        
        // Update counter on input
        textarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            const counter = counterContainer.querySelector('.current-count');
            counter.textContent = currentLength;
            
            // Add warning class if approaching limit
            if (currentLength > maxLength * 0.9) {
                counterContainer.classList.add('text-warning');
            } else {
                counterContainer.classList.remove('text-warning');
            }
        });
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const form = document.querySelector('form.needs-validation');
    if (!form) return;
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
}

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
