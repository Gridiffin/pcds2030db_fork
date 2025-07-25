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
                    Quick Finalize Submissions
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
                    <!-- Instructions -->
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Quick Finalize Process:</strong> Select the programs and reporting periods you want to finalize, then click "Finalize Selected" to complete the process.
                    </div>

                    <!-- Program Selection -->
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-3">
                                <i class="fas fa-list me-2"></i>
                                Available Draft Submissions
                            </h6>
                            
                            <!-- Programs List Container -->
                            <div id="finalizeProgramsList" class="finalize-programs-container">
                                <!-- Content will be loaded via JavaScript -->
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Selection Summary -->
                            <div class="selection-summary-card">
                                <h6 class="mb-3">
                                    <i class="fas fa-clipboard-check me-2"></i>
                                    Finalization Summary
                                </h6>
                                
                                <div id="selectionSummary">
                                    <div class="text-muted text-center py-4">
                                        <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                                        <p>Select programs to see summary</p>
                                    </div>
                                </div>
                                
                                <!-- Finalize Button -->
                                <div class="mt-3">
                                    <button type="button" class="btn btn-success w-100" id="finalizeSelectedBtn" disabled>
                                        <i class="fas fa-check-circle me-2"></i>
                                        Finalize Selected (<span id="selectedCount">0</span>)
                                    </button>
                                </div>
                            </div>
                        </div>
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
                    
                    <div>
                        <button type="button" class="btn btn-info me-2" id="viewDetailsBtn" disabled title="View submission details for selected program">
                            <i class="fas fa-file-alt me-1"></i> View Details
                        </button>
                        
                        <button type="button" class="btn btn-success" id="finalizeConfirmBtn" style="display: none;">
                            <i class="fas fa-check me-1"></i> Confirm Finalization
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Quick Finalize Modal Styles */
.finalize-programs-container {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #f8f9fa;
}

.finalize-program-item {
    background: white;
    border-bottom: 1px solid #e9ecef;
    padding: 1rem;
    transition: all 0.2s ease;
}

.finalize-program-item:last-child {
    border-bottom: none;
}

.finalize-program-item:hover {
    background: #f8f9fa;
}

.finalize-program-item.selected {
    background: #e7f3ff;
    border-left: 4px solid #007bff;
}

.program-checkbox {
    margin-right: 0.75rem;
}

.program-info h6 {
    margin-bottom: 0.25rem;
    color: #2c5aa0;
}

.program-meta {
    font-size: 0.875rem;
    color: #6c757d;
}

.period-selection {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

.period-option {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.period-option:last-child {
    margin-bottom: 0;
}

.period-option input[type="checkbox"] {
    margin-right: 0.5rem;
}

.selection-summary-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.25rem;
    position: sticky;
    top: 20px;
}

.summary-item {
    display: flex;
    justify-content-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-program-name {
    font-weight: 500;
    color: #2c5aa0;
}

.summary-period {
    font-size: 0.875rem;
    color: #6c757d;
}

.finalize-results-item {
    display: flex;
    align-items-center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.finalize-results-item:last-child {
    border-bottom: none;
}

.result-status.success {
    color: #198754;
}

.result-status.error {
    color: #dc3545;
}
</style>

<script>
// Quick Finalize Modal JavaScript will be added to finalization-tutorial.js
</script>