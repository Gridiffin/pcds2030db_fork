<?php
/**
 * Program Modals Partial
 * Contains all modal dialogs for program details page
 */
?>

<!-- Status History Modal -->
<div class="modal fade" id="statusHistoryModal" tabindex="-1" aria-labelledby="statusHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusHistoryModalLabel">Program Status History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="status-history-modal-body">
                <!-- Status history will be loaded here by JS -->
            </div>
        </div>
    </div>
</div>

<!-- Status Edit Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStatusModalLabel">Change Program Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="edit-status-modal-body">
                <!-- Status edit form will be loaded here by JS -->
            </div>
        </div>
    </div>
</div>

<!-- Select Submission Modal -->
<?php if ($has_submissions): ?>
<div class="modal fade" id="selectSubmissionModal" tabindex="-1" aria-labelledby="selectSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="selectSubmissionModalLabel">Select Submission to View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Choose which quarterly submission you want to view:</p>
                
                <?php if (!empty($submission_history['submissions'])): ?>
                    <div class="list-group">
                        <?php foreach ($submission_history['submissions'] as $submission): ?>
                            <div class="list-group-item list-group-item-action submission-option" 
                                 data-submission-id="<?php echo $submission['submission_id'] ?? ''; ?>"
                                 data-period-id="<?php echo $submission['reporting_period_id'] ?? $submission['period_id'] ?? ''; ?>"
                                 data-period-display="<?php echo htmlspecialchars($submission['period_display'] ?? ''); ?>"
                                 data-is-draft="<?php echo $submission['is_draft'] ? 'true' : 'false'; ?>"
                                 data-submission-date="<?php echo $submission['submission_date'] ?? ''; ?>"
                                 style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($submission['period_display'] ?? 'Unknown Period'); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo htmlspecialchars($submission['submitted_by_name'] ?? 'Unknown'); ?>
                                            <i class="fas fa-clock ms-2 me-1"></i>
                                            <?php echo htmlspecialchars($submission['formatted_date'] ?? ''); ?>
                                        </small>
                                    </div>
                                    <div>
                                        <span class="badge bg-<?php echo ($submission['is_draft'] ? 'warning' : 'success'); ?>">
                                            <?php echo $submission['is_draft'] ? 'Draft' : 'Finalized'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted p-4">
                        <i class="fas fa-folder-open fa-2x mb-2"></i>
                        <p>No submissions found for this program.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- View Submission Modal -->
<?php if ($has_submissions): ?>
<div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-labelledby="viewSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSubmissionModalLabel">Submission Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewSubmissionModalBody">
                <!-- Content will be loaded dynamically by JavaScript -->
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading submission details...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Delete Submission Modal -->
<?php if ($has_submissions && $can_edit): ?>
<div class="modal fade" id="deleteSubmissionModal" tabindex="-1" aria-labelledby="deleteSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSubmissionModalLabel">Delete Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                
                <p>Are you sure you want to delete the latest submission for this program?</p>
                
                <div class="submission-info bg-light p-3 rounded">
                    <h6>Submission Details:</h6>
                    <ul class="mb-0">
                        <li><strong>Period:</strong> <?php echo htmlspecialchars($latest_submission['period_display'] ?? 'Unknown'); ?></li>
                        <li><strong>Status:</strong> <?php echo $is_draft ? 'Draft' : 'Finalized'; ?></li>
                        <li><strong>Targets:</strong> <?php echo count($targets); ?> target(s)</li>
                        <li><strong>Submitted:</strong> <?php echo isset($latest_submission['submission_date']) ? date('M j, Y', strtotime($latest_submission['submission_date'])) : 'Not submitted'; ?></li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger delete-submission-btn" 
                        data-submission-id="<?php echo $latest_submission['submission_id'] ?? ''; ?>">
                    <i class="fas fa-trash me-2"></i>Delete Submission
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>