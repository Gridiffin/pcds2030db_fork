import '../../../css/agency/programs/add_submission_entry.css';

// Import main utilities including showToast
import '../../main.js';

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addSubmissionForm');
    if (!form) return;

    const periodSelect = document.getElementById('period_id');
    const targetsContainer = document.getElementById('targets-container');
    const addTargetBtn = document.getElementById('add-target-btn');
    
    const programNumber = form.dataset.programNumber || '';
    const programId = form.dataset.programId || '';
    
    if (periodSelect) {
        Array.from(periodSelect.options).forEach(option => {
            if (option.dataset.status === 'open') {
                option.classList.add('text-success', 'fw-bold');
            }
        });
    }
    
    let targetCounter = 0;
    const addNewTarget = () => {
        targetCounter++;
        const targetEntry = document.createElement('div');
        targetEntry.className = 'target-entry border rounded p-2 mb-2 position-relative';
        targetEntry.innerHTML = `
            <button type="button" class="btn-close remove-target" aria-label="Remove target" style="position: absolute; top: 5px; right: 5px;"></button>
            <div class="mb-2">
                <label class="form-label small">Target ${targetCounter}</label>
                <textarea class="form-control form-control-sm" name="target_text[]" rows="2" placeholder="Define a measurable target" required></textarea>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small">Target Number</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">${programNumber}.</span>
                        <input type="number" min="1" class="form-control form-control-sm target-counter-input" 
                               name="target_counter[]" placeholder="Counter (e.g., 1)">
                    </div>
                    <input type="hidden" name="target_number[]" value="">
                </div>
                <div class="col-6">
                    <label class="form-label small">Status Indicator</label>
                    <select class="form-select form-select-sm" name="target_status[]">
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="delayed">Delayed</option>
                    </select>
                </div>
            </div>
            <div class="mt-2">
                <textarea class="form-control form-control-sm" name="target_status_description[]" rows="1" placeholder="Achievements/Status"></textarea>
            </div>
        `;
        if (targetsContainer) {
            targetsContainer.appendChild(targetEntry);
        }
        
        const removeBtn = targetEntry.querySelector('.remove-target');
        removeBtn.addEventListener('click', () => {
            targetEntry.remove();
            updateTargetNumbers();
        });
        
        const counterInput = targetEntry.querySelector('.target-counter-input');
        counterInput.addEventListener('blur', () => {
            validateTargetNumber(counterInput);
        });
    };

    const updateTargetNumbers = () => {
        if (!targetsContainer) return;
        const targets = targetsContainer.querySelectorAll('.target-entry');
        targets.forEach((target, index) => {
            const label = target.querySelector('label');
            if (label) {
                label.textContent = `Target ${index + 1}`;
            }
        });
        targetCounter = targets.length;
    };
    
    const validateTargetNumber = (input) => {
        const value = input.value.trim();
        const targetEntry = input.closest('.target-entry');
        const hiddenInput = targetEntry.querySelector('input[name="target_number[]"]');
        
        input.classList.remove('is-valid', 'is-invalid');
        
        const existingFeedback = targetEntry.querySelector('.invalid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }
        
        if (value === '') {
            hiddenInput.value = '';
            return true;
        }
        
        const numValue = parseInt(value, 10);
        if (isNaN(numValue) || numValue < 1) {
            input.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'Please enter a positive number';
            input.parentNode.appendChild(feedback);
            return false;
        }
        
        if (targetsContainer) {
            const allCounterInputs = targetsContainer.querySelectorAll('.target-counter-input');
            let duplicateCount = 0;
            allCounterInputs.forEach(otherInput => {
                if (otherInput !== input && otherInput.value.trim() === value) {
                    duplicateCount++;
                }
            });
            
            if (duplicateCount > 0) {
                input.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'This target number is already used';
                input.parentNode.appendChild(feedback);
                return false;
            }
        }
        
        const fullTargetNumber = `${programNumber}.${value}`;
        hiddenInput.value = fullTargetNumber;
        input.classList.add('is-valid');
        return true;
    };
    
    if (addTargetBtn) {
        addTargetBtn.addEventListener('click', addNewTarget);
    }
    
    // Add one target by default if the container exists
    if (targetsContainer) {
        addNewTarget();
    }

    const addAttachmentBtn = document.getElementById('add-attachment-btn');
    const attachmentsInput = document.getElementById('attachments');
    const attachmentsList = document.getElementById('attachments-list');
    let selectedFiles = [];

    function renderAttachmentsList() {
        if (!attachmentsList) return;
        attachmentsList.innerHTML = '';
        selectedFiles.forEach((file, idx) => {
            const li = document.createElement('li');
            li.className = 'd-flex align-items-center justify-content-between mb-1';
            li.innerHTML = `<span>${file.name}</span>`;
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-link text-danger p-0 ms-2';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.addEventListener('click', function() {
                selectedFiles.splice(idx, 1);
                renderAttachmentsList();
            });
            li.appendChild(removeBtn);
            attachmentsList.appendChild(li);
        });
    }

    if (addAttachmentBtn && attachmentsInput) {
        addAttachmentBtn.addEventListener('click', function() {
            attachmentsInput.value = '';
            attachmentsInput.click();
        });
        attachmentsInput.addEventListener('change', function() {
            if (attachmentsInput.files.length > 0) {
                Array.from(attachmentsInput.files).forEach(file => {
                    if (!selectedFiles.some(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified)) {
                        selectedFiles.push(file);
                    }
                });
                renderAttachmentsList();
            }
        });
    }

    // Convert form submission to AJAX
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        // Validate target numbers
        if (targetsContainer) {
            const allCounterInputs = targetsContainer.querySelectorAll('.target-counter-input');
            let hasValidationErrors = false;
            
            allCounterInputs.forEach(input => {
                if (!validateTargetNumber(input)) {
                    hasValidationErrors = true;
                }
            });
            
            if (hasValidationErrors) {
                if (typeof showToast === 'function') {
                    showToast('Error', 'Please fix the target number validation errors before submitting.', 'danger');
                }
                return;
            }
        }
        
        // Prepare form data
        const formData = new FormData(form);
        
        // Add program_id to form data
        formData.append('program_id', programId);
        
        // Add selected files to form data
        if (selectedFiles.length > 0) {
            selectedFiles.forEach(file => {
                formData.append('attachments[]', file);
            });
        }
        
        // Prepare targets data for JSON
        const targets = [];
        const targetTexts = formData.getAll('target_text[]');
        const targetNumbers = formData.getAll('target_number[]');
        const targetStatuses = formData.getAll('target_status[]');
        const targetStatusDescriptions = formData.getAll('target_status_description[]');
        
        for (let i = 0; i < targetTexts.length; i++) {
            if (targetTexts[i].trim()) {
                targets.push({
                    target_number: targetNumbers[i] || '',
                    target_text: targetTexts[i].trim(),
                    target_status: targetStatuses[i] || 'not_started',
                    status_description: targetStatusDescriptions[i] || ''
                });
            }
        }
        
        // Add targets as JSON
        formData.append('targets_json', JSON.stringify(targets));
        
        // Show loading state
        const submitButtons = form.querySelectorAll('button[type="submit"]');
        submitButtons.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        });
        
        // Submit the form normally (revert to original behavior)
        form.submit();
    });
}); 