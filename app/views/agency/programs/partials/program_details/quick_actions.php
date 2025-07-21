<?php
/**
 * Quick Actions Section
 * 
 * Displays action buttons for program owners/editors.
 */
?>

<?php if ($can_edit): ?>
<div class="card quick-actions-card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-bolt me-2"></i>Quick Actions
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <a href="<?php echo APP_URL; ?>/app/views/agency/programs/add_submission.php?program_id=<?php echo $program_id; ?>" class="btn btn-outline-success w-100">
                    <i class="fas fa-plus me-2"></i>Add New Submission
                </a>
                <small class="text-muted d-block mt-1">Create a new progress report for this program</small>
            </div>
            <div class="col-md-6 mb-3">
                <a href="<?php echo APP_URL; ?>/app/views/agency/programs/edit_program.php?id=<?php echo $program_id; ?>" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-edit me-2"></i>Edit Program Details
                </a>
                <small class="text-muted d-block mt-1">Modify program information and settings</small>
            </div>
            <div class="col-md-6 mb-3">
                <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#viewSubmissionModal">
                    <i class="fas fa-eye me-2"></i>View Submission
                </button>
                <small class="text-muted d-block mt-1">View the latest progress report for this program</small>
            </div>
            <div class="col-md-6 mb-3">
                <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#submitSubmissionModal">
                    <i class="fas fa-trash me-2"></i>Delete Submission
                </button>
                <small class="text-muted d-block mt-1">Delete a draft or latest progress report for this program</small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
