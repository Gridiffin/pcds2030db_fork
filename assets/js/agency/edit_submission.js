/**
 * Enhanced Edit Submission Page JavaScript
 * 
 * Handles period selection, dynamic content loading, and form interactions
 * for the enhanced edit submission page.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the page
    initPeriodSelector();
    initEventListeners();
    
    // Check if there's a period in URL params
    const urlParams = new URLSearchParams(window.location.search);
    const periodId = urlParams.get('period_id');
    if (periodId) {
        document.getElementById('period_selector').value = periodId;
        loadSubmissionData(periodId);
    }
});

/**
 * Initialize period selector functionality
 */
function initPeriodSelector() {
    const periodSelector = document.getElementById('period_selector');
    if (!periodSelector) return;

    periodSelector.addEventListener('change', function() {
        const selectedPeriodId = this.value;
        if (selectedPeriodId) {
            loadSubmissionData(selectedPeriodId);
        } else {
            showDefaultContent();
        }
    });
}

/**
 * Initialize event listeners for dynamic content
 */
function initEventListeners() {
    // Delegate event listeners for dynamically added content
    document.addEventListener('click', function(e) {
        // Add new submission button
        if (e.target.id === 'add-new-submission-btn') {
            showAddSubmissionForm();
        }
        
        // Add target button
        if (e.target.id === 'add-target-btn') {
            addTargetRow();
        }
        
        // Remove target button
        if (e.target.classList.contains('remove-target-btn')) {
            removeTargetRow(e.target);
        }
        
        // Add attachment button
        if (e.target.id === 'add-attachment-btn') {
            document.getElementById('attachments').click();
        }
    });

    // Handle file input change
    document.addEventListener('change', function(e) {
        if (e.target.id === 'attachments') {
            handleFileSelection(e.target.files);
        }
    });

    // Handle form submission
    document.addEventListener('submit', function(e) {
        if (e.target.id === 'submission-form') {
            e.preventDefault();
            handleFormSubmission(e.target);
        }
    });
}

/**
 * Load submission data for the selected period
 */
