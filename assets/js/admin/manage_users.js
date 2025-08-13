/**
 * Manage Users Page - Custom Implementation
 * Fixed password validation issue
 * Uses modular CSS import: users.css (~80kB vs 352kB main.css)
 */

// Import users-specific CSS bundle
import '../../css/admin/users/users.css';
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Modal management functions
    function showModal(templateId) {
        // Clone the template
        const template = document.getElementById(templateId);
        const modal = template.cloneNode(true);
        modal.removeAttribute('id');
        modal.classList.add('modal-active');
        
        // Add to the body
        document.body.appendChild(modal);
        
        // Prevent body scrolling
        document.body.style.overflow = 'hidden';
        
        // Setup event listeners
        setupModalEvents(modal);
        
        // Setup password validation
        setupPasswordValidation(modal);
        
        return modal;
    }
    
    function hideModal(modal) {
        // Remove from DOM
        if (modal && modal.parentNode) {
            modal.parentNode.removeChild(modal);
        }
        
        // Restore body scrolling
        document.body.style.overflow = '';
    }
    
    function setupModalEvents(modal) {
        // Close buttons
        const closeButtons = modal.querySelectorAll('.close-modal, .cancel-modal');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                hideModal(modal);
            });
        });
        
        // Close on overlay click
        const overlay = modal.querySelector('.user-modal-overlay');
        if (overlay) {
            overlay.addEventListener('click', function(event) {
                if (event.target === overlay) {
                    hideModal(modal);
                }
            });
        }
        
        // Toggle password visibility
        const toggleButtons = modal.querySelectorAll('.toggle-password');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const passwordInput = this.parentNode.querySelector('input');
                if (passwordInput) {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        this.innerHTML = '<i class="far fa-eye-slash"></i>';
                    } else {
                        passwordInput.type = 'password';
                        this.innerHTML = '<i class="far fa-eye"></i>';
                    }
                }
            });
        });
        
        // Handle form submission
        const form = modal.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Basic validation
                if (!validateForm(form)) {
                    e.preventDefault();
                    return false;
                }
                
                // Disable buttons during submission
                const buttons = form.querySelectorAll('button[type="submit"]');
                buttons.forEach(button => {
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                });
            });
        }
    }
    
    // Form validation - Fixed to allow interactive typing
    function validateForm(form) {
        let isValid = true;
        
        // Reset previous validation
        form.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(element => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
        });
        
        // Check required fields
        form.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
                
                // Add error message
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'This field is required';
                field.parentNode.appendChild(feedback);
            }
        });
        
        // Password validation for add user form - only validate on form submission
        const passwordField = form.querySelector('#password');
        if (passwordField && passwordField.required && passwordField.value.length < 8) {
            isValid = false;
            passwordField.classList.add('is-invalid');
            
            // Add error message
            let existingFeedback = passwordField.parentNode.querySelector('.invalid-feedback');
            if (!existingFeedback) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Password must be at least 8 characters';
                
                // Append to parent container instead of directly to input field
                // This prevents issues with the password toggle button
                if (passwordField.parentNode.classList.contains('password-container')) {
                    passwordField.parentNode.parentNode.appendChild(feedback);
                } else {
                    passwordField.parentNode.appendChild(feedback);
                }
            }
        }
        
        return isValid;
    }
    
    // Add real-time validation for password field
    function setupPasswordValidation(modal) {
        const passwordField = modal.querySelector('#password');
        if (passwordField && passwordField.required) {
            passwordField.addEventListener('input', function() {
                const minLength = 8;
                
                // Remove existing validation
                this.classList.remove('is-invalid');
                const container = this.parentNode.parentNode;
                const existingFeedback = container.querySelector('.invalid-feedback');
                if (existingFeedback) {
                    container.removeChild(existingFeedback);
                }
                
                // Add validation message below the field if too short
                if (this.value.length > 0 && this.value.length < minLength) {
                    const hint = document.createElement('div');
                    hint.className = 'text-danger small mt-1';
                    hint.textContent = `Password should be at least ${minLength} characters (${this.value.length}/${minLength})`;
                    container.appendChild(hint);
                }
            });
        }
    }
    
    // Toggle agency fields based on role selection
    function toggleAgencyFields(select, fieldsSelector) {
        const fields = document.querySelectorAll(fieldsSelector);
        const isAgency = select.value === 'agency';
        
        fields.forEach(field => {
            if (isAgency) {
                field.style.display = 'block';
                field.querySelectorAll('input, select').forEach(input => {
                    input.setAttribute('required', '');
                });
            } else {
                field.style.display = 'none';
                field.querySelectorAll('input, select').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        });
    }
    
    // Add User Modal
    var addUserBtn = document.querySelector('.add-user-btn');
    if (addUserBtn) {
        addUserBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = showModal('addUserTemplate');
            
            // Setup role toggle
            const roleSelect = modal.querySelector('#role');
            if (roleSelect) {
                roleSelect.addEventListener('change', function() {
                    toggleAgencyFields(this, '.agency-field');
                });
                
                // Initial toggle based on default value
                toggleAgencyFields(roleSelect, '.agency-field');
            }
        });
    }
    
    // Edit User Modal
    document.querySelectorAll('.edit-user-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get user data from data attributes
            const userId = this.getAttribute('data-user-id');
            const username = this.getAttribute('data-username');
            const role = this.getAttribute('data-role');
            const agency = this.getAttribute('data-agency');
            const sectorId = this.getAttribute('data-sector');
            
            const modal = showModal('editUserTemplate');
            
            // Fill form with user data
            modal.querySelector('#edit_user_id').value = userId;
            modal.querySelector('#edit_username').value = username;
            modal.querySelector('#edit_role').value = role;
            
            const agencyNameField = modal.querySelector('#edit_agency_name');
            if (agencyNameField) {
                agencyNameField.value = agency || '';
            }
            
            const sectorIdField = modal.querySelector('#edit_sector_id');
            if (sectorIdField) {
                sectorIdField.value = sectorId || '';
            }
            
            // Setup role toggle
            const roleSelect = modal.querySelector('#edit_role');
            if (roleSelect) {
                roleSelect.addEventListener('change', function() {
                    toggleAgencyFields(this, '.edit-agency-field');
                });
                
                // Initial toggle based on current role
                toggleAgencyFields(roleSelect, '.edit-agency-field');
            }
        });
    });
    
    // Deactivate User Modal
    document.querySelectorAll('.deactivate-user-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get user data from data attributes
            const userId = this.getAttribute('data-user-id');
            const username = this.getAttribute('data-username');
            
            const modal = showModal('deactivateUserTemplate');
            
            // Set form values
            modal.querySelector('#deactivate_user_id').value = userId;
            modal.querySelector('#deactivate_username').textContent = username;
        });
    });
    
    // Close active modal when Escape key is pressed
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-active');
            if (activeModal) {
                hideModal(activeModal);
            }
        }
    });

    // Copy email button functionality with inline feedback
    function initializeCopyEmailButtons() {
        const copyButtons = document.querySelectorAll('.btn-copy-email');
        console.log('Found copy email buttons:', copyButtons.length);
        
        copyButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Copy email button clicked');
                
                var emailSpan = btn.closest('.d-flex').querySelector('.user-email');
                var copiedFeedback = btn.parentNode.querySelector('.copied-feedback');
                var email = emailSpan ? emailSpan.getAttribute('data-email') : '';
                
                console.log('Email to copy:', email);
                
                if (email && email !== '' && email !== '-') {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(email).then(function() {
                            console.log('Email copied successfully');
                            if (copiedFeedback) {
                                copiedFeedback.style.display = 'inline-block';
                                setTimeout(function() {
                                    copiedFeedback.style.display = 'none';
                                }, 1200);
                            }
                            btn.querySelector('i').classList.add('text-success');
                            setTimeout(function() {
                                btn.querySelector('i').classList.remove('text-success');
                            }, 1200);
                        }).catch(function(err) {
                            console.error('Failed to copy email: ', err);
                            alert('Failed to copy email to clipboard');
                        });
                    } else {
                        // Fallback for older browsers
                        const textArea = document.createElement('textarea');
                        textArea.value = email;
                        document.body.appendChild(textArea);
                        textArea.select();
                        try {
                            document.execCommand('copy');
                            console.log('Email copied using fallback method');
                            if (copiedFeedback) {
                                copiedFeedback.style.display = 'inline-block';
                                setTimeout(function() {
                                    copiedFeedback.style.display = 'none';
                                }, 1200);
                            }
                        } catch (err) {
                            console.error('Fallback copy failed:', err);
                            alert('Failed to copy email to clipboard');
                        }
                        document.body.removeChild(textArea);
                    }
                } else {
                    console.log('No valid email found to copy');
                    alert('No email address found');
                }
            });
        });
    }
    
    // Initialize copy email buttons
    initializeCopyEmailButtons();

    // Toggle active status functionality
    function initializeToggleButtons() {
        const toggleButtons = document.querySelectorAll('.toggle-active-btn');
        console.log('Found toggle buttons:', toggleButtons.length);
        
        toggleButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Toggle button clicked');
                
                const userId = this.getAttribute('data-user-id');
                const username = this.getAttribute('data-username');
                const currentStatus = this.getAttribute('data-status') === '1';
                const newStatus = currentStatus ? 0 : 1;
                const actionText = currentStatus ? 'deactivate' : 'activate';
                
                console.log('Toggle user:', { userId, username, currentStatus, newStatus, actionText });
                
                if (confirm(`Are you sure you want to ${actionText} user "${username}"?`)) {
                    console.log('User confirmed action');
                    
                    // Check if window.APP_URL is available
                    const appUrl = window.APP_URL || '';
                    if (!appUrl) {
                        console.error('APP_URL not defined');
                        alert('Configuration error: APP_URL not defined');
                        return;
                    }
                    
                    // Send AJAX request to toggle status
                    fetch(appUrl + '/app/ajax/toggle_user_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `user_id=${encodeURIComponent(userId)}&status=${encodeURIComponent(newStatus)}`
                    })
                    .then(response => {
                        console.log('Response received:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            // Update the button appearance and data
                            this.setAttribute('data-status', newStatus);
                            const icon = this.querySelector('i');
                            const statusSpan = this.closest('tr').querySelector('.user-status, .badge');
                            
                            if (newStatus === 1) {
                                icon.className = 'fas fa-toggle-on text-success';
                                this.title = 'Deactivate User';
                                if (statusSpan) {
                                    if (statusSpan.classList.contains('badge')) {
                                        statusSpan.className = 'badge bg-success';
                                        statusSpan.textContent = 'Active';
                                    } else {
                                        statusSpan.className = 'user-status active';
                                        statusSpan.textContent = 'Active';
                                    }
                                }
                            } else {
                                icon.className = 'fas fa-toggle-off text-secondary';
                                this.title = 'Activate User';
                                if (statusSpan) {
                                    if (statusSpan.classList.contains('badge')) {
                                        statusSpan.className = 'badge bg-danger';
                                        statusSpan.textContent = 'Inactive';
                                    } else {
                                        statusSpan.className = 'user-status inactive';
                                        statusSpan.textContent = 'Inactive';
                                    }
                                }
                            }
                            
                            console.log(`User ${username} has been ${actionText}d successfully`);
                            alert(`User ${username} has been ${actionText}d successfully`);
                        } else {
                            console.error('Server error:', data.message);
                            alert('Error: ' + (data.message || 'Failed to update user status'));
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        alert('An error occurred while updating user status: ' + error.message);
                    });
                } else {
                    console.log('User cancelled action');
                }
            });
        });
    }
    
    // Initialize toggle buttons
    initializeToggleButtons();

    // Delete User functionality
    function initializeDeleteButtons() {
        const deleteButtons = document.querySelectorAll('.delete-user-btn');
        
        if (deleteButtons.length === 0) {
            // Retry once if buttons not found (timing issue)
            setTimeout(initializeDeleteButtons, 100);
            return;
        }
        
        deleteButtons.forEach(function(btn, index) {
            // Remove any existing listeners by cloning the button
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const userId = this.getAttribute('data-user-id');
                const username = this.getAttribute('data-username');
                
                // Show custom centered confirmation modal
                showDeleteConfirmationModal(username, userId, this);
            });
        });
    }
    
    // Function to show centered delete confirmation modal
    function showDeleteConfirmationModal(username, userId, deleteButton) {
        // Create modal HTML with proper centering
        const modalHTML = `
            <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true" style="display: flex !important; align-items: center !important; justify-content: center !important;">
                <div class="modal-dialog modal-dialog-centered" style="margin: 0 auto !important; max-width: 500px !important;">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="deleteUserModalLabel">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Delete User
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">
                                Are you sure you want to delete the user <strong>"${username}"</strong>?
                            </p>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> This action cannot be undone.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                                <i class="fas fa-trash me-2"></i>
                                Delete User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('deleteUserModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Get modal element
        const modal = document.getElementById('deleteUserModal');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        // Show modal with proper centering
        const bsModal = new bootstrap.Modal(modal, {
            backdrop: 'static',
            keyboard: false
        });
        
        // Ensure modal is centered
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.padding = '0';
        
        bsModal.show();
        
        // Force centering after modal is shown
        setTimeout(() => {
            const dialog = modal.querySelector('.modal-dialog');
            if (dialog) {
                dialog.style.margin = '0 auto';
                dialog.style.position = 'relative';
                dialog.style.top = '50%';
                dialog.style.transform = 'translateY(-50%)';
            }
        }, 10);
        
        // Handle confirm button click
        confirmBtn.addEventListener('click', function() {
            console.log('User confirmed deletion');
            
            // Hide modal
            bsModal.hide();
            
            // Check if window.APP_URL is available
            const appUrl = window.APP_URL || '';
            if (!appUrl) {
                console.error('APP_URL not defined');
                alert('Configuration error: APP_URL not defined');
                return;
            }
            
            // Create form data for deletion
            const formData = new FormData();
            formData.append('action', 'delete_user');
            formData.append('user_id', userId);
            formData.append('ajax_request', '1');
            
            // Send AJAX request to delete user
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    console.log(`User ${username} has been deleted successfully`);
                    
                    // Show success message
                    if (typeof window.showToast === 'function') {
                        window.showToast('Success', `User "${username}" has been deleted successfully.`, 'success');
                    } else {
                        alert(`User "${username}" has been deleted successfully.`);
                    }
                    
                    // Remove the row from the table with animation
                    const row = deleteButton.closest('tr');
                    if (row) {
                        row.style.backgroundColor = '#ffe6e6';
                        row.style.transition = 'all 0.5s';
                        
                        setTimeout(() => {
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(20px)';
                            
                            setTimeout(() => {
                                row.remove();
                            }, 500);
                        }, 300);
                    }
                } else {
                    console.error('Server error:', data.error);
                    if (typeof window.showToast === 'function') {
                        window.showToast('Error', data.error || 'Failed to delete user', 'danger');
                    } else {
                        alert('Error: ' + (data.error || 'Failed to delete user'));
                    }
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('Error', 'An error occurred while deleting user: ' + error.message, 'danger');
                } else {
                    alert('An error occurred while deleting user: ' + error.message);
                }
            });
        });
        
        // Handle modal hidden event to clean up
        modal.addEventListener('hidden.bs.modal', function() {
            // Clean up modal
            modal.remove();
            
            // Force cleanup of any remaining modal artifacts
            document.body.classList.remove('modal-open');
            
            // Remove any remaining backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            
            // Reset body styles
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
        
        // Add explicit cancel button handler for extra safety
        const cancelBtn = modal.querySelector('[data-bs-dismiss="modal"]');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                bsModal.hide();
            });
        }
    }
    
    // Initialize delete buttons
    initializeDeleteButtons();
    
    // Also initialize after a short delay to catch any dynamically loaded content
    setTimeout(() => {
        initializeDeleteButtons();
    }, 500);
});
