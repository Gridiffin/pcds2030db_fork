<?php
/**
 * Submission Targets Partial
 * Displays program targets for the submission
 */
?>
<!-- Program Targets Section -->
<?php if (!empty($targets)): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-bullseye me-2 text-white"></i>
            Program Targets (<?php echo count($targets); ?>)
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($targets as $index => $target): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card border-start border-4 border-primary">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0 me-3">
                                    <i class="fas fa-target me-1"></i>
                                    Target <?php echo htmlspecialchars($target['target_number'] ?: ($index + 1)); ?>
                                </h6>
                                <span class="badge bg-<?php 
                                    echo match($target['status_indicator']) {
                                        'not_started' => 'secondary',
                                        'in_progress' => 'warning',
                                        'completed' => 'success',
                                        'delayed' => 'danger',
                                        default => 'secondary'
                                    };
                                ?> px-3 py-2">
                                    <i class="fas <?php 
                                        echo match($target['status_indicator']) {
                                            'not_started' => 'fa-clock',
                                            'in_progress' => 'fa-spinner',
                                            'completed' => 'fa-check-circle',
                                            'delayed' => 'fa-exclamation-triangle',
                                            default => 'fa-question'
                                        };
                                    ?> me-1"></i>
                                    <?php echo ucwords(str_replace('_', ' ', $target['status_indicator'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Target Description -->
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-align-left me-1"></i>Target Name/Description
                                </h6>
                                <?php if (!empty($target['target_description'])): ?>
                                    <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($target['target_description'])); ?></p>
                                <?php else: ?>
                                    <p class="text-muted mb-0 small">-</p>
                                <?php endif; ?>
                            </div>

                            <!-- Status Description -->
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-info-circle me-1"></i>Achievements/Status Description
                                </h6>
                                <?php if (!empty($target['status_description'])): ?>
                                    <div class="bg-light p-2 rounded">
                                        <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($target['status_description'])); ?></p>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0 small">-</p>
                                <?php endif; ?>
                            </div>

                            <!-- Timeline -->
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-calendar-alt me-1"></i>Timeline
                                </h6>
                                <div class="row small">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-play text-success me-2"></i>
                                            <div>
                                                <div class="fw-medium">Start Date</div>
                                                <small class="text-muted">
                                                    <?php echo !empty($target['start_date']) ? date('M j, Y', strtotime($target['start_date'])) : '-'; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-flag text-danger me-2"></i>
                                            <div>
                                                <div class="fw-medium">End Date</div>
                                                <small class="text-muted">
                                                    <?php echo !empty($target['end_date']) ? date('M j, Y', strtotime($target['end_date'])) : '-'; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="mb-0">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-comment me-1"></i>Remarks
                                </h6>
                                <?php if (!empty($target['remarks'])): ?>
                                    <div class="alert alert-light mb-0">
                                        <small><?php echo nl2br(htmlspecialchars($target['remarks'])); ?></small>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0 small">-</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Targets Summary -->
        <div class="mt-3 pt-3 border-top">
            <div class="row text-center">
                <?php
                $target_stats = array_count_values(array_column($targets, 'status_indicator'));
                ?>
                <div class="col-3">
                    <div class="text-success">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <div class="fw-bold"><?php echo $target_stats['completed'] ?? 0; ?></div>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="text-warning">
                        <i class="fas fa-spinner fa-2x mb-2"></i>
                        <div class="fw-bold"><?php echo $target_stats['in_progress'] ?? 0; ?></div>
                        <small class="text-muted">In Progress</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <div class="fw-bold"><?php echo $target_stats['delayed'] ?? 0; ?></div>
                        <small class="text-muted">Delayed</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="text-secondary">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <div class="fw-bold"><?php echo $target_stats['not_started'] ?? 0; ?></div>
                        <small class="text-muted">Not Started</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card shadow-sm mb-4">
    <div class="card-body text-center py-5">
        <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No Targets Found</h5>
        <p class="text-muted mb-0">This submission doesn't have any targets defined yet.</p>
    </div>
</div>
<?php endif; ?>