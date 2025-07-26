/**
 * Finalization Tutorial Modal JavaScript
 * Handles step navigation and tutorial functionality
 */

class FinalizationTutorial {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 5;
        this.modal = document.getElementById('finalizationTutorialModal');
        
        if (!this.modal) {
            console.warn('Finalization tutorial modal not found');
            return;
        }
        
        this.init();
    }
    
    init() {
        // Get DOM elements
        this.prevBtn = document.getElementById('tutorialPrevBtn');
        this.nextBtn = document.getElementById('tutorialNextBtn');
        this.finishBtn = document.getElementById('tutorialFinishBtn');
        this.progressBar = document.getElementById('tutorialProgressBar');
        this.stepIndicator = document.getElementById('currentStepIndicator');
        this.steps = document.querySelectorAll('.tutorial-step');
        
        // Bind event listeners
        this.bindEvents();
        
        // Initialize first step
        this.updateStep();
    }
    
    bindEvents() {
        // Navigation buttons
        this.prevBtn?.addEventListener('click', () => this.goToPreviousStep());
        this.nextBtn?.addEventListener('click', () => this.goToNextStep());
        
        // Reset when modal is opened
        this.modal.addEventListener('shown.bs.modal', () => {
            this.resetTutorial();
        });
        
        // Keyboard navigation
        this.modal.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft' && !this.prevBtn.disabled) {
                this.goToPreviousStep();
            } else if (e.key === 'ArrowRight' && this.currentStep < this.totalSteps) {
                this.goToNextStep();
            }
        });
    }
    
    resetTutorial() {
        this.currentStep = 1;
        this.updateStep();
    }
    
    goToNextStep() {
        if (this.currentStep < this.totalSteps) {
            this.currentStep++;
            this.updateStep();
        }
    }
    
    goToPreviousStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateStep();
        }
    }
    
    updateStep() {
        // Hide all steps
        this.steps.forEach(step => step.classList.remove('active'));
        
        // Show current step
        const currentStepElement = document.querySelector(`[data-step="${this.currentStep}"]`);
        if (currentStepElement) {
            currentStepElement.classList.add('active');
        }
        
        // Update progress bar
        const progressPercent = (this.currentStep / this.totalSteps) * 100;
        this.progressBar.style.width = `${progressPercent}%`;
        
        // Update step indicator
        this.stepIndicator.textContent = `Step ${this.currentStep} of ${this.totalSteps}`;
        
        // Update button states
        this.updateButtons();
    }
    
    updateButtons() {
        // Previous button
        this.prevBtn.disabled = this.currentStep === 1;
        
        // Next/Finish buttons
        if (this.currentStep === this.totalSteps) {
            this.nextBtn.style.display = 'none';
            this.finishBtn.style.display = 'inline-block';
        } else {
            this.nextBtn.style.display = 'inline-block';
            this.finishBtn.style.display = 'none';
        }
    }
    
    
    // Public method to open tutorial
    static open() {
        const modalElement = document.getElementById('finalizationTutorialModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    }
    
    // Public method to show tutorial on specific step
    static openToStep(stepNumber) {
        const tutorial = new FinalizationTutorial();
        tutorial.currentStep = stepNumber;
        tutorial.updateStep();
        
        const modalElement = document.getElementById('finalizationTutorialModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    }
}

// Utility functions for external use
window.FinalizationTutorial = FinalizationTutorial;

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tutorial modal only for focal users
    const tutorialModal = document.getElementById('finalizationTutorialModal');
    if (tutorialModal && window.currentUserRole === 'focal') {
        new FinalizationTutorial();
    }
    
    // Initialize submission selection modal for all users
    if (document.getElementById('submissionSelectionModal') && !submissionSelectionModal) {
        submissionSelectionModal = new SubmissionSelectionModal();
    }
});


/**
 * Show tutorial for first-time users
 */
function showTutorialForNewUsers() {
    // Check if user has seen tutorial before
    const hasSeenTutorial = localStorage.getItem('finalization_tutorial_seen');
    
    if (!hasSeenTutorial && window.currentUserRole === 'focal') {
        // Show tutorial after a short delay
        setTimeout(() => {
            FinalizationTutorial.open();
            localStorage.setItem('finalization_tutorial_seen', 'true');
        }, 2000);
    }
}

/**
 * Reset tutorial for testing (can be called from console)
 */
window.resetFinalizationTutorial = function() {
    localStorage.removeItem('finalization_tutorial_seen');
    console.log('Finalization tutorial reset. Refresh page to see tutorial again.');
};

// Optional: Show tutorial for new users
// Uncomment the line below if you want to auto-show tutorial for first-time users
// showTutorialForNewUsers();

/**
 * Quick Finalize Modal Functionality
 */
class QuickFinalizeModal {
    constructor() {
        this.modal = document.getElementById('quickFinalizeModal');
        this.selectedPrograms = new Map(); // programId -> Set of periodIds
        
        if (!this.modal) {
            console.warn('Quick finalize modal not found');
            return;
        }
        
        this.init();
    }
    
    init() {
        // Get DOM elements
        this.loadingState = document.getElementById('finalizeLoadingState');
        this.errorState = document.getElementById('finalizeErrorState');
        this.mainContent = document.getElementById('finalizeMainContent');
        this.successState = document.getElementById('finalizeSuccessState');
        this.programsList = document.getElementById('finalizeProgramsList');
        this.selectionSummary = document.getElementById('selectionSummary');
        this.finalizeBtn = document.getElementById('finalizeSelectedBtn');
        this.selectedCount = document.getElementById('selectedCount');
        this.viewDetailsBtn = document.getElementById('viewDetailsBtn');
        
        // Bind events
        this.bindEvents();
    }
    
    bindEvents() {
        // Load data when modal is shown
        this.modal.addEventListener('shown.bs.modal', () => {
            this.loadDraftSubmissions();
        });
        
        // Reset when modal is hidden
        this.modal.addEventListener('hidden.bs.modal', () => {
            this.reset();
        });
        
        // Finalize button
        this.finalizeBtn?.addEventListener('click', () => {
            this.finalizeSelected();
        });
        
        // View Details button
        this.viewDetailsBtn?.addEventListener('click', () => {
            this.viewSelectedProgramDetails();
        });
    }
    
    reset() {
        this.selectedPrograms.clear();
        this.showLoadingState();
        this.updateSelectionSummary();
    }
    
    showLoadingState() {
        this.loadingState.classList.remove('d-none');
        this.errorState.classList.add('d-none');
        this.mainContent.classList.add('d-none');
        this.successState.classList.add('d-none');
    }
    
    showErrorState(message) {
        this.loadingState.classList.add('d-none');
        this.errorState.classList.remove('d-none');
        this.mainContent.classList.add('d-none');
        this.successState.classList.add('d-none');
        
        const errorMessage = document.getElementById('finalizeErrorMessage');
        if (errorMessage) {
            errorMessage.textContent = message;
        }
    }
    
    showMainContent() {
        this.loadingState.classList.add('d-none');
        this.errorState.classList.add('d-none');
        this.mainContent.classList.remove('d-none');
        this.successState.classList.add('d-none');
    }
    
    showSuccessState() {
        this.loadingState.classList.add('d-none');
        this.errorState.classList.add('d-none');
        this.mainContent.classList.add('d-none');
        this.successState.classList.remove('d-none');
    }
    
    async loadDraftSubmissions() {
        try {
            // Get draft programs from the global variable
            const draftPrograms = window.allPrograms?.filter(p => p.is_draft) || [];
            
            if (draftPrograms.length === 0) {
                this.showErrorState('No draft submissions found to finalize.');
                return;
            }
            
            this.renderProgramsList(draftPrograms);
            this.showMainContent();
            
        } catch (error) {
            console.error('Error loading draft submissions:', error);
            this.showErrorState('Failed to load draft submissions. Please try again.');
        }
    }
    
    renderProgramsList(programs) {
        if (!this.programsList) return;
        
        const html = programs.map(program => `
            <div class="finalize-program-item" data-program-id="${program.program_id}">
                <div class="d-flex align-items-start">
                    <input type="checkbox" class="program-checkbox" 
                           id="program_${program.program_id}" 
                           data-program-id="${program.program_id}"
                           onchange="quickFinalize.toggleProgram(${program.program_id})">
                    
                    <div class="program-info flex-grow-1">
                        <h6>${this.escapeHtml(program.program_name)}</h6>
                        <div class="program-meta">
                            <span class="me-3">
                                <i class="fas fa-hashtag me-1"></i>
                                ${program.program_number || 'No Number'}
                            </span>
                            <span class="me-3">
                                <i class="fas fa-lightbulb me-1"></i>
                                ${program.initiative_name || 'No Initiative'}
                            </span>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-edit me-1"></i>Draft
                            </span>
                        </div>
                        
                        <!-- Reporting Periods -->
                        <div class="period-selection">
                            <small class="text-muted d-block mb-2">Available Reporting Periods:</small>
                            <div id="periods_${program.program_id}">
                                <!-- Periods will be loaded here -->
                                <div class="period-option">
                                    <input type="checkbox" id="period_${program.program_id}_current" 
                                           data-program-id="${program.program_id}" 
                                           data-period-id="current"
                                           onchange="quickFinalize.togglePeriod(${program.program_id}, 'current')">
                                    <label for="period_${program.program_id}_current" class="mb-0">
                                        Current Reporting Period
                                        <small class="text-success d-block">✓ Draft ready for finalization</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        this.programsList.innerHTML = html;
    }
    
    toggleProgram(programId) {
        const checkbox = document.getElementById(`program_${programId}`);
        const programItem = document.querySelector(`[data-program-id="${programId}"]`);
        
        if (checkbox.checked) {
            if (!this.selectedPrograms.has(programId)) {
                this.selectedPrograms.set(programId, new Set());
            }
            programItem.classList.add('selected');
            
            // Auto-select the first available period
            const firstPeriodCheckbox = programItem.querySelector('input[data-period-id]');
            if (firstPeriodCheckbox && !firstPeriodCheckbox.checked) {
                firstPeriodCheckbox.checked = true;
                this.togglePeriod(programId, firstPeriodCheckbox.dataset.periodId);
            }
        } else {
            this.selectedPrograms.delete(programId);
            programItem.classList.remove('selected');
            
            // Uncheck all period checkboxes
            const periodCheckboxes = programItem.querySelectorAll('input[data-period-id]');
            periodCheckboxes.forEach(cb => cb.checked = false);
        }
        
        this.updateSelectionSummary();
    }
    
    togglePeriod(programId, periodId) {
        const checkbox = document.getElementById(`period_${programId}_${periodId}`);
        
        if (!this.selectedPrograms.has(programId)) {
            this.selectedPrograms.set(programId, new Set());
        }
        
        const periods = this.selectedPrograms.get(programId);
        
        if (checkbox.checked) {
            periods.add(periodId);
        } else {
            periods.delete(periodId);
            
            // If no periods selected, uncheck program
            if (periods.size === 0) {
                const programCheckbox = document.getElementById(`program_${programId}`);
                if (programCheckbox) {
                    programCheckbox.checked = false;
                    const programItem = document.querySelector(`[data-program-id="${programId}"]`);
                    programItem?.classList.remove('selected');
                }
            }
        }
        
        this.updateSelectionSummary();
    }
    
    updateSelectionSummary() {
        if (!this.selectionSummary || !this.selectedCount || !this.finalizeBtn) return;
        
        const totalSelections = Array.from(this.selectedPrograms.values())
            .reduce((sum, periods) => sum + periods.size, 0);
        
        this.selectedCount.textContent = totalSelections;
        this.finalizeBtn.disabled = totalSelections === 0;
        this.viewDetailsBtn.disabled = this.selectedPrograms.size === 0;
        
        if (totalSelections === 0) {
            this.selectionSummary.innerHTML = `
                <div class="text-muted text-center py-4">
                    <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                    <p>Select programs to see summary</p>
                </div>
            `;
            return;
        }
        
        let summaryHtml = '<div class="summary-items">';
        
        for (const [programId, periods] of this.selectedPrograms) {
            const program = window.allPrograms?.find(p => p.program_id == programId);
            if (program && periods.size > 0) {
                summaryHtml += `
                    <div class="summary-item">
                        <div>
                            <div class="summary-program-name">${this.escapeHtml(program.program_name)}</div>
                            <div class="summary-period">${periods.size} period(s) selected</div>
                        </div>
                        <div>
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                    </div>
                `;
            }
        }
        
        summaryHtml += '</div>';
        this.selectionSummary.innerHTML = summaryHtml;
    }
    
    async finalizeSelected() {
        // Implementation would go here to actually finalize the submissions
        // For now, show success state
        this.showSuccessState();
        
        // Update the results
        const resultsHtml = Array.from(this.selectedPrograms.entries())
            .map(([programId, periods]) => {
                const program = window.allPrograms?.find(p => p.program_id == programId);
                return `
                    <div class="finalize-results-item">
                        <div class="flex-grow-1">
                            <div class="fw-bold">${this.escapeHtml(program?.program_name || 'Unknown Program')}</div>
                            <small class="text-muted">${periods.size} period(s) finalized</small>
                        </div>
                        <div class="result-status success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                `;
            }).join('');
        
        const resultsContainer = document.getElementById('finalizeResults');
        if (resultsContainer) {
            resultsContainer.innerHTML = `
                <div class="results-list border rounded p-3">
                    ${resultsHtml}
                </div>
            `;
        }
    }
    
    viewSelectedProgramDetails() {
        // Get the first selected program
        const firstProgramId = Array.from(this.selectedPrograms.keys())[0];
        if (firstProgramId) {
            // Open submission selection modal for the selected program
            openSubmissionSelection(firstProgramId);
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Static method to open the modal
    static open() {
        const modalElement = document.getElementById('quickFinalizeModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    }
}

// Initialize Quick Finalize Modal
let quickFinalize;

// Initialize quick finalize modal (only for focal users)
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('quickFinalizeModal') && window.currentUserRole === 'focal') {
        quickFinalize = new QuickFinalizeModal();
        
        // Connect the button to open the modal
        const quickFinalizeBtn = document.getElementById('quickFinalizeBtn');
        if (quickFinalizeBtn) {
            quickFinalizeBtn.addEventListener('click', () => QuickFinalizeModal.open());
        }
    }
});

// Make QuickFinalizeModal available globally
window.QuickFinalizeModal = QuickFinalizeModal;

/**
 * Submission Selection Modal Functionality
 */
class SubmissionSelectionModal {
    constructor() {
        this.modal = document.getElementById('submissionSelectionModal');
        this.loadingState = document.getElementById('submissionSelectionLoading');
        this.errorState = document.getElementById('submissionSelectionError');
        this.listState = document.getElementById('submissionSelectionList');
        this.emptyState = document.getElementById('submissionSelectionEmpty');
        this.itemsContainer = document.getElementById('submissionItems');
        this.errorMessage = document.getElementById('submissionSelectionErrorMessage');
        
        if (!this.modal) {
            console.warn('Submission selection modal not found');
            return;
        }
    }
    
    async openForProgram(programId, forFinalization = false) {
        if (!this.modal) return;
        
        // Store the finalization flag for later use
        this.isFinalizationMode = forFinalization;
        this.currentProgramId = programId;
        
        // Update modal title based on mode
        const modalTitle = document.getElementById('submissionSelectionModalLabel');
        if (modalTitle) {
            if (forFinalization) {
                modalTitle.innerHTML = '<i class="fas fa-check-circle me-2"></i>Select Submission to Review & Finalize';
            } else {
                modalTitle.innerHTML = '<i class="fas fa-eye me-2"></i>Select Submission to View';
            }
        }
        
        // Show modal
        const modalInstance = new bootstrap.Modal(this.modal);
        modalInstance.show();
        
        // Show loading state
        this.showLoadingState();
        
        try {
            // Load submissions for the program
            await this.loadSubmissions(programId);
        } catch (error) {
            console.error('Error loading submissions:', error);
            this.showErrorState('Failed to load submissions. Please try again.');
        }
    }
    
    async loadSubmissions(programId) {
        try {
            // For now, we'll use the program data from the global variable
            // In a real implementation, you might want to fetch from an API
            const program = window.allPrograms?.find(p => p.program_id == programId);
            
            if (!program) {
                throw new Error('Program not found');
            }
            
            // Create mock submission data based on available information
            // In a real implementation, this would come from an API call
            const submissions = [];
            
            if (program.period_id && program.latest_submission_id) {
                // We have at least one submission
                submissions.push({
                    period_id: program.period_id,
                    submission_id: program.latest_submission_id,
                    period_display: program.period_display || `Period ${program.period_id}`,
                    is_draft: program.is_draft || false,
                    is_draft_label: program.is_draft ? 'Draft' : 'Finalized',
                    submitted_at: program.submitted_at || new Date().toISOString(),
                    submitted_by_name: 'Current User'
                });
            }
            
            if (submissions.length === 0) {
                this.showEmptyState();
                return;
            }
            
            this.renderSubmissions(programId, submissions);
            this.showListState();
            
        } catch (error) {
            throw error;
        }
    }
    
    renderSubmissions(programId, submissions) {
        if (!this.itemsContainer) return;
        
        const html = submissions.map(submission => {
            const statusClass = submission.is_draft ? 'warning' : 'success';
            const statusText = submission.is_draft_label || (submission.is_draft ? 'Draft' : 'Finalized');
            const submittedDate = new Date(submission.submitted_at).toLocaleDateString();
            
            return `
                <div class="list-group-item submission-item" 
                     onclick="navigateToSubmission(${programId}, ${submission.period_id})"
                     data-program-id="${programId}" 
                     data-period-id="${submission.period_id}">
                    <div class="submission-header">
                        <div class="submission-period">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            ${this.escapeHtml(submission.period_display)}
                        </div>
                        <span class="badge bg-${statusClass} submission-status">
                            ${this.escapeHtml(statusText)}
                        </span>
                    </div>
                    <div class="submission-meta">
                        <i class="fas fa-user me-1"></i>
                        Submitted by: ${this.escapeHtml(submission.submitted_by_name)}
                        <span class="mx-2">|</span>
                        <i class="fas fa-clock me-1"></i>
                        ${submittedDate}
                    </div>
                </div>
            `;
        }).join('');
        
        this.itemsContainer.innerHTML = html;
    }
    
    showLoadingState() {
        this.loadingState?.classList.remove('d-none');
        this.errorState?.classList.add('d-none');
        this.listState?.classList.add('d-none');
        this.emptyState?.classList.add('d-none');
    }
    
    showErrorState(message) {
        this.loadingState?.classList.add('d-none');
        this.errorState?.classList.remove('d-none');
        this.listState?.classList.add('d-none');
        this.emptyState?.classList.add('d-none');
        
        if (this.errorMessage) {
            this.errorMessage.textContent = message;
        }
    }
    
    showListState() {
        this.loadingState?.classList.add('d-none');
        this.errorState?.classList.add('d-none');
        this.listState?.classList.remove('d-none');
        this.emptyState?.classList.add('d-none');
    }
    
    showEmptyState() {
        this.loadingState?.classList.add('d-none');
        this.errorState?.classList.add('d-none');
        this.listState?.classList.add('d-none');
        this.emptyState?.classList.remove('d-none');
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }
}

// Global instance - initialize immediately
let submissionSelectionModal = null;

// Ensure global functions are available immediately
window.openSubmissionSelection = function(programId, forFinalization = false) {
    // Initialize if not already done
    if (!submissionSelectionModal) {
        submissionSelectionModal = new SubmissionSelectionModal();
    }
    
    // If modal still not available, wait for DOM and try again
    if (!submissionSelectionModal.modal) {
        setTimeout(() => {
            submissionSelectionModal = new SubmissionSelectionModal();
            if (submissionSelectionModal.modal) {
                submissionSelectionModal.openForProgram(programId, forFinalization);
            }
        }, 100);
        return;
    }
    
    // Pass the finalization flag to the modal
    submissionSelectionModal.openForProgram(programId, forFinalization);
};

window.navigateToSubmission = function(programId, periodId) {
    // Close the modal first
    const modalElement = document.getElementById('submissionSelectionModal');
    if (modalElement) {
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) {
            modalInstance.hide();
        }
    }
    
    // Check if we're in finalization mode
    const isFinalizationMode = submissionSelectionModal && submissionSelectionModal.isFinalizationMode;
    
    // Navigate to the submission view with finalization flag if needed
    let url = `view_submissions.php?program_id=${programId}&period_id=${periodId}`;
    if (isFinalizationMode) {
        url += '&finalize=1';
    }
    
    window.location.href = url;
};

