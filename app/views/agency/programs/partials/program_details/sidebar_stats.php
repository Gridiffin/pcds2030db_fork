<?php
/**
 * Sidebar Statistics
 * 
 * Displays key statistics about the program.
 */
?>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-chart-pie me-2"></i>Statistics
        </h6>
    </div>
    <div class="card-body">
        <div class="stat-item d-flex justify-content-between align-items-center mb-3">
            <span>Total Submissions</span>
            <span class="badge bg-primary"><?php echo count($submission_history['submissions']); ?></span>
        </div>
        <div class="stat-item d-flex justify-content-between align-items-center mb-3">
            <span>Targets</span>
            <span class="badge bg-info"><?php echo count($targets); ?></span>
        </div>
        <div class="stat-item d-flex justify-content-between align-items-center mb-3">
            <span>Attachments</span>
            <span class="badge bg-secondary"><?php echo count($program_attachments); ?></span>
        </div>
        <div class="stat-item d-flex justify-content-between align-items-center">
            <span>Last Activity</span>
            <small class="text-muted" id="last-activity-value">
                <?php if ($has_submissions && isset($latest_submission['submission_date'])): ?>
                    <?php echo date('M j', strtotime($latest_submission['submission_date'])); ?>
                <?php else: ?>
                    Never
                <?php endif; ?>
            </small>
        </div>
    </div>
</div>
