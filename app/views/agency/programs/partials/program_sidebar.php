<?php
/**
 * Program Sidebar Partial
 * Displays program statistics, attachments, and quick info
 */
?>

<!-- Program Statistics -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-chart-bar me-2"></i>Program Statistics
        </h6>
    </div>
    <div class="card-body">
        <div class="stat-item d-flex justify-content-between align-items-center mb-3">
            <div>
                <i class="fas fa-file-alt text-primary me-2"></i>
                <span class="stat-label">Total Submissions</span>
            </div>
            <span class="badge bg-primary"><?php echo count($submission_history['submissions'] ?? []); ?></span>
        </div>
        
        <div class="stat-item d-flex justify-content-between align-items-center mb-3">
            <div>
                <i class="fas fa-bullseye text-success me-2"></i>
                <span class="stat-label">Active Targets</span>
            </div>
            <span class="badge bg-success"><?php echo count($targets); ?></span>
        </div>
        
        <div class="stat-item d-flex justify-content-between align-items-center mb-3">
            <div>
                <i class="fas fa-paperclip text-info me-2"></i>
                <span class="stat-label">Attachments</span>
            </div>
            <span class="badge bg-info"><?php echo count($attachments); ?></span>
        </div>
        
        <div class="stat-item d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-clock text-secondary me-2"></i>
                <span class="stat-label">Last Activity</span>
            </div>
            <span class="text-muted small" id="last-activity-value">
                <?php 
                if ($has_submissions && isset($latest_submission['submission_date'])) {
                    echo date('M j, Y', strtotime($latest_submission['submission_date']));
                } else {
                    echo 'Never';
                }
                ?>
            </span>
        </div>
    </div>
</div>

<!-- Program Attachments -->
<?php if (!empty($attachments)): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-paperclip me-2"></i>Attachments
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php foreach ($attachments as $attachment): ?>
                <div class="list-group-item attachment-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?php echo htmlspecialchars($attachment['original_filename']); ?></h6>
                            <p class="mb-1 text-muted small">
                                <?php echo formatFileSize($attachment['file_size'] ?? 0); ?>
                                <span class="mx-1">•</span>
                                <?php echo date('M j, Y', strtotime($attachment['uploaded_at'])); ?>
                            </p>
                        </div>
                        <div class="attachment-actions">
                            <a href="<?php echo APP_URL; ?>/download.php?type=program_attachment&id=<?php echo $attachment['attachment_id']; ?>" 
                               class="btn btn-outline-primary btn-sm" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Program Status Actions -->
<?php if ($can_edit): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-cog me-2"></i>Status Management
        </h6>
    </div>
    <div class="card-body">
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" id="edit-status-btn">
                <i class="fas fa-edit me-2"></i>Change Status
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="view-status-history-btn">
                <i class="fas fa-history me-2"></i>View History
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Info -->
<div class="card shadow-sm">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-info-circle me-2"></i>Quick Info
        </h6>
    </div>
    <div class="card-body">
        <div class="info-item mb-3">
            <div class="info-label text-muted small">Created</div>
            <div class="info-value">
                <?php echo isset($program['created_at']) ? date('M j, Y', strtotime($program['created_at'])) : 'Unknown'; ?>
            </div>
        </div>
        
        <div class="info-item mb-3">
            <div class="info-label text-muted small">Agency</div>
            <div class="info-value">
                <?php echo htmlspecialchars($program['agency_name'] ?? 'Unknown'); ?>
            </div>
        </div>
        
        <?php if (!empty($program['sector_name'])): ?>
        <div class="info-item mb-3">
            <div class="info-label text-muted small">Sector</div>
            <div class="info-value">
                <?php echo htmlspecialchars($program['sector_name']); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="info-item">
            <div class="info-label text-muted small">Program ID</div>
            <div class="info-value">
                <code><?php echo $program['program_id']; ?></code>
            </div>
        </div>
    </div>
</div>