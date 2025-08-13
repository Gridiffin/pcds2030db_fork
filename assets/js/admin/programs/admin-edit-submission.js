/**
 * Admin Edit Submission JavaScript
 * Functionality for admin edit submission page
 */

// Import admin edit submission styles (includes shared base)
import '../../../css/admin/programs/admin-edit-submission.css';

// Import essential utilities
import '../../utilities/initialization.js';
import '../../utilities/dropdown_init.js';

// Import main utilities including showToast
import '../../main.js';

// Import Bootstrap modal fix
import '../bootstrap_modal_fix.js';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize target management
    initializeTargetManagement();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize auto-save functionality
    initializeAutoSave();
    
    // Initialize form submission handling
    initializeFormSubmission();
    
    console.log('Admin edit submission page initialized');
});

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Initialize target management functionality
 */
function initializeTargetManagement() {
    const addTargetBtn = document.getElementById('addTargetBtn');
    const targetContainer = document.getElementById('targetContainer');
    
    if (addTargetBtn && targetContainer) {
        addTargetBtn.addEventListener('click', function() {
            addNewTarget();
        });
        
        // Initialize remove buttons for existing targets
        initializeRemoveButtons();
    }
}

/**
 * Add a new target to the form
 */
function addNewTarget() {
    const targetContainer = document.getElementById('targetContainer');
    const targetCount = targetContainer.querySelectorAll('.target-item').length;
    const targetIndex = targetCount + 1;
    
    const targetHtml = `
        <div class="target-item" data-target-index="${targetIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Target ${targetIndex}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-target-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Target Number</label>
                    <input type="text" class="form-control" name="targets[${targetIndex}][target_number]" placeholder="e.g., T1, T2">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Indicator</label>
                    <select class="form-select" name="targets[${targetIndex}][status_indicator]">
                        <option value="">Select status...</option>
                        <option value="not_started">Not Started</option>
                        <option value="on_track">On Track</option>
                        <option value="at_risk">At Risk</option>
                        <option value="delayed">Delayed</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Target Description</label>
                <textarea class="form-control" name="targets[${targetIndex}][target_description]" rows="3" placeholder="Describe the target..."></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status Description</label>
                    <textarea class="form-control" name="targets[${targetIndex}][status_description]" rows="2" placeholder="Current status and achievements..."></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="targets[${targetIndex}][remarks]" rows="2" placeholder="Additional remarks..."></textarea>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="targets[${targetIndex}][start_date]">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="targets[${targetIndex}][end_date]">
                </div>
            </div>
        </div>
    `;
    
    targetContainer.insertAdjacentHTML('beforeend', targetHtml);
    
    // Initialize remove button for the new target
    const newTarget = targetContainer.lastElementChild;
    const removeBtn = newTarget.querySelector('.remove-target-btn');
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            newTarget.remove();
            updateTargetNumbers();
        });
    }
    
    updateTargetNumbers();
}

/**
 * Initialize remove buttons for existing targets
 */
function initializeRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-target-btn');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetItem = this.closest('.target-item');
            if (targetItem) {
                targetItem.remove();
                updateTargetNumbers();
            }
        });
    });
}

/**
 * Update target numbers after adding/removing targets
 */
function updateTargetNumbers() {
    const targetItems = document.querySelectorAll('.target-item');
    targetItems.forEach((item, index) => {
        const title = item.querySelector('h6');
        if (title) {
            title.textContent = `Target ${index + 1}`;
        }
        item.setAttribute('data-target-index', index + 1);
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const form = document.getElementById('editSubmissionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
        });
    }
}

/**
 * Validate form before submission
 */
