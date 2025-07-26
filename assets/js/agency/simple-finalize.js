/**
 * Simple Finalize JavaScript - Brand New Implementation
 * Clean, working finalization for single programs
 */

// Global variables
let simpleProgramId = null;
let simpleProgramName = null;
let simpleSelectedPeriodId = null;
let simpleSelectedSubmissionId = null;
let simpleSelectedPeriodName = null;

/**
 * Open the simple finalize modal for a program
 */
function openSimpleFinalize(programId, programName) {
    console.log('Opening simple finalize for program:', programId, programName);
    
    // Store program info
    simpleProgramId = programId;
    simpleProgramName = programName;
    
    // Reset modal state
    resetSimpleModal();
    
    // Update program info (with safety check)
    const programInfoElement = document.getElementById('simpleProgramInfo');
    if (programInfoElement) {
        programInfoElement.textContent = `Program: ${programName} (ID: ${programId})`;
    }
    
    // Show modal (with safety check)
    const modalElement = document.getElementById('simpleFinalizeModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else {
        console.error('Simple finalize modal not found in DOM');
        alert('Modal not found. Please refresh the page and try again.');
        return;
    }
    
    // Load periods
    loadSimplePeriods();
}

/**
 * Reset modal to initial state
 */
function resetSimpleModal() {
    // Helper function to safely manipulate element classes
    function safeClassList(elementId, action, className) {
        const element = document.getElementById(elementId);
        if (element) {
            if (action === 'add') {
                element.classList.add(className);
            } else if (action === 'remove') {
                element.classList.remove(className);
            }
        }
    }
    
    // Hide all content sections
    safeClassList('simpleLoadingState', 'remove', 'd-none');
    safeClassList('simpleErrorState', 'add', 'd-none');
    safeClassList('simpleMainContent', 'add', 'd-none');
    safeClassList('simpleStep1', 'remove', 'd-none');
    safeClassList('simpleStep2', 'add', 'd-none');
    safeClassList('simpleSuccessState', 'add', 'd-none');
    
    // Hide all buttons
    safeClassList('simpleBackBtn', 'add', 'd-none');
    safeClassList('simpleNextBtn', 'add', 'd-none');
    safeClassList('simpleFinalizeBtn', 'add', 'd-none');
    safeClassList('simpleCloseBtn', 'add', 'd-none');
    
    // Reset selections
    simpleSelectedPeriodId = null;
    simpleSelectedSubmissionId = null;
    simpleSelectedPeriodName = null;
}

/**
 * Load available periods for the program
 */
