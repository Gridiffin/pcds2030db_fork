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
            
            <div class="mb-3">
                <label class="form-label">Status Description</label>
                <textarea class="form-control" name="targets[${targetIndex}][status_description]" rows="2" placeholder="Describe current status..."></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea class="form-control" name="targets[${targetIndex}][remarks]" rows="2" placeholder="Additional remarks..."></textarea>
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
    initializeRemoveButtons();
    
    // Update target numbers
    updateTargetNumbers();
}

/**
 * Initialize remove target buttons
 */
function initializeRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-target-btn');
    removeButtons.forEach(button => {
        button.replaceWith(button.cloneNode(true)); // Remove existing listeners
    });
    
    // Add new listeners
    document.querySelectorAll('.remove-target-btn').forEach(button => {
        button.addEventListener('click', function() {
            const targetItem = this.closest('.target-item');
            if (confirm('Are you sure you want to remove this target?')) {
                targetItem.remove();
                updateTargetNumbers();
            }
        });
    });
}

/**
 * Update target numbers after add/remove
 */
function updateTargetNumbers() {
    const targetItems = document.querySelectorAll('.target-item');
    targetItems.forEach((item, index) => {
        const targetNumber = index + 1;
        const heading = item.querySelector('h6');
        if (heading) {
            heading.textContent = `Target ${targetNumber}`;
        }
        item.setAttribute('data-target-index', targetNumber);
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
                showToast('Error', 'Please fix the validation errors before submitting.', 'error');
            }
        });
    }
}

/**
 * Validate form fields
 */
function validateForm() {
    let isValid = true;
    const requiredFields = document.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

/**
 * Initialize auto-save functionality
 */
function initializeAutoSave() {
    const form = document.getElementById('editSubmissionForm');
    if (form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('change', debounce(autoSave, 2000));
        });
    }
}

/**
 * Auto-save form data
 */
function autoSave() {
    const form = document.getElementById('editSubmissionForm');
    if (form) {
        const formData = new FormData(form);
        formData.append('auto_save', '1');
        
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Auto-saved successfully');
                // Optionally show a subtle indicator
                showAutoSaveIndicator();
            }
        })
        .catch(error => {
            console.error('Auto-save failed:', error);
        });
    }
}

/**
 * Show auto-save indicator
 */
function showAutoSaveIndicator() {
    const indicator = document.getElementById('autoSaveIndicator');
    if (indicator) {
        indicator.style.display = 'inline';
        setTimeout(() => {
            indicator.style.display = 'none';
        }, 2000);
    }
}

/**
 * Debounce function
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
window.AdminEditSubmission = {
    addNewTarget,
    updateTargetNumbers,
    validateForm,
    autoSave
};