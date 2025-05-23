/**
 * Initialization Utilities
 * 
 * Provides functions to initialize various components of the application
 * across different pages. This helps maintain consistency and reduce
 * code duplication.
 */

/**
 * Initialize all basic components on page load
 */
function initializeBasicComponents() {
    // Preloader handling
    handlePreloader();
    
    // Initialize tooltips
    initTooltips();
    
    // Initialize interactive form elements
    initFormElements();
    
    // Handle alerts
    initAlerts();
    
    // Initialize dropdown toggles
    initDropdowns();
}

/**
 * Handle the preloader and ensure it disappears
 */
function handlePreloader() {
    const preloader = document.getElementById('preloader');
    if (!preloader) return;
    
    // Hide preloader when page is loaded
    window.addEventListener('load', function() {
        hidePreloader(preloader);
    });
    
    // Fallback: Hide preloader after a timeout even if load event doesn't fire
    setTimeout(() => {
        hidePreloader(preloader);
    }, 2000);
}

/**
 * Hide the preloader with animation
 * @param {HTMLElement} preloader - The preloader element
 */
function hidePreloader(preloader) {
    preloader.classList.add('preloader-hide');
    setTimeout(() => {
        preloader.style.display = 'none';
        document.body.classList.add('page-loaded');
    }, 300);
}

/**
 * Initialize Bootstrap tooltips
 */
function initTooltips() {
    if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip !== 'undefined') {
        const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipElements.forEach(el => {
            new bootstrap.Tooltip(el);
        });
    }
}

/**
 * Initialize interactive form elements
 */
function initFormElements() {
    // Password toggles
    initPasswordToggles();
    
    // Character counters
    initCharacterCounters();
    
    // Date range validators
    initDateRangeValidators();
}

/**
 * Initialize password toggle functionality
 */
function initPasswordToggles() {
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const passwordField = this.closest('.input-group').querySelector('input');
            if (!passwordField) return;
            
            // Toggle password visibility
            const newType = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', newType);
            
            // Update icon
            const icon = this.querySelector('i');
            if (icon) {
                icon.className = newType === 'password' ? 'far fa-eye' : 'far fa-eye-slash';
            }
        });
    });
}

/**
 * Initialize character counters for textareas and inputs
 */
function initCharacterCounters() {
    document.querySelectorAll('[data-max-length]').forEach(field => {
        const maxLength = parseInt(field.getAttribute('data-max-length'), 10);
        if (isNaN(maxLength)) return;
        
        // Create or find counter element
        let counter = field.parentNode.querySelector('.character-counter');
        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'text-muted float-end character-counter';
            field.parentNode.appendChild(counter);
        }
        
        // Initial count
        updateCharacterCount(field, counter, maxLength);
        
        // Update counter on input
        field.addEventListener('input', function() {
            updateCharacterCount(this, counter, maxLength);
        });
    });
}

/**
 * Update character count display
 * @param {HTMLElement} field - The input or textarea
 * @param {HTMLElement} counter - The counter element
 * @param {number} maxLength - Maximum allowed characters
 */
function updateCharacterCount(field, counter, maxLength) {
    const currentLength = field.value.length;
    counter.textContent = `${currentLength}/${maxLength} characters`;
    
    if (currentLength > maxLength) {
        counter.classList.add('text-danger');
        field.classList.add('is-invalid');
    } else {
        counter.classList.remove('text-danger');
        field.classList.remove('is-invalid');
    }
}

/**
 * Initialize date range validators
 */
function initDateRangeValidators() {
    document.querySelectorAll('[data-range-start]').forEach(startField => {
        const endFieldId = startField.getAttribute('data-range-start');
        const endField = document.getElementById(endFieldId);
        if (!endField) return;
        
        const validateRange = () => {
            if (!startField.value || !endField.value) return;
            
            const startDate = new Date(startField.value);
            const endDate = new Date(endField.value);
            
            if (startDate > endDate) {
                // Show error
                endField.classList.add('is-invalid');
                
                // Add error message if it doesn't exist
                let errorMsg = endField.nextElementSibling;
                if (!errorMsg || !errorMsg.classList.contains('invalid-feedback')) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'invalid-feedback';
                    errorMsg.textContent = 'End date cannot be before start date';
                    endField.parentNode.insertBefore(errorMsg, endField.nextSibling);
                }
            } else {
                // Clear error
                endField.classList.remove('is-invalid');
                const errorMsg = endField.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('invalid-feedback')) {
                    errorMsg.remove();
                }
            }
        };
        
        // Validate on change for both fields
        startField.addEventListener('change', validateRange);
        endField.addEventListener('change', validateRange);
    });
}

/**
 * Initialize alert dismissal
 */
function initAlerts() {
    // Auto-hide alerts after timeout
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                fade(alert, 500, () => {
                    alert.parentNode.removeChild(alert);
                });
            }
        }, 5000);
    });
    
    // Alert close buttons
    document.querySelectorAll('.alert .btn-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const alert = this.closest('.alert');
            if (alert) {
                fade(alert, 300, () => {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                });
            }
        });
    });
}

/**
 * Fade out an element
 * @param {HTMLElement} element - Element to fade
 * @param {number} duration - Fade duration in ms
 * @param {Function} callback - Callback after fade completion
 */
function fade(element, duration, callback) {
    element.style.transition = `opacity ${duration}ms ease`;
    element.style.opacity = '0';
    setTimeout(() => {
        if (callback) callback();
    }, duration);
}

/**
 * Initialize dropdown functionality
 */
function initDropdowns() {
    // Initialize Bootstrap dropdowns
    if (typeof bootstrap !== 'undefined' && typeof bootstrap.Dropdown !== 'undefined') {
        document.querySelectorAll('.dropdown-toggle').forEach(function(element) {
            new bootstrap.Dropdown(element);
        });
    }
    
    // Custom dropdown toggle
    document.querySelectorAll('.custom-dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const dropdown = this.nextElementSibling;
            if (dropdown && dropdown.classList.contains('custom-dropdown-menu')) {
                dropdown.classList.toggle('show');
                
                // Close when clicking outside
                const closeDropdown = function(event) {
                    if (!toggle.contains(event.target) && !dropdown.contains(event.target)) {
                        dropdown.classList.remove('show');
                        document.removeEventListener('click', closeDropdown);
                    }
                };
                
                document.addEventListener('click', closeDropdown);
            }
        });
    });
}

// Initialize all components when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeBasicComponents();
});

// Also initialize on content updates
document.addEventListener('contentUpdated', function() {
    initFormElements();
    initTooltips();
    initAlerts();
    initDropdowns();
});
