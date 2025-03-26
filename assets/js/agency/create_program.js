/**
 * Create Program Form Functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for start date to today
    const startDateField = document.getElementById('start_date');
    if (startDateField) {
        const today = new Date().toISOString().split('T')[0];
        startDateField.setAttribute('min', today);
    }
    
    // Form validation is handled inline in create_program.php
});
