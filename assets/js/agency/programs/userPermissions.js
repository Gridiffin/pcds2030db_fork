/**
 * Create Program - User Permissions
 * Handles user permission selection and management
 */

/**
 * Selects all user checkboxes
 */
function selectAllUsers() {
    const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
    userCheckboxes.forEach(checkbox => checkbox.checked = true);
}

/**
 * Deselects all user checkboxes
 */
function selectNoUsers() {
    const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]');
    userCheckboxes.forEach(checkbox => checkbox.checked = false);
}

/**
 * Toggles visibility of user selection section
 * @param {boolean} show - Whether to show or hide the section
 */
function toggleUserSelection(show) {
    const userSection = document.getElementById('userSelectionSection');
    if (userSection) {
        userSection.style.display = show ? 'block' : 'none';
        
        // If hiding, uncheck all users
        if (!show) {
            selectNoUsers();
        }
    }
}

/**
 * Validates user selection when restrictions are enabled
 * @returns {boolean} True if valid, false otherwise
 */
function validateUserSelection() {
    const restrictCheckbox = document.getElementById('restrict_editors');
    if (!restrictCheckbox || !restrictCheckbox.checked) {
        return true;
    }

    const userCheckboxes = document.querySelectorAll('input[name="assigned_editors[]"]:checked');
    if (userCheckboxes.length === 0) {
        // Show error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger mt-3';
        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Please select at least one user when restricting editors.';
        
        const userSection = document.getElementById('userSelectionSection');
        const existingError = userSection.querySelector('.alert-danger');
        if (existingError) {
            existingError.remove();
        }
        userSection.appendChild(errorDiv);
        
        return false;
    }
    
    return true;
}

/**
 * Initializes user permissions functionality
 */
export function initUserPermissions() {
    const restrictCheckbox = document.getElementById('restrict_editors');
    if (!restrictCheckbox) {
        console.warn('User permissions elements not found');
        return;
    }

    // Handle restriction toggle
    restrictCheckbox.addEventListener('change', () => {
        toggleUserSelection(restrictCheckbox.checked);
        
        // Remove any existing error messages when toggling
        const userSection = document.getElementById('userSelectionSection');
        const existingError = userSection?.querySelector('.alert-danger');
        if (existingError) {
            existingError.remove();
        }
    });

    // Initialize select all/none buttons
    document.addEventListener('click', (e) => {
        if (e.target.matches('[data-action="select-all-users"]')) {
            selectAllUsers();
        } else if (e.target.matches('[data-action="select-no-users"]')) {
            selectNoUsers();
        }
    });

    // Add form validation
    const form = document.getElementById('createProgramForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            if (!validateUserSelection()) {
                e.preventDefault();
                // Scroll to user section
                const userSection = document.getElementById('userSelectionSection');
                if (userSection) {
                    userSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }

    // Initialize visibility based on initial state
    toggleUserSelection(restrictCheckbox.checked);
} 