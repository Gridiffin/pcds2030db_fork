/**
 * User Form Manager
 * Handles user form interactions for admin user management
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the user form manager
    initUserFormManager();
});

/**
 * Initialize user form functionality
 */
function initUserFormManager() {
    // Get elements
    const addUserBtn = document.getElementById('addUserBtn');
    const userForms = document.querySelectorAll('.user-form');
    const editUserBtns = document.querySelectorAll('.edit-user-btn');
    const deleteUserBtns = document.querySelectorAll('.delete-user-btn');
    
    // Setup event listeners
    if (addUserBtn) {
        addUserBtn.addEventListener('click', showAddUserForm);
    }
    
    if (editUserBtns.length) {
        editUserBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                showEditUserForm(userId);
            });
        });
    }
    
    if (deleteUserBtns.length) {
        deleteUserBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                const userName = this.getAttribute('data-user-name');
                showDeleteConfirmation(userId, userName);
            });
        });
    }
    
    // Initialize form validations
    userForms.forEach(form => {
        form.addEventListener('submit', validateUserForm);
    });
    
    // Initialize password toggles
    const passwordToggles = document.querySelectorAll('.toggle-password');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', togglePasswordVisibility);
    });
    
    // Initialize role change handlers
    const roleSelects = document.querySelectorAll('select[name="role"]');
    roleSelects.forEach(select => {
        select.addEventListener('change', handleRoleChange);
    });
}

/**
 * Show the add user form
 */
function showAddUserForm() {
    const addUserModal = document.getElementById('addUserModal');
    if (!addUserModal) return;
    
    // Reset form
    const form = addUserModal.querySelector('form');
    if (form) form.reset();
    
    // Show the modal
    addUserModal.classList.add('modal-active');
    
    // Focus on first input field
    setTimeout(() => {
        const firstInput = addUserModal.querySelector('input:not([type="hidden"])');
        if (firstInput) firstInput.focus();
    }, 300);
}

/**
 * Show the edit user form
 * @param {string} userId - The ID of the user to edit
 */
function showEditUserForm(userId) {
    // Load user data via AJAX
    fetch(`${APP_URL}/admin/get_user.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditForm(data.user);
                
                // Show the modal
                const editUserModal = document.getElementById('editUserModal');
                if (editUserModal) editUserModal.classList.add('modal-active');
            } else {
                showToast('Error', data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Failed to load user data', 'danger');
        });
}

/**
 * Populate the edit user form with user data
 * @param {object} user - The user data to populate the form with
 */
function populateEditForm(user) {
    const form = document.getElementById('editUserForm');
    if (!form) return;
    
    // Set user ID
    const userIdInput = form.querySelector('input[name="user_id"]');
    if (userIdInput) userIdInput.value = user.user_id;
    
    // Set user data
    const inputs = {
        'username': user.username,
        'email': user.email,
        'first_name': user.first_name,
        'last_name': user.last_name,
        'agency_name': user.agency_name || '',
        'phone': user.phone || '',
        'address': user.address || '',
        'active': user.active
    };
    
    for (const field in inputs) {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
            if (input.type === 'checkbox') {
                input.checked = inputs[field] === '1' || inputs[field] === true;
            } else {
                input.value = inputs[field];
            }
        }
    }
    
    // Set role
    const roleSelect = form.querySelector('select[name="role"]');
    if (roleSelect) {
        roleSelect.value = user.role;
        handleRoleChange.call(roleSelect); // Update form based on role
    }
    
    // Set sector
    const sectorSelect = form.querySelector('select[name="sector_id"]');
    if (sectorSelect && user.sector_id) {
        sectorSelect.value = user.sector_id;
    }
}

/**
 * Show delete confirmation dialog
 * @param {string} userId - The ID of the user to delete
 * @param {string} userName - The name of the user to delete
 */
function showDeleteConfirmation(userId, userName) {
    const modal = createModal({
        title: 'Confirm Deletion',
        content: `<p>Are you sure you want to delete the user "${userName}"?</p>
                  <p>This action cannot be undone.</p>`,
        isDanger: true,
        buttons: [
            {
                text: 'Cancel',
                type: 'secondary'
            },
            {
                text: 'Delete User',
                type: 'danger',
                handler: () => {
                    deleteUser(userId);
                    modal.hide();
                }
            }
        ]
    });
    
    modal.show();
}

/**
 * Delete a user
 * @param {string} userId - The ID of the user to delete
 */
function deleteUser(userId) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('action', 'delete');
    
    fetch(`${APP_URL}/admin/process_user.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            
            // Remove the user row from the table
            const userRow = document.getElementById(`user-row-${userId}`);
            if (userRow) {
                userRow.classList.add('fade-out');
                setTimeout(() => {
                    userRow.remove();
                    
                    // Update user count
                    const userCountElement = document.getElementById('userCount');
                    if (userCountElement) {
                        const currentCount = parseInt(userCountElement.textContent, 10);
                        userCountElement.textContent = currentCount - 1;
                    }
                }, 500);
            }
        } else {
            showToast('Error', data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to delete user', 'danger');
    });
}