/**
 * Single Program Finalization Workflow
 * Handles the new guided finalization process for individual programs
 */

// Global variables for the workflow
let currentProgramId = null;
let currentProgramName = '';
let selectedPeriodId = null;
let selectedPeriodName = '';
let selectedSubmissionId = null;

/**
 * Opens the single program finalize modal
 * Called when user clicks "Finalize Submission" on a program
 */
function openSingleProgramFinalize(programId, programName) {
    currentProgramId = programId;
    currentProgramName = programName;
    
    // Check if modal exists
    const modalElement = document.getElementById('quickFinalizeModal');
    if (!modalElement) {
        console.error('Quick finalize modal not found');
        alert('Modal not available. Please refresh the page and try again.');
        return;
    }
    
    // Update modal title and program name with safety checks
    const modalTitleText = document.getElementById('modalTitleText');
    const selectedProgramName = document.getElementById('selectedProgramName');
    
    if (modalTitleText) modalTitleText.textContent = `Finalize Submission - ${programName}`;
    if (selectedProgramName) selectedProgramName.textContent = programName;
    
    // Reset modal state
    resetFinalizationModal();
    
    // Load available draft periods for this program
    loadAvailablePeriods(programId);
    
    // Show the modal
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

/**
 * Resets the modal to its initial state
 */
function resetFinalizationModal() {
    // Hide all content sections with safety checks
    const finalizeLoadingState = document.getElementById('finalizeLoadingState');
    const finalizeErrorState = document.getElementById('finalizeErrorState');
    const finalizeMainContent = document.getElementById('finalizeMainContent');
    const finalizeSuccessState = document.getElementById('finalizeSuccessState');
    
    if (finalizeLoadingState) finalizeLoadingState.classList.remove('d-none');
    if (finalizeErrorState) finalizeErrorState.classList.add('d-none');
    if (finalizeMainContent) finalizeMainContent.classList.add('d-none');
    if (finalizeSuccessState) finalizeSuccessState.classList.add('d-none');
    
    // Reset step visibility
    const step1Content = document.getElementById('step1Content');
    const step2Content = document.getElementById('step2Content');
    
    if (step1Content) step1Content.classList.remove('d-none');
    if (step2Content) step2Content.classList.add('d-none');
    
    // Reset footer actions
    const step1Actions = document.getElementById('step1Actions');
    const step2Actions = document.getElementById('step2Actions');
    
    if (step1Actions) step1Actions.classList.remove('d-none');
    if (step2Actions) step2Actions.classList.add('d-none');
    
    // Reset button states
    const reviewSubmissionBtn = document.getElementById('reviewSubmissionBtn');
    if (reviewSubmissionBtn) reviewSubmissionBtn.disabled = true;
    
    // Clear selected period
    selectedPeriodId = null;
    selectedPeriodName = '';
    selectedSubmissionId = null;
}

/**
 * Loads available draft periods for the selected program
 */
function loadAvailablePeriods(programId) {
    fetch(`../../../ajax/get_program_draft_periods.php?program_id=${programId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAvailablePeriods(data.periods);
                // Show main content, hide loading
                document.getElementById('finalizeLoadingState').classList.add('d-none');
                document.getElementById('finalizeMainContent').classList.remove('d-none');
            } else {
                showFinalizationError(data.message || 'Failed to load available periods');
            }
        })
        .catch(error => {
            console.error('Error loading periods:', error);
            showFinalizationError('An error occurred while loading periods');
        });
}

/**
 * Displays the available draft periods in the modal
 */
function displayAvailablePeriods(periods) {
    const container = document.getElementById('availablePeriodsList');
    
    if (periods.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <p>No draft submissions found for this program.</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    periods.forEach(period => {
        html += `
            <div class="period-item" onclick="selectPeriod(${period.period_id}, '${period.period_name}', '${period.submission_id}')">
                <div class="period-info">
                    <h6>${period.period_name}</h6>
                    <div class="period-meta">
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
 * Handles period selection
 */
function selectPeriod(periodId, periodName, submissionId) {
    // Remove previous selection
    document.querySelectorAll('.period-item').forEach(item => {
        item.classList.remove('selected');
    });
    
    // Add selection to clicked item
    event.currentTarget.classList.add('selected');
    
    // Store selection
    selectedPeriodId = periodId;
    selectedPeriodName = periodName;
    selectedSubmissionId = submissionId;
    
    // Enable review button
    document.getElementById('reviewSubmissionBtn').disabled = false;
}

/**
 * Loads submission details for review in step 2
 */
function loadSubmissionForReview(submissionId) {
    // Show loading state
    document.getElementById('step1Content').classList.add('d-none');
    document.getElementById('finalizeLoadingState').classList.remove('d-none');
    document.getElementById('finalizeMainContent').classList.remove('d-none');
    
    fetch(`../../../ajax/get_submission_preview.php?submission_id=${submissionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySubmissionReview(data.submission);
                // Show step 2
                document.getElementById('finalizeLoadingState').classList.add('d-none');
                document.getElementById('step2Content').classList.remove('d-none');
                
                // Update footer actions
                document.getElementById('step1Actions').classList.add('d-none');
                document.getElementById('step2Actions').classList.remove('d-none');
                
                // Update modal title
                document.getElementById('modalTitleText').textContent = `Review Submission - ${selectedPeriodName}`;
            } else {
                showFinalizationError(data.message || 'Failed to load submission details');
            }
        })
        .catch(error => {
            console.error('Error loading submission:', error);
            showFinalizationError('An error occurred while loading submission details');
        });
}

/**
 * Displays submission details for review
 */
function displaySubmissionReview(submission) {
    const step2Content = document.getElementById('step2Content');
    
    let html = `
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Step 2:</strong> Review your submission below. You can edit if needed or confirm to finalize.
        </div>
        
        <div class="submission-preview-card p-4 border rounded">
            <h6 class="mb-3">
                <i class="fas fa-file-alt me-2"></i>
                Submission Preview - ${selectedPeriodName}
            </h6>
            
            <div class="submission-details">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Program:</strong> ${currentProgramName}
                    </div>
                    <div class="col-md-6">
                        <strong>Period:</strong> ${selectedPeriodName}
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Status:</strong> <span class="badge bg-warning">Draft</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Last Updated:</strong> ${submission.updated_at || 'Not set'}
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6>Summary:</h6>
                    <p class="text-muted">${submission.summary || 'No summary provided'}</p>
                </div>
            </div>
        </div>
    `;
    
    step2Content.innerHTML = html;
}

/**
 * Finalizes the selected submission
 */
function finalizeSubmission(submissionId) {
    // Show loading state
    document.getElementById('step2Content').classList.add('d-none');
    document.getElementById('finalizeLoadingState').classList.remove('d-none');
    
    fetch('../../../ajax/finalize_submission.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            submission_id: submissionId,
            program_id: currentProgramId,
            period_id: selectedPeriodId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showFinalizationSuccessState();
        } else {
            showFinalizationError(data.message || 'Failed to finalize submission');
        }
    })
    .catch(error => {
        console.error('Error finalizing submission:', error);
        showFinalizationError('An error occurred while finalizing submission');
    });
}

/**
 * Shows success state after finalization
 */
function showFinalizationSuccessState() {
    document.getElementById('finalizeLoadingState').classList.add('d-none');
    document.getElementById('finalizeSuccessState').classList.remove('d-none');
    
    // Update success message
    const successContent = `
        <div class="text-success mb-3">
            <i class="fas fa-check-circle fa-3x"></i>
        </div>
        <h5 class="text-success">Submission Finalized Successfully!</h5>
        <p class="text-muted">Your submission for <strong>${selectedPeriodName}</strong> has been finalized and is now available for review.</p>
        
        <div class="mt-4">
            <button type="button" class="btn btn-primary me-2" onclick="window.location.reload()">
                <i class="fas fa-refresh me-1"></i> Refresh Page
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times me-1"></i> Close
            </button>
        </div>
    `;
    
    document.getElementById('finalizeSuccessState').innerHTML = successContent;
}

/**
 * Shows error state
 */
function showFinalizationError(message) {
    document.getElementById('finalizeLoadingState').classList.add('d-none');
    document.getElementById('finalizeMainContent').classList.add('d-none');
    document.getElementById('finalizeErrorState').classList.remove('d-none');
    document.getElementById('finalizeErrorMessage').textContent = message;
}

/**
 * Initialize event listeners for the new workflow
 */
document.addEventListener('DOMContentLoaded', function() {
    // Review submission button
    document.getElementById('reviewSubmissionBtn')?.addEventListener('click', function() {
        if (!selectedPeriodId) {
            alert('Please select a reporting period first.');
            return;
        }
        
        // Load submission details for review
        loadSubmissionForReview(selectedSubmissionId);
    });

    // Edit submission button
    document.getElementById('editSubmissionBtn')?.addEventListener('click', function() {
        if (selectedSubmissionId) {
            // Redirect to edit submission page
            window.location.href = `edit_submission.php?submission_id=${selectedSubmissionId}&program_id=${currentProgramId}`;
        }
    });

    // Confirm finalize button
    document.getElementById('confirmFinalizeBtn')?.addEventListener('click', function() {
        // Show confirmation dialog
        const confirmMessage = `Are you sure you want to finalize this submission?\n\nYou can still edit after finalizing, but the changes would be saved as a draft.`;
        
        if (confirm(confirmMessage)) {
            finalizeSubmission(selectedSubmissionId);
        }
    });
});

// Make functions globally available
window.openSingleProgramFinalize = openSingleProgramFinalize;

