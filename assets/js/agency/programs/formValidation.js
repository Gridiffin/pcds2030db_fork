/**
 * Create Program - Form Validation
 * Handles form validation and submission
 */

// Constants
const DATE_REGEX = /^\d{4}-\d{2}-\d{2}$/;

/**
 * Validates a date string format and actual date validity
 * @param {string} date - The date string to validate
 * @returns {boolean} True if valid, false otherwise
 */
export function validateDateFormat(date) {
    if (!date) return true; // Optional dates are valid
    
    // Check format first
    if (!DATE_REGEX.test(date)) return false;
    
    // Fix: Check if the date is actually valid (handles leap years, month boundaries)
    const parsedDate = new Date(date + 'T00:00:00'); // Add time to avoid timezone issues
    const [year, month, day] = date.split('-').map(Number);
    
    // Check if the parsed date matches the input (catches invalid dates like Feb 29 in non-leap years)
    return parsedDate.getFullYear() === year && 
           parsedDate.getMonth() === month - 1 && // getMonth() is 0-based
           parsedDate.getDate() === day;
}

/**
 * Validates date range (start date must be before or equal to end date)
 * @param {string} startDate - The start date string
 * @param {string} endDate - The end date string
 * @returns {boolean} True if valid, false otherwise
 */
export function validateDateRange(startDate, endDate) {
    if (!startDate || !endDate) return true; // If either date is empty, range is valid
    return new Date(startDate) <= new Date(endDate);
}

/**
 * Validates program name
 * @param {string} name - The program name to validate
 * @returns {Object} Validation result with isValid and message
 */
export function validateProgramName(name) {
    // Fix: Add null/undefined safety check
    if (!name || typeof name !== 'string' || !name.trim()) {
        return { isValid: false, message: 'Program name is required' };
    }
    if (name.length > 255) {
        return { isValid: false, message: 'Program name is too long (max 255 characters)' };
    }
    return { isValid: true, message: '' };
}

/**
 * Shows validation error message
 * @param {HTMLElement} input - The input element
 * @param {string} message - The error message
 */
function showError(input, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    
    let errorDiv = input.nextElementSibling;
    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        input.parentNode.insertBefore(errorDiv, input.nextSibling);
    }
    errorDiv.textContent = message;
}

/**
 * Clears validation state
 * @param {HTMLElement} input - The input element
 */
function clearValidation(input) {
    input.classList.remove('is-invalid', 'is-valid');
    const errorDiv = input.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
        errorDiv.textContent = '';
    }
}

/**
 * Validates the entire form
 * @param {HTMLFormElement} form - The form element
 * @returns {boolean} True if valid, false otherwise
 */
function validateForm(form) {
    let isValid = true;
    
    // Validate program name
    const nameInput = form.querySelector('#program_name');
    const nameValidation = validateProgramName(nameInput.value);
    if (!nameValidation.isValid) {
        showError(nameInput, nameValidation.message);
        isValid = false;
    } else {
        clearValidation(nameInput);
    }
    
    // Validate dates
    const startDateInput = form.querySelector('#start_date');
    const endDateInput = form.querySelector('#end_date');
    
    if (startDateInput.value && !validateDateFormat(startDateInput.value)) {
        showError(startDateInput, 'Invalid date format. Use YYYY-MM-DD');
        isValid = false;
    } else {
        clearValidation(startDateInput);
    }
    
    if (endDateInput.value && !validateDateFormat(endDateInput.value)) {
        showError(endDateInput, 'Invalid date format. Use YYYY-MM-DD');
        isValid = false;
    } else {
        clearValidation(endDateInput);
    }
    
    if (startDateInput.value && endDateInput.value && !validateDateRange(startDateInput.value, endDateInput.value)) {
        showError(endDateInput, 'End date must be after start date');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Initializes form validation
 */
export function initFormValidation() {
    const form = document.getElementById('createProgramForm');
    if (!form) {
        console.warn('Create program form not found');
        return;
    }
    
    // Handle form submission
    form.addEventListener('submit', (e) => {
        if (!validateForm(form)) {
            e.preventDefault();
            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    // Real-time validation for program name
    const nameInput = form.querySelector('#program_name');
    if (nameInput) {
        nameInput.addEventListener('input', () => {
            const validation = validateProgramName(nameInput.value);
            if (!validation.isValid) {
                showError(nameInput, validation.message);
            } else {
                clearValidation(nameInput);
            }
        });
    }
    
    // Date range validation
    const startDateInput = form.querySelector('#start_date');
    const endDateInput = form.querySelector('#end_date');
    if (startDateInput && endDateInput) {
        [startDateInput, endDateInput].forEach(input => {
            input.addEventListener('change', () => {
                if (startDateInput.value && endDateInput.value) {
                    if (!validateDateRange(startDateInput.value, endDateInput.value)) {
                        showError(endDateInput, 'End date must be after start date');
                    } else {
                        clearValidation(endDateInput);
                    }
                }
            });
        });
    }
} 