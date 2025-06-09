/**
 * Unified Program Management
 * 
 * Handles all aspects of agency program management:
 * - Program viewing
 * - Program creation
 * - Program data submission
 */

document.addEventListener('DOMContentLoaded', function() {
    // Ensure loading spinner is properly handled
    stopLoadingSpinner();
    
    // Initialize general program functionality
    initProgramViewing();
    initProgramCreation();
    initProgramSubmission();
});

/**
 * Stop loading spinner and any pending requests
 */
function stopLoadingSpinner() {
    // Apply these fixes regardless of state to ensure spinner disappears
    document.body.classList.add('page-loaded');
    const preloader = document.getElementById('preloader');
    if (preloader) {
        preloader.classList.add('preloader-hide');
        preloader.style.display = 'none';
    }
    
    // Force clear any loading indicators in the browser UI
    if (window.stop) window.stop();
    
    // Reset cursor that might be stuck in loading state
    document.body.style.cursor = 'default';
}

/**
 * Initialize program viewing functionality
 */
function initProgramViewing() {
    // Initialize search functionality
    const searchInput = document.getElementById('programSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', filterPrograms);
    }
    
    // Initialize rating filter (updated from status to rating terminology)
    const ratingFilter = document.getElementById('ratingFilter') || document.getElementById('statusFilter'); // Support both for backward compatibility
    if (ratingFilter) {
        ratingFilter.addEventListener('change', filterPrograms);
    }
}

/**
 * Filter programs based on search input and rating filter
 */
function filterPrograms() {
    const searchValue = document.getElementById('programSearch')?.value.toLowerCase() || '';
    const ratingValue = (document.getElementById('ratingFilter') || document.getElementById('statusFilter'))?.value.toLowerCase() || '';
    const table = document.getElementById('programsTable');
    
    if (!table) return;
    
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const programNameCell = rows[i].getElementsByTagName('td')[0];
        const ratingCell = rows[i].getElementsByTagName('td')[2];
        
        if (!programNameCell || !ratingCell) continue;
        
        const programName = programNameCell.textContent || programNameCell.innerText;
        const rating = ratingCell.textContent || ratingCell.innerText;
        
        const matchesSearch = programName.toLowerCase().indexOf(searchValue) > -1;
        const matchesRating = ratingValue === '' || rating.toLowerCase().indexOf(ratingValue) > -1;
        
        rows[i].style.display = (matchesSearch && matchesRating) ? '' : 'none';
    }
}

/**
 * Initialize program creation functionality
 */
function initProgramCreation() {
    const form = document.getElementById('createProgramForm');
    
    if (!form) return;
    
    // Handle date field validation
    const startDateField = document.getElementById('start_date');
    const endDateField = document.getElementById('end_date');
    
    if (startDateField && endDateField) {
        endDateField.addEventListener('change', function() {
            validateDates(startDateField, endDateField);
        });
        
        startDateField.addEventListener('change', function() {
            validateDates(startDateField, endDateField);
        });
        
        // Removed the date restriction to allow selecting past dates
    }
    
    // Character counter for description
    const descriptionField = document.getElementById('description');
    if (descriptionField) {
        const maxLength = 500;
        
        // Create counter element
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end';
        counter.textContent = `0/${maxLength} characters`;
        descriptionField.parentNode.appendChild(counter);
        
        descriptionField.addEventListener('input', function() {
            const remaining = this.value.length;
            counter.textContent = `${remaining}/${maxLength} characters`;
            
            if (remaining > maxLength) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        });
    }
    
    // Form submission validation
    form.addEventListener('submit', validateProgramForm);
}

/**
 * Initialize program submission functionality
 */
