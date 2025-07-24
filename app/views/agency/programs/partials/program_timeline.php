<?php
/**
 * Program Timeline Partial
 * Displays submission timeline and history
 */
?>

<!-- Submission Timeline -->
<?php if (!empty($submission_history['submissions'])): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-history me-2"></i>Submission Timeline
            <i class="fas fa-info-circle text-muted ms-2" 
               data-bs-toggle="tooltip" 
               data-bs-placement="top" 
               title="Click on any timeline item to view full submission details"></i>
        </h5>
    </div>
    <div class="card-body">
        <div class="timeline-container">
            <div class="alert alert-info alert-sm mb-3">
                <i class="fas fa-lightbulb me-2"></i>
                <strong>Tip:</strong> Click on any timeline item below to view full submission details and expand the content.
            </div>
            <?php foreach ($submission_history['submissions'] as $submission): ?>
                <div class="timeline-item clickable-timeline-item" style="cursor: pointer;" 
                     data-bs-toggle="tooltip" 
                     data-bs-placement="right" 
                     title="Click to view full submission details">
                    <div class="timeline-marker">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h6 class="timeline-title"><?php echo htmlspecialchars($submission['period_display']); ?></h6>
                            <span class="badge bg-<?php echo ($submission['is_draft'] ? 'warning' : 'success'); ?>">
                                <?php echo $submission['is_draft_label']; ?>
                            </span>
                        </div>
                        <div class="timeline-meta">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($submission['submitted_by_name'] ?? 'Unknown'); ?>
                                <i class="fas fa-clock ms-2 me-1"></i>
                                <?php echo htmlspecialchars($submission['formatted_date']); ?>
                            </small>
                        </div>
                        <?php if ($can_edit): ?>
                            <div class="timeline-actions mt-2">
                                <a href="view_submissions.php?program_id=<?php echo $program['program_id']; ?>&period_id=<?php echo $submission['reporting_period_id'] ?? $submission['period_id'] ?? ''; ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                <?php if ($submission['is_draft'] ?? false): ?>
                                    <a href="edit_submission.php?program_id=<?php echo $program['program_id']; ?>&period_id=<?php echo $submission['reporting_period_id'] ?? $submission['period_id'] ?? ''; ?>" 
                                       class="btn btn-outline-warning btn-sm ms-1">
                                        <i class="fas fa-edit me-1"></i>Edit Draft
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Related Programs -->
<?php if (!empty($related_programs)): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-link me-2"></i>Related Programs
            <small class="text-muted ms-2">(Same Initiative)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($related_programs as $rel_prog): ?>
                <div class="col-md-6 mb-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($rel_prog['program_name']); ?></h6>
                                    <p class="card-text small text-muted mb-2">
                                        <?php echo htmlspecialchars($rel_prog['agency_name'] ?? 'Unknown Agency'); ?>
                                    </p>
                                    <?php if (!empty($rel_prog['program_number'])): ?>
                                        <span class="badge bg-info small"><?php echo htmlspecialchars($rel_prog['program_number']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="program_details.php?id=<?php echo (int)$rel_prog['program_id']; ?>" 
                                   class="btn btn-outline-primary btn-sm btn-icon ms-2" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>