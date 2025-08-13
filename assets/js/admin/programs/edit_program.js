/**
 * Admin Programs - Edit Program JavaScript
 * Handles functionality for the admin edit program page
 */

// Import CSS for admin edit program
import '../../../css/admin/programs/edit_program.css';

// Import essential utilities
import '../../utilities/initialization.js';
import '../../utilities/dropdown_init.js';

// Import main utilities including showToast
import '../../main.js';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize form components
    initializeFormValidation();
    initializeProgramNumbering();
    initializeUserPermissions();
    initializeDateValidation();
    initializeAutoSave();
    
    console.log('Admin edit program page initialized');
});

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const form = document.getElementById('editProgramForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                showToast('Validation Error', 'Please correct the errors below', 'error');
            } else {
                showSubmitLoadingState();
            }
        });
        
        // Real-time validation
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('input', () => clearFieldError(field));
        });
    }
}

/**
 * Validate entire form
 */
function validateForm() {
    const form = document.getElementById('editProgramForm');
    let isValid = true;
    
    // Validate required fields
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    // Validate date range
    if (!validateDateRange()) {
        isValid = false;
    }
    
    // Validate user permissions
    if (!validateUserPermissions()) {
        isValid = false;
    }
    
    return isValid;
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Check if required field is empty
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Specific field validations
    switch (field.name) {
        case 'program_name':
            if (value && value.length < 3) {
                isValid = false;
                errorMessage = 'Program name must be at least 3 characters long';
            }
            break;
        case 'program_number':
            if (value && !/^[a-zA-Z0-9.]+$/.test(value)) {
                isValid = false;
                errorMessage = 'Program number can only contain letters, numbers, and dots';
            }
            break;
        case 'brief_description':
            if (value && value.length > 500) {
                isValid = false;
                errorMessage = 'Description cannot exceed 500 characters';
            }
            break;
    }
    
    // Show/hide error message
    if (isValid) {
        clearFieldError(field);
    } else {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Initialize program numbering functionality
 */
function initializeProgramNumbering() {
    const initiativeSelect = document.getElementById('initiative_id');
    const programNumberInput = document.getElementById('program_number');
    
    if (initiativeSelect && programNumberInput) {
        // Handle initiative selection
        initiativeSelect.addEventListener('change', function() {
            const selectedInitiative = this.value;
            updateProgramNumberField(selectedInitiative);
        });
        
        // Handle program number input
        programNumberInput.addEventListener('input', function() {
            validateProgramNumber(this.value);
            updateFinalNumberPreview();
        });
        
        // Initialize on page load
        const currentInitiative = initiativeSelect.value;
        if (currentInitiative) {
            updateProgramNumberField(currentInitiative);
        }
    }
}

/**
 * Update program number field based on initiative selection
 */
function updateProgramNumberField(initiativeId) {
    const programNumberInput = document.getElementById('program_number');
    const helpText = document.getElementById('number-help-text');
    const finalNumberDisplay = document.getElementById('final-number-display');
    
    if (initiativeId) {
        programNumberInput.disabled = false;
        programNumberInput.placeholder = 'Enter program number';
        helpText.textContent = 'Enter a program number or leave blank for auto-generation';
        finalNumberDisplay.style.display = 'block';
        updateFinalNumberPreview();
    } else {
        programNumberInput.disabled = true;
        programNumberInput.placeholder = 'Select initiative first';
        helpText.textContent = 'Select an initiative to enable program numbering';
        finalNumberDisplay.style.display = 'none';
    }
}

/**
 * Validate program number format
 */
function validateProgramNumber(number) {
    const validationDiv = document.getElementById('number-validation');
    const validationMessage = document.getElementById('validation-message');
    
    if (!validationDiv || !validationMessage) return;
    
    if (number.trim()) {
        if (/^[a-zA-Z0-9.]+$/.test(number)) {
            validationDiv.style.display = 'block';
            validationMessage.className = 'text-success';
            validationMessage.textContent = 'Valid program number format';
        } else {
            validationDiv.style.display = 'block';
            validationMessage.className = 'text-danger';
            validationMessage.textContent = 'Invalid format. Use only letters, numbers, and dots.';
        }
    } else {
        validationDiv.style.display = 'none';
    }
}

/**
 * Update final number preview
 */
function updateFinalNumberPreview() {
    const programNumberInput = document.getElementById('program_number');
    const finalNumberPreview = document.getElementById('final-number-preview');
    
    if (finalNumberPreview) {
        const number = programNumberInput.value.trim();
        finalNumberPreview.textContent = number || 'Will be generated automatically';
    }
}

/**
 * Initialize user permissions functionality
 */
function initializeUserPermissions() {
    const restrictCheckbox = document.getElementById('restrict_editors');
    const userSection = document.getElementById('userSelectionSection');
    
    if (restrictCheckbox && userSection) {
        // Handle restriction toggle
        restrictCheckbox.addEventListener('change', function() {
            toggleUserSelection(this.checked);
        });
        
        // Initialize select all/none buttons
        const selectAllBtn = document.querySelector('[onclick="selectAllUsers()"]');
        const selectNoneBtn = document.querySelector('[onclick="selectNoUsers()"]');
        
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', selectAllUsers);
        }
        if (selectNoneBtn) {
            selectNoneBtn.addEventListener('click', selectNoUsers);
        }
        
        // Initialize state
        toggleUserSelection(restrictCheckbox.checked);
    }
}

/**
 * Toggle user selection section
 */
function toggleUserSelection(show) {
    const userSection = document.getElementById('userSelectionSection');
    
    if (userSection) {
        userSection.style.display = show ? 'block' : 'none';
        
        if (!show) {
            // Uncheck all user checkboxes when disabling restrictions
            const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
            userCheckboxes.forEach(checkbox => checkbox.checked = false);
        }
    }
}

/**
 * Select all users
 */
function selectAllUsers() {
    const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
    userCheckboxes.forEach(checkbox => checkbox.checked = true);
}

/**
 * Select no users
 */
function selectNoUsers() {
    const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
    userCheckboxes.forEach(checkbox => checkbox.checked = false);
}

/**
 * Validate user permissions
 */
function validateUserPermissions() {
    const restrictCheckbox = document.getElementById('restrict_editors');
    
    if (restrictCheckbox && restrictCheckbox.checked) {
        const selectedUsers = document.querySelectorAll('input[name="assigned_editors[]"]:checked');
        
        if (selectedUsers.length === 0) {
            showToast('Validation Error', 'Please select at least one user when restricting editors', 'error');
            return false;
        }
    }
    
    return true;
}

/**
 * Initialize date validation
 */
function initializeDateValidation() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', validateDateRange);
        endDateInput.addEventListener('change', validateDateRange);
    }
}

