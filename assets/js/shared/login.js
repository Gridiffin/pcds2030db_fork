console.log('login.js loaded');
import { validateUsernameOrEmail, validatePassword } from './loginLogic.js';
import '../../css/shared/login.css';

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loginSpinner = document.getElementById('loginSpinner');
    const usernameField = document.getElementById('username');
    const passwordField = document.getElementById('password');
    const loginError = document.getElementById('loginError');

    // Focus username field on page load if empty
    if (usernameField && !usernameField.value) {
        usernameField.focus();
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

    // Handle form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form submit event triggered');
            e.preventDefault();
            const username = usernameField.value.trim();
            const password = passwordField.value;
            if (!validateUsernameOrEmail(username)) {
                showError('Username or email is required');
                return;
            }
            if (!validatePassword(password)) {
                showError('Password must be at least 8 characters');
                return;
            }
            loginBtn.disabled = true;
            loginSpinner.classList.remove('d-none');
            const basePath = window.location.pathname.split('/login.php')[0];
            fetch(`${window.location.origin}${basePath}/app/api/login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.role === 'admin') {
                        window.location.href = `${basePath}/app/views/admin/dashboard/dashboard.php`;
                    } else {
                        window.location.href = `${basePath}/app/views/agency/dashboard/dashboard.php`;
                    }
                } else {
                    showError(data.error || 'Login failed');
                    loginBtn.disabled = false;
                    loginSpinner.classList.add('d-none');
                }
            })
            .catch(() => {
                showError('Network error. Please try again.');
                loginBtn.disabled = false;
                loginSpinner.classList.add('d-none');
            });
        });
    }
    function showError(msg) {
        if (loginError) {
            loginError.textContent = msg;
            loginError.className = 'alert alert-danger mt-2';
        }
    }
}); 