<?php
/**
 * Submission Selection Modal
 * Allows users to choose which reporting period submission to view
 */
?>

<!-- Submission Selection Modal -->
<div class="modal fade" id="submissionSelectionModal" tabindex="-1" aria-labelledby="submissionSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submissionSelectionModalLabel">
                    <i class="fas fa-eye me-2"></i>Select Submission to View
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Loading State -->
                <div id="submissionSelectionLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading available submissions...</p>
                </div>

                <!-- Error State -->
                <div id="submissionSelectionError" class="alert alert-danger d-none">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="submissionSelectionErrorMessage">Unable to load submissions.</span>
                </div>

                <!-- Submissions List -->
                <div id="submissionSelectionList" class="d-none">
                    <p class="text-muted mb-3">Choose a reporting period to view its submission details:</p>
                    <div class="list-group" id="submissionItems">
                        <!-- Submission items will be populated here -->
                    </div>
                </div>

                <!-- No Submissions State -->
                <div id="submissionSelectionEmpty" class="text-center py-4 d-none">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Submissions Found</h6>
                    <p class="text-muted mb-0">This program doesn't have any submissions yet.</p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Submission Selection Modal Styles */
#submissionSelectionModal {
    z-index: 9999 !important; /* Ensure it appears above program rows and other elements */
}

.submission-item {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.submission-item:hover {
    background-color: #f8f9fa;
}

.submission-item .submission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.25rem;
}

.submission-period {
    font-weight: 500;
    color: #2c5aa0;
}

.submission-status {
    font-size: 0.875rem;
}

.submission-meta {
    font-size: 0.875rem;
    color: #6c757d;
}

.submission-item:hover .submission-period {
    color: #1e3a5f;
}

.list-group-item.submission-item {
    border-left: 4px solid transparent;
}

.list-group-item.submission-item:hover {
    border-left-color: #007bff;
}
</style>

<script>
// Submission Selection Modal JavaScript will be added to finalization-tutorial.js
</script>