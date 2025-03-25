/**
 * Login page functionality
 */
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
    
    // Toggle password visibility
    document.addEventListener('click', function(e) {
        // Find the clicked element or its parent with toggle-password class
        let toggleElement = null;
        if (e.target.classList.contains('toggle-password')) {
            toggleElement = e.target;
        } else if (e.target.parentElement && e.target.parentElement.classList.contains('toggle-password')) {
            toggleElement = e.target.parentElement;
        }
        
        if (toggleElement) {
            const icon = toggleElement.querySelector('i') || toggleElement;
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.className = 'far fa-eye-slash';
            } else {
                passwordField.type = 'password';
                icon.className = 'far fa-eye';
            }
        }
    });
    
    // Handle invalid session message
    if (new URLSearchParams(window.location.search).get('error') === 'invalid_session') {
        console.log('Session expired');
    }
});