function loadSimplePeriods() {
    console.log('Loading periods for program:', simpleProgramId);
    
    fetch(`../../../../app/ajax/simple_get_periods.php?program_id=${simpleProgramId}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            
            // Try to parse as JSON
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid JSON response: ' + text);
            }
            
            if (data.success) {
                displaySimplePeriods(data.periods);
                showSimpleMainContent();
            } else {
                showSimpleError(data.message || 'Failed to load periods');
            }
        })
        .catch(error => {
            console.error('Error loading periods:', error);
            showSimpleError('Error loading periods: ' + error.message);
        });
}

/**
 * Display the periods in the modal
 */
function displaySimplePeriods(periods) {
    const container = document.getElementById('simplePeriodsContainer');
    
    if (!periods || periods.length === 0) {
        container.innerHTML = '<div class="alert alert-info">No draft submissions found for this program.</div>';
        return;
    }
    
    let html = '';
    periods.forEach(period => {
        html += `
            <div class="simple-period-item" onclick="selectSimplePeriod(${period.period_id}, '${period.period_name}', ${period.submission_id})">
                <div class="simple-period-info">
                    <h6>${period.period_name}</h6>
                    <div class="simple-period-meta">
                        <span class="badge bg-warning me-2">Draft</span>
                        <span class="text-muted">Last updated: ${period.last_updated}</span>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Select a period
 */
function selectSimplePeriod(periodId, periodName, submissionId) {
    console.log('Selected period:', periodId, periodName, submissionId);
    
    // Remove previous selection
    document.querySelectorAll('.simple-period-item').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Add selection to clicked item
    event.currentTarget.classList.add('selected');
    
    // Store selection
    simpleSelectedPeriodId = periodId;
    simpleSelectedPeriodName = periodName;
    simpleSelectedSubmissionId = submissionId;
    
    // Show next button
    document.getElementById('simpleNextBtn').classList.remove('d-none');
}

/**
 * Go to step 2
 */
function simpleGoToStep2() {
    if (!simpleSelectedPeriodId || !simpleSelectedSubmissionId) {
        alert('Please select a reporting period first.');
        return;
    }
    
    console.log('Going to step 2');
    
    // Hide step 1, show step 2
    document.getElementById('simpleStep1').classList.add('d-none');
    document.getElementById('simpleStep2').classList.remove('d-none');
    
    // Update buttons
    document.getElementById('simpleNextBtn').classList.add('d-none');
    document.getElementById('simpleBackBtn').classList.remove('d-none');
    document.getElementById('simpleFinalizeBtn').classList.remove('d-none');
    
    // Load submission preview
    loadSimpleSubmissionPreview();
}

/**
 * Go back to step 1
 */
function simpleGoToStep1() {
    console.log('Going back to step 1');
    
    // Show step 1, hide step 2
    document.getElementById('simpleStep1').classList.remove('d-none');
    document.getElementById('simpleStep2').classList.add('d-none');
    
    // Update buttons
    document.getElementById('simpleBackBtn').classList.add('d-none');
    document.getElementById('simpleFinalizeBtn').classList.add('d-none');
    document.getElementById('simpleNextBtn').classList.remove('d-none');
}

/**
 * Load submission preview
 */
function loadSimpleSubmissionPreview() {
    console.log('Loading submission preview for:', simpleSelectedSubmissionId);
    
    const previewContainer = document.getElementById('simpleSubmissionPreview');
    previewContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div></div>';
    
    fetch(`../../../../app/ajax/simple_get_submission.php?submission_id=${simpleSelectedSubmissionId}`)
        .then(response => response.text())
        .then(text => {
            console.log('Submission preview response:', text);
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid JSON response: ' + text);
            }
            
            if (data.success) {
                displaySimpleSubmissionPreview(data.submission);
            } else {
                previewContainer.innerHTML = '<div class="alert alert-danger">Failed to load submission details</div>';
            }
        })
        .catch(error => {
            console.error('Error loading submission:', error);
            previewContainer.innerHTML = '<div class="alert alert-danger">Error loading submission details</div>';
        });
}

/**
 * Display submission preview
 */
function displaySimpleSubmissionPreview(submission) {
    const container = document.getElementById('simpleSubmissionPreview');
    
    let html = `
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">${submission.program_name}</h6>
                <p class="card-text"><strong>Period:</strong> ${submission.period_name}</p>
                <p class="card-text"><strong>Description:</strong> ${submission.summary || 'No description provided'}</p>
                <p class="card-text"><small class="text-muted">Last updated: ${submission.updated_at || 'Never'}</small></p>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

/**
 * Finalize the submission
 */
function simpleFinalize() {
    if (!confirm('Are you sure you want to finalize this submission? This action cannot be undone.')) {
        return;
    }
    
    console.log('Finalizing submission:', simpleSelectedSubmissionId);
    
    // Disable button and show loading
    const finalizeBtn = document.getElementById('simpleFinalizeBtn');
    finalizeBtn.disabled = true;
    finalizeBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-1"></div> Finalizing...';
    
    const payload = {
        submission_id: simpleSelectedSubmissionId,
        program_id: simpleProgramId,
        period_id: simpleSelectedPeriodId
    };
    
    fetch('../../../../app/ajax/simple_finalize.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.text())
    .then(text => {
        console.log('Finalize response:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            throw new Error('Invalid JSON response: ' + text);
        }
        
        if (data.success) {
            showSimpleSuccess();
        } else {
            throw new Error(data.message || 'Failed to finalize submission');
        }
    })
    .catch(error => {
        console.error('Error finalizing:', error);
        alert('Error: ' + error.message);
        
        // Re-enable button
        finalizeBtn.disabled = false;
        finalizeBtn.innerHTML = '<i class="fas fa-check me-1"></i> Finalize Submission';
    });
}

/**
 * Show success state
 */
function showSimpleSuccess() {
    console.log('Showing success state');
    
    // Hide other content
    document.getElementById('simpleStep2').classList.add('d-none');
    document.getElementById('simpleSuccessState').classList.remove('d-none');
    
    // Update buttons
    document.getElementById('simpleBackBtn').classList.add('d-none');
    document.getElementById('simpleFinalizeBtn').classList.add('d-none');
    document.getElementById('simpleCloseBtn').classList.remove('d-none');
    
    // Refresh page after 3 seconds
    setTimeout(() => {
        window.location.reload();
    }, 3000);
}

/**
 * Show main content
 */
function showSimpleMainContent() {
    document.getElementById('simpleLoadingState').classList.add('d-none');
    document.getElementById('simpleErrorState').classList.add('d-none');
    document.getElementById('simpleMainContent').classList.remove('d-none');
}

/**
 * Show error message
 */
function showSimpleError(message) {
    console.error('Showing error:', message);
    
    document.getElementById('simpleLoadingState').classList.add('d-none');
    document.getElementById('simpleMainContent').classList.add('d-none');
    document.getElementById('simpleErrorState').classList.remove('d-none');
    document.getElementById('simpleErrorMessage').textContent = message;
}

// Make function globally available
window.openSimpleFinalize = openSimpleFinalize;