function loadSubmissionData(periodId) {
    const dynamicContent = document.getElementById('dynamic-content');
    
    // Show loading spinner
    showLoadingSpinner();
    
    // Update period status display
    updatePeriodStatusDisplay(periodId);
    
    // Fetch submission data
    const formData = new FormData();
    formData.append('program_id', window.programId);
    formData.append('period_id', periodId);
    
    fetch(`${window.APP_URL}/app/ajax/get_submission_by_period.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.has_submission) {
                showEditSubmissionForm(data);
            } else {
                showNoSubmissionMessage(data);
            }
        } else {
            showError('Failed to load submission data: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('An error occurred while loading submission data.');
    });
}

/**
 * Show loading spinner
 */
function showLoadingSpinner() {
    const dynamicContent = document.getElementById('dynamic-content');
    const template = document.getElementById('loading-template');
    dynamicContent.innerHTML = template.innerHTML;
}

/**
 * Update period status display
 */
function updatePeriodStatusDisplay(periodId) {
    const periodSelector = document.getElementById('period_selector');
    const selectedOption = periodSelector.querySelector(`option[value="${periodId}"]`);
    const statusDisplay = document.querySelector('.period-status-display');
    
    if (selectedOption && statusDisplay) {
        const hasSubmission = selectedOption.getAttribute('data-has-submission') === 'true';
        const status = selectedOption.getAttribute('data-status');
        
        let statusHtml = '';
        if (status === 'open') {
            statusHtml = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Open</span>';
        } else {
            statusHtml = '<span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Closed</span>';
        }
        
        if (hasSubmission) {
            const submissionStatus = selectedOption.textContent.includes('Draft') ? 'Draft' : 'Finalized';
            const statusClass = submissionStatus === 'Draft' ? 'warning' : 'info';
            statusHtml += ` <span class="badge bg-${statusClass} ms-2"><i class="fas fa-file-alt me-1"></i>${submissionStatus}</span>`;
        } else {
            statusHtml += ' <span class="badge bg-light text-dark ms-2"><i class="fas fa-plus me-1"></i>No Submission</span>';
        }
        
        statusDisplay.innerHTML = statusHtml;
    }
}

/**
 * Show edit submission form with existing data
 */
function showEditSubmissionForm(data) {
    const dynamicContent = document.getElementById('dynamic-content');
    
    const submission = data.submission;
    const periodInfo = data.period_info;
    const attachments = data.attachments;
    
    const formHtml = `
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Edit Submission - ${periodInfo.display_name}
                </h5>
            </div>
            <div class="card-body">
                <form id="submission-form" enctype="multipart/form-data">
                    <input type="hidden" name="program_id" value="${window.programId}">
                    <input type="hidden" name="period_id" value="${periodInfo.period_id}">
                    <input type="hidden" name="submission_id" value="${submission.submission_id}">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          placeholder="Describe the submission for this period">${escapeHtml(submission.description)}</textarea>
                            </div>

                            <!-- Targets Section -->
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-bullseye me-2"></i>
                                        Targets
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="targets-container">
                                        ${generateTargetsHtml(submission.targets)}
                                    </div>
                                    <button type="button" id="add-target-btn" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="fas fa-plus-circle me-1"></i> Add Target
                                    </button>
                                </div>
                            </div>

                            <!-- Rating and Remarks -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label for="rating" class="form-label">Progress Rating</label>
                                    <select class="form-select" id="rating" name="rating">
                                        <option value="not-started" ${submission.rating === 'not-started' ? 'selected' : ''}>Not Started</option>
                                        <option value="on-track" ${submission.rating === 'on-track' ? 'selected' : ''}>On Track</option>
                                        <option value="on-track-yearly" ${submission.rating === 'on-track-yearly' ? 'selected' : ''}>On Track for Year</option>
                                        <option value="target-achieved" ${submission.rating === 'target-achieved' ? 'selected' : ''}>Target Achieved</option>
                                        <option value="delayed" ${submission.rating === 'delayed' ? 'selected' : ''}>Delayed</option>
                                        <option value="severe-delay" ${submission.rating === 'severe-delay' ? 'selected' : ''}>Severe Delays</option>
                                        <option value="completed" ${submission.rating === 'completed' ? 'selected' : ''}>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                              placeholder="Additional remarks">${escapeHtml(submission.remarks)}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Submission Info -->
                            <div class="card shadow-sm mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Submission Info
                                    </h6>
                                    <ul class="list-unstyled mb-0 small">
                                        <li class="mb-2">
                                            <i class="fas fa-calendar text-primary me-2"></i>
                                            Period: ${periodInfo.display_name}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-file-alt text-info me-2"></i>
                                            Status: ${submission.is_draft ? 'Draft' : 'Finalized'}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-clock text-warning me-2"></i>
                                            Updated: ${formatDate(submission.updated_at)}
                                        </li>
                                        ${submission.submission_date ? `
                                        <li>
                                            <i class="fas fa-check text-success me-2"></i>
                                            Submitted: ${formatDate(submission.submission_date)}
                                        </li>
                                        ` : ''}
                                    </ul>
                                </div>
                            </div>

                            <!-- Attachments Section -->
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">
                                        <i class="fas fa-paperclip me-2"></i>
                                        Attachments
                                    </h6>
                                    <button type="button" id="add-attachment-btn" class="btn btn-outline-secondary btn-sm mb-2">
                                        <i class="fas fa-plus me-1"></i> Add File(s)
                                    </button>
                                    <input type="file" class="form-control d-none" name="attachments[]" id="attachments" multiple>
                                    <div class="form-text mt-1">
                                        You can add files one by one or in batches.
                                    </div>
                                    <ul id="attachments-list" class="list-unstyled small mt-2">
                                        ${generateAttachmentsHtml(attachments)}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <a href="view_programs.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Cancel
                        </a>
                        <div>
                            <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-primary me-2">
                                <i class="fas fa-save me-2"></i>
                                Save as Draft
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>
                                Finalize Submission
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    dynamicContent.innerHTML = formHtml;
}

/**
 * Show no submission message with option to add new
 */
function showNoSubmissionMessage(data) {
    const dynamicContent = document.getElementById('dynamic-content');
    const template = document.getElementById('no-submission-template');
    dynamicContent.innerHTML = template.innerHTML;
    
    // Update the button to include period info
    const addButton = document.getElementById('add-new-submission-btn');
    if (addButton) {
        addButton.setAttribute('data-period-id', data.period_info.period_id);
    }
}

/**
 * Show add submission form for new submission
 */
function showAddSubmissionForm() {
    const periodId = document.getElementById('period_selector').value;
    const periodSelector = document.getElementById('period_selector');
    const selectedOption = periodSelector.querySelector(`option[value="${periodId}"]`);
    const periodName = selectedOption ? selectedOption.textContent.split(' - ')[0] : 'Selected Period';
    
    const formHtml = `
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus-circle me-2"></i>
                    Add New Submission - ${periodName}
                </h5>
            </div>
            <div class="card-body">
                <form id="submission-form" enctype="multipart/form-data">
                    <input type="hidden" name="program_id" value="${window.programId}">
                    <input type="hidden" name="period_id" value="${periodId}">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          placeholder="Describe the submission for this period"></textarea>
                            </div>

                            <!-- Targets Section -->
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-bullseye me-2"></i>
                                        Targets
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="targets-container">
                                        <!-- Targets will be added here -->
                                    </div>
                                    <button type="button" id="add-target-btn" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="fas fa-plus-circle me-1"></i> Add Target
                                    </button>
                                </div>
                            </div>

                            <!-- Rating and Remarks -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label for="rating" class="form-label">Progress Rating</label>
                                    <select class="form-select" id="rating" name="rating">
                                        <option value="not-started">Not Started</option>
                                        <option value="on-track">On Track</option>
                                        <option value="on-track-yearly">On Track for Year</option>
                                        <option value="target-achieved">Target Achieved</option>
                                        <option value="delayed">Delayed</option>
                                        <option value="severe-delay">Severe Delays</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                              placeholder="Additional remarks"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Submission Info -->
                            <div class="card shadow-sm mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Submission Info
                                    </h6>
                                    <ul class="list-unstyled mb-0 small">
                                        <li class="mb-2">
                                            <i class="fas fa-calendar-plus text-primary me-2"></i>
                                            Creates a new submission for ${periodName}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-edit text-info me-2"></i>
                                            You can edit this submission later
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-paperclip text-warning me-2"></i>
                                            Add attachments after creating
                                        </li>
                                        <li>
                                            <i class="fas fa-save text-success me-2"></i>
                                            Save as draft or finalize when ready
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Attachments Section -->
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">
                                        <i class="fas fa-paperclip me-2"></i>
                                        Attachments
                                    </h6>
                                    <button type="button" id="add-attachment-btn" class="btn btn-outline-secondary btn-sm mb-2">
                                        <i class="fas fa-plus me-1"></i> Add File(s)
                                    </button>
                                    <input type="file" class="form-control d-none" name="attachments[]" id="attachments" multiple>
                                    <div class="form-text mt-1">
                                        You can add files one by one or in batches.
                                    </div>
                                    <ul id="attachments-list" class="list-unstyled small mt-2"></ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <a href="view_programs.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Cancel
                        </a>
                        <div>
                            <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-primary me-2">
                                <i class="fas fa-save me-2"></i>
                                Save as Draft
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>
                                Finalize Submission
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('dynamic-content').innerHTML = formHtml;
}

/**
 * Show default content when no period is selected
 */
function showDefaultContent() {
    const dynamicContent = document.getElementById('dynamic-content');
    dynamicContent.innerHTML = `
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fas fa-calendar-alt fa-3x text-muted"></i>
            </div>
            <h5 class="text-muted">Select a Reporting Period</h5>
            <p class="text-muted">Choose a reporting period from the dropdown above to view or edit submissions.</p>
        </div>
    `;
    
    // Clear period status display
    const statusDisplay = document.querySelector('.period-status-display');
    if (statusDisplay) {
        statusDisplay.innerHTML = '';
    }
}

/**
 * Generate HTML for targets
 */
function generateTargetsHtml(targets) {
    if (!targets || targets.length === 0) {
        return '<div class="text-muted small mb-3">No targets added yet. Click "Add Target" to get started.</div>';
    }
    
    let html = '';
    targets.forEach((target, index) => {
        html += `
            <div class="target-row border rounded p-3 mb-3">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label small">Target Number</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="target_number[]" value="${escapeHtml(target.target_number || '')}"
                               placeholder="e.g., 1.1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Target Description</label>
                        <textarea class="form-control" name="target_text[]" rows="2" required
                                  placeholder="Describe the target">${escapeHtml(target.target_text || '')}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Status</label>
                        <select class="form-select form-select-sm" name="target_status[]">
                            <option value="not_started" ${target.target_status === 'not_started' ? 'selected' : ''}>Not Started</option>
                            <option value="in_progress" ${target.target_status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                            <option value="completed" ${target.target_status === 'completed' ? 'selected' : ''}>Completed</option>
                            <option value="delayed" ${target.target_status === 'delayed' ? 'selected' : ''}>Delayed</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-target-btn" title="Remove Target">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <label class="form-label small">Status Description</label>
                        <textarea class="form-control form-control-sm" name="target_status_description[]" rows="1"
                                  placeholder="Provide details about the current status">${escapeHtml(target.status_description || '')}</textarea>
                    </div>
                </div>
            </div>
        `;
    });
    
    return html;
}

/**
 * Generate HTML for attachments
 */
function generateAttachmentsHtml(attachments) {
    if (!attachments || attachments.length === 0) {
        return '<li class="text-muted">No attachments</li>';
    }
    
    let html = '';
    attachments.forEach(attachment => {
        html += `
            <li class="mb-2 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-file me-2 text-primary"></i>
                    <div>
                        <div class="fw-medium">${escapeHtml(attachment.original_filename)}</div>
                        <small class="text-muted">${attachment.file_size_formatted} • ${formatDate(attachment.upload_date)}</small>
                    </div>
                </div>
                <a href="${window.APP_URL}/app/ajax/download_program_attachment.php?id=${attachment.attachment_id}" 
                   class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="fas fa-download"></i>
                </a>
            </li>
        `;
    });
    
    return html;
}

/**
 * Add a new target row
 */
function addTargetRow() {
    const container = document.getElementById('targets-container');
    const targetRow = document.createElement('div');
    targetRow.className = 'target-row border rounded p-3 mb-3';
    targetRow.innerHTML = `
        <div class="row">
            <div class="col-md-2">
                <label class="form-label small">Target Number</label>
                <input type="text" class="form-control form-control-sm" 
                       name="target_number[]" placeholder="e.g., 1.1">
            </div>
            <div class="col-md-6">
                <label class="form-label small">Target Description</label>
                <textarea class="form-control" name="target_text[]" rows="2" required
                          placeholder="Describe the target"></textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Status</label>
                <select class="form-select form-select-sm" name="target_status[]">
                    <option value="not_started">Not Started</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="delayed">Delayed</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm remove-target-btn" title="Remove Target">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <label class="form-label small">Status Description</label>
                <textarea class="form-control form-control-sm" name="target_status_description[]" rows="1"
                          placeholder="Provide details about the current status"></textarea>
            </div>
        </div>
    `;
    
    container.appendChild(targetRow);
}

/**
 * Remove a target row
 */
function removeTargetRow(button) {
    button.closest('.target-row').remove();
}

/**
 * Handle file selection
 */
function handleFileSelection(files) {
    const attachmentsList = document.getElementById('attachments-list');
    if (!attachmentsList) return;
    
    Array.from(files).forEach(file => {
        const listItem = document.createElement('li');
        listItem.className = 'mb-2 d-flex justify-content-between align-items-center';
        listItem.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-file me-2 text-primary"></i>
                <div>
                    <div class="fw-medium">${escapeHtml(file.name)}</div>
                    <small class="text-muted">${formatFileSize(file.size)} • Ready to upload</small>
                </div>
            </div>
            <span class="badge bg-success">New</span>
        `;
        attachmentsList.appendChild(listItem);
    });
}

/**
 * Handle form submission
 */
function handleFormSubmission(form) {
    const formData = new FormData(form);
    
    // Show loading state
    const submitButtons = form.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    });
    
    fetch(`${window.APP_URL}/app/ajax/save_submission.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            
            // Reload the submission data to show updated form
            setTimeout(() => {
                loadSubmissionData(formData.get('period_id'));
            }, 1500);
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
            } else {
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Finalize Submission';
            }
        });
    });
}

/**
 * Show error message
 */
function showError(message) {
    const dynamicContent = document.getElementById('dynamic-content');
    dynamicContent.innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${escapeHtml(message)}
        </div>
    `;
}

/**
 * Utility function to escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Utility function to format date
 */
function formatDate(dateString) {
    if (!dateString) return 'Not set';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Utility function to format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
} 