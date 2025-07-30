<?php
/**
 * Program Actions Partial
 * Quick action buttons for program management
 */
?>

<!-- Quick Actions Section -->
<div class="card-modern card-elevated-modern quick-actions-card mb-4">
    <div class="card-header-modern">
        <h5 class="card-title-modern">
            <div class="card-icon-modern text-forest-deep">
                <i class="fas fa-bolt"></i>
            </div>
            Quick Actions
        </h5>
    </div>
    <div class="card-body-modern">
        <div class="row">
            <?php if ($is_draft || !$has_submissions): ?>
            <div class="col-md-6 mb-3">
                <a href="<?php echo APP_URL; ?>/app/views/agency/programs/add_submission.php?program_id=<?php echo $program['program_id']; ?>" 
                   class="btn-modern btn-outline-success-modern w-100">
                    <i class="fas fa-plus me-2"></i>Add New Submission
                </a>
                <small class="text-muted d-block mt-1">Create a new progress report for this program</small>
            </div>
            <div class="col-md-6 mb-3">
                <a href="<?php echo APP_URL; ?>/app/views/agency/programs/edit_program.php?id=<?php echo $program['program_id']; ?>" 
                   class="btn-modern btn-outline-secondary-modern w-100">
                    <i class="fas fa-edit me-2"></i>Edit Program Details
                </a>
                <small class="text-muted d-block mt-1">Modify program information and settings</small>
            </div>
            <?php endif; ?>
            <?php if ($has_submissions): ?>
            <div class="col-md-6 mb-3">
                <button type="button" class="btn-modern btn-outline-info-modern w-100" data-bs-toggle="modal" data-bs-target="#selectSubmissionModal">
                    <i class="fas fa-eye me-2"></i>View Submission
                </button>
                <small class="text-muted d-block mt-1">Select and view a progress report by quarter</small>
            </div>
            <div class="col-md-6 mb-3">
                <button type="button" class="btn-modern btn-outline-danger-modern w-100" data-bs-toggle="modal" data-bs-target="#deleteProgramModal">
                    <i class="fas fa-trash me-2"></i>Delete Program
                </button>
                <small class="text-muted d-block mt-1">Permanently delete this program and all its data</small>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>