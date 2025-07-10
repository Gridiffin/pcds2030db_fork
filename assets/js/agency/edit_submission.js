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
        
        // Remove target button - handle both button and icon clicks
        if (e.target.classList.contains('remove-target-btn') || e.target.closest('.remove-target-btn')) {
            e.preventDefault();
            e.stopPropagation();
            const button = e.target.classList.contains('remove-target-btn') ? e.target : e.target.closest('.remove-target-btn');
            console.log('Delete button clicked:', button);
            removeTargetRow(button);
        }
        
        // Add attachment button
        if (e.target.id === 'add-attachment-btn') {
            document.getElementById('attachments').click();
        }
    });

    // Add remove attachment event
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-attachment-btn') || e.target.closest('.remove-attachment-btn')) {
            e.preventDefault();
            const btn = e.target.classList.contains('remove-attachment-btn') ? e.target : e.target.closest('.remove-attachment-btn');
            const attachmentId = btn.getAttribute('data-attachment-id');
            if (attachmentId) {
                // Call backend to delete the attachment
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                fetch(`${window.APP_URL}/app/ajax/delete_program_attachment.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `attachment_id=${encodeURIComponent(attachmentId)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success', 'Attachment deleted successfully', 'success');
                        showLoadingSpinner();
                        // Use the current period ID from the form or global context
                        const periodId = document.querySelector('input[name="period_id"]').value;
                        loadSubmissionData(periodId);
                    } else {
                        showToast('Error', data.error || 'Failed to delete attachment', 'danger');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash"></i>';
                    }
                })
                .catch(error => {
                    showToast('Error', 'Failed to delete attachment', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash"></i>';
                });
            }
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Edit Submission - ${periodInfo.display_name}
                </h5>
                <div class="header-actions">
                    <!-- View History button removed -->
                </div>
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
                            <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-primary">
                                <i class="fas fa-save me-2"></i>
                                Save as Draft
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
                            <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-primary">
                                <i class="fas fa-save me-2"></i>
                                Save as Draft
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
        const targetNumber = index + 1;
        const tid = target.target_id ? escapeHtml(target.target_id) : '';
        const isExisting = !!tid;
        html += `
            <div class="target-container card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bullseye me-2"></i>
                        Target #${targetNumber}
                    </h6>
                </div>
                <div class="card-body">
                    <input type="hidden" name="target_id[]" value="${tid}">
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
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label small">Status Description</label>
                            <textarea class="form-control form-control-sm" name="target_status_description[]" rows="2"
                                      placeholder="Provide details about the current status">${escapeHtml(target.status_description || '')}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Remarks</label>
                            <textarea class="form-control form-control-sm" name="target_remarks[]" rows="2"
                                      placeholder="Additional remarks for this target">${escapeHtml(target.remarks || '')}</textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label small">Start Date</label>
                            <input type="date" class="form-control form-control-sm" name="target_start_date[]"
                                   value="${target.start_date || ''}" placeholder="Select start date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">End Date</label>
                            <input type="date" class="form-control form-control-sm" name="target_end_date[]"
                                   value="${target.end_date || ''}" placeholder="Select end date">
                        </div>
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
let deletedAttachmentIds = [];
let pendingFiles = [];

function generateAttachmentsHtml(attachments) {
    if (!attachments || attachments.length === 0) {
        return '';
    }
    let html = '';
    attachments.forEach(attachment => {
        const displayName = attachment.original_filename && attachment.original_filename.trim() !== '' ? attachment.original_filename : 'Unnamed file';
        html += `
            <li class="mb-2 d-flex justify-content-between align-items-center attachment-item" data-attachment-id="${attachment.attachment_id}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-file me-2 text-primary"></i>
                    <div>
                        <div class="fw-medium">${escapeHtml(displayName)}</div>
                        <small class="text-muted">${attachment.file_size_formatted} • ${formatDate(attachment.upload_date)}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="${window.APP_URL}/app/ajax/download_program_attachment.php?id=${attachment.attachment_id}" 
                       class="btn btn-sm btn-outline-primary me-1" target="_blank" title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-attachment-btn" title="Remove" data-attachment-id="${attachment.attachment_id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </li>
        `;
    });
    return html;
}

function renderPendingFiles() {
    const attachmentsList = document.getElementById('attachments-list');
    if (!attachmentsList) return;
    // Remove all .pending-attachment items
    attachmentsList.querySelectorAll('.pending-attachment').forEach(el => el.remove());
    // Remove the dynamic no-attachments message if present
    const noMsg = attachmentsList.querySelector('.no-attachments-msg');
    if (noMsg) noMsg.remove();
    // Check if there are any files (existing or pending)
    const hasExisting = attachmentsList.querySelectorAll('.attachment-item').length > 0;
    const hasPending = pendingFiles.length > 0;
    if (!hasExisting && !hasPending) {
        const msg = document.createElement('li');
        msg.className = 'text-muted no-attachments-msg';
        msg.textContent = 'No attachments';
        attachmentsList.appendChild(msg);
    }
    pendingFiles.forEach((file, idx) => {
        const listItem = document.createElement('li');
        listItem.className = 'mb-2 d-flex justify-content-between align-items-center pending-attachment';
        listItem.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-file me-2 text-primary"></i>
                <div>
                    <div class="fw-medium">${escapeHtml(file.name)}</div>
                    <small class="text-muted">${formatFileSize(file.size)} • Ready to upload</small>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-pending-file-btn" data-pending-idx="${idx}" title="Remove">
                <i class="fas fa-trash"></i>
            </button>
        `;
        attachmentsList.appendChild(listItem);
    });
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-pending-file-btn') || e.target.closest('.remove-pending-file-btn')) {
        const btn = e.target.classList.contains('remove-pending-file-btn') ? e.target : e.target.closest('.remove-pending-file-btn');
        const idx = parseInt(btn.getAttribute('data-pending-idx'), 10);
        if (!isNaN(idx)) {
            pendingFiles.splice(idx, 1);
            renderPendingFiles();
            // Also clear the file input so user can re-add the same file if needed
            const fileInput = document.getElementById('attachments');
            if (fileInput) fileInput.value = '';
        }
    }
});

