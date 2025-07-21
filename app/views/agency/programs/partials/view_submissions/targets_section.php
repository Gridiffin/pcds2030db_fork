<?php
/**
 * Program Targets Section
 * Displays targets for the current submission
 */
?>

<!-- Program Targets Section -->
<?php if (!empty($targets)): ?>
<div class="card submission-card targets-section mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0 text-white">
            <i class="fas fa-bullseye me-2"></i>
            Program Targets (<?php echo count($targets); ?>)
        </h5>
    </div>
    <div class="card-body">
        <div class="targets-container">
            <div class="row">
                <?php foreach ($targets as $index => $target): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="target-card p-3 h-100" data-target-id="<?php echo $target['target_id'] ?? $index; ?>" data-status="<?php echo $target['status_indicator'] ?? 'unknown'; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <?php if (!empty($target['target_number'])): ?>
                                    <span class="target-number"><?php echo htmlspecialchars($target['target_number']); ?></span>
                                <?php else: ?>
                                    <span class="target-number"><?php echo $index + 1; ?></span>
                                <?php endif; ?>
                                
                                <?php if (!empty($target['status_indicator'])): ?>
                                    <?php
                                    $status_colors = [
                                        'on_track' => 'success',
                                        'at_risk' => 'warning', 
                                        'behind' => 'danger',
                                        'completed' => 'info',
                                        'not_started' => 'secondary'
                                    ];
                                    $status_color = $status_colors[$target['status_indicator']] ?? 'secondary';
                                    $status_labels = [
                                        'on_track' => 'On Track',
                                        'at_risk' => 'At Risk',
                                        'behind' => 'Behind',
                                        'completed' => 'Completed',
                                        'not_started' => 'Not Started'
                                    ];
                                    $status_label = $status_labels[$target['status_indicator']] ?? 'Unknown';
                                    ?>
                                    <span class="badge bg-<?php echo $status_color; ?> target-status" data-status="<?php echo $target['status_indicator']; ?>">
                                        <?php echo $status_label; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="target-text mb-3">
                                <?php echo nl2br(htmlspecialchars($target['text'] ?? $target['target_text'] ?? 'No target description')); ?>
                            </div>
                            
                            <?php if (!empty($target['status_description'])): ?>
                                <div class="target-description">
                                    <strong>Status:</strong> <?php echo nl2br(htmlspecialchars($target['status_description'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($target['start_date']) || !empty($target['end_date'])): ?>
                                <div class="target-dates mt-2">
                                    <small class="text-muted">
                                        <?php if (!empty($target['start_date'])): ?>
                                            <i class="fas fa-calendar-check me-1"></i>
                                            Start: <?php echo date('M j, Y', strtotime($target['start_date'])); ?>
                                        <?php endif; ?>
                                        <?php if (!empty($target['end_date'])): ?>
                                            <?php if (!empty($target['start_date'])): ?> • <?php endif; ?>
                                            <i class="fas fa-calendar-times me-1"></i>
                                            End: <?php echo date('M j, Y', strtotime($target['end_date'])); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Targets Summary -->
        <div class="mt-4 pt-3 border-top target-stats">
            <div class="row text-center">
                <?php
                $target_stats = array_count_values(array_column($targets, 'status_indicator'));
                $total_targets = count($targets);
                ?>
                <div class="col-3">
                    <div class="stat-number text-success" data-stat="on_track"><?php echo $target_stats['on_track'] ?? 0; ?></div>
                    <div class="stat-label">On Track</div>
                </div>
                <div class="col-3">
                    <div class="stat-number text-warning" data-stat="at_risk"><?php echo $target_stats['at_risk'] ?? 0; ?></div>
                    <div class="stat-label">At Risk</div>
                </div>
                <div class="col-3">
                    <div class="stat-number text-danger" data-stat="behind"><?php echo $target_stats['behind'] ?? 0; ?></div>
                    <div class="stat-label">Behind</div>
                </div>
                <div class="col-3">
                    <div class="stat-number text-info" data-stat="completed"><?php echo $target_stats['completed'] ?? 0; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- No Targets State -->
<div class="card submission-card targets-section mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0 text-white">
            <i class="fas fa-bullseye me-2"></i>
            Program Targets
        </h5>
    </div>
    <div class="card-body no-targets-state">
        <div class="icon">
            <i class="fas fa-target fa-3x text-muted"></i>
        </div>
        <h6 class="text-muted">No Targets Defined</h6>
        <p class="text-muted mb-0">This submission doesn't have any targets defined yet.</p>
        <?php if ($can_edit): ?>
            <p class="text-muted small mt-2">You can add targets when editing this submission.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
