<?php
/**
 * Quick Finalize Modal
 * Allows focal users to quickly finalize multiple program submissions
 */

// Only show to focal users
if (!is_focal_user()) {
    return;
}
?>

<!-- Quick Finalize Modal -->
<div class="modal fade" id="quickFinalizeModal" tabindex="-1" aria-labelledby="quickFinalizeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="quickFinalizeModalLabel">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="modalTitleText">Finalize Submission</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Loading State -->
                <div id="finalizeLoadingState" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading draft submissions...</p>
                </div>

                <!-- Error State -->
                <div id="finalizeErrorState" class="alert alert-danger d-none">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="finalizeErrorMessage">An error occurred while loading submissions.</span>
                </div>

                <!-- Main Content -->
                <div id="finalizeMainContent" class="d-none">
                    <!-- Step 1: Program Info and Period Selection -->
                    <div id="step1Content">
                        <!-- Program Information -->
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Step 1:</strong> Choose a reporting period to review and finalize your submission.
                        </div>

                        <div class="program-info-card mb-4">
                            <h6 class="mb-3">
                                <i class="fas fa-folder me-2"></i>
                                Program: <span id="selectedProgramName"></span>
                            </h6>
                        </div>

                        <!-- Available Draft Periods -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="mb-3">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Available Draft Reporting Periods
                                </h6>
                                
                                <!-- Periods List Container -->
                                <div id="availablePeriodsList" class="periods-container">
                                    <!-- Content will be loaded via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Submission Review (will be shown after period selection) -->
                    <div id="step2Content" class="d-none">
                        <!-- Step 2 content will be dynamically loaded -->
                    </div>
                </div>

                <!-- Success State -->
                <div id="finalizeSuccessState" class="d-none text-center py-4">
                    <div class="text-success mb-3">
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                    <h5 class="text-success">Submissions Finalized Successfully!</h5>
                    <p class="text-muted">Your selected submissions have been finalized and are now available for review.</p>
                    
                    <div id="finalizeResults" class="mt-3">
                        <!-- Results will be populated here -->
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="w-100 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    
                    <div id="modalFooterActions">
                        <!-- Step 1 Actions -->
                        <div id="step1Actions">
                            <button type="button" class="btn btn-primary" id="reviewSubmissionBtn" disabled>
                                <i class="fas fa-eye me-1"></i> Review Submission
                            </button>
                        </div>
                        
                        <!-- Step 2 Actions -->
                        <div id="step2Actions" class="d-none">
                            <button type="button" class="btn btn-outline-primary me-2" id="editSubmissionBtn">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                            <button type="button" class="btn btn-success" id="confirmFinalizeBtn">
                                <i class="fas fa-check me-1"></i> Confirm & Submit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Quick Finalize Modal JavaScript will be added to finalization-tutorial.js
</script>