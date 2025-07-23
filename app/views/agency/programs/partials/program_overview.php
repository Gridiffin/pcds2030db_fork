<?php
/**
 * Program Overview Partial
 * Displays basic program information and status
 */

// Get program status info
$status = isset($program['status']) ? $program['status'] : 'active';
$status_info = get_program_status_info($status);
?>

<!-- Program Information Card -->
<div class="card program-info-card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-clipboard-list me-2"></i>Program Information
        </h5>
        <?php if ($is_draft): ?>
        <span class="badge bg-warning text-dark ms-2" title="Latest submission is in draft status">
            <i class="fas fa-pencil-alt me-1"></i> Draft Submission
        </span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-tag text-primary"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Program Name</div>
                        <div class="info-value fw-medium"><?php echo htmlspecialchars($program['program_name']); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-hashtag text-info"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Program Number</div>
                        <div class="info-value">
                            <?php if (!empty($program['program_number'])): ?>
                                <span class="badge bg-info"><?php echo htmlspecialchars($program['program_number']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-circle-notch text-primary"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge status-badge bg-<?php echo $status_info['class']; ?> py-2 px-3" id="program-status-badge">
                                <i class="<?php echo $status_info['icon']; ?> me-1"></i>
                                <?php echo $status_info['label']; ?>
                            </span>
                            <div id="hold-point-info"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-project-diagram text-warning"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Initiative</div>
                        <div class="info-value">
                            <?php if (!empty($program['initiative_name'])): ?>
                                <span class="fw-medium"><?php echo htmlspecialchars($program['initiative_name']); ?></span>
                                <?php if (!empty($program['initiative_number'])): ?>
                                    <span class="badge bg-secondary ms-2" title="Initiative Number"><?php echo htmlspecialchars($program['initiative_number']); ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-calendar-alt text-danger"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Timeline</div>
                        <div class="info-value">
                            <?php if (!empty($program['start_date'])): ?>
                                <i class="far fa-calendar-alt me-1"></i>
                                <?php echo date('M j, Y', strtotime($program['start_date'])); ?>
                                <?php if (!empty($program['end_date'])): ?>
                                    <i class="fas fa-long-arrow-alt-right mx-1"></i>
                                    <?php echo date('M j, Y', strtotime($program['end_date'])); ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock text-secondary"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Last Updated</div>
                        <div class="info-value">
                            <?php if ($has_submissions && isset($latest_submission['submission_date']) && $latest_submission['submission_date']): ?>
                                <i class="far fa-clock me-1"></i>
                                <?php echo date('M j, Y', strtotime($latest_submission['submission_date'])); ?>
                                <span class="text-muted small ms-2">(Latest submission)</span>
                            <?php elseif (isset($program['created_at']) && $program['created_at']): ?>
                                <i class="far fa-clock me-1"></i>
                                <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                <span class="text-muted small ms-2">(Program created)</span>
                            <?php else: ?>
                                <span class="text-muted">Not available</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($program['description'])): ?>
        <div class="mt-4">
            <h6 class="info-section-title">
                <i class="fas fa-align-left me-2"></i>Description
            </h6>
            <div class="description-box">
                <?php echo nl2br(htmlspecialchars($program['description'])); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Hold Point Management Table (Read-only) -->
<div class="card shadow-sm my-4" id="holdPointHistorySection">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-pause-circle me-2"></i>Hold Point History
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($hold_points)): ?>
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>Reason</th>
                            <th>Remarks</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hold_points as $hp): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($hp['reason'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($hp['remarks'] ?? ''); ?></td>
                                <td><span class="text-nowrap"><?php echo date('M j, Y H:i', strtotime($hp['created_at'])); ?></span></td>
                                <td>
                                    <?php if ($hp['ended_at']): ?>
                                        <span class="text-nowrap"><?php echo date('M j, Y H:i', strtotime($hp['ended_at'])); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$hp['ended_at']): ?>
                                        <span class="badge bg-warning text-dark">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ended</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center text-muted p-4">
                <i class="fas fa-folder-open fa-2x mb-2"></i>
                <div>No hold points recorded for this program.</div>
            </div>
        <?php endif; ?>
    </div>
</div>