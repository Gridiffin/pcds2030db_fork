/**
 * User management functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('User management JS loaded');
    
    // Initialize form submit events
    initializeFormEvents();
    
    // Check for page messages to show toast notifications
    if (window.pageMessages && window.pageMessages.message) {
        // Only show toast if useToast flag is true
        if (window.pageMessages.useToast) {
            showToast(
                window.pageMessages.type === 'success' ? 'Success' : 'Error',
                window.pageMessages.message,
                window.pageMessages.type
            );
        }
        
        // Clear the message after showing it to prevent repeat on refresh
        window.pageMessages.message = '';
        
        // Remove the message from URL if present (in case of page refresh)
        if (window.history && window.history.replaceState) {
            const url = new URL(window.location.href);
            if (url.searchParams.has('message')) {
                url.searchParams.delete('message');
                url.searchParams.delete('message_type');
                window.history.replaceState({}, document.title, url.toString());
            }
        }
    }
    
    // Setup role toggle on load
    setupRoleToggle();
    
    // NOTE: Delete buttons are now handled by UserTableManager
});

/**
 * Initialize form submit events
 */
function initializeFormEvents() {
    // Add User Form
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            const isValid = validateUserForm(this);
            if (!isValid) {
                e.preventDefault();
            } else {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
                }
            }
        });
    }
    
    // Edit User Form
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            const isValid = validateUserForm(this, true);
            if (!isValid) {
                e.preventDefault();
            } else {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
                }
            }
        });
    }
}

/**
 * Delete a user via AJAX
 */
function deleteUser(userId, username) {
    const formData = new FormData();
    formData.append('action', 'delete_user');
    formData.append('user_id', userId);
    
    fetch(`${window.APP_URL}/app/handlers/admin/process_user.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', `User "${username}" deleted successfully.`, 'success');
            // Refresh the table after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('Error', data.error || 'Failed to delete user', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'An unexpected error occurred', 'danger');
    });
}

/**
 * Setup role toggle functionality
 */
function setupRoleToggle() {
    const roleSelects = document.querySelectorAll('select[name="role"]');
    roleSelects.forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            if (!form) return;
            
            const agencyFields = form.querySelector('#agencyFields');
            if (!agencyFields) return;
            
            const agencyInputs = agencyFields.querySelectorAll('input, select');
            
            if (this.value === 'agency') {
                agencyFields.style.display = 'block';
                agencyInputs.forEach(input => {
                    input.setAttribute('required', '');
                });
            } else {
                agencyFields.style.display = 'none';
                agencyInputs.forEach(input => {
                    input.removeAttribute('required');
                });
            }
        });
        
        // Trigger change event to set initial state
        select.dispatchEvent(new Event('change'));
    });
}

/**
 * Validate user form
 * @param {HTMLFormElement} form - The form to validate
 * @param {boolean} isEdit - Whether this is an edit form (password not required)
 * @return {boolean} Whether the form is valid
 */
function validateUserForm(form, isEdit = false) {
    let isValid = true;
    
    // Reset previous validation messages
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    
    // Helper to add error message
    const addError = (element, message) => {
        element.classList.add('is-invalid');
        
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        
        const parent = element.parentNode;
        parent.appendChild(feedback);
    };
    
    // Check username
    const username = form.querySelector('[name="username"]');
    if (!username.value.trim()) {
        addError(username, 'Username is required');
        isValid = false;
    }
    
    // Check password
    const password = form.querySelector('[name="password"]');
    if (password && password.value) {
        if (password.value.length < 8) {
            addError(password, 'Password must be at least 8 characters');
            isValid = false;
        }
        
        // If there's a confirm password field, check it matches
        const confirmPassword = form.querySelector('[name="confirm_password"]');
        if (confirmPassword && confirmPassword.value !== password.value) {
            addError(confirmPassword, 'Passwords do not match');
            isValid = false;
        }
    } else if (!isEdit && password) {
        // Password is required for new users
        addError(password, 'Password is required');
        isValid = false;
    }
    
    // Check role
    const role = form.querySelector('[name="role"]');
    if (!role.value) {
        addError(role, 'Role is required');
        isValid = false;
    }
    
    // If role is agency, check agency fields
    if (role.value === 'agency') {
        const agencyName = form.querySelector('[name="agency_name"]');
        if (agencyName && !agencyName.value.trim()) {
            addError(agencyName, 'Agency name is required');
            isValid = false;
        }
        
        const sectorId = form.querySelector('[name="sector_id"]');
        if (sectorId && !sectorId.value) {
            addError(sectorId, 'Sector is required');
            isValid = false;
        }
    }
    
    return isValid;
}

/**
 * Show a toast notification
 * Uses the global showToast function for consistency
 */
function showToast(title, message, type = 'info') {
    if (typeof window.showToast === 'function') {
        window.showToast(title, message, type);
    } else {
        // Fallback if global showToast isn't loaded
        alert(`${title}: ${message}`);
    }
}
