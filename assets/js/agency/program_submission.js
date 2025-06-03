/**
 * Program Submission Form
 * Handles program data submission form interactions
 */
document.addEventListener('DOMContentLoaded', function() {
    // Rating pill selection (updated from status to rating terminology)
    const ratingPills = document.querySelectorAll('.rating-pill, .status-pill'); // Support both for backward compatibility
    const ratingInput = document.getElementById('rating') || document.getElementById('status'); // Support both field names
    
    if (ratingPills.length && ratingInput) {
        // Set up click handler for each pill
        ratingPills.forEach(pill => {
            pill.addEventListener('click', function() {
                // Remove active class from all pills
                ratingPills.forEach(p => p.classList.remove('active'));
                
                // Add active class to clicked pill
                this.classList.add('active');
                
                // Update hidden input value (support both data attributes)
                ratingInput.value = this.getAttribute('data-rating') || this.getAttribute('data-status');
            });
        });
    }
    
    // Form validation and submission
    const form = document.getElementById('programSubmissionForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Add validation logic if needed
            
            // Disable submit button to prevent double submission
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
            }
        });
    }
    
    // Program select dropdown enhancements
    const programSelect = document.getElementById('program-select');
    if (programSelect) {
        programSelect.addEventListener('change', function() {
            if (this.value) {
                // Show loading indicator next to select
                const selectCol = this.closest('.col-md-8');
                if (selectCol) {
                    const loadingIndicator = document.createElement('div');
                    loadingIndicator.className = 'spinner-border spinner-border-sm text-primary ms-2';
                    loadingIndicator.setAttribute('role', 'status');
                    selectCol.querySelector('label').appendChild(loadingIndicator);
                }
                
                // Submit the form
                this.closest('form').submit();
            }
        });
    }
});