function validateForm() {
    let isValid = true;
    
    // Basic validation - can be extended as needed
    const description = document.getElementById('description');
    if (description && description.value.trim() === '') {
        showToast('Validation Error', 'Please provide a submission description.', 'warning');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Initialize auto-save functionality
 */
function initializeAutoSave() {
    const form = document.getElementById('editSubmissionForm');
    if (form) {
        // Auto-save on form changes
        const formElements = form.querySelectorAll('input, textarea, select');
        formElements.forEach(element => {
            element.addEventListener('change', debounce(autoSave, 2000));
            element.addEventListener('blur', debounce(autoSave, 2000));
        });
    }
}

/**
 * Auto-save form data
 */
function autoSave() {
    const form = document.getElementById('editSubmissionForm');
    if (!form) return;
    
    const formData = new FormData(form);
    
    // Add auto-save indicator
    formData.append('auto_save', '1');
    
    fetch(`${window.APP_URL || ''}/app/ajax/save_submission.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAutoSaveIndicator('Auto-saved successfully');
        } else {
            console.warn('Auto-save failed:', data.error);
        }
    })
    .catch(error => {
        console.error('Auto-save error:', error);
    });
}

/**
 * Show auto-save indicator
 */
function showAutoSaveIndicator(message) {
    // Create or update auto-save indicator
    let indicator = document.getElementById('auto-save-indicator');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'auto-save-indicator';
        indicator.className = 'position-fixed top-0 end-0 p-3';
        indicator.style.zIndex = '9999';
        document.body.appendChild(indicator);
    }
    
    indicator.innerHTML = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        if (indicator) {
            indicator.remove();
        }
    }, 3000);
}

/**
 * Debounce function for auto-save
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Initialize form submission handling
 */
function initializeFormSubmission() {
    const form = document.getElementById('editSubmissionForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        // Validate form
        if (!validateForm()) {
            return false;
        }
        
        // Prepare form data
        const formData = new FormData(form);
        
        // Show loading state
        const submitButtons = form.querySelectorAll('button[type="submit"]');
        submitButtons.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        });
        
        // Make AJAX request
        fetch(`${window.APP_URL || ''}/app/ajax/save_submission.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
                // Show success modal instead of direct redirect
                showSuccessModal();
            } else {
                showToast('Error', data.error || 'An error occurred while saving the submission.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'An error occurred while saving the submission.', 'danger');
        })
        .finally(() => {
            // Reset button states
            submitButtons.forEach(btn => {
                btn.disabled = false;
                if (btn.name === 'save_as_draft') {
                    btn.innerHTML = '<i class="fas fa-save me-2"></i>Save as Draft';
                } else if (btn.name === 'finalize_submission') {
                    btn.innerHTML = '<i class="fas fa-lock me-2"></i>Finalize Submission';
                } else {
                    btn.innerHTML = '<i class="fas fa-check me-2"></i>Submit';
                }
            });
        });
    });
}

/**
 * Show success modal
 */
function showSuccessModal() {
    // Create modal HTML
    const modalHtml = `
        <div class="modal fade" id="adminSubmissionSuccessModal" tabindex="-1" aria-labelledby="adminSubmissionSuccessModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="adminSubmissionSuccessModalLabel">
                            <i class="fas fa-check-circle me-2"></i>Submission Updated Successfully!
                        </h5>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-4">
                            <i class="fas fa-file-alt text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="mb-3">The submission has been updated!</h6>
                        <p class="text-muted mb-4">Would you like to continue editing or return to the programs list?</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pb-4">
                        <button type="button" class="btn btn-success me-2" onclick="window.location.reload()">
                            <i class="fas fa-edit me-1"></i>Continue Editing
                        </button>
                        <a href="${window.APP_URL || ''}/app/views/admin/programs/programs.php" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>View All Programs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('adminSubmissionSuccessModal'));
    modal.show();
    
    // Remove modal from DOM after it's hidden
    document.getElementById('adminSubmissionSuccessModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

/**
 * Show toast notification
 */
function showToast(title, message, type = 'info') {
    if (typeof window.showToast === 'function') {
        window.showToast(title, message, type);
    } else {
        // Fallback toast implementation
        console.log(`${title}: ${message}`);
    }
}