/**
 * Validate date range
 */
function validateDateRange() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (!startDateInput || !endDateInput) return true;
    
    const startDate = startDateInput.value;
    const endDate = endDateInput.value;
    
    // Clear previous errors
    clearFieldError(startDateInput);
    clearFieldError(endDateInput);
    
    if (startDate && endDate) {
        if (new Date(startDate) >= new Date(endDate)) {
            showFieldError(endDateInput, 'End date must be after start date');
            return false;
        }
    }
    
    return true;
}

/**
 * Initialize auto-save functionality
 */
function initializeAutoSave() {
    const form = document.getElementById('editProgramForm');
    let autoSaveTimer;
    
    if (form) {
        const formInputs = form.querySelectorAll('input, textarea, select');
        
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(autoSaveForm, 2000); // Auto-save after 2 seconds of inactivity
            });
        });
    }
}

/**
 * Auto-save form data
 */
function autoSaveForm() {
    const form = document.getElementById('editProgramForm');
    
    if (!form || !validateForm()) return;
    
    const formData = new FormData(form);
    formData.append('ajax', '1');
    formData.append('auto_save', '1');
    
    fetch(form.action || window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAutoSaveIndicator();
        }
    })
    .catch(error => {
        console.log('Auto-save failed:', error);
    });
}

/**
 * Show auto-save indicator
 */
function showAutoSaveIndicator() {
    // Create or update auto-save indicator
    let indicator = document.getElementById('autoSaveIndicator');
    
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'autoSaveIndicator';
        indicator.className = 'auto-save-indicator';
        indicator.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i>Changes saved automatically';
        document.body.appendChild(indicator);
    }
    
    indicator.style.display = 'block';
    indicator.style.opacity = '1';
    
    // Fade out after 2 seconds
    setTimeout(() => {
        indicator.style.opacity = '0';
        setTimeout(() => {
            indicator.style.display = 'none';
        }, 300);
    }, 2000);
}

/**
 * Show submit loading state
 */
function showSubmitLoadingState() {
    const submitBtn = document.querySelector('button[type="submit"]');
    
    if (submitBtn) {
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
        submitBtn.disabled = true;
        
        // Store original text for potential restoration
        submitBtn.dataset.originalText = originalText;
    }
}

/**
 * Show toast notification
 */
function showToast(title, message, type = 'info') {
    // Use existing toast function if available, otherwise create simple alert
    if (typeof window.showToast === 'function') {
        window.showToast(title, message, type);
    } else {
        alert(`${title}: ${message}`);
    }
}

// Global functions for inline usage
window.selectAllUsers = selectAllUsers;
window.selectNoUsers = selectNoUsers;

// Export functions for global access
window.AdminEditProgram = {
    validateForm,
    validateField,
    updateProgramNumberField,
    toggleUserSelection,
    autoSaveForm,
    showSubmitLoadingState
};