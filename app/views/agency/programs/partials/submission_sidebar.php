<?php
/**
 * Submission Sidebar Partial
 * Displays program summary, period info, and actions
 * 
 * Expected variables:
 * - $attachments (from parent view)
 * - $program, $period, $submission, $rating_info
 * - $can_edit, $program_id, $period_id
 */
?>

<!-- Program Attachments (if any) -->
<?php if (!empty($attachments)): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-paperclip me-2 text-success"></i>
                Program Attachments
            </h5>
            <?php if ($can_edit && (!isset($is_finalize_mode) || !$is_finalize_mode)): ?>
                <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" 
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>Edit Submission
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($attachments as $attachment): ?>
                <div class="col-md-12 mb-3">
                    <div class="d-flex align-items-center p-2 border rounded">
                        <i class="fas fa-file text-primary me-2"></i>
                        <div class="flex-grow-1">
                            <div class="fw-medium"><?php echo htmlspecialchars($attachment['file_name'] ?? 'Unknown file'); ?></div>
                            <small class="text-muted">
                                Uploaded: <?php echo !empty($attachment['uploaded_at']) ? date('M j, Y', strtotime($attachment['uploaded_at'])) : 'Unknown date'; ?>
                                <?php if (!empty($attachment['uploaded_by'])): ?>
                                    <br>By: <span class="fw-medium"><?php echo htmlspecialchars($attachment['uploaded_by']); ?></span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <a href="<?php echo htmlspecialchars($attachment['file_path'] ?? '#'); ?>" 
                           class="btn btn-sm btn-outline-primary ms-2" target="_blank">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Program Summary Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-info-circle me-2"></i>Program Summary
        </h6>
    </div>
    <div class="card-body">
        <dl class="row mb-0 small">
            <dt class="col-5">Program:</dt>
            <dd class="col-7"><?php echo htmlspecialchars($program['program_name']); ?></dd>
            
            <dt class="col-5">Number:</dt>
            <dd class="col-7">
                <?php if (!empty($program['program_number'])): ?>
                    <span class="badge bg-info"><?php echo htmlspecialchars($program['program_number']); ?></span>
                <?php else: ?>
                    <span class="text-muted">Not assigned</span>
                <?php endif; ?>
            </dd>
            
            <dt class="col-5">Agency:</dt>
            <dd class="col-7"><?php echo htmlspecialchars($program['agency_name'] ?? 'Unknown'); ?></dd>
            
            <dt class="col-5">Initiative:</dt>
            <dd class="col-7">
                <?php if (!empty($program['initiative_name'])): ?>
                    <?php echo htmlspecialchars($program['initiative_name']); ?>
                    <?php if (!empty($program['initiative_number'])): ?>
                        <br><span class="badge bg-secondary mt-1"><?php echo htmlspecialchars($program['initiative_number']); ?></span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="text-muted">Not linked</span>
                <?php endif; ?>
            </dd>
            
            <dt class="col-5">Rating:</dt>
            <dd class="col-7">
                <span class="badge" style="background-color: <?php echo $rating_info['color']; ?>; color: white;">
                    <?php echo $rating_info['label']; ?>
                </span>
            </dd>
        </dl>
    </div>
</div>

<!-- Reporting Period Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-calendar-alt me-2"></i>Reporting Period
        </h6>
    </div>
    <div class="card-body">
        <div class="text-center">
            <div class="display-6 text-primary mb-2">
                <?php echo htmlspecialchars($submission['period_display']); ?>
            </div>
            <div class="text-muted small">
                <?php echo date('F j, Y', strtotime($period['start_date'])); ?> - 
                <?php echo date('F j, Y', strtotime($period['end_date'])); ?>
            </div>
            <div class="mt-2">
                <span class="badge bg-<?php echo $period['status'] === 'open' ? 'success' : 'secondary'; ?> px-3 py-2">
                    Period <?php echo ucfirst($period['status']); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php if (!isset($is_finalize_mode) || !$is_finalize_mode): ?>
<!-- Actions Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-cogs me-2"></i>Actions
        </h6>
    </div>
    <div class="card-body">
        <div class="d-grid gap-2">
            <?php if ($can_edit): ?>
                <!-- Edit Submission -->
                <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Edit Submission
                </a>
                
                <!-- Submit for Review (if draft) -->
                <?php if ($submission['is_draft'] && !$submission['is_submitted']): ?>
                    <button type="button" class="btn btn-success" 
                            onclick="submitSubmission(<?php echo $submission['submission_id']; ?>)">
                        <i class="fas fa-paper-plane me-2"></i>Submit for Review
                    </button>
                <?php endif; ?>
                
                <!-- Add New Submission -->
                <a href="add_submission.php?program_id=<?php echo $program_id; ?>" 
                   class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>Add New Submission
                </a>
            <?php endif; ?>
            
            <!-- View Program Details -->
            <a href="program_details.php?id=<?php echo $program_id; ?>" 
               class="btn btn-outline-secondary">
                <i class="fas fa-chart-line me-2"></i>View Program Details
            </a>
            
            <!-- Back to Programs -->
            <a href="view_programs.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Programs
            </a>
        </div>
    </div>
</div>
<?php endif; ?>