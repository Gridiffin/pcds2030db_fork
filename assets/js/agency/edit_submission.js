/**
 * Enhanced Edit Submission Page JavaScript
 * 
 * Handles period selection, dynamic content loading, and form interactions
 * for the enhanced edit submission page.
 */

// Import CSS for bundle generation
import '../../css/agency/programs/programs.css';

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
    // Track the last clicked submit button
    let lastClickedSubmitButton = null;

    document.addEventListener('click', function(e) {
        if (e.target.type === 'submit' && e.target.form && e.target.form.id === 'submission-form') {
            lastClickedSubmitButton = e.target;
        }
    });

    document.addEventListener('submit', function(e) {
        if (e.target.id === 'submission-form') {
            e.preventDefault();
            // Use the submitter property if available (modern browsers), fallback to lastClickedSubmitButton
            let submitter = e.submitter || lastClickedSubmitButton;
            handleFormSubmission(e.target, submitter);
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
    
    // --- NEW: Build targetId to index mapping ---
    window.targetIdToIndex = {};
    if (Array.isArray(submission.targets)) {
        submission.targets.forEach((t, idx) => {
            if (t.target_id) window.targetIdToIndex[t.target_id] = idx + 1;
        });
    }
    // --- END NEW ---
    
    // Determine if focal user and draft
    const isFocal = window.currentUserRole === 'focal';
    const isDraft = submission.is_draft;
    
    let finalizeButtonHtml = '';
    if (isFocal && isDraft) {
        finalizeButtonHtml = `
            <button type="submit" name="finalize_submission" value="1" class="btn btn-success ms-2">
                <i class="fas fa-lock me-2"></i> Finalize Submission
            </button>
        `;
    }

    // Add Save and Exit button
    let saveAndExitButtonHtml = `
        <button type="submit" name="save_and_exit" value="1" class="btn btn-primary ms-2">
            <i class="fas fa-save me-2"></i> Save and Exit
        </button>
    `;

    const formHtml = `
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Edit Submission - ${periodInfo.display_name}
                </h5>
                <div class="header-actions"></div>
            </div>
            <div class="card-body">
                <form id="submission-form" enctype="multipart/form-data">
                    <input type="hidden" name="program_id" value="${window.programId}">
                    <input type="hidden" name="period_id" value="${periodInfo.period_id}">
                    <input type="hidden" name="submission_id" value="${submission.submission_id}">

                    <!-- Two-column area: Submission Info + Description -->
                    <div class="d-flex flex-row gap-4 info-description-row mb-4">
                        <div class="flex-shrink-0 info-section-card" style="width: 350px; max-width: 100%; min-width: 250px;">
                            <div class="card shadow-sm h-100">
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
                        </div>
                        <div class="flex-grow-1 description-section-card">
                            <div class="mb-4 h-100 d-flex flex-column">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control flex-grow-1" id="description" name="description" rows="3"
                                          placeholder="Describe the submission for this period">${escapeHtml(submission.description)}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments Section (full width) -->
                    <div class="card shadow-sm mb-4">
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

                    <!-- Two-column area: Targets + History Sidebar -->
                    <div class="d-flex flex-row gap-4 targets-history-row">
                        <div class="flex-grow-1 targets-section-card">
                            <div class="card shadow-sm h-100">
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
                        <div style="width: 350px; max-width: 100%;" id="history-sidebar-inside-card"></div>
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
                            ${saveAndExitButtonHtml}
                            ${finalizeButtonHtml}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    dynamicContent.innerHTML = formHtml;
    // Make targets section scrollable if content is too tall
    const targetsContainerCard = document.querySelector('.col-md-8 .card.shadow-sm');
    if (targetsContainerCard) {
        targetsContainerCard.style.maxHeight = '600px';
        targetsContainerCard.style.overflowY = 'auto';
    }
    // Render the sidebar inside the card after attachments
    if (submission && submission.submission_id) {
        renderHistorySidebar(submission.submission_id, 'history-sidebar-inside-card');
    }
}

/**
 * Show no submission message with option to add new
 */
function showNoSubmissionMessage(data) {
    // Fetch incomplete targets first, then decide what to show
    fetchIncompleteTargets(data.period_info.period_id).then((targets) => {
        if (targets.length > 0) {
            // If we have incomplete targets, automatically show the form with auto-filled targets
            showAddSubmissionForm();
        } else {
            // If no incomplete targets, show the normal "no submission" message
            const dynamicContent = document.getElementById('dynamic-content');
            const template = document.getElementById('no-submission-template');
            dynamicContent.innerHTML = template.innerHTML;
            // Update the button to include period info
            const addButton = document.getElementById('add-new-submission-btn');
            if (addButton) {
                addButton.setAttribute('data-period-id', data.period_info.period_id);
            }
        }
    });
}

// Global variable to store incomplete targets
let incompleteTargets = [];

/**
 * Fetch incomplete targets for the selected period
 * @returns {Promise} Promise that resolves when targets are fetched
 */
function fetchIncompleteTargets(periodId) {
    const formData = new FormData();
    formData.append('program_id', window.programId);
    formData.append('period_id', periodId);
    
    return fetch(`${window.APP_URL}/app/ajax/get_incomplete_targets.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            incompleteTargets = data.incomplete_targets || [];
            
        } else {
            console.warn('Failed to fetch incomplete targets:', data.error);
            incompleteTargets = [];
        }
        return incompleteTargets;
    })
    .catch(error => {
        console.error('Error fetching incomplete targets:', error);
        incompleteTargets = [];
        return incompleteTargets;
    });
}