function initProgramSubmission() {
    // Use shared rating pill selection from rating_utils.js
    if (typeof initRatingPills === 'function') {
        initRatingPills();
    } else {
        // Fallback implementation
        const ratingPills = document.querySelectorAll('.rating-pill, .status-pill'); // Support both for backward compatibility
        const ratingInput = document.getElementById('rating') || document.getElementById('status'); // Support both field names
        
        if (ratingPills.length && ratingInput) {
            ratingPills.forEach(pill => {
                pill.addEventListener('click', function() {
                    // Remove active class from all pills
                    ratingPills.forEach(p => p.classList.remove('active'));
                    
                    // Add active class to clicked pill
                    this.classList.add('active');
                    
                    // Update hidden input (support both data attributes)
                    ratingInput.value = this.getAttribute('data-rating') || this.getAttribute('data-status');
                });
            });
        }
    }
    
    // Handle program selection
    const programSelect = document.getElementById('program-select');
    if (programSelect) {
        programSelect.addEventListener('change', function() {
            if (this.value) {
                const form = this.closest('form');
                if (form) form.submit();
            }
        });
    }
    
    // Program submission form validation
    const programSubmissionForm = document.getElementById('programSubmissionForm');
    if (programSubmissionForm) {
        // Different validation for draft vs final submission
        const saveAsDraftBtn = document.querySelector('button[name="save_draft"]');
        const submitFinalBtn = document.querySelector('button[name="submit_program"]');
        
        if (saveAsDraftBtn) {
            saveAsDraftBtn.addEventListener('click', function(e) {
                // Minimal validation for drafts - allow submission with incomplete data
                const form = this.closest('form');
                
                // Set a flag to indicate this is a draft submission
                const draftIndicator = document.createElement('input');
                draftIndicator.type = 'hidden';
                draftIndicator.name = 'is_draft';
                draftIndicator.value = '1';
                form.appendChild(draftIndicator);
                
                // Disable submit button to prevent double submission
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
            });
        }
        
        if (submitFinalBtn) {
            submitFinalBtn.addEventListener('click', function(e) {
                // Full validation for final submission
                if (!validateProgramSubmission(e)) {
                    e.preventDefault();
                    return false;
                }
                
                // Disable submit button to prevent double submission
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';
            });
        }
    }
}

/**
 * Validate program creation form
 */
function validateProgramForm(e) {
    const programName = document.getElementById('program_name').value.trim();
    const startDate = document.getElementById('start_date')?.value;
    const endDate = document.getElementById('end_date')?.value;
    
    if (programName === '') {
        e.preventDefault();
        showValidationError('program_name', 'Program name is required');
        return false;
    }
    
    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        e.preventDefault();
        showValidationError('end_date', 'End date cannot be before start date');
        return false;
    }
    
    return true;
}

/**
 * Validate program submission form (for final submissions)
 */
function validateProgramSubmission(e) {
    const form = e.target.closest('form');
    const target = form.querySelector('#target').value.trim();
    const rating = form.querySelector('#rating, #status').value.trim(); // Support both field names
    
    let isValid = true;
    let errorMessage = '';
    
    if (target === '') {
        isValid = false;
        errorMessage += 'Target is required for final submission.<br>';
        showValidationError('target', 'Target is required');
    }
    
    if (rating === '') {
        isValid = false;
        errorMessage += 'Rating is required for final submission.<br>';
        showValidationError('rating', 'Rating is required');
        showValidationError('status', 'Rating is required'); // Support both field names
    }
    
    if (!isValid) {
        showToast('Validation Error', errorMessage, 'danger');
        return false;
    }
    
    return true;
}

/**
 * Validate date ranges
 */
function validateDates(startField, endField) {
    if (startField.value && endField.value) {
        const startDate = new Date(startField.value);
        const endDate = new Date(endField.value);
        
        if (startDate > endDate) {
            showValidationError('end_date', 'End date cannot be before start date');
            return false;
        }
    }
    
    clearValidationError('end_date');
    return true;
}

/**
 * Show validation error
 */
function showValidationError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.add('is-invalid');
    
    // Add error message if it doesn't exist
    if (!document.getElementById(`${fieldId}-error`)) {
        const errorDiv = document.createElement('div');
        errorDiv.id = `${fieldId}-error`;
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
}

/**
 * Clear validation error
 */
function clearValidationError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.remove('is-invalid');
    
    // Remove error message if it exists
    const errorDiv = document.getElementById(`${fieldId}-error`);
    if (errorDiv) errorDiv.remove();
}