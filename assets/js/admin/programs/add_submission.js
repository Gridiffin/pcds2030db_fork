/**
 * Admin Programs - Add Submission JavaScript
 * Handles functionality for the admin add submission page
 */

// Import CSS for admin add submission
import '../../../css/admin/programs/add_submission.css';

// Import essential utilities
import '../../utilities/initialization.js';
import '../../utilities/dropdown_init.js';

// Import main utilities including showToast
import '../../main.js';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize page components
    initializeProgramSelector();
    initializePeriodSelector();
    initializeFormValidation();
    initializeSubmissionForm();
    
    console.log('Admin add submission page initialized');
});

/**
 * Initialize program selector functionality
 */
function initializeProgramSelector() {
    const programSelect = document.getElementById('program_id');
    
    if (programSelect) {
        programSelect.addEventListener('change', function() {
            const programId = this.value;
            
            if (programId) {
                loadProgramDetails(programId);
                updatePeriodOptions(programId);
            } else {
                clearProgramDetails();
                clearPeriodOptions();
            }
        });
        
        // Load details if program is pre-selected
        const selectedProgram = programSelect.value;
        if (selectedProgram) {
            loadProgramDetails(selectedProgram);
            updatePeriodOptions(selectedProgram);
        }
    }
}

/**
 * Load program details
 */
function loadProgramDetails(programId) {
    showLoadingState('program-details');
    
    fetch(`get_program_details.php?program_id=${programId}`)
        .then(response => response.json())
        .then(data => {
            hideLoadingState('program-details');
            
            if (data.success) {
                displayProgramDetails(data.program);
            } else {
                showToast('Error', data.error || 'Failed to load program details', 'error');
                clearProgramDetails();
            }
        })
        .catch(error => {
            hideLoadingState('program-details');
            console.error('Error loading program details:', error);
            showToast('Error', 'Failed to load program details', 'error');
            clearProgramDetails();
        });
}

/**
 * Display program details
 */
function displayProgramDetails(program) {
    const detailsContainer = document.getElementById('programDetails');
    
    if (detailsContainer) {
        detailsContainer.innerHTML = `
            <div class="program-info-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1">${escapeHtml(program.program_name)}</h6>
                        ${program.program_number ? `<span class="program-badge">${escapeHtml(program.program_number)}</span>` : ''}
                    </div>
                    <span class="badge bg-info">${escapeHtml(program.agency_name)}</span>
                </div>
                
                ${program.program_description ? `
                    <p class="text-muted mb-3">${escapeHtml(program.program_description)}</p>
                ` : ''}
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Initiative:</strong><br>
                        <span class="text-muted">${program.initiative_name || 'Not assigned'}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Current Rating:</strong><br>
                        <span class="badge bg-${getRatingColor(program.rating)}">${getRatingLabel(program.rating)}</span>
                    </div>
                </div>
                
                ${program.start_date || program.end_date ? `
                    <div class="row mt-3">
                        ${program.start_date ? `
                            <div class="col-md-6">
                                <strong>Start Date:</strong><br>
                                <span class="text-muted">${formatDate(program.start_date)}</span>
                            </div>
                        ` : ''}
                        ${program.end_date ? `
                            <div class="col-md-6">
                                <strong>End Date:</strong><br>
                                <span class="text-muted">${formatDate(program.end_date)}</span>
                            </div>
                        ` : ''}
                    </div>
                ` : ''}
            </div>
        `;
        
        detailsContainer.style.display = 'block';
    }
}

/**
 * Clear program details
 */
function clearProgramDetails() {
    const detailsContainer = document.getElementById('programDetails');
    if (detailsContainer) {
        detailsContainer.style.display = 'none';
        detailsContainer.innerHTML = '';
    }
}

/**
 * Initialize period selector functionality
 */
function initializePeriodSelector() {
    const periodSelect = document.getElementById('period_id');
    
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            const periodId = this.value;
            
            if (periodId) {
                loadPeriodDetails(periodId);
            } else {
                clearPeriodDetails();
            }
        });
        
        // Load details if period is pre-selected
        const selectedPeriod = periodSelect.value;
        if (selectedPeriod) {
            loadPeriodDetails(selectedPeriod);
        }
    }
}

/**
 * Update period options based on program
 */
function updatePeriodOptions(programId) {
    const periodSelect = document.getElementById('period_id');
    
    if (!periodSelect) return;
    
    showLoadingState('period-selector');
    
    fetch(`get_available_periods.php?program_id=${programId}`)
        .then(response => response.json())
        .then(data => {
            hideLoadingState('period-selector');
            
            if (data.success) {
                populatePeriodOptions(data.periods);
            } else {
                showToast('Error', data.error || 'Failed to load periods', 'error');
                clearPeriodOptions();
            }
        })
        .catch(error => {
            hideLoadingState('period-selector');
            console.error('Error loading periods:', error);
            clearPeriodOptions();
        });
}

/**
 * Populate period select options
 */
