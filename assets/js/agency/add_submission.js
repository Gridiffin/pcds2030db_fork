document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('period_id');
    const targetsContainer = document.getElementById('targets-container');
    const addTargetBtn = document.getElementById('add-target-btn');
    
    // Get program data for target number construction
    const programNumber = window.programNumber || '';
    
    // Highlight open periods
    Array.from(periodSelect.options).forEach(option => {
        if (option.dataset.status === 'open') {
            option.classList.add('text-success', 'fw-bold');
        }
    });
    
    // Target management
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
                    <label class="form-label small">Status</label>
                    <select class="form-select form-select-sm" name="target_status[]">
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="delayed">Delayed</option>
                    </select>
                </div>
            </div>
            <div class="mt-2">
                <textarea class="form-control form-control-sm" name="target_status_description[]" rows="1" placeholder="Status description"></textarea>
            </div>
        `;
        targetsContainer.appendChild(targetEntry);
        
        // Add remove functionality
        const removeBtn = targetEntry.querySelector('.remove-target');
        removeBtn.addEventListener('click', () => {
            targetEntry.remove();
            updateTargetNumbers();
        });
        
        // Add target number validation
        const counterInput = targetEntry.querySelector('.target-counter-input');
        counterInput.addEventListener('blur', () => {
            validateTargetNumber(counterInput);
        });
    };
    const updateTargetNumbers = () => {
        const targets = targetsContainer.querySelectorAll('.target-entry');
        targets.forEach((target, index) => {
            const label = target.querySelector('label');
            if (label) {
                label.textContent = `Target ${index + 1}`;
            }
        });
        targetCounter = targets.length;
    };
    
    // Target number validation function
    const validateTargetNumber = (input) => {
        const value = input.value.trim();
        const targetEntry = input.closest('.target-entry');
        const hiddenInput = targetEntry.querySelector('input[name="target_number[]"]');
        
        // Remove existing validation classes
        input.classList.remove('is-valid', 'is-invalid');
        
        // Clear validation message
        const existingFeedback = targetEntry.querySelector('.invalid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }
        
        // If empty, it's valid (optional field)
        if (value === '') {
            hiddenInput.value = '';
            return true;
        }
        
        // Check if it's a positive number
        const numValue = parseInt(value, 10);
        if (isNaN(numValue) || numValue < 1) {
            input.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'Please enter a positive number';
            input.parentNode.appendChild(feedback);
            return false;
        }
        
        // Check for duplicates within the same submission
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
        
        // Valid - construct full target number
        const fullTargetNumber = `${programNumber}.${value}`;
        hiddenInput.value = fullTargetNumber;
        input.classList.add('is-valid');
        return true;
    };
    
    addTargetBtn.addEventListener('click', addNewTarget);
    // Add one target by default
    addNewTarget();

    // Modern multi-file upload UX
    const addAttachmentBtn = document.getElementById('add-attachment-btn');
    const attachmentsInput = document.getElementById('attachments');
    const attachmentsList = document.getElementById('attachments-list');
    let selectedFiles = [];

    function renderAttachmentsList() {
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
                    // Prevent duplicates
                    if (!selectedFiles.some(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified)) {
                        selectedFiles.push(file);
                    }
                });
                renderAttachmentsList();
            }
        });
    }

    // On form submit, validate target numbers and append all files to FormData
    const form = document.getElementById('addSubmissionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validate all target numbers before submission
            const allCounterInputs = targetsContainer.querySelectorAll('.target-counter-input');
            let hasValidationErrors = false;
            
            allCounterInputs.forEach(input => {
                if (!validateTargetNumber(input)) {
                    hasValidationErrors = true;
                }
            });
            
            if (hasValidationErrors) {
                e.preventDefault();
                showToast('Error', 'Please fix the target number validation errors before submitting.', 'danger');
                return;
            }
            
            if (selectedFiles.length > 0) {
                // Remove any existing file inputs
                const oldInputs = form.querySelectorAll('input[type="file"][name="attachments[]"]');
                oldInputs.forEach(input => input.remove());
                // Create a new DataTransfer to hold all files
                const dataTransfer = new DataTransfer();
                selectedFiles.forEach(file => dataTransfer.items.add(file));
                // Create a new file input and append to form
                const newInput = document.createElement('input');
                newInput.type = 'file';
                newInput.name = 'attachments[]';
                newInput.multiple = true;
                newInput.className = 'd-none';
                newInput.files = dataTransfer.files;
                form.appendChild(newInput);
            }
        });
    }
}); 