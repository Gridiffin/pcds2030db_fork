/**
 * Login page functionality
 */

// Import CSS for bundle generation
import '../css/main.css';
import '../css/shared/login.css';

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loginSpinner = document.getElementById('loginSpinner');
    const usernameField = document.getElementById('username');
    const passwordField = document.getElementById('password');
    
    // Focus username field on page load if empty
    if (usernameField && !usernameField.value) {
        usernameField.focus();
    }
    
    // Show loading spinner when form is submitted
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            // Basic form validation
            if (!usernameField.value || !passwordField.value) {
                return false;
            }
            
            // Disable button and show spinner
            loginBtn.disabled = true;
            loginSpinner.classList.remove('d-none');
            return true;
        });
    }
    
    // Handle password visibility toggle
    const togglePassword = document.querySelector('.toggle-password');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                this.querySelector('i').className = 'far fa-eye-slash';
            } else {
                passwordField.type = 'password';
                this.querySelector('i').className = 'far fa-eye';
            }
        });
    }
    
    // Handle invalid session message
    if (new URLSearchParams(window.location.search).get('error') === 'invalid_session') {
        console.log('Session expired');
    }
});
