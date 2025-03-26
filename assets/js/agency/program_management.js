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
    
    // Fix for Firefox download notification
    setTimeout(function() {
        if (window.stop) window.stop();
    }, 500);
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
    
    // Initialize status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterPrograms);
    }
}

/**
 * Filter programs based on search input and status filter
 */
function filterPrograms() {
    const searchValue = document.getElementById('programSearch')?.value.toLowerCase() || '';
    const statusValue = document.getElementById('statusFilter')?.value.toLowerCase() || '';
    const table = document.getElementById('programsTable');
    
    if (!table) return;
    
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const programNameCell = rows[i].getElementsByTagName('td')[0];
        const statusCell = rows[i].getElementsByTagName('td')[2];
        
        if (!programNameCell || !statusCell) continue;
        
        const programName = programNameCell.textContent || programNameCell.innerText;
        const status = statusCell.textContent || statusCell.innerText;
        
        const matchesSearch = programName.toLowerCase().indexOf(searchValue) > -1;
        const matchesStatus = statusValue === '' || status.toLowerCase().indexOf(statusValue) > -1;
        
        rows[i].style.display = (matchesSearch && matchesStatus) ? '' : 'none';
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
        
        // Set minimum date for start date to today
        const today = new Date().toISOString().split('T')[0];
        startDateField.setAttribute('min', today);
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
    // Initialize status pill selection
    const statusPills = document.querySelectorAll('.status-pill');
    const statusInput = document.getElementById('status');
    
    if (statusPills.length && statusInput) {
        statusPills.forEach(pill => {
            pill.addEventListener('click', function() {
                // Remove active class from all pills
                statusPills.forEach(p => p.classList.remove('active'));
                
                // Add active class to clicked pill
                this.classList.add('active');
                
                // Update hidden input
                statusInput.value = this.getAttribute('data-status');
            });
        });
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
        programSubmissionForm.addEventListener('submit', validateProgramSubmission);
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
 * Validate program submission form
 */
function validateProgramSubmission(e) {
    const target = document.getElementById('target').value.trim();
    const achievement = document.getElementById('achievement').value.trim();
    
    if (target === '') {
        e.preventDefault();
        showValidationError('target', 'Target is required');
        return false;
    }
    
    if (achievement === '') {
        e.preventDefault();
        showValidationError('achievement', 'Achievement is required');
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
    
    // Add error class
    field.classList.add('is-invalid');
    
    // Create or update error message
    let errorElement = field.nextElementSibling;
    if (!errorElement || !errorElement.classList.contains('invalid-feedback')) {
        errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    }
    
    errorElement.textContent = message;
}

/**
 * Clear validation error
 */
function clearValidationError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.remove('is-invalid');
    
    const errorElement = field.nextElementSibling;
    if (errorElement && errorElement.classList.contains('invalid-feedback')) {
        errorElement.textContent = '';
    }
}
