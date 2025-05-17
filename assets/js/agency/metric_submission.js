/**
 * Outcome Submission Form
 * Handles outcome submission form validation and formatting
 */
document.addEventListener('DOMContentLoaded', function() {
    // Format numeric inputs
    const numericInputs = document.querySelectorAll('.numeric-input');
    numericInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Remove any non-numeric characters except decimal point
            this.value = this.value.replace(/[^\d.]/g, '');
            
            // Ensure only one decimal point
            const decimalCount = (this.value.match(/\./g) || []).length;
            if (decimalCount > 1) {
                this.value = this.value.replace(/\.(?=.*\.)/g, '');
            }
        });
    });
    
    // Format percentage inputs
    const percentageInputs = document.querySelectorAll('.percentage-input');
    percentageInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Remove any non-numeric characters except decimal point
            this.value = this.value.replace(/[^\d.]/g, '');
            
            // Ensure only one decimal point
            const decimalCount = (this.value.match(/\./g) || []).length;
            if (decimalCount > 1) {
                this.value = this.value.replace(/\.(?=.*\.)/g, '');
            }
            
            // Don't allow values > 100
            if (parseFloat(this.value) > 100) {
                this.value = '100';
            }
        });
    });
    
    // Form submission
    const metricsForm = document.getElementById('metricsForm');
    if (metricsForm) {
        metricsForm.addEventListener('submit', function(e) {
            // Disable submit button to prevent double submission
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';
            }
        });
    }
});
