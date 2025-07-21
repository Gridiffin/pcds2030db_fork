<?php
/**
 * Initiative Information Card Partial
 * Contains the detailed initiative information including description, timeline, and program overview
 */
?>
<!-- Initiative Information Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title m-0">
                <i class="fas fa-lightbulb me-2"></i>Initiative Information
            </h5>
            <div>
                <?php if ($initiative['is_active']): ?>
                    <span class="badge bg-success">Active</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Inactive</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Description -->
        <?php if (!empty($initiative[$initiative_description_col])): ?>
        <div class="initiative-description mb-4">
            <h6 class="text-muted mb-2">
                <i class="fas fa-align-left me-1"></i>Description
            </h6>
            <div class="p-3 bg-light rounded">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($initiative[$initiative_description_col])); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Timeline Information -->
        <div class="mb-4">
            <h6 class="text-muted mb-2">
                <i class="fas fa-calendar-alt me-1"></i>Timeline
            </h6>
            <div class="timeline-info p-3 bg-light rounded">
                <?php if (!empty($initiative[$start_date_col]) || !empty($initiative[$end_date_col])): ?>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-check me-2 text-success"></i>
                        <span>
                            <?php 
                            if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                                echo date('M j, Y', strtotime($initiative[$start_date_col])) . ' - ' . date('M j, Y', strtotime($initiative[$end_date_col]));
                                
                                // Calculate duration
                                $start = new DateTime($initiative[$start_date_col]);
                                $end = new DateTime($initiative[$end_date_col]);
                                $interval = $start->diff($end);
                                echo ' <span class="text-muted">(' . $interval->days . ' days)</span>';
                            } elseif (!empty($initiative[$start_date_col])) {
                                echo 'Started: ' . date('M j, Y', strtotime($initiative[$start_date_col]));
                            } elseif (!empty($initiative[$end_date_col])) {
                                echo 'Due: ' . date('M j, Y', strtotime($initiative[$end_date_col]));
                            }
                            ?>
                        </span>
                    </div>
                <?php else: ?>
                    <div class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        No timeline information available
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Program Statistics -->
        <div class="mb-0">
            <h6 class="text-muted mb-3">
                <i class="fas fa-chart-bar me-1"></i>Program Overview
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?php echo $initiative['agency_program_count']; ?></h3>
                            <div class="small">Your Programs</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-1"><?php echo $initiative['total_program_count']; ?></h3>
                            <div class="small">Total Programs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
