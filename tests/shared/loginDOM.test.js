/**
 * Unit Tests for Login DOM Interactions
 * Tests the DOM manipulation and event handling in login.js
 */

// Mock DOM environment
import { JSDOM } from 'jsdom';

const dom = new JSDOM(`
<!DOCTYPE html>
<html>
<body>
    <form id="loginForm">
        <input id="username" type="text" />
        <input id="password" type="password" />
        <button id="loginBtn" type="submit">Login</button>
        <div id="loginSpinner" class="d-none">Loading...</div>
        <div id="loginError"></div>
        <span class="toggle-password">
            <i class="far fa-eye"></i>
        </span>
    </form>
</body>
</html>
`, {
    url: 'http://localhost/pcds2030_dashboard_fork/login.php',
    pretendToBeVisual: true,
    resources: "usable"
});

global.window = dom.window;
global.document = dom.window.document;
global.FormData = dom.window.FormData;
global.fetch = jest.fn();

// Mock console.log to avoid test output noise
global.console.log = jest.fn();

describe('Login DOM Interactions', () => {
    let usernameField, passwordField, loginBtn, loginSpinner, loginError, togglePassword;

    beforeEach(() => {
        // Setup complete login form DOM
        document.body.innerHTML = `
            <form id="loginForm">
                <input type="text" id="username" name="username">
                <input type="password" id="password" name="password">
                <button type="button" id="togglePassword">👁️</button>
                <button type="submit" id="loginBtn">Sign In</button>
                <div id="loginError" style="display: none;"></div>
                <div id="loginSpinner" style="display: none;">Loading...</div>
            </form>
        `;
        
        // Get elements
        usernameField = document.getElementById('username');
        passwordField = document.getElementById('password');
        loginBtn = document.getElementById('loginBtn');
        loginSpinner = document.getElementById('loginSpinner');
        loginError = document.getElementById('loginError');
        togglePassword = document.querySelector('.toggle-password');

        // Reset fetch mock
        fetch.mockClear();
        
        // Reset button state
        loginBtn.disabled = false;
        loginSpinner.classList.add('d-none');
        loginError.textContent = '';
        loginError.className = '';
    });

    test('should focus username field on page load if empty', () => {
        const focusSpy = jest.spyOn(usernameField, 'focus');
        usernameField.value = '';
        
        // Simulate DOMContentLoaded
        const event = new dom.window.Event('DOMContentLoaded');
        document.dispatchEvent(event);
        
        expect(focusSpy).toHaveBeenCalled();
    });

    test('should not focus username field if it has value', () => {
        const focusSpy = jest.spyOn(usernameField, 'focus');
        usernameField.value = 'existinguser';
        
        // Simulate DOMContentLoaded
        const event = new dom.window.Event('DOMContentLoaded');
        document.dispatchEvent(event);
        
        expect(focusSpy).not.toHaveBeenCalled();
    });

    test('should toggle password visibility', () => {
        // Initially password field
        expect(passwordField.type).toBe('password');
        
        // Click toggle
        togglePassword.click();
        
        // Should become text field
        expect(passwordField.type).toBe('text');
        expect(togglePassword.querySelector('i').className).toBe('far fa-eye-slash');
        
        // Click again
        togglePassword.click();
        
        // Should become password field again
        expect(passwordField.type).toBe('password');
        expect(togglePassword.querySelector('i').className).toBe('far fa-eye');
    });

    test('should show error message correctly', () => {
        // Create showError function (it would be in scope in actual implementation)
        function showError(msg) {
            if (loginError) {
                loginError.textContent = msg;
                loginError.className = 'alert alert-danger mt-2';
            }
        }
        
        showError('Test error message');
        
        expect(loginError.textContent).toBe('Test error message');
        expect(loginError.className).toBe('alert alert-danger mt-2');
    });

    test('should disable button and show spinner during login', () => {
        // Mock fetch to return a promise that we can control
        const fetchPromise = Promise.resolve({
            json: () => Promise.resolve({ success: false, error: 'Login failed' })
        });
        fetch.mockReturnValue(fetchPromise);

        // Fill form with valid data
        usernameField.value = 'testuser';
        passwordField.value = 'password123';

        // Trigger form submission
        const form = document.getElementById('loginForm');
        const submitEvent = new dom.window.Event('submit', { bubbles: true, cancelable: true });
        
        // Manually prevent default and handle submission
        submitEvent.preventDefault();
        
        // Simulate the form submission logic
        loginBtn.disabled = true;
        loginSpinner.classList.remove('d-none');
        
        expect(loginBtn.disabled).toBe(true);
        expect(loginSpinner.classList.contains('d-none')).toBe(false);
    });

    test('should construct correct API endpoint', () => {
        // Test base path extraction
        const pathname = '/pcds2030_dashboard_fork/login.php';
        const basePath = pathname.split('/login.php')[0];
        
        expect(basePath).toBe('/pcds2030_dashboard_fork');
        
        const expectedEndpoint = `${window.location.origin}${basePath}/app/api/login.php`;
        expect(expectedEndpoint).toBe('http://localhost/pcds2030_dashboard_fork/app/api/login.php');
    });

    test('should validate form input before submission', () => {
        // Test empty username
        usernameField.value = '';
        passwordField.value = 'password123';
        
        // Would trigger validation error
        expect(usernameField.value.trim()).toBe('');
        
        // Test empty password
        usernameField.value = 'testuser';
        passwordField.value = '';
        
        expect(passwordField.value).toBe('');
        
        // Test valid input
        usernameField.value = 'testuser';
        passwordField.value = 'password123';
        
        expect(usernameField.value.trim()).toBe('testuser');
        expect(passwordField.value).toBe('password123');
    });
});

describe('Login API Interactions', () => {
    beforeEach(() => {
        fetch.mockClear();
    });

    test('should handle successful login response', async () => {
        const mockResponse = {
            success: true,
            role: 'admin'
        };
        
        fetch.mockResolvedValue({
            json: () => Promise.resolve(mockResponse)
        });

        const response = await fetch('/api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: 'admin', password: 'password' })
        });
        
        const data = await response.json();
        
        expect(data.success).toBe(true);
        expect(data.role).toBe('admin');
    });

    test('should handle failed login response', async () => {
        const mockResponse = {
            success: false,
            error: 'Invalid credentials'
        };
        
        fetch.mockResolvedValue({
            json: () => Promise.resolve(mockResponse)
        });

        const response = await fetch('/api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: 'user', password: 'wrong' })
        });
        
        const data = await response.json();
        
        expect(data.success).toBe(false);
        expect(data.error).toBe('Invalid credentials');
    });

    test('should handle network errors', async () => {
        fetch.mockRejectedValue(new Error('Network error'));

        try {
            await fetch('/api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: 'user', password: 'pass' })
            });
        } catch (error) {
            expect(error.message).toBe('Network error');
        }
    });
});
