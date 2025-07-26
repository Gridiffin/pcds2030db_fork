<?php
/**
 * Simple Finalize Modal - Brand New Implementation
 * Clean, working finalization modal for single programs
 */
?>

<!-- Simple Finalize Modal -->
<div class="modal fade" id="simpleFinalizeModal" tabindex="-1" aria-labelledby="simpleFinalizeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="simpleFinalizeModalLabel">Finalize Program Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Loading State -->
                <div id="simpleLoadingState" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading program details...</p>
                </div>
                
                <!-- Error State -->
                <div id="simpleErrorState" class="alert alert-danger d-none">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="simpleErrorMessage">An error occurred.</span>
                </div>
                
                <!-- Main Content -->
                <div id="simpleMainContent" class="d-none">
                    <!-- Program Info -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading mb-2">
                            <i class="fas fa-info-circle me-2"></i>
                            Program Information
                        </h6>
                        <p class="mb-0" id="simpleProgramInfo">Loading...</p>
                    </div>
                    
                    <!-- Step 1: Select Period -->
                    <div id="simpleStep1" class="step-content">
                        <h6 class="mb-3">Step 1: Select Reporting Period</h6>
                        <div id="simplePeriodsContainer">
                            <!-- Periods will be loaded here -->
                        </div>
                    </div>
                    
                    <!-- Step 2: Confirm Finalization -->
                    <div id="simpleStep2" class="step-content d-none">
                        <h6 class="mb-3">Step 2: Confirm Finalization</h6>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> Once finalized, this submission cannot be edited. Please review carefully.
                        </div>
                        
                        <div id="simpleSubmissionPreview">
                            <!-- Submission details will be shown here -->
                        </div>
                    </div>
                    
                    <!-- Success State -->
                    <div id="simpleSuccessState" class="text-center py-4 d-none">
                        <div class="text-success mb-3">
                            <i class="fas fa-check-circle" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-success">Submission Finalized Successfully!</h5>
                        <p class="text-muted">The program submission has been finalized and can no longer be edited.</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="simpleBackBtn" class="btn btn-outline-primary d-none" onclick="simpleGoToStep1()">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </button>
                <button type="button" id="simpleNextBtn" class="btn btn-primary d-none" onclick="simpleGoToStep2()">
                    Next <i class="fas fa-arrow-right ms-1"></i>
                </button>
                <button type="button" id="simpleFinalizeBtn" class="btn btn-success d-none" onclick="simpleFinalize()">
                    <i class="fas fa-check me-1"></i> Finalize Submission
                </button>
                <button type="button" id="simpleCloseBtn" class="btn btn-primary d-none" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Simple Modal Styles */
#simpleFinalizeModal {
    z-index: 2147483647 !important;
}

#simpleFinalizeModal .modal-backdrop {
    z-index: 2147483646 !important;
}

#simpleFinalizeModal .modal-dialog {
    z-index: 2147483647 !important;
}

.simple-period-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.simple-period-item:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
}

.simple-period-item.selected {
    background-color: #e7f3ff;
    border-color: #007bff;
    border-width: 2px;
}

.simple-period-info h6 {
    margin-bottom: 0.5rem;
    color: #2c5aa0;
}

.simple-period-meta {
    font-size: 0.875rem;
    color: #6c757d;
}

.step-content {
    min-height: 200px;
}
</style>