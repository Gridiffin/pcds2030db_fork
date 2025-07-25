<?php
/**
 * Finalization Tutorial Modal
 * Step-by-step guide for focal users to finalize program submissions
 */

// Only show to focal users
if (!is_focal_user()) {
    return;
}
?>

<!-- Finalization Tutorial Modal -->
<div class="modal fade" id="finalizationTutorialModal" tabindex="-1" aria-labelledby="finalizationTutorialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="finalizationTutorialModalLabel">
                    <i class="fas fa-graduation-cap me-2"></i>
                    Submission Finalization Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Progress Bar -->
                <div class="tutorial-progress-bar">
                    <div class="progress" style="height: 6px; border-radius: 0;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 25%" id="tutorialProgressBar"></div>
                    </div>
                </div>

                <!-- Step Content Container -->
                <div class="tutorial-content p-4" id="tutorialContent">
                    
                    <!-- Step 1: Overview -->
                    <div class="tutorial-step active" data-step="1">
                        <div class="step-header mb-3">
                            <div class="step-number">
                                <span class="badge bg-primary rounded-circle">1</span>
                            </div>
                            <h4 class="step-title">Getting Started</h4>
                            <p class="step-subtitle text-muted">Let's walk through the submission finalization process</p>
                        </div>
                        
                        <div class="step-content">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>What is Submission Finalization?</strong><br>
                                Finalizing a submission locks in your program data for the reporting period and makes it available for review by administrators.
                            </div>
                            
                            <h5>Process Overview:</h5>
                            <ul class="checklist">
                                <li><i class="fas fa-check-circle text-success me-2"></i>Select a program with draft submissions</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Choose the reporting period</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Review submission details</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Finalize the submission</li>
                            </ul>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    This process typically takes 2-3 minutes per submission.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Select Program -->
                    <div class="tutorial-step" data-step="2">
                        <div class="step-header mb-3">
                            <div class="step-number">
                                <span class="badge bg-primary rounded-circle">2</span>
                            </div>
                            <h4 class="step-title">Select Program</h4>
                            <p class="step-subtitle text-muted">Choose a program with draft submissions to finalize</p>
                        </div>
                        
                        <div class="step-content">
                            <div class="tutorial-example">
                                <div class="example-box">
                                    <div class="d-flex align-items-center">
                                        <div class="status-indicator status-draft me-3"></div>
                                        <div>
                                            <h6 class="mb-1">Education Enhancement Program</h6>
                                            <small class="text-muted">Program #EDU-2024-001</small>
                                        </div>
                                        <div class="ms-auto">
                                            <span class="badge bg-warning">Draft</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> Only programs with draft submissions can be finalized. 
                                Look for programs marked with a yellow "Draft" badge in the "Draft Submissions" tab.
                            </div>
                            
                            <div class="action-note">
                                <strong>Your Task:</strong> Navigate to the "Draft Submissions" tab and select a program to finalize.
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Choose Reporting Period -->
                    <div class="tutorial-step" data-step="3">
                        <div class="step-header mb-3">
                            <div class="step-number">
                                <span class="badge bg-primary rounded-circle">3</span>
                            </div>
                            <h4 class="step-title">Choose Reporting Period</h4>
                            <p class="step-subtitle text-muted">Select which submission period you want to finalize</p>
                        </div>
                        
                        <div class="step-content">
                            <div class="tutorial-example">
                                <div class="example-box">
                                    <h6>Available Reporting Periods:</h6>
                                    <div class="period-selector mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="examplePeriod" id="q1" checked>
                                            <label class="form-check-label" for="q1">
                                                Q1 2024 (January - March 2024)
                                                <small class="text-success d-block">✓ Draft ready for finalization</small>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="examplePeriod" id="q2" disabled>
                                            <label class="form-check-label text-muted" for="q2">
                                                Q2 2024 (April - June 2024)
                                                <small class="text-muted d-block">No submission yet</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-calendar-alt me-2"></i>
                                You can only finalize periods that have draft submissions. 
                                Each reporting period can only be finalized once.
                            </div>
                            
                            <div class="action-note">
                                <strong>Your Task:</strong> Select the reporting period you want to finalize from the dropdown or radio buttons.
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Review & Finalize -->
                    <div class="tutorial-step" data-step="4">
                        <div class="step-header mb-3">
                            <div class="step-number">
                                <span class="badge bg-success rounded-circle">4</span>
                            </div>
                            <h4 class="step-title">Review & Finalize</h4>
                            <p class="step-subtitle text-muted">Final review before submission</p>
                        </div>
                        
                        <div class="step-content">
                            <h5>Before Finalizing, Verify:</h5>
                            <ul class="checklist">
                                <li><i class="fas fa-check-circle text-success me-2"></i>All required targets are filled</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Program rating is accurate</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Achievement data is complete</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Notes and comments are added</li>
                            </ul>
                            
                            <div class="tutorial-example mt-3">
                                <div class="example-box text-center">
                                    <button class="btn btn-success btn-lg">
                                        <i class="fas fa-check me-2"></i>
                                        Finalize Submission
                                    </button>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-lock me-2"></i>
                                <strong>Final Step:</strong> Once finalized, submissions cannot be edited. 
                                Make sure all information is accurate before proceeding.
                            </div>
                            
                            <div class="action-note">
                                <strong>Your Task:</strong> Click the "Finalize Submission" button to complete the process.
                            </div>
                        </div>
                    </div>

                    <!-- Completion Step -->
                    <div class="tutorial-step" data-step="5">
                        <div class="step-header mb-3 text-center">
                            <div class="step-number mb-3">
                                <span class="badge bg-success rounded-circle" style="font-size: 1.5rem; padding: 1rem;">
                                    <i class="fas fa-trophy"></i>
                                </span>
                            </div>
                            <h4 class="step-title text-success">Congratulations!</h4>
                            <p class="step-subtitle text-muted">You're ready to finalize submissions</p>
                        </div>
                        
                        <div class="step-content text-center">
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                You now understand the submission finalization process!
                            </div>
                            
                            <div class="next-steps mt-4">
                                <h5>Next Steps:</h5>
                                <p>Close this tutorial and look for the "Finalize Submission" button on your programs list to get started.</p>
                            </div>
                            
                            <div class="help-reminder mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-question-circle me-1"></i>
                                    Need help? You can always re-open this tutorial from the help menu.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-secondary" id="tutorialPrevBtn" disabled>
                        <i class="fas fa-chevron-left me-1"></i> Previous
                    </button>
                </div>
                
                <div class="step-indicator">
                    <span id="currentStepIndicator">Step 1 of 5</span>
                </div>
                
                <div>
                    <button type="button" class="btn btn-primary" id="tutorialNextBtn">
                        Next <i class="fas fa-chevron-right ms-1"></i>
                    </button>
                    <button type="button" class="btn btn-success" id="tutorialFinishBtn" style="display: none;" data-bs-dismiss="modal">
                        <i class="fas fa-check me-1"></i> Got It!
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Tutorial Modal Styles */
.tutorial-progress-bar {
    position: sticky;
    top: 0;
    z-index: 1000;
}

.tutorial-step {
    display: none;
    min-height: 400px;
}

.tutorial-step.active {
    display: block;
}

.step-header {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1rem;
}

.step-number {
    display: inline-block;
    margin-bottom: 0.5rem;
}

.step-number .badge {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.step-title {
    color: #2c5aa0;
    margin-bottom: 0.25rem;
}

.step-subtitle {
    font-size: 0.95rem;
}

.tutorial-example {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    border-left: 4px solid #007bff;
}

.example-box {
    background: white;
    border-radius: 6px;
    padding: 1rem;
    border: 1px solid #dee2e6;
}

.checklist {
    list-style: none;
    padding-left: 0;
}

.checklist li {
    padding: 0.25rem 0;
    display: flex;
    align-items: center;
}

.action-note {
    background: #e7f3ff;
    border: 1px solid #b8daff;
    border-radius: 6px;
    padding: 1rem;
    margin-top: 1rem;
}

.period-selector .form-check {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
}

.period-selector .form-check:hover {
    background: #f8f9fa;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.status-draft {
    background-color: #ffc107;
}

.next-steps {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.help-reminder {
    padding: 0.75rem;
    background: #e9ecef;
    border-radius: 6px;
}
</style>