/**
 * Add a new target row
 */
function addTargetRow() {
    const container = document.getElementById('targets-container');
    if (!container) {
        console.error('Targets container not found');
        return;
    }
    
    const existingTargets = container.querySelectorAll('.target-container');
    const targetNumber = existingTargets.length + 1;
    
    const targetRow = document.createElement('div');
    targetRow.className = 'target-container card shadow-sm mb-4';
    targetRow.innerHTML = `
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">
                <i class="fas fa-bullseye me-2"></i>
                Target #${targetNumber}
            </h6>
        </div>
        <div class="card-body">
            <input type="hidden" name="target_id[]" value="">
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
            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label small">Status Description</label>
                    <textarea class="form-control form-control-sm" name="target_status_description[]" rows="2"
                              placeholder="Provide details about the current status"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Remarks</label>
                    <textarea class="form-control form-control-sm" name="target_remarks[]" rows="2"
                              placeholder="Additional remarks for this target"></textarea>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label small">Start Date</label>
                    <input type="date" class="form-control form-control-sm" name="target_start_date[]"
                           placeholder="Select start date">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">End Date</label>
                    <input type="date" class="form-control form-control-sm" name="target_end_date[]"
                           placeholder="Select end date">
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(targetRow);
    
    // Log for debugging
    console.log(`Added target #${targetNumber}`);
}

/**
 * Remove a target row
 */
function removeTargetRow(button) {
    const targetContainer = button.closest('.target-container');
    if (targetContainer) {
        // Disable the button to prevent multiple clicks
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        // Remove the container immediately
        targetContainer.remove();
        
        // Renumber targets
        renumberTargets();
        
        console.log('Target removed successfully');
    } else {
        console.error('Target container not found');
    }
}

/**
 * Renumber targets after removal
 */
function renumberTargets() {
    const targets = document.querySelectorAll('.target-container');
    targets.forEach((target, index) => {
        const targetNumber = index + 1;
        const header = target.querySelector('.card-title');
        if (header) {
            header.innerHTML = `<i class="fas fa-bullseye me-2"></i>Target #${targetNumber}`;
        }
    });
    
    // Log for debugging
    console.log(`Renumbered ${targets.length} targets`);
}

/**
 * Handle file selection
 */
function handleFileSelection(files) {
    const attachmentsList = document.getElementById('attachments-list');
    if (!attachmentsList) return;
    // Add new files to pendingFiles
    Array.from(files).forEach(file => {
        pendingFiles.push(file);
    });
    renderPendingFiles();
}

/**
 * Handle form submission
 */
function handleFormSubmission(form) {
    // Serialize targets into a JSON array
    const targetContainers = form.querySelectorAll('.target-container');
    const targets = [];
    targetContainers.forEach(container => {
        let tid = container.querySelector('[name="target_id[]"]').value;
        tid = tid && !isNaN(tid) && tid !== '' ? parseInt(tid, 10) : null;
        targets.push({
            target_id: tid,
            target_number: container.querySelector('[name="target_number[]"]').value || '',
            target_text: container.querySelector('[name="target_text[]"]').value || '',
            target_status: container.querySelector('[name="target_status[]"]').value || '',
            status_description: container.querySelector('[name="target_status_description[]"]').value || '',
            remarks: container.querySelector('[name="target_remarks[]"]').value || '',
            start_date: container.querySelector('[name="target_start_date[]"]').value || '',
            end_date: container.querySelector('[name="target_end_date[]"]').value || ''
        });
    });

    const formData = new FormData(form);
    // Remove all target_*[] fields from FormData
    [
        'target_id[]',
        'target_number[]',
        'target_text[]',
        'target_status[]',
        'target_status_description[]',
        'target_remarks[]',
        'target_start_date[]',
        'target_end_date[]'
    ].forEach(field => formData.delete(field));
    // Append all pendingFiles to FormData
    pendingFiles.forEach(file => {
        formData.append('attachments[]', file);
    });
    // Add the serialized targets JSON
    formData.append('targets_json', JSON.stringify(targets));

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
            // Show loading spinner and refresh all submission data (including attachments)
            showLoadingSpinner();
            setTimeout(() => {
                loadSubmissionData(formData.get('period_id'));
            }, 1000);
        } else {
            showToast('Error', data.error || 'An error occurred while saving the submission.', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'An error occurred while saving the submission.', 'danger');
    })
    .finally(() => {
        submitButtons.forEach(btn => {
            btn.disabled = false;
            if (btn.name === 'save_as_draft') {
                btn.innerHTML = '<i class="fas fa-save me-2"></i>Save as Draft';
            } else {
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Finalize Submission';
            }
        });
        // Reset deleted attachment IDs after successful save
        deletedAttachmentIds = [];
        pendingFiles = []; // Reset pending files after successful save
        renderPendingFiles(); // Re-render pending files to show "Ready to upload"
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

/**
 * Show audit history for a submission
 */
function showAuditHistory(submissionId) {
    if (window.submissionAuditHistory) {
        window.submissionAuditHistory.showAuditHistory(submissionId);
    } else {
        console.error('Audit history functionality not loaded');
        showToast('Error', 'Audit history functionality not available', 'error');
    }
} 