/**
 * Show add submission form for new submission
 * Always fetch incomplete targets before rendering the form
 */
function showAddSubmissionForm() {
    const periodId = document.getElementById('period_selector').value;
    fetchIncompleteTargets(periodId).then(() => {
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
                                            ${incompleteTargets.length > 0 ? `<span class="badge bg-info ms-2">${incompleteTargets.length} auto-filled</span>` : ''}
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
        
        
        // Always auto-fill targets if we have any
        autoFillIncompleteTargets();
    });
}

/**
 * Auto-fill the targets container with incomplete targets from previous periods
 */
function autoFillIncompleteTargets() {
    
    
    
    
    const targetsContainer = document.getElementById('targets-container');
    
    
    if (!targetsContainer) {
        
        return;
    }
    
    if (incompleteTargets.length === 0) {
        
        return;
    }
    
    // Clear any existing targets
    targetsContainer.innerHTML = '';
    
    
    // Add each incomplete target
    incompleteTargets.forEach((target, index) => {
        
        addTargetRow(target);
    });
    
    // Show a notification to the user
    if (incompleteTargets.length > 0) {
        showToast('Info', `${incompleteTargets.length} incomplete targets from previous periods have been auto-filled. You can edit or remove them as needed.`, 'info');
    }
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
    const programNumber = window.programNumber || '';
    
    targets.forEach((target, index) => {
        const targetNumber = index + 1;
        const tid = target.target_id ? escapeHtml(target.target_id) : '';
        const isExisting = !!tid;
        // Parse the counter from the full target_number if possible
        let counter = '';
        if (target.target_number) {
            // Extract the last part after the last dot (most robust approach)
            const match = target.target_number.match(/\.([^.]+)$/);
            counter = match ? match[1] : '';
        }
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
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="form-label small">Target Number</label>
                            <div class="input-group">
                                <span class="input-group-text">${programNumber}.</span>
                                <input type="number" min="1" class="form-control form-control-sm target-counter-input" 
                                       name="target_counter[]" value="${escapeHtml(counter)}" placeholder="Counter (e.g., 1)">
                            </div>
                            <input type="hidden" name="target_number[]" value="${escapeHtml(target.target_number || '')}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Target Description</label>
                            <textarea class="form-control" name="target_text[]" rows="2" required
                                      placeholder="Describe the target">${escapeHtml(target.target_text || '')}</textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label small">Status Indicator</label>
                            <select class="form-select form-select-sm" name="target_status[]">
                                <option value="not_started" ${target.target_status === 'not_started' ? 'selected' : ''}>Not Started</option>
                                <option value="in_progress" ${target.target_status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                <option value="completed" ${target.target_status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="delayed" ${target.target_status === 'delayed' ? 'selected' : ''}>Delayed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Remarks</label>
                            <textarea class="form-control form-control-sm" name="target_remarks[]" rows="2"
                                      placeholder="Additional remarks for this target">${escapeHtml(target.remarks || '')}</textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="form-label small">Achievements/Status</label>
                            <textarea class="form-control form-control-sm" name="target_status_description[]" rows="2"
                                      placeholder="Provide details about achievements and current status">${escapeHtml(target.status_description || '')}</textarea>
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
 * @param {Object} targetData - Optional target data to pre-fill the row
 */
function addTargetRow(targetData = null) {
    const container = document.getElementById('targets-container');
    if (!container) {
        console.error('Targets container not found');
        return;
    }
    
    const existingTargets = container.querySelectorAll('.target-container');
    const targetNumber = existingTargets.length + 1;
    
    // Extract target number counter from target_number if available
    let targetCounter = '';
    let targetNumberValue = '';
    if (targetData && targetData.target_number) {
        targetNumberValue = targetData.target_number;
        // Extract the counter part (e.g., "30.1A.1" -> "1")
        const match = targetData.target_number.match(/\.([^.]+)$/);
        targetCounter = match ? match[1] : '';
    }
    
    const targetRow = document.createElement('div');
    targetRow.className = 'target-container card shadow-sm mb-4';
    targetRow.innerHTML = `
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">
                <i class="fas fa-bullseye me-2"></i>
                Target #${targetNumber}
                ${targetData ? '<span class="badge bg-info ms-2">Auto-filled</span>' : ''}
            </h6>
        </div>
        <div class="card-body">
            <input type="hidden" name="target_id[]" value="">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label class="form-label small">Target Number</label>
                    <div class="input-group">
                        <span class="input-group-text">${programNumber}.</span>
                        <input type="number" min="1" class="form-control form-control-sm target-counter-input" 
                               name="target_counter[]" placeholder="Counter (e.g., 1)" value="${targetCounter}">
                    </div>
                    <input type="hidden" name="target_number[]" value="${targetNumberValue}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Target Description</label>
                    <textarea class="form-control" name="target_text[]" rows="2" required
                              placeholder="Describe the target">${targetData ? escapeHtml(targetData.target_text || '') : ''}</textarea>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label small">Status Indicator</label>
                    <select class="form-select form-select-sm" name="target_status[]">
                        <option value="not_started" ${targetData && targetData.target_status === 'not_started' ? 'selected' : ''}>Not Started</option>
                        <option value="in_progress" ${targetData && targetData.target_status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                        <option value="completed" ${targetData && targetData.target_status === 'completed' ? 'selected' : ''}>Completed</option>
                        <option value="delayed" ${targetData && targetData.target_status === 'delayed' ? 'selected' : ''}>Delayed</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Remarks</label>
                    <textarea class="form-control form-control-sm" name="target_remarks[]" rows="2"
                              placeholder="Additional remarks for this target">${targetData ? escapeHtml(targetData.remarks || '') : ''}</textarea>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <label class="form-label small">Achievements/Status</label>
                    <textarea class="form-control form-control-sm" name="target_status_description[]" rows="2"
                              placeholder="Provide details about achievements and current status">${targetData ? escapeHtml(targetData.status_description || '') : ''}</textarea>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label small">Start Date</label>
                    <input type="date" class="form-control form-control-sm" name="target_start_date[]"
                           placeholder="Select start date" value="${targetData ? (targetData.start_date || '') : ''}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">End Date</label>
                    <input type="date" class="form-control form-control-sm" name="target_end_date[]"
                           placeholder="Select end date" value="${targetData ? (targetData.end_date || '') : ''}">
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(targetRow);
    
    // Log for debugging
    
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
// Track the last clicked submit button
let lastClickedSubmitButton = null;

function handleFormSubmission(form, submitter) {
    // Serialize targets into a JSON array
    const targetContainers = form.querySelectorAll('.target-container');
    const targets = [];
            const programNumber = window.programNumber || '';
    targetContainers.forEach(container => {
        // Safely get form elements with null checks
        const targetIdElement = container.querySelector('[name="target_id[]"]');
        const counterElement = container.querySelector('[name="target_counter[]"]');
        const textElement = container.querySelector('[name="target_text[]"]');
        const statusElement = container.querySelector('[name="target_status[]"]');
        const statusDescElement = container.querySelector('[name="target_status_description[]"]');
        const remarksElement = container.querySelector('[name="target_remarks[]"]');
        const startDateElement = container.querySelector('[name="target_start_date[]"]');
        const endDateElement = container.querySelector('[name="target_end_date[]"]');
        
        // Skip if essential elements are missing
        if (!textElement || !counterElement) {
            console.warn('Skipping target container with missing essential elements:', container);
            return;
        }
        
        // Parse target ID safely
        let tid = targetIdElement ? targetIdElement.value : '';
        tid = tid && !isNaN(tid) && tid !== '' ? parseInt(tid, 10) : null;
        
        // Get counter value safely
        const counter = counterElement.value || '';
        const fullTargetNumber = counter ? `${programNumber}.${counter}` : '';
        
        targets.push({
            target_id: tid,
            target_number: fullTargetNumber,
            target_text: textElement.value || '',
            target_status: statusElement ? statusElement.value : '',
            status_description: statusDescElement ? statusDescElement.value : '',
            remarks: remarksElement ? remarksElement.value : '',
            start_date: startDateElement ? startDateElement.value : '',
            end_date: endDateElement ? endDateElement.value : ''
        });
    });

    const formData = new FormData(form);
    // Add the clicked submit button's name/value to FormData
    if (submitter && submitter.name) {
        formData.append(submitter.name, submitter.value);
    }
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
            refreshReportingPeriodsDropdown();
            if (submitter && submitter.name === 'save_and_exit') {
                // Redirect based on user role
                if (window.currentUserRole === 'admin') {
                    window.location.href = window.APP_URL + '/app/views/admin/programs/programs.php';
                } else {
                    window.location.href = window.APP_URL + '/app/views/admin/programs/programs.php';
                }
                return;
            }
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
        // After submission, reset the tracker
        lastClickedSubmitButton = null;
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

function refreshReportingPeriodsDropdown() {
    fetch(`${window.APP_URL}/app/ajax/get_reporting_periods.php?program_id=${window.programId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const periodSelector = document.getElementById('period_selector');
                const currentValue = periodSelector.value;
                periodSelector.innerHTML = '<option value="">Choose a reporting period...</option>';
                data.periods.forEach(period => {
                    let label = period.display_name;
                    if (period.status === 'open') label += ' (Open)';
                    if (period.has_submission) {
                        label += period.is_draft ? ' - Draft' : ' - Finalized';
                    } else {
                        label += ' - No Submission';
                    }
                    periodSelector.innerHTML += `<option value="${period.period_id}" data-has-submission="${period.has_submission}" data-submission-id="${period.submission_id || ''}" data-status="${period.status}">${label}</option>`;
                });
                // Restore selection if possible
                periodSelector.value = currentValue;
            }
        });
} 

function renderHistorySidebar(submissionId, targetId = 'history-sidebar-container') {
    const sidebar = document.getElementById(targetId);
    if (!sidebar) return;
    sidebar.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading change history...</p></div>`;

    fetch(`${window.APP_URL}/app/ajax/get_submission_audit_history.php?submission_id=${submissionId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success || !data.data) {
                sidebar.innerHTML = `<div class='alert alert-warning'>No change history found for this submission.</div>`;
                return;
            }
            let history = data.data.audit_history;
            if (!history || history.length === 0) {
                sidebar.innerHTML = `<div class='alert alert-info'>No changes have been made to this submission yet.</div>`;
                return;
            }
            // Build a set of fields with history
            const fieldSet = new Set();
            history.forEach(entry => {
                entry.field_changes.forEach(change => {
                    fieldSet.add(change.field_name);
                });
            });
            const fields = Array.from(fieldSet);
            // Render clickable list of fields
            let html = `<div class='card shadow-sm'><div class='card-header d-flex justify-content-between align-items-center'><h6 class='mb-0'><i class='fas fa-history me-2'></i>View Field Change History</h6></div><div class='card-body' id='history-sidebar-body'>`;
            html += `<div class='mb-3'><input type='text' class='form-control form-control-sm' id='history-field-search' placeholder='Search field...'></div>`;
            html += `<ul class='list-group'>`;
            fields.forEach(field => {
                const label = history.find(entry => entry.field_changes.find(c => c.field_name === field)).field_changes.find(c => c.field_name === field).field_label || field;
                html += `<li class='list-group-item list-group-item-action history-field-item' data-field='${field}'>
                    <i class='fas fa-angle-right me-2'></i>${label}
                </li>`;
            });
            html += `</ul></div></div>`;
            sidebar.innerHTML = html;
            // Search logic
            document.getElementById('history-field-search').addEventListener('input', function() {
                const val = this.value.toLowerCase();
                document.querySelectorAll('.history-field-item').forEach(item => {
                    const match = item.textContent.toLowerCase().includes(val);
                    item.style.display = match ? '' : 'none';
                });
            });
            // Click logic for field items
            document.querySelectorAll('.history-field-item').forEach(item => {
                item.addEventListener('click', function() {
                    const field = this.getAttribute('data-field');
                    renderFieldHistory(submissionId, field, targetId, fields);
                });
            });
        })
        .catch(() => {
            sidebar.innerHTML = `<div class='alert alert-danger'>Failed to load change history.</div>`;
        });
}

function renderFieldHistory(submissionId, field, targetId, allFields) {
    const sidebar = document.getElementById(targetId);
    if (!sidebar) return;
    sidebar.innerHTML = `<div class='text-center py-4'><div class='spinner-border text-primary' role='status'><span class='visually-hidden'>Loading...</span></div><p class='mt-3 text-muted'>Loading field history...</p></div>`;
    
    // Use get_field_history.php for field-specific history
    // We need program_id and period_id for this endpoint, so get them from the form
    const programId = window.programId;
    const periodId = document.querySelector('input[name="period_id"]').value;
    
    fetch(`${window.APP_URL}/app/ajax/get_field_history.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            program_id: programId,
            period_id: periodId,
            field_name: field,
            offset: 0,
            limit: 20 // Reduced from 50 to 20
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            sidebar.innerHTML = `<div class='alert alert-danger'>${data.error}</div>`;
            return;
        }
        
        if (!data.history || data.history.length === 0) {
            sidebar.innerHTML = `
                <div class='text-center py-4'>
                    <i class='fas fa-history text-muted' style='font-size: 2rem;'></i>
                    <p class='mt-3 text-muted'>No history found for this field.</p>
                </div>
            `;
            return;
        }
        
        // Group history entries by target_id
        const groupedHistory = {};
        data.history.forEach(entry => {
            const targetKey = entry.target_id || 'no_target';
            if (!groupedHistory[targetKey]) {
                groupedHistory[targetKey] = [];
            }
            groupedHistory[targetKey].push(entry);
        });
        
        // Debug: Log the first few entries to see what data we're getting
        
        
        
        let historyHtml = `
            <div class='history-header mb-3'>
                <div class='d-flex justify-content-between align-items-center'>
                    <h6 class='mb-0'><i class='fas fa-history'></i> Field History: ${getFieldDisplayName(field)}</h6>
                    <button class='btn btn-sm btn-outline-secondary back-to-fields-btn' onclick="renderHistorySidebar(${submissionId}, '${targetId}')">
                        <i class='fas fa-arrow-left'></i> Back to Fields
                    </button>
                </div>
                <small class='text-muted'>Showing ${data.history.length} of ${data.total_count || data.history.length} changes</small>
            </div>
        `;
        
        // Sort targets by their most recent change date (newest first)
        const sortedTargetKeys = Object.keys(groupedHistory).sort((a, b) => {
            if (a === 'no_target') return 1;
            if (b === 'no_target') return -1;
            
            const targetA = groupedHistory[a];
            const targetB = groupedHistory[b];
            
            // Get the most recent change date for each target
            const latestA = Math.max(...targetA.map(entry => new Date(entry.submitted_at).getTime()));
            const latestB = Math.max(...targetB.map(entry => new Date(entry.submitted_at).getTime()));
            
            // Sort by most recent first
            return latestB - latestA;
        });
        
        // Limit the number of target groups shown to prevent overflow
        const maxTargetGroups = 3;
        const limitedTargetKeys = sortedTargetKeys.slice(0, maxTargetGroups);
        
        limitedTargetKeys.forEach(targetKey => {
            const entries = groupedHistory[targetKey];
            const firstEntry = entries[0];
            
            // Get target label
            let targetLabel = 'Unknown Target';
            if (targetKey === 'no_target') {
                targetLabel = 'General Changes';
            } else if (window.targetIdToIndex && firstEntry.target_id) {
                const index = window.targetIdToIndex[firstEntry.target_id];
                if (index) {
                    targetLabel = `Target #${index}`;
                } else if (firstEntry.target_number && firstEntry.target_number.trim() !== '') {
                    targetLabel = `Target #${firstEntry.target_number}`;
                } else {
                    targetLabel = `Target (ID: ${firstEntry.target_id})`;
                }
            } else if (firstEntry.target_number && firstEntry.target_number.trim() !== '') {
                targetLabel = `Target #${firstEntry.target_number}`;
            } else {
                targetLabel = `Target (ID: ${firstEntry.target_id})`;
            }
            
            historyHtml += `
                <div class='target-history-group mb-3'>
                    <div class='target-header d-flex align-items-center mb-2'>
                        <span class='badge bg-primary me-2'>${targetLabel}</span>
                        <small class='text-muted'>${entries.length} change${entries.length > 1 ? 's' : ''}</small>
                    </div>
                    <div class='target-changes'>
            `;
            
            // Sort entries by date (newest first) and limit to 5 entries per target
            entries.sort((a, b) => new Date(b.submitted_at) - new Date(a.submitted_at));
            const limitedEntries = entries.slice(0, 5);
            
            limitedEntries.forEach(entry => {
                const changeTypeClass = getChangeTypeClass(entry.change_type);
                const changeTypeIcon = getChangeTypeIcon(entry.change_type);
                const changeTypeText = getChangeTypeText(entry.change_type);
                
                let valueDisplay = '';
                if (entry.change_type === 'modified') {
                    valueDisplay = `
                        <div class='change-values'>
                            <div class='old-value'><strong>From:</strong> ${entry.old_value || '<em>empty</em>'}</div>
                            <div class='new-value'><strong>To:</strong> ${entry.new_value || '<em>empty</em>'}</div>
                        </div>
                    `;
                } else if (entry.change_type === 'added') {
                    valueDisplay = `<div class='new-value'><strong>Added:</strong> ${entry.new_value || '<em>empty</em>'}</div>`;
                } else if (entry.change_type === 'removed') {
                    valueDisplay = `<div class='old-value'><strong>Removed:</strong> ${entry.old_value || '<em>empty</em>'}</div>`;
                }
                
                historyHtml += `
                    <div class='change-entry mb-2 p-2 border-start border-3 ${changeTypeClass}'>
                        <div class='change-header d-flex justify-content-between align-items-start'>
                            <div class='change-type'>
                                <i class='${changeTypeIcon}'></i>
                                <span class='ms-1'>${changeTypeText}</span>
                            </div>
                            <div class='change-meta text-end'>
                                <div class='change-date small'>${entry.formatted_date}</div>
                                <div class='change-user small text-muted'>by ${entry.submitted_by}</div>
                                ${entry.is_draft ? '<div class="draft-badge small text-warning">Draft</div>' : ''}
                            </div>
                        </div>
                        ${valueDisplay}
                    </div>
                `;
            });
            
            // Show "more" indicator if there are more entries
            if (entries.length > 5) {
                historyHtml += `
                    <div class='text-center mt-2'>
                        <small class='text-muted'>+${entries.length - 5} more changes</small>
                    </div>
                `;
            }
            
            historyHtml += `
                    </div>
                </div>
            `;
        });
        
        // Show "more targets" indicator if there are more target groups
        if (sortedTargetKeys.length > maxTargetGroups) {
            historyHtml += `
                <div class='text-center mt-3'>
                    <small class='text-muted'>+${sortedTargetKeys.length - maxTargetGroups} more targets</small>
                </div>
            `;
        }
        
        sidebar.innerHTML = historyHtml;
    })
    .catch(error => {
        console.error('Error loading field history:', error);
        sidebar.innerHTML = `<div class='alert alert-danger'>Error loading history: ${error.message}</div>`;
    });
}

// Helper functions for change types
function getChangeTypeClass(changeType) {
    switch (changeType) {
        case 'added': return 'border-success bg-light';
        case 'modified': return 'border-warning bg-light';
        case 'removed': return 'border-danger bg-light';
        default: return 'border-secondary bg-light';
    }
}

function getChangeTypeIcon(changeType) {
    switch (changeType) {
        case 'added': return 'fas fa-plus-circle text-success';
        case 'modified': return 'fas fa-edit text-warning';
        case 'removed': return 'fas fa-minus-circle text-danger';
        default: return 'fas fa-circle text-secondary';
    }
}

function getChangeTypeText(changeType) {
    switch (changeType) {
        case 'added': return 'Added';
        case 'modified': return 'Modified';
        case 'removed': return 'Removed';
        default: return 'Changed';
    }
}

function getFieldDisplayName(fieldName) {
    const fieldNames = {
        'target_number': 'Target Number',
        'target_description': 'Target Description',
        'status_indicator': 'Status Indicator',
        'status_description': 'Achievements/Status',
        'remarks': 'Remarks',
        'start_date': 'Start Date',
        'end_date': 'End Date',
        'description': 'Description'
    };
    return fieldNames[fieldName] || fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
} 