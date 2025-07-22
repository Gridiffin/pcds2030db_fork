/**
 * Program Form Functionality
 * Handles program creation and editing forms
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize character counter
    initCharacterCounter();
    
    // Initialize rating pills (updated from status to rating terminology)
    initRatingPills();
});

/**
 * Initialize character counter for description field
 */
function initCharacterCounter() {
    const descriptionField = document.getElementById('description');
    if (!descriptionField) return;
    
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

/**
 * Validate program form before submission
 */
function validateProgramForm() {
    const programName = document.getElementById('program_name');
    const target = document.getElementById('target');
    const rating = document.getElementById('rating') || document.getElementById('status'); // Support both field names
    const ratingDate = document.getElementById('rating_date') || document.getElementById('status_date'); // Support both field names
    const description = document.getElementById('description');
    
    let isValid = true;
    let errorMessage = '';
    
    // Clear previous errors
    [programName, target, rating, ratingDate].forEach(field => {
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
    
    // Validate rating selection
    if (!rating.value) {
        isValid = false;
        errorMessage += 'Rating is required.<br>';
        showValidationError('rating', 'Rating is required');
        showValidationError('status', 'Rating is required'); // Support both field names
    }
    
    // Validate rating date
    if (!ratingDate.value) {
        isValid = false;
        errorMessage += 'Rating date is required.<br>';
        showValidationError('rating_date', 'Rating date is required');
        showValidationError('status_date', 'Rating date is required'); // Support both field names
    }
    
    // Validate description length
    if (description && description.value.length > 500) {
        isValid = false;
        errorMessage += 'Description cannot exceed 500 characters.<br>';
        showValidationError('description', 'Description cannot exceed 500 characters');
    }
    
    if (!isValid) {
        showToast('Validation Error', errorMessage, 'danger');
    }
    
    return isValid;
}

/**
 * Show validation error
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
 * Clear validation error
 */
function clearValidationError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.remove('is-invalid');
    
    // Remove error message if it exists
    const errorDiv = document.getElementById(`${fieldId}-error`);
    if (errorDiv) errorDiv.remove();
}

// Helper for toast notification (uses global showToast for consistency)
function showToast(title, message, type = 'info', duration = 5000) {
    if (typeof window.showToast === 'function') {
        window.showToast(title, message, type, duration);
    } else {
        // Fallback if global showToast isn't loaded
        
    }
}