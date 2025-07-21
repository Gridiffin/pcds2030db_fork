<?php
/**
 * Reporting Period Information Sidebar Card
 * Displays period details and status
 */
?>

<!-- Reporting Period Card -->
<div class="card sidebar-card">
    <div class="card-header bg-success text-white">
        <h6 class="card-title text-white">
            <i class="fas fa-calendar-alt me-2"></i>Reporting Period
        </h6>
    </div>
    <div class="card-body">
        <div class="period-display">
            <div class="display-6 text-primary mb-2">
                <?php echo htmlspecialchars($submission['period_display']); ?>
            </div>
            <div class="period-meta">
                <?php echo ucfirst($submission['period_type']); ?> Period 
                <?php echo $submission['period_number']; ?> of <?php echo $submission['year']; ?>
            </div>
            <div class="period-status">
                <span class="badge bg-<?php echo $submission['period_status'] === 'open' ? 'success' : 'secondary'; ?>">
                    <i class="fas fa-<?php echo $submission['period_status'] === 'open' ? 'unlock' : 'lock'; ?> me-1"></i>
                    <?php echo ucfirst($submission['period_status']); ?>
                </span>
            </div>
        </div>
        
        <?php if ($submission['period_status'] === 'closed'): ?>
            <div class="alert alert-info alert-sm mt-3 mb-0">
                <i class="fas fa-info-circle me-2"></i>
                <small>This reporting period is now closed. No further edits are allowed.</small>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($submission['created_at'])): ?>
            <div class="mt-3 pt-3 border-top">
                <small class="text-muted">
                    <strong>Submission Created:</strong><br>
                    <?php echo date('M j, Y g:i A', strtotime($submission['created_at'])); ?>
                </small>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($submission['submitted_at']) && !$submission['is_draft']): ?>
            <div class="mt-2">
                <small class="text-muted">
                    <strong>Submitted:</strong><br>
                    <?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?>
                </small>
            </div>
        <?php endif; ?>
    </div>
</div>
