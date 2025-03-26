/**
 * Program Form Functionality
 * Handles form validation and status selection for program creation/update
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize status pill selection
    initStatusPills();
    
    // Add form validation
    const form = document.querySelector('.program-form');
    if (form) {
        form.addEventListener('submit', validateProgramForm);
    }
    
    // Date validation for target and status dates
    const targetDateInput = document.getElementById('target_date');
    const statusDateInput = document.getElementById('status_date');
    
    if (targetDateInput && statusDateInput) {
        // Initially set status date to today if empty
        if (!statusDateInput.value) {
            statusDateInput.valueAsDate = new Date();
        }
        
        // Handle date validation
        [targetDateInput, statusDateInput].forEach(input => {
            input.addEventListener('change', validateDates);
        });
    }
});

/**
 * Initialize status pill selection behavior
 */
function initStatusPills() {
    const statusPills = document.querySelectorAll('.status-pill');
    const statusInput = document.getElementById('status');
    
    if (!statusPills.length || !statusInput) return;
    
    statusPills.forEach(pill => {
        pill.addEventListener('click', function() {
            // Remove active class from all pills
            statusPills.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked pill
            this.classList.add('active');
            
            // Update hidden input value
            statusInput.value = this.getAttribute('data-status');
            
            // Validate dates when status changes
            validateDates();
        });
    });
}

/**
 * Validate the program form before submission
 */
function validateProgramForm(e) {
    let isValid = true;
    
    // Check required fields
    const programName = document.getElementById('program_name');
    const target = document.getElementById('target');
    const targetDate = document.getElementById('target_date');
    const statusDate = document.getElementById('status_date');
    
    // Basic validation for required fields
    [programName, target, targetDate, statusDate].forEach(field => {
        if (field && !field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else if (field) {
            field.classList.remove('is-invalid');
        }
    });
    
    // Validate start/end dates if both are provided
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    if (startDate && endDate && startDate.value && endDate.value) {
        if (new Date(startDate.value) > new Date(endDate.value)) {
            startDate.classList.add('is-invalid');
            endDate.classList.add('is-invalid');
            
            // Show error message
            if (!document.getElementById('date-error')) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'date-error';
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i> End date cannot be before start date.';
                endDate.parentNode.appendChild(errorDiv);
            }
            
            isValid = false;
        } else {
            startDate.classList.remove('is-invalid');
            endDate.classList.remove('is-invalid');
            
            // Remove error message if exists
            const errorDiv = document.getElementById('date-error');
            if (errorDiv) errorDiv.remove();
        }
    }
    
    // Validate target and status dates
    if (!validateDates()) {
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
        
        // Scroll to first invalid field
        const firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.focus();
        }
    } else {
        // Disable submit button to prevent double submission
        const submitButton = document.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
        }
    }
}

/**
 * Validate dates for logical consistency
 * @returns {boolean} True if valid, false otherwise
 */
function validateDates() {
    const targetDate = document.getElementById('target_date');
    const statusDate = document.getElementById('status_date');
    
    if (!targetDate || !statusDate || !targetDate.value || !statusDate.value) return true;
    
    // Clear any existing error
    const errorDiv = document.getElementById('status-date-error');
    if (errorDiv) errorDiv.remove();
    
    // Get date values
    const targetDateVal = new Date(targetDate.value);
    const statusDateVal = new Date(statusDate.value);
    
    // Get status
    const status = document.getElementById('status').value;
    
    // If status is "completed", target date should be on or before status date
    if (status === 'completed' && targetDateVal > statusDateVal) {
        statusDate.classList.add('is-invalid');
        
        // Show error message
        const newErrorDiv = document.createElement('div');
        newErrorDiv.id = 'status-date-error';
        newErrorDiv.className = 'invalid-feedback';
        newErrorDiv.textContent = 'For completed status, target date should be on or before status date.';
        statusDate.parentNode.appendChild(newErrorDiv);
        
        return false;
    }
    
    // Reset validation state
    statusDate.classList.remove('is-invalid');
    return true;
}
