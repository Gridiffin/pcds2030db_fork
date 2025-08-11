<?php
/**
 * Submission Overview Partial
 * Displays submission details and timeline
 */
?>
<!-- Submission Overview Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 me-3">
                <i class="fas fa-file-alt me-2 text-white"></i>
                Submission Details
            </h5>
            <div class="d-flex gap-3">
                <!-- Submission Status Badge -->
                <?php if ($submission['is_submitted']): ?>
                    <span class="badge bg-success px-3 py-2">
                        <i class="fas fa-check-circle me-1"></i>Submitted
                    </span>
                <?php elseif ($submission['is_draft']): ?>
                    <span class="badge bg-warning px-3 py-2">
                        <i class="fas fa-edit me-1"></i>Draft
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary px-3 py-2">
                        <i class="fas fa-clock me-1"></i>Not Started
                    </span>
                <?php endif; ?>
                
                <!-- Period Status Badge -->
                <?php 
                $period_status = $submission['period_status'] ?? 'closed';
                $period_status_display = ucfirst($period_status);
                ?>
                <span class="badge bg-<?php echo $period_status === 'open' ? 'info' : 'secondary'; ?> px-3 py-2">
                    Period: <?php echo htmlspecialchars($period_status_display); ?>
                </span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Submission Description -->
        <div class="mb-4">
            <h6 class="text-muted mb-2">
                <i class="fas fa-align-left me-1"></i>Description
            </h6>
            <div class="bg-light p-3 rounded">
                <?php if (!empty($submission['description'])): ?>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($submission['description'])); ?></p>
                <?php else: ?>
                    <p class="text-muted mb-0 fst-italic">No description provided for this submission.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submission Timeline -->
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">
                    <i class="fas fa-clock me-1"></i>Timeline
                </h6>
                <div class="d-flex flex-column gap-2">
                    <?php if (!empty($submission['submitted_at'])): ?>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check text-success me-2"></i>
                            <div>
                                <div class="fw-medium">Submitted</div>
                                <small class="text-muted">
                                    <?php echo date('F j, Y \a\t g:i A', strtotime($submission['submitted_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($submission['updated_at'])): ?>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-edit text-info me-2"></i>
                            <div>
                                <div class="fw-medium">Last Updated</div>
                                <small class="text-muted">
                                    <?php echo date('F j, Y \a\t g:i A', strtotime($submission['updated_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <h6 class="text-muted mb-2">
                    <i class="fas fa-user me-1"></i>Submitted By
                </h6>
                <?php if (!empty($submission['submitted_by_name'])): ?>
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                             style="width: 40px; height: 40px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="fw-medium">
                                <?php echo htmlspecialchars($submission['submitted_by_fullname'] ?: $submission['submitted_by_name']); ?>
                            </div>
                            <?php if (!empty($submission['submitted_by_agency'])): ?>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($submission['submitted_by_agency']); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Information not available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>