/**
 * Close a modal
 * @param {Event} event - The click event
 */
function closeModal(event) {
    const modal = event.target.closest('.user-modal');
    if (modal) {
        modal.classList.remove('modal-active');
    }
}

/**
 * Toggle password visibility
 */
function togglePasswordVisibility() {
    const passwordField = this.previousElementSibling;
    if (passwordField) {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        // Change icon
        this.innerHTML = type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
    }
}

/**
 * Handle role change to show/hide sector selection
 */
function handleRoleChange() {
    const sectorField = this.closest('form').querySelector('.sector-field');
    const agencyField = this.closest('form').querySelector('.agency-field');
    
    if (sectorField) {
        sectorField.style.display = this.value === 'agency' ? 'block' : 'none';
    }
    
    if (agencyField) {
        agencyField.style.display = this.value === 'agency' ? 'block' : 'none';
    }
}

/**
 * Validate the user form before submission
 * @param {Event} e - The form submit event
 */
function validateUserForm(e) {
    let isValid = true;
    const form = e.target;
    
    // Required fields
    const requiredFields = ['username', 'email'];
    
    // Role-specific required fields
    const role = form.querySelector('[name="role"]').value;
    if (role === 'agency') {
        requiredFields.push('agency_name', 'sector_id');
    }
    
    // Check if password is required for new users
    const userIdField = form.querySelector('[name="user_id"]');
    if (!userIdField || !userIdField.value) {
        requiredFields.push('password');
    }
    
    // Validate each required field
    requiredFields.forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (field && !field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
            
            // Add error message if it doesn't exist
            if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'This field is required';
                field.insertAdjacentElement('afterend', errorDiv);
            }
        } else if (field) {
            field.classList.remove('is-invalid');
        }
    });
    
    // Validate email format
    const emailField = form.querySelector('[name="email"]');
    if (emailField && emailField.value.trim()) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailField.value.trim())) {
            isValid = false;
            emailField.classList.add('is-invalid');
            
            // Update or add error message
            let errorDiv = emailField.nextElementSibling;
            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                emailField.insertAdjacentElement('afterend', errorDiv);
            }
            errorDiv.textContent = 'Please enter a valid email address';
        }
    }
    
    // Password validation for new users or password changes
    const passwordField = form.querySelector('[name="password"]');
    if (passwordField && passwordField.value.trim()) {
        const confirmField = form.querySelector('[name="confirm_password"]');
        
        // Check password length
        if (passwordField.value.length < 8) {
            isValid = false;
            passwordField.classList.add('is-invalid');
            
            // Add error message
            let errorDiv = passwordField.nextElementSibling;
            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                passwordField.insertAdjacentElement('afterend', errorDiv);
            }
            errorDiv.textContent = 'Password must be at least 8 characters';
        }
        
        // Check if passwords match
        if (confirmField && passwordField.value !== confirmField.value) {
            isValid = false;
            confirmField.classList.add('is-invalid');
            
            // Add error message
            let errorDiv = confirmField.nextElementSibling;
            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                confirmField.insertAdjacentElement('afterend', errorDiv);
            }
            errorDiv.textContent = 'Passwords do not match';
        }
    }
    
    if (!isValid) {
        e.preventDefault();
    } else {
        // Disable submit button to prevent double submission
        const submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
        }
    }
    
    return isValid;
}

// Set up close buttons
document.addEventListener('DOMContentLoaded', function() {
    const closeButtons = document.querySelectorAll('.close-modal, .cancel-btn');
    closeButtons.forEach(button => {
        button.addEventListener('click', closeModal);
    });
});
