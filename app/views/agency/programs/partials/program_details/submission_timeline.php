<?php
/**
 * Submission Timeline
 * 
 * Displays chronological history of program submissions.
 */
?>

<?php if (!empty($submission_history['submissions'])): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-history me-2"></i>Submission Timeline
        </h5>
    </div>
    <div class="card-body">
        <div class="timeline-container">
            <?php foreach ($submission_history['submissions'] as $submission): ?>
                <div class="timeline-item">
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
                        <?php if ($is_owner): ?>
                            <div class="timeline-actions mt-2">
                                <a href="<?php echo APP_URL; ?>/app/views/agency/programs/view_submissions.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $submission['period_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
