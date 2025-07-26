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
    document.querySelectorAll('.btn-copy-email').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var emailSpan = btn.closest('.d-flex').querySelector('.user-email');
            var copiedFeedback = btn.parentNode.querySelector('.copied-feedback');
            var email = emailSpan ? emailSpan.getAttribute('data-email') : '';
            if (email) {
                navigator.clipboard.writeText(email).then(function() {
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
                });
            }
        });
    });
});
