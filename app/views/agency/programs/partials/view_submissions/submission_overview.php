<?php
/**
 * Submission Overview Card
 * Displays main submission information and metadata
 */
?>

<!-- Submission Overview Card -->
<div class="card submission-card submission-overview mb-4">
    <div class="card-header bg-success text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 text-white">
                <i class="fas fa-file-alt me-2"></i>
                Submission Overview
            </h5>
            <?php if ($submission['is_draft']): ?>
                <span class="badge bg-warning">
                    <i class="fas fa-pencil-alt me-1"></i>Draft
                </span>
            <?php else: ?>
                <span class="badge bg-success">
                    <i class="fas fa-check-circle me-1"></i>Submitted
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h6 class="text-primary mb-3"><?php echo htmlspecialchars($program['program_name']); ?></h6>
                
                <div class="submission-meta mb-3">
                    <div class="row">
                        <div class="col-6">
                            <strong>Reporting Period:</strong><br>
                            <span class="text-primary"><?php echo htmlspecialchars($submission['period_display']); ?></span>
                        </div>
                        <div class="col-6">
                            <strong>Status:</strong><br>
                            <span class="submission-status text-<?php echo $submission['is_draft'] ? 'warning' : 'success'; ?>">
                                <?php echo $submission['is_draft'] ? 'Draft' : 'Submitted'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($submission['submitted_by_name'])): ?>
                    <div class="submission-meta mb-3">
                        <div class="row">
                            <div class="col-6">
                                <strong>Submitted By:</strong><br>
                                <?php echo htmlspecialchars($submission['submitted_by_fullname'] ?: $submission['submitted_by_name']); ?>
                            </div>
                            <div class="col-6">
                                <strong>Last Updated:</strong><br>
                                <?php echo date('M j, Y g:i A', strtotime($submission['updated_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($submission['remarks'])): ?>
                    <div class="mt-3">
                        <strong>Remarks:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($submission['remarks'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="text-center">
                    <!-- Submission Status Indicator -->
                    <div class="mb-3">
                        <?php if ($submission['is_draft']): ?>
                            <i class="fas fa-edit fa-3x text-warning mb-2"></i>
                            <p class="text-muted small">This submission is still in draft mode</p>
                        <?php else: ?>
                            <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                            <p class="text-muted small">This submission has been submitted for review</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Actions -->
                    <?php if ($can_edit): ?>
                        <div class="submission-actions d-flex flex-column gap-2">
                            <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" 
                               class="btn btn-outline-primary btn-sm" data-action="edit-submission">
                                <i class="fas fa-edit me-2"></i>Edit Submission
                            </a>
                            
                            <?php if ($submission['is_draft']): ?>
                                <button type="button" 
                                        class="btn btn-success btn-sm" 
                                        data-action="submit-submission"
                                        data-submission-id="<?php echo $submission['submission_id']; ?>">
                                    <i class="fas fa-paper-plane me-2"></i>Submit for Review
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
