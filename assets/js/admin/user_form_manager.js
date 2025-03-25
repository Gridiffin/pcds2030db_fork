/**
 * User Form Manager
 * Handles form creation, validation and submission for user management
 */
function UserFormManager() {
    const formContainer = document.getElementById('formContainer');
    
    // Form display functions
    function showAddUserForm() {
        const sectors = getSectorsData();
        
        const formHtml = `
            <div class="form-overlay">
                <div class="form-wrapper">
                    <div class="form-header">
                        <h3>Add New User</h3>
                        <button type="button" class="close-form">&times;</button>
                    </div>
                    <form method="post" class="p-3" id="addUserForm">
                        <input type="hidden" name="action" value="add_user">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required autocomplete="off">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary password-toggle">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text password-hint">Password should be at least 8 characters</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="agency" selected>Agency</option>
                            </select>
                        </div>
                        
                        <div class="mb-3 agency-field">
                            <label for="agency_name" class="form-label">Agency Name</label>
                            <input type="text" class="form-control" id="agency_name" name="agency_name">
                        </div>
                        
                        <div class="mb-3 agency-field">
                            <label for="sector_id" class="form-label">Sector</label>
                            <select class="form-select" id="sector_id" name="sector_id">
                                <option value="">Select Sector</option>
                                ${sectors.map(sector => `<option value="${sector.id}">${sector.name}</option>`).join('')}
                            </select>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-secondary close-form">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i> Add User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        showForm(formHtml);
        
        // Setup role toggle
        const roleSelect = document.getElementById('role');
        roleSelect.addEventListener('change', () => toggleAgencyFields(roleSelect.value));
        
        // Initial toggle
        toggleAgencyFields(roleSelect.value);
        
        // Setup password validation
        setupPasswordValidation();
        
        // Add form submit handler
        const form = document.getElementById('addUserForm');
        const passwordInput = document.getElementById('password');
        const passwordHint = document.querySelector('.password-hint');
        
        handleFormSubmit(form, 'Adding User...', 'User added successfully!', passwordInput, passwordHint);
    }
    
    function showEditUserForm(userData) {
        const sectors = getSectorsData();
        
        const formHtml = `
            <div class="form-overlay">
                <div class="form-wrapper">
                    <div class="form-header">
                        <h3>Edit User</h3>
                        <button type="button" class="close-form">&times;</button>
                    </div>
                    <form method="post" class="p-3" id="editUserForm">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="user_id" value="${userData.userId}">
                        
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" value="${userData.username}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="edit_password" name="password" placeholder="Leave blank to keep current">
                                <button type="button" class="btn btn-outline-secondary password-toggle">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Leave blank to keep current password</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="admin" ${userData.role === 'admin' ? 'selected' : ''}>Admin</option>
                                <option value="agency" ${userData.role === 'agency' ? 'selected' : ''}>Agency</option>
                            </select>
                        </div>
                        
                        <div class="mb-3 edit-agency-field">
                            <label for="edit_agency_name" class="form-label">Agency Name</label>
                            <input type="text" class="form-control" id="edit_agency_name" name="agency_name" value="${userData.agency || ''}">
                        </div>
                        
                        <div class="mb-3 edit-agency-field">
                            <label for="edit_sector_id" class="form-label">Sector</label>
                            <select class="form-select" id="edit_sector_id" name="sector_id">
                                <option value="">Select Sector</option>
                                ${sectors.map(sector => `<option value="${sector.id}" ${sector.id == userData.sectorId ? 'selected' : ''}>${sector.name}</option>`).join('')}
                            </select>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-secondary close-form">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        showForm(formHtml);
        
        // Setup role toggle
        const roleSelect = document.getElementById('edit_role');
        roleSelect.addEventListener('change', () => toggleAgencyFields(roleSelect.value, '.edit-agency-field'));
        
        // Initial toggle
        toggleAgencyFields(roleSelect.value, '.edit-agency-field');
        
        // Add form submit handler
        const form = document.getElementById('editUserForm');
        handleFormSubmit(form, 'Updating User...', 'User updated successfully!');
    }
    
    function showDeleteForm(userId, username) {
        const formHtml = `
            <div class="form-overlay">
                <div class="form-wrapper form-wrapper-sm">
                    <div class="form-header form-header-danger">
                        <h3>Confirm Deletion</h3>
                        <button type="button" class="close-form">&times;</button>
                    </div>
                    <div class="text-center p-4">
                        <i class="fas fa-trash fa-3x text-danger mb-3"></i>
                        <p>Are you sure you want to delete user <strong>${username}</strong>?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                        
                        <form method="post" id="deleteUserForm">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="${userId}">
                            
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <button type="button" class="btn btn-secondary close-form">Cancel</button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i> Delete User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        showForm(formHtml);
        
        // Handle form submission with animated row deletion
        const form = document.getElementById('deleteUserForm');
        handleDeleteFormSubmit(form, userId, username);
    }
    
    // Form utility functions
    function showForm(html) {
        formContainer.innerHTML = html;
        
        // Setup event listeners for closing
        formContainer.querySelectorAll('.close-form').forEach(button => {
            button.addEventListener('click', hideForm);
        });
        
        // Close on overlay click
        const overlay = formContainer.querySelector('.form-overlay');
        overlay.addEventListener('click', function(e) {
            if (e.target === this) hideForm();
        });
        
        // Setup password toggle functionality
        formContainer.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', togglePasswordVisibility);
        });
        
        // Prevent scrolling on the body
        document.body.style.overflow = 'hidden';
        
        // Focus on first input field
        setTimeout(() => {
            const firstInput = formContainer.querySelector('input:not([type="hidden"]), select');
            if (firstInput) firstInput.focus();
        }, 100);
    }
    
    function hideForm() {
        formContainer.innerHTML = '';
        document.body.style.overflow = '';
    }
    
    function togglePasswordVisibility() {
        const input = this.closest('.input-group').querySelector('input');
        const icon = this.querySelector('i');
        
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'far fa-eye' : 'far fa-eye-slash';
    }
    
    function toggleAgencyFields(role, selector = '.agency-field') {
        document.querySelectorAll(selector).forEach(field => {
            const inputs = field.querySelectorAll('input, select');
            const isAdmin = role === 'admin';
            
            field.style.display = isAdmin ? 'none' : 'block';
            inputs.forEach(input => isAdmin 
                ? input.removeAttribute('required') 
                : input.setAttribute('required', '')
            );
        });
    }
    
    function setupPasswordValidation() {
        const passwordInput = document.getElementById('password');
        const passwordHint = document.querySelector('.password-hint');
        
        if (passwordInput && passwordHint) {
            passwordInput.addEventListener('input', function() {
                const value = this.value;
                
                if (value.length > 0 && value.length < 8) {
                    passwordHint.textContent = `Password must be at least 8 characters (${value.length}/8)`;
                    passwordHint.className = 'form-text text-danger';
                } else if (value.length >= 8) {
                    passwordHint.textContent = 'Password meets minimum length requirement';
                    passwordHint.className = 'form-text text-success';
                } else {
                    passwordHint.textContent = 'Password should be at least 8 characters';
                    passwordHint.className = 'form-text';
                }
            });
        }
    }
    
    // Form submission handlers
    function handleFormSubmit(form, loadingText, successMessage, passwordInput = null, passwordHint = null) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Password validation if needed
            if (passwordInput && passwordHint && passwordInput.required && passwordInput.value.length < 8) {
                passwordHint.textContent = 'Password must be at least 8 characters';
                passwordHint.className = 'form-text text-danger';
                passwordInput.focus();
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> ${loadingText}`;
            
            // Submit form via AJAX
            const formData = new FormData(this);
            
            // Add a flag to indicate this is an AJAX request
            formData.append('ajax_request', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())  // Expect JSON response instead of HTML
            .then(data => {
                if (data.error) {
                    // Show error message
                    window.ToastManager().show('Error', data.error, 'danger');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                } else if (data.success) {
                    // Success case
                    hideForm();
                    window.ToastManager().show('Success', successMessage, 'success');
                    window.UserTableManager().refreshTable();
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                window.ToastManager().show('Error', 'An error occurred. Please try again.', 'danger');
                console.error('Form submission error:', error);
            });
        });
    }
    
    function handleDeleteFormSubmit(form, userId, username) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';
            
            const formData = new FormData(this);
            
            // Add a flag to indicate this is an AJAX request
            formData.append('ajax_request', '1');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())  // Expect JSON response instead of HTML
            .then(data => {
                if (data.error) {
                    // Show error message
                    window.ToastManager().show('Error', data.error, 'danger');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-trash me-1"></i> Delete User';
                } else if (data.success) {
                    // Success case
                    hideForm();
                    window.ToastManager().show('Success', `User "${username}" deleted successfully.`, 'success');
                    
                    // Animate deletion effect and refresh table
                    window.UserTableManager().animateRowDeletion(userId);
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-trash me-1"></i> Delete User';
                window.ToastManager().show('Error', 'Failed to delete user. Please try again.', 'danger');
                console.error('Delete form submission error:', error);
            });
        });
    }
    
    // Data utility functions
    function getSectorsData() {
        // Try to get sectors from global variable first
        if (window.sectorsData && Array.isArray(window.sectorsData) && window.sectorsData.length > 0) {
            return window.sectorsData.map(sector => ({
                id: sector.sector_id,
                name: sector.sector_name
            }));
        }
        
        // Otherwise use the fallback sectors
        return getFallbackSectors();
    }
    
    function getFallbackSectors() {
        return [
            { id: '1', name: 'Forestry' },
            { id: '2', name: 'Land' },
            { id: '3', name: 'Environment' },
            { id: '4', name: 'Natural Resources' },
            { id: '5', name: 'Urban Development' }
        ];
    }
    
    // Return public API
    return {
        showAddUserForm,
        showEditUserForm,
        showDeleteForm,
        hideForm
    };
}

// Make function globally available (with safety check for multiple loads)
if (typeof window.UserFormManager === 'undefined') {
    window.UserFormManager = UserFormManager;
}
