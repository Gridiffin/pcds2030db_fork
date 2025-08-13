<?php
/**
 * Admin Edit Submission Content
 * Main content for admin edit submission page
 */
?>

<main>
    <!-- Save Actions Hero -->
    <div class="hero-section bg-light border-bottom mb-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-1 text-primary">
                        <i class="fas fa-edit me-2"></i><?php echo $is_new_submission ? 'Creating New Submission' : 'Editing Submission'; ?>
                    </h6>
                    <p class="mb-0 text-muted">
                        <?php if (!empty($program['program_number'])): ?>
                            <?php echo htmlspecialchars($program['program_number']); ?> - 
                        <?php endif; ?>
                        <?php echo htmlspecialchars($program['program_name']); ?> | <?php echo htmlspecialchars($period['period_display']); ?>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="view_submissions.php?program_id=<?php echo $program_id; ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <button type="submit" form="editSubmissionForm" class="btn btn-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $is_new_submission ? 'Create Submission' : 'Update Submission'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Alert for new submission -->
        <?php if ($is_new_submission): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div class="alert-icon me-3">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h5 class="alert-heading">New Submission</h5>
                    <p class="mb-0">You are creating a new submission for this reporting period. This submission will be marked as finalized upon saving.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <form id="editSubmissionForm" method="POST" action="<?php echo APP_URL; ?>/app/handlers/admin/save_submission.php" enctype="multipart/form-data">
            <input type="hidden" name="program_id" value="<?php echo $program_id; ?>">
            <input type="hidden" name="period_id" value="<?php echo $period_id; ?>">
            <input type="hidden" name="submission_id" value="<?php echo $submission['submission_id']; ?>">
            <input type="hidden" name="is_new_submission" value="<?php echo $is_new_submission ? '1' : '0'; ?>">

            <div class="row">
                <!-- Main Form Column -->
                <div class="col-lg-8">
                    <!-- Program & Period Info -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title m-0">
                                <i class="fas fa-info-circle me-2"></i>Submission Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Program</label>
                                    <div class="fw-medium">
                                        <?php if (!empty($program['program_number'])): ?>
                                            <span class="badge bg-info me-2"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Agency</label>
                                    <div class="fw-medium">
                                        <i class="fas fa-building me-2 text-primary"></i>
                                        <?php echo htmlspecialchars($agency_info['agency_name']); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Reporting Period</label>
                                    <div class="fw-medium">
                                        <span class="badge bg-info fs-6"><?php echo htmlspecialchars($period['period_display']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Status</label>
                                    <div>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-edit me-1"></i>
                                            <?php echo $is_new_submission ? 'New Submission' : 'Editing Finalized'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Description -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title m-0">
                                <i class="fas fa-align-left me-2"></i>Submission Description
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" 
                                          placeholder="Enter submission description..."><?php echo htmlspecialchars($submission['description'] ?? ''); ?></textarea>
                                <div class="form-text">Provide a detailed description of this submission.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Program Targets -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title m-0">
                                <i class="fas fa-bullseye me-2"></i>Program Targets
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTarget()">
                                <i class="fas fa-plus me-1"></i>Add Target
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="targets-container">
                                <?php if (!empty($targets)): ?>
                                    <?php foreach ($targets as $index => $target): ?>
                                        <div class="target-item border rounded p-3 mb-3" data-target-index="<?php echo $index; ?>">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">Target <?php echo $index + 1; ?></h6>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTarget(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            
                                            <input type="hidden" name="targets[<?php echo $index; ?>][target_id]" value="<?php echo $target['target_id']; ?>">
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Target Number</label>
                                                    <input type="text" class="form-control" name="targets[<?php echo $index; ?>][target_number]" 
                                                           value="<?php echo htmlspecialchars($target['target_number'] ?? ''); ?>" placeholder="e.g., T1, Target A">
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Status Indicator</label>
                                                    <select class="form-select" name="targets[<?php echo $index; ?>][status_indicator]">
                                                        <option value="not_started" <?php echo ($target['status_indicator'] == 'not_started') ? 'selected' : ''; ?>>Not Started</option>
                                                        <option value="in_progress" <?php echo ($target['status_indicator'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="completed" <?php echo ($target['status_indicator'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                        <option value="delayed" <?php echo ($target['status_indicator'] == 'delayed') ? 'selected' : ''; ?>>Delayed</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Target Description</label>
                                                <textarea class="form-control" name="targets[<?php echo $index; ?>][target_description]" rows="3" 
                                                          placeholder="Describe the target..."><?php echo htmlspecialchars($target['target_description'] ?? ''); ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Status Description / Achievements</label>
                                                <textarea class="form-control" name="targets[<?php echo $index; ?>][status_description]" rows="3" 
                                                          placeholder="Describe current status and achievements..."><?php echo htmlspecialchars($target['status_description'] ?? ''); ?></textarea>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Start Date</label>
                                                    <input type="date" class="form-control" name="targets[<?php echo $index; ?>][start_date]" 
                                                           value="<?php echo $target['start_date']; ?>">
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">End Date</label>
                                                    <input type="date" class="form-control" name="targets[<?php echo $index; ?>][end_date]" 
                                                           value="<?php echo $target['end_date']; ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-0">
                                                <label class="form-label">Remarks</label>
                                                <textarea class="form-control" name="targets[<?php echo $index; ?>][remarks]" rows="2" 
                                                          placeholder="Additional remarks..."><?php echo htmlspecialchars($target['remarks'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4" id="no-targets-message">
                                        <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">No Targets Added Yet</h6>
                                        <p class="text-muted mb-3">Click "Add Target" to start adding program targets.</p>
                                        <button type="button" class="btn btn-primary" onclick="addTarget()">
                                            <i class="fas fa-plus me-1"></i>Add First Target
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Auto-save Indicator -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h6 class="card-title m-0">
                                <i class="fas fa-info-circle me-2"></i>Save Status
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-save text-muted me-2"></i>
                                <span class="text-muted">Auto-save enabled</span>
                                <span id="autoSaveIndicator" class="ms-2 text-success" style="display: none;">
                                    <i class="fas fa-check-circle me-1"></i>Saved
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Submission History -->
                    <?php if (!$is_new_submission): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h6 class="card-title m-0">
                                <i class="fas fa-history me-2"></i>Submission History
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <?php if (!empty($submission['finalized_at'])): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Finalized</h6>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y g:i A', strtotime($submission['finalized_at'])); ?>
                                            <?php if (!empty($submission['finalized_by_name'])): ?>
                                                <br>by <?php echo htmlspecialchars($submission['finalized_by_name']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($submission['submitted_at'])): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Submitted</h6>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?>
                                            <?php if (!empty($submission['submitted_by_name'])): ?>
                                                <br>by <?php echo htmlspecialchars($submission['submitted_by_name']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Program Attachments -->
                    <?php if (!empty($attachments)): ?>
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="card-title m-0">
                                <i class="fas fa-paperclip me-2"></i>Program Attachments
                                <span class="badge bg-secondary ms-1"><?php echo count($attachments); ?></span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($attachments as $attachment): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-file text-primary me-2"></i>
                                    <div class="flex-grow-1">
                                        <div class="small fw-medium"><?php echo htmlspecialchars($attachment['file_name'] ?? ''); ?></div>
                                        <div class="text-muted small"><?php echo $attachment['file_size_formatted']; ?></div>
                                    </div>
                                    <a href="<?php echo APP_URL; ?>/app/ajax/download_program_attachment.php?id=<?php echo $attachment['attachment_id']; ?>" 
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- Target Template -->
<template id="target-template">
    <div class="target-item border rounded p-3 mb-3" data-target-index="">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Target <span class="target-number"></span></h6>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTarget(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        
        <input type="hidden" name="targets[][target_id]" value="">
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Target Number</label>
                <input type="text" class="form-control" name="targets[][target_number]" placeholder="e.g., T1, Target A">
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label">Status Indicator</label>
                <select class="form-select" name="targets[][status_indicator]">
                    <option value="not_started">Not Started</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="delayed">Delayed</option>
                </select>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Target Description</label>
            <textarea class="form-control" name="targets[][target_description]" rows="3" placeholder="Describe the target..."></textarea>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Status Description / Achievements</label>
            <textarea class="form-control" name="targets[][status_description]" rows="3" placeholder="Describe current status and achievements..."></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="targets[][start_date]">
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="targets[][end_date]">
            </div>
        </div>
        
        <div class="mb-0">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="targets[][remarks]" rows="2" placeholder="Additional remarks..."></textarea>
        </div>
    </div>
</template>

<script>
let targetIndex = <?php echo count($targets); ?>;

function addTarget() {
    const template = document.getElementById('target-template');
    const container = document.getElementById('targets-container');
    const noTargetsMessage = document.getElementById('no-targets-message');
    
    if (noTargetsMessage) {
        noTargetsMessage.remove();
    }
    
    const clone = template.content.cloneNode(true);
    const targetItem = clone.querySelector('.target-item');
    
    // Update target index and number
    targetItem.setAttribute('data-target-index', targetIndex);
    clone.querySelector('.target-number').textContent = targetIndex + 1;
    
    // Update form field names with proper index
    const inputs = clone.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        if (input.name.includes('targets[]')) {
            input.name = input.name.replace('targets[]', `targets[${targetIndex}]`);
        }
    });
    
    container.appendChild(clone);
    targetIndex++;
}

function removeTarget(button) {
    const targetItem = button.closest('.target-item');
    const container = document.getElementById('targets-container');
    
    targetItem.remove();
    
    // If no targets left, show the no targets message
    if (container.children.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4" id="no-targets-message">
                <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No Targets Added Yet</h6>
                <p class="text-muted mb-3">Click "Add Target" to start adding program targets.</p>
                <button type="button" class="btn btn-primary" onclick="addTarget()">
                    <i class="fas fa-plus me-1"></i>Add First Target
                </button>
            </div>
        `;
    }
}

// Form validation and submission
document.getElementById('editSubmissionForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
});
</script>