function populatePeriodOptions(periods) {
    const periodSelect = document.getElementById('period_id');
    
    if (periodSelect) {
        // Clear existing options except the first one
        periodSelect.innerHTML = '<option value="">Select a reporting period</option>';
        
        periods.forEach(period => {
            const option = document.createElement('option');
            option.value = period.period_id;
            option.textContent = `${period.period_type} ${period.period_number} ${period.year}`;
            
            if (period.is_current) {
                option.setAttribute('selected', 'selected');
            }
            
            periodSelect.appendChild(option);
        });
        
        // Trigger change event if a period was pre-selected
        if (periodSelect.value) {
            periodSelect.dispatchEvent(new Event('change'));
        }
    }
}

/**
 * Clear period options
 */
function clearPeriodOptions() {
    const periodSelect = document.getElementById('period_id');
    
    if (periodSelect) {
        periodSelect.innerHTML = '<option value="">Select a program first</option>';
    }
    
    clearPeriodDetails();
}

/**
 * Load period details
 */
function loadPeriodDetails(periodId) {
    const periodInfo = document.getElementById('periodInfo');
    
    if (periodInfo) {
        showLoadingState('period-info');
        
        fetch(`get_period_details.php?period_id=${periodId}`)
            .then(response => response.json())
            .then(data => {
                hideLoadingState('period-info');
                
                if (data.success) {
                    displayPeriodDetails(data.period);
                } else {
                    clearPeriodDetails();
                }
            })
            .catch(error => {
                hideLoadingState('period-info');
                console.error('Error loading period details:', error);
                clearPeriodDetails();
            });
    }
}

/**
 * Display period details
 */
function displayPeriodDetails(period) {
    const periodInfo = document.getElementById('periodInfo');
    
    if (periodInfo) {
        periodInfo.innerHTML = `
            <div class="period-info">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Period:</strong><br>
                        <span>${period.period_type} ${period.period_number}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Year:</strong><br>
                        <span>${period.year}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Status:</strong><br>
                        <span class="badge bg-${period.is_active ? 'success' : 'secondary'}">
                            ${period.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                </div>
                
                ${period.description ? `
                    <div class="mt-3">
                        <strong>Description:</strong><br>
                        <span class="text-muted">${escapeHtml(period.description)}</span>
                    </div>
                ` : ''}
            </div>
        `;
        
        periodInfo.style.display = 'block';
    }
}

/**
 * Clear period details
 */
function clearPeriodDetails() {
    const periodInfo = document.getElementById('periodInfo');
    if (periodInfo) {
        periodInfo.style.display = 'none';
        periodInfo.innerHTML = '';
    }
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const form = document.getElementById('addSubmissionForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateSubmissionForm()) {
                e.preventDefault();
                showToast('Validation Error', 'Please correct the errors below', 'error');
            }
        });
        
        // Real-time validation
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('change', () => validateField(field));
        });
    }
}

/**
 * Validate submission form
 */
function validateSubmissionForm() {
    const form = document.getElementById('addSubmissionForm');
    let isValid = true;
    
    // Validate required fields
    const programSelect = document.getElementById('program_id');
    const periodSelect = document.getElementById('period_id');
    
    if (!programSelect.value) {
        showFieldError(programSelect, 'Please select a program');
        isValid = false;
    } else {
        clearFieldError(programSelect);
    }
    
    if (!periodSelect.value) {
        showFieldError(periodSelect, 'Please select a reporting period');
        isValid = false;
    } else {
        clearFieldError(periodSelect);
    }
    
    return isValid;
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    if (isValid) {
        clearFieldError(field);
    } else {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

/**
 * Initialize submission form handling
 */
function initializeSubmissionForm() {
    const form = document.getElementById('addSubmissionForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateSubmissionForm()) {
                submitForm();
            }
        });
    }
}

/**
 * Submit form via AJAX
 */
function submitForm() {
    const form = document.getElementById('addSubmissionForm');
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Submission...';
    submitBtn.disabled = true;
    
    fetch(form.action || window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            
            // Redirect to submission edit page or programs list
            setTimeout(() => {
                if (data.submission_id) {
                    window.location.href = `edit_submission.php?id=${data.submission_id}`;
                } else {
                    window.location.href = 'programs.php';
                }
            }, 1500);
        } else {
            showToast('Error', data.error || 'Failed to create submission', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Submission error:', error);
        showToast('Error', 'An error occurred while creating the submission', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Show loading state for specific section
 */
function showLoadingState(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.style.opacity = '0.6';
        section.style.pointerEvents = 'none';
    }
}

/**
 * Hide loading state for specific section
 */
function hideLoadingState(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.style.opacity = '1';
        section.style.pointerEvents = 'auto';
    }
}

/**
 * Utility functions
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function getRatingColor(rating) {
    const colors = {
        'not_started': 'secondary',
        'planning': 'info',
        'in_progress': 'warning',
        'completed': 'success',
        'on_hold': 'danger'
    };
    return colors[rating] || 'secondary';
}

function getRatingLabel(rating) {
    const labels = {
        'not_started': 'Not Started',
        'planning': 'Planning',
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'on_hold': 'On Hold'
    };
    return labels[rating] || 'Unknown';
}

/**
 * Show toast notification
 */
function showToast(title, message, type = 'info') {
    if (typeof window.showToast === 'function') {
        window.showToast(title, message, type);
    } else {
        alert(`${title}: ${message}`);
    }
}

// Export functions for global access
window.AdminAddSubmission = {
    loadProgramDetails,
    updatePeriodOptions,
    validateSubmissionForm,
    submitForm
};