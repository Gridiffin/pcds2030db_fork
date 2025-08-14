/**
 * User Profile JavaScript
 * Handles client-side validation, form submission, and interactive features
 */

document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    
    // Form validation state
    let validationState = {
        username: true,
        email: true,
        password: true,
        confirm_password: true
    };
    
    // Initialize form
    initializeForm();
    
    function initializeForm() {
        // Add event listeners
        if (usernameInput) {
            usernameInput.addEventListener('input', debounce(validateUsername, 300));
            usernameInput.addEventListener('blur', validateUsername);
        }
        
        if (emailInput) {
            emailInput.addEventListener('input', debounce(validateEmail, 300));
            emailInput.addEventListener('blur', validateEmail);
        }
        
        if (passwordInput) {
            passwordInput.addEventListener('input', debounce(validatePassword, 300));
            passwordInput.addEventListener('blur', validatePassword);
        }
        
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', debounce(validateConfirmPassword, 300));
            confirmPasswordInput.addEventListener('blur', validateConfirmPassword);
        }
        
        if (profileForm) {
            profileForm.addEventListener('submit', handleFormSubmit);
        }
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', resetForm);
        }
        
        // Initialize password strength indicator
        initializePasswordStrength();
    }
    
    function validateUsername() {
        const value = usernameInput.value.trim();
        const feedback = getFeedbackElement(usernameInput.parentNode);
        
        // Clear previous validation state
        clearValidationState(usernameInput, feedback);
        
        if (!value) {
            // Empty username is allowed (no change)
            validationState.username = true;
            return true;
        }
        
        // Length validation
        if (value.length < 3 || value.length > 50) {
            setInvalidState(usernameInput, feedback, 'Username must be between 3 and 50 characters');
            validationState.username = false;
            return false;
        }
        
        // Format validation
        if (!/^[a-zA-Z0-9_]+$/.test(value)) {
            setInvalidState(usernameInput, feedback, 'Username can only contain letters, numbers, and underscores');
            validationState.username = false;
            return false;
        }
        
        // Set valid state
        setValidState(usernameInput, feedback, 'Username format is valid');
        validationState.username = true;
        return true;
    }
    
    function validateEmail() {
        const value = emailInput.value.trim();
        const feedback = getFeedbackElement(emailInput.parentNode);
        
        // Clear previous validation state
        clearValidationState(emailInput, feedback);
        
        if (!value) {
            // Empty email is allowed (no change)
            validationState.email = true;
            return true;
        }
        
        // Email format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            setInvalidState(emailInput, feedback, 'Please enter a valid email address');
            validationState.email = false;
            return false;
        }
        
        // Set valid state
        setValidState(emailInput, feedback, 'Email format is valid');
        validationState.email = true;
        return true;
    }
    
    function validatePassword() {
        const value = passwordInput.value;
        const feedback = getFeedbackElement(passwordInput.parentNode);
        
        // Clear previous validation state
        clearValidationState(passwordInput, feedback);
        
        if (!value) {
            // Empty password is allowed (no change)
            validationState.password = true;
            updatePasswordStrength(0, 'No change');
            return true;
        }
        
        // Password strength validation
        const strength = calculatePasswordStrength(value);
        updatePasswordStrength(strength.score, strength.message);
        
        if (strength.score < 3) {
            setInvalidState(passwordInput, feedback, strength.message);
            validationState.password = false;
            return false;
        }
        
        // Set valid state
        setValidState(passwordInput, feedback, strength.message);
        validationState.password = true;
        
        // Also validate confirm password if it has a value
        if (confirmPasswordInput && confirmPasswordInput.value) {
            validateConfirmPassword();
        }
        
        return true;
    }
    
    function validateConfirmPassword() {
        const value = confirmPasswordInput.value;
        const passwordValue = passwordInput.value;
        const feedback = getFeedbackElement(confirmPasswordInput.parentNode);
        
        // Clear previous validation state
        clearValidationState(confirmPasswordInput, feedback);
        
        if (!passwordValue) {
            // If no password entered, confirm password should be empty
            if (value) {
                setInvalidState(confirmPasswordInput, feedback, 'Please enter a password first');
                validationState.confirm_password = false;
                return false;
            }
            validationState.confirm_password = true;
            return true;
        }
        
        if (!value) {
            setInvalidState(confirmPasswordInput, feedback, 'Please confirm your password');
            validationState.confirm_password = false;
            return false;
        }
        
        if (value !== passwordValue) {
            setInvalidState(confirmPasswordInput, feedback, 'Passwords do not match');
            validationState.confirm_password = false;
            return false;
        }
        
        // Set valid state
        setValidState(confirmPasswordInput, feedback, 'Passwords match');
        validationState.confirm_password = true;
        return true;
    }
    
    function calculatePasswordStrength(password) {
        let score = 0;
        let feedback = [];
        
        // Length check
        if (password.length < 8) {
            return { score: 0, level: 'weak', message: 'Password must be at least 8 characters long' };
        } else if (password.length >= 12) {
            score += 2;
        } else {
            score += 1;
        }
        
        // Character variety checks
        if (/[a-z]/.test(password)) {
            score += 1;
        } else {
            feedback.push('lowercase letter');
        }
        
        if (/[A-Z]/.test(password)) {
            score += 1;
        } else {
            feedback.push('uppercase letter');
        }
        
        if (/[0-9]/.test(password)) {
            score += 1;
        } else {
            feedback.push('number');
        }
        
        if (/[^a-zA-Z0-9]/.test(password)) {
            score += 1;
        } else {
            feedback.push('special character');
        }
        
        // Determine strength level and message
        let level = 'weak';
        let message = '';
        
        if (score >= 5) {
            level = 'strong';
            message = 'Password is strong';
        } else if (score >= 3) {
            level = 'medium';
            message = feedback.length > 0 ? 
                     `Password is good. Consider adding: ${feedback.join(', ')}` : 
                     'Password is good';
        } else {
            level = 'weak';
            message = `Password is weak. Please include: ${feedback.join(', ')}`;
        }
        
        return { score, level, message };
    }
    
    function initializePasswordStrength() {
        const passwordStrengthContainer = document.getElementById('passwordStrength');
        if (!passwordStrengthContainer) return;
        
        passwordStrengthContainer.innerHTML = `
            <div class="password-strength-bar">
                <div class="password-strength-fill" id="passwordStrengthFill"></div>
            </div>
            <div class="password-strength-text" id="passwordStrengthText">Enter a password to see strength</div>
        `;
    }
    
    function updatePasswordStrength(score, message) {
        const fill = document.getElementById('passwordStrengthFill');
        const text = document.getElementById('passwordStrengthText');
        
        if (!fill || !text) return;
        
        // Remove previous classes
        fill.className = 'password-strength-fill';
        text.className = 'password-strength-text';
        
        if (score === 0) {
            text.textContent = message;
            return;
        }
        
        // Determine level based on score
        let level = 'weak';
        if (score >= 5) level = 'strong';
        else if (score >= 3) level = 'medium';
        
        // Add level class
        fill.classList.add(level);
        text.classList.add(level);
        text.textContent = message;
    }
    
    function setValidState(input, feedback, message) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        feedback.textContent = message;
        feedback.className = 'valid-feedback';
    }
    
    function setInvalidState(input, feedback, message) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        feedback.textContent = message;
        feedback.className = 'invalid-feedback';
    }
    
    function clearValidationState(input, feedback) {
        input.classList.remove('is-valid', 'is-invalid');
        feedback.textContent = '';
        feedback.className = 'invalid-feedback';
    }
    
    function createFeedbackElement(parent) {
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        parent.appendChild(feedback);
        return feedback;
    }
    
    function getFeedbackElement(parent) {
        // Re-use existing feedback element whether it's currently valid or invalid
        return parent.querySelector('.invalid-feedback, .valid-feedback') || createFeedbackElement(parent);
    }
    
    function isFormValid() {
        return Object.values(validationState).every(valid => valid);
    }
    
    function hasFormChanges() {
        const fields = ['username', 'email', 'fullname', 'password'];
        return fields.some(field => {
            const input = document.getElementById(field);
            return input && input.value.trim() !== '';
        });
    }
    
    function handleFormSubmit(e) {
        e.preventDefault();
        
        // Validate all fields
        let isValid = true;
        if (usernameInput) isValid &= validateUsername();
        if (emailInput) isValid &= validateEmail();
        if (passwordInput) isValid &= validatePassword();
        if (confirmPasswordInput) isValid &= validateConfirmPassword();
        
        if (!isValid) {
            showAlert('Please correct the errors before submitting', 'danger');
            return;
        }
        
        if (!hasFormChanges()) {
            showAlert('No changes detected', 'info');
            return;
        }
        
        // Show loading state
        setLoadingState(true);
        
        // Prepare form data
        const formData = new FormData(profileForm);
        
        // Submit form
        fetch('/pcds2030_dashboard_fork/app/handlers/profile_handler.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            setLoadingState(false);
            
            if (data.success) {
                showAlert(data.message, 'success');
                
                // Clear password fields on success
                if (passwordInput) passwordInput.value = '';
                if (confirmPasswordInput) confirmPasswordInput.value = '';
                
                // Reset validation states
                clearAllValidation();
                
                // Update password strength indicator
                updatePasswordStrength(0, 'Enter a password to see strength');
                
                // Optionally reload page to update navbar with new username
                if (data.updated_fields && data.updated_fields.includes('username')) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                showAlert(data.message, 'danger');
                
                // Show field-specific errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const input = document.getElementById(field);
                        if (input) {
                            const feedback = getFeedbackElement(input.parentNode);
                            setInvalidState(input, feedback, data.errors[field]);
                            validationState[field] = false;
                        }
                    });
                }
            }
        })
        .catch(error => {
            setLoadingState(false);
            console.error('Profile update error:', error);
            showAlert('An unexpected error occurred. Please try again.', 'danger');
        });
    }
    
    function resetForm() {
        profileForm.reset();
        clearAllValidation();
        updatePasswordStrength(0, 'Enter a password to see strength');
        hideAlert();
    }
    
    function clearAllValidation() {
        const inputs = profileForm.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
        
        const feedbacks = profileForm.querySelectorAll('.valid-feedback, .invalid-feedback');
        feedbacks.forEach(feedback => {
            feedback.textContent = '';
        });
        
        // Reset validation state
        Object.keys(validationState).forEach(key => {
            validationState[key] = true;
        });
    }
    
    function setLoadingState(loading) {
        if (loading) {
            profileForm.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';
        } else {
            profileForm.classList.remove('loading');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Profile';
        }
    }
    
    function showAlert(message, type) {
        hideAlert();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'}"></i>
            ${message}
        `;
        
        const container = document.querySelector('.profile-card-body');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(hideAlert, 5000);
        }
    }
    
    function hideAlert() {
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
    }
    
    // Utility function for debouncing
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + S to save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            if (isFormValid() && hasFormChanges()) {
                profileForm.dispatchEvent(new Event('submit'));
            }
        }
        
        // Escape to reset
        if (e.key === 'Escape') {
            resetForm();
        }
    });
});
