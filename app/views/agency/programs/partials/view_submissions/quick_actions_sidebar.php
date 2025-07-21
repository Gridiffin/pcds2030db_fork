<?php
/**
 * Quick Actions Sidebar Card
 * Displays action buttons for the submission
 */
?>

<!-- Quick Actions Card -->
<div class="card sidebar-card">
    <div class="card-header bg-success text-white">
        <h6 class="card-title text-white">
            <i class="fas fa-bolt me-2"></i>Quick Actions
        </h6>
    </div>
    <div class="card-body quick-actions">
        <div class="d-grid gap-2">
            <?php if ($can_edit): ?>
                <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" 
                   class="btn btn-primary action-button"
                   data-action="edit-submission">
                    <i class="fas fa-edit me-2"></i>Edit This Submission
                </a>
                
                <?php if ($submission['is_draft'] && $submission['period_status'] === 'open'): ?>
                    <button type="button" 
                            class="btn btn-success action-button" 
                            data-action="submit-submission"
                            data-submission-id="<?php echo $submission['submission_id']; ?>">
                        <i class="fas fa-paper-plane me-2"></i>Submit for Review
                    </button>
                <?php endif; ?>
                
                <a href="add_submission.php?program_id=<?php echo $program_id; ?>" 
                   class="btn btn-outline-primary action-button"
                   data-action="add-submission">
                    <i class="fas fa-plus me-2"></i>Add New Submission
                </a>
            <?php else: ?>
                <!-- No edit access - show view-only message -->
                <div class="alert alert-info alert-sm">
                    <i class="fas fa-eye me-2"></i>
                    <small>You have view-only access to this submission.</small>
                </div>
            <?php endif; ?>
            
            <a href="program_details.php?id=<?php echo $program_id; ?>" 
               class="btn btn-outline-secondary action-button">
                <i class="fas fa-chart-line me-2"></i>View Program Details
            </a>
            
            <a href="view_programs.php" 
               class="btn btn-outline-info action-button">
                <i class="fas fa-list me-2"></i>All Programs
            </a>
            
            <?php if (!empty($program['initiative_id'])): ?>
                <a href="../initiatives/view_initiative.php?id=<?php echo $program['initiative_id']; ?>" 
                   class="btn btn-outline-warning action-button">
                    <i class="fas fa-sitemap me-2"></i>View Initiative
                </a>
            <?php endif; ?>
        </div>
        
        <?php if ($submission['is_draft'] && $can_edit): ?>
            <div class="submit-confirmation mt-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle icon"></i>
                    <div>
                        <small><strong>Draft Status</strong></small><br>
                        <small class="text-muted">Remember to submit this submission when ready for review.</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
