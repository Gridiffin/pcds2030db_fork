<?php
/**
 * Program Details Modals
 * 
 * Contains all modal dialogs used in the program details page.
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

<!-- Status Edit Modal (for owner/focal) -->
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

<!-- View Submissions Modal -->
<div class="modal fade" id="viewSubmissionsModal" tabindex="-1" aria-labelledby="viewSubmissionsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewSubmissionsModalLabel"><i class="fas fa-list-alt me-2"></i>Submissions by Reporting Period</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($latest_by_period)): ?>
          <div class="list-group">
            <?php foreach ($latest_by_period as $period_id => $submission): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <div class="fw-medium">
                    <i class="fas fa-calendar-alt me-1 text-primary"></i>
                    <?php echo htmlspecialchars($submission['period_display'] ?? 'Unknown Period'); ?>
                  </div>
                  <div class="small text-muted">
                    Submitted: <?php echo !empty($submission['submission_date']) ? date('M j, Y', strtotime($submission['submission_date'])) : 'N/A'; ?>
                    <?php if (!empty($submission['submitted_by_name'])): ?>
                      &bull; By <?php echo htmlspecialchars($submission['submitted_by_name']); ?>
                    <?php endif; ?>
                  </div>
                </div>
                <a href="<?php echo APP_URL; ?>/app/views/agency/programs/view_submissions.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" class="btn btn-sm btn-outline-primary ms-2" title="View Submission">
                  <i class="fas fa-eye"></i> View
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center text-muted py-4">
            <i class="fas fa-folder-open fa-2x mb-2"></i>
            <div>No submissions found for this program.</div>
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- View Submission Modal -->
<?php if ($has_submissions && !empty($submission_history['submissions'])): ?>
<div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-labelledby="viewSubmissionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewSubmissionModalLabel"><i class="fas fa-eye me-2"></i>Select Submission to View</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="list-group">
          <?php foreach ($submission_history['submissions'] as $submission): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong><?php echo htmlspecialchars($submission['period_display']); ?></strong>
                <span class="badge bg-<?php echo ($submission['is_draft'] ? 'warning' : 'success'); ?> ms-2">
                  <?php echo $submission['is_draft_label']; ?>
                </span>
                <br>
                <small class="text-muted">
                  Submitted by: <?php echo htmlspecialchars($submission['submitted_by_name'] ?? 'Unknown'); ?>
                  &nbsp;|&nbsp;
                  <?php echo htmlspecialchars($submission['formatted_date']); ?>
                </small>
              </div>
              <a href="<?php echo APP_URL; ?>/app/views/agency/programs/view_submissions.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $submission['period_id']; ?>" class="btn btn-outline-primary" title="View Submission">
                <i class="fas fa-eye"></i> View Submission
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Submit Submission Modal -->
<div class="modal fade" id="submitSubmissionModal" tabindex="-1" aria-labelledby="submitSubmissionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="submitSubmissionModalLabel"><i class="fas fa-trash me-2"></i>Select Draft Submission to Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($draft_submissions)): ?>
          <div class="list-group">
            <?php foreach ($draft_submissions as $submission): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong><?php echo htmlspecialchars($submission['period_display']); ?></strong>
                  <span class="badge bg-warning ms-2">
                    <?php echo $submission['is_draft_label']; ?>
                  </span>
                  <br>
                  <small class="text-muted">
                    Saved by: <?php echo htmlspecialchars($submission['submitted_by_name'] ?? 'Unknown'); ?>
                    &nbsp;|&nbsp;
                    <?php echo htmlspecialchars($submission['formatted_date']); ?>
                  </small>
                </div>
                <button class="btn btn-danger delete-submission-btn" data-submission-id="<?php echo $submission['submission_id']; ?>">
                  <i class="fas fa-trash"></i> Delete Submission
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center text-muted py-4">
            <i class="fas fa-folder-open fa-2x mb-2"></i>
            <div>No draft submissions available for this program.</div>
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
