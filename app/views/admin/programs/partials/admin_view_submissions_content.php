<?php
/**
 * Admin View Submissions Content
 * Main content for admin view submissions page
 */
?>

<main>
    <div class="container-fluid">
        <?php if (!$submission && empty($all_submissions)): ?>
            <!-- No submissions alert -->
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <div class="d-flex">
                    <div class="alert-icon me-3">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading">No Submissions Found</h5>
                        <p class="mb-0">This program doesn't have any finalized submissions yet. Only finalized submissions are visible in the admin view.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php if (!empty($all_submissions)): ?>
            <!-- Submissions List/Selector -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title m-0">
                            <i class="fas fa-list me-2"></i>Available Submissions
                            <span class="badge bg-secondary ms-2"><?php echo count($all_submissions); ?></span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($all_submissions as $sub): ?>
                                <a href="view_submissions.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $sub['period_id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo ($submission && $sub['period_id'] == $submission['period_id']) ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($sub['period_display']); ?></h6>
                                            <small class="text-muted">
                                                <?php if (!empty($sub['submitted_at'])): ?>
                                                    Submitted: <?php echo date('M j, Y', strtotime($sub['submitted_at'])); ?>
                                                <?php else: ?>
                                                    No submission date
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge bg-success">Finalized</span>
                                        </div>
                                    </div>
                                    <?php if (!empty($sub['submitted_by_name'])): ?>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-user-check me-1"></i>
                                            Submitted by: <?php echo htmlspecialchars($sub['submitted_by_name']); ?>
                                        </small>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Main Content -->
            <div class="<?php echo !empty($all_submissions) ? 'col-lg-8' : 'col-12'; ?>">
                <?php if ($submission): ?>
                    <!-- Submission Details -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title m-0">
                                    <i class="fas fa-file-alt me-2"></i>Submission Details
                                </h5>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-info px-3 py-2">
                                        <?php echo htmlspecialchars($submission['period_display']); ?>
                                    </span>
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i>Finalized
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Submission Info Grid -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Program</label>
                                    <div class="fw-medium">
                                        <?php if (!empty($program['program_number'])): ?>
                                            <span class="badge bg-info me-2"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Agency</label>
                                    <div class="fw-medium">
                                        <i class="fas fa-building me-2 text-primary"></i>
                                        <?php echo htmlspecialchars($agency_info['agency_name']); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Submission Date</label>
                                    <div>
                                        <?php if (!empty($submission['submitted_at'])): ?>
                                            <i class="fas fa-calendar me-2"></i>
                                            <?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not available</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Submitted By</label>
                                    <div>
                                        <?php if (!empty($submission['submitted_by_name'])): ?>
                                            <i class="fas fa-user-check me-2 text-success"></i>
                                            <?php echo htmlspecialchars($submission['submitted_by_name']); ?>
                                            <?php if (!empty($submission['submitted_at'])): ?>
                                                <small class="text-muted d-block">
                                                    <?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not available</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label class="form-label text-muted small">Submission Description</label>
                                <div class="border rounded p-3 bg-light">
                                    <?php echo !empty($submission['description']) 
                                        ? nl2br(htmlspecialchars($submission['description'])) 
                                        : '<span class="text-muted">-</span>'; ?>
                                </div>
                            </div>

                            <!-- Program Information Section -->
                            <div class="mb-4">
                                <label class="form-label text-muted small">Program Details</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 bg-light mb-2">
                                            <strong>Rating:</strong>
                                            <span class="badge bg-<?php 
                                                echo match($program['rating'] ?? 'not_started') {
                                                    'not_started' => 'secondary',
                                                    'on_track_for_year' => 'warning',
                                                    'monthly_target_achieved' => 'success',
                                                    'severe_delay' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?> ms-2">
                                                <?php 
                                                echo match($program['rating'] ?? 'not_started') {
                                                    'not_started' => 'Not Started',
                                                    'on_track_for_year' => 'On Track for Year',
                                                    'monthly_target_achieved' => 'Monthly Target Achieved',
                                                    'severe_delay' => 'Severe Delays',
                                                    default => 'Not Started'
                                                };
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <?php if (!empty($program['description'])): ?>
                                        <div class="border rounded p-3 bg-light mb-2">
                                            <strong>Program Description:</strong><br>
                                            <small><?php echo nl2br(htmlspecialchars($program['description'])); ?></small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($program['start_date']) || !empty($program['end_date'])): ?>
                                <div class="border rounded p-3 bg-light mt-2">
                                    <strong>Program Timeline:</strong>
                                    <div class="row mt-2">
                                        <?php if (!empty($program['start_date'])): ?>
                                        <div class="col-md-6">
                                            <small><i class="fas fa-play text-success me-1"></i><strong>Start:</strong> <?php echo date('M j, Y', strtotime($program['start_date'])); ?></small>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($program['end_date'])): ?>
                                        <div class="col-md-6">
                                            <small><i class="fas fa-flag text-danger me-1"></i><strong>End:</strong> <?php echo date('M j, Y', strtotime($program['end_date'])); ?></small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        This submission can be edited to update targets, achievements, and attachments.
                                    </small>
                                </div>
                                <div>
                                    <?php if ($submission): ?>
                                        <?php 
                                        // Use the period_id from URL if available, otherwise use the submission's period_id
                                        $edit_period_id = $period_id ?? $submission['period_id'];
                                        ?>
                                        <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $edit_period_id; ?>" 
                                           class="btn btn-primary">
                                            <i class="fas fa-edit me-2"></i>Edit Submission
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Program Targets -->
                    <?php if (!empty($targets)): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title m-0">
                                    <i class="fas fa-bullseye me-2"></i>Program Targets & Timeline Analysis
                                    <span class="badge bg-secondary ms-2"><?php echo count($targets); ?></span>
                                </h5>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-info px-2 py-1">
                                        <i class="fas fa-calendar me-1"></i>Period: <?php echo htmlspecialchars($submission['period_display']); ?>
                                    </span>
                                    <?php 
                                    // Calculate target status summary
                                    $status_counts = array_count_values(array_column($targets, 'status_indicator'));
                                    ?>
                                    <?php if (!empty($status_counts['completed'])): ?>
                                        <span class="badge bg-success px-2 py-1">
                                            <i class="fas fa-check me-1"></i><?php echo $status_counts['completed']; ?> Completed
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($status_counts['in_progress'])): ?>
                                        <span class="badge bg-warning px-2 py-1">
                                            <i class="fas fa-spinner me-1"></i><?php echo $status_counts['in_progress']; ?> In Progress
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($status_counts['delayed'])): ?>
                                        <span class="badge bg-danger px-2 py-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i><?php echo $status_counts['delayed']; ?> Delayed
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Targets Summary Dashboard -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info border-info bg-info bg-opacity-10">
                                        <div class="row text-center">
                                            <div class="col-md-3">
                                                <div class="target-stat">
                                                    <h4 class="text-info mb-1"><?php echo count($targets); ?></h4>
                                                    <small class="text-muted">Total Targets</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="target-stat">
                                                    <h4 class="text-success mb-1"><?php echo $status_counts['completed'] ?? 0; ?></h4>
                                                    <small class="text-muted">Completed</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="target-stat">
                                                    <h4 class="text-warning mb-1"><?php echo $status_counts['in_progress'] ?? 0; ?></h4>
                                                    <small class="text-muted">In Progress</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="target-stat">
                                                    <h4 class="text-danger mb-1"><?php echo ($status_counts['delayed'] ?? 0) + ($status_counts['not_started'] ?? 0); ?></h4>
                                                    <small class="text-muted">At Risk</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="targets-list">
                                <?php foreach ($targets as $index => $target): ?>
                                    <?php
                                    // Enhanced target calculations
                                    $target_start = !empty($target['start_date']) ? new DateTime($target['start_date']) : null;
                                    $target_end = !empty($target['end_date']) ? new DateTime($target['end_date']) : null;
                                    $period_start = !empty($submission['period_start_date']) ? new DateTime($submission['period_start_date']) : null;
                                    $period_end = !empty($submission['period_end_date']) ? new DateTime($submission['period_end_date']) : null;
                                    $now = new DateTime();
                                    
                                    // Calculate target timeline status
                                    $timeline_status = 'unknown';
                                    $timeline_message = '';
                                    $timeline_class = 'secondary';
                                    
                                    if ($target_start && $target_end) {
                                        $total_duration = $target_start->diff($target_end)->days;
                                        
                                        if ($now < $target_start) {
                                            $timeline_status = 'upcoming';
                                            $days_until = $now->diff($target_start)->days;
                                            $timeline_message = "Starts in {$days_until} days";
                                            $timeline_class = 'info';
                                        } elseif ($now > $target_end) {
                                            $timeline_status = 'overdue';
                                            $days_past = $target_end->diff($now)->days;
                                            $timeline_message = "Ended {$days_past} days ago";
                                            $timeline_class = $target['status_indicator'] == 'completed' ? 'success' : 'danger';
                                        } else {
                                            $timeline_status = 'active';
                                            $days_remaining = $now->diff($target_end)->days;
                                            $elapsed_days = $target_start->diff($now)->days;
                                            $progress_percentage = $total_duration > 0 ? round(($elapsed_days / $total_duration) * 100) : 0;
                                            $timeline_message = "{$days_remaining} days remaining ({$progress_percentage}% elapsed)";
                                            $timeline_class = 'warning';
                                        }
                                    }
                                    
                                    // Status consistency check
                                    $status_consistent = true;
                                    $consistency_message = '';
                                    
                                    if ($target['status_indicator'] == 'completed' && $timeline_status == 'active') {
                                        $status_consistent = false;
                                        $consistency_message = 'Target marked completed but timeline is still active';
                                    } elseif ($target['status_indicator'] == 'not_started' && $timeline_status == 'overdue') {
                                        $status_consistent = false;
                                        $consistency_message = 'Target not started but timeline has ended';
                                    } elseif ($target['status_indicator'] == 'in_progress' && $timeline_status == 'overdue') {
                                        $status_consistent = false;
                                        $consistency_message = 'Target in progress but timeline has ended';
                                    }
                                    ?>
                                    <div class="target-item border rounded p-4 mb-4 shadow-sm <?php echo !$status_consistent ? 'border-warning' : ''; ?>">
                                        <!-- Target Header with Enhanced Status -->
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="text-primary mb-1">
                                                    <i class="fas fa-bullseye me-2"></i>Target <?php echo $index + 1; ?>
                                                    <?php if (!empty($target['target_number'])): ?>
                                                        <span class="badge bg-info ms-2"><?php echo htmlspecialchars($target['target_number']); ?></span>
                                                    <?php endif; ?>
                                                </h5>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <span class="badge bg-<?php 
                                                        echo match($target['status_indicator']) {
                                                            'not_started' => 'secondary',
                                                            'in_progress' => 'warning',
                                                            'completed' => 'success',
                                                            'delayed' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?> px-3 py-2">
                                                        <i class="fas fa-<?php 
                                                            echo match($target['status_indicator']) {
                                                                'not_started' => 'clock',
                                                                'in_progress' => 'spinner',
                                                                'completed' => 'check-circle',
                                                                'delayed' => 'exclamation-triangle',
                                                                default => 'clock'
                                                            };
                                                        ?> me-1"></i>
                                                        <?php echo ucwords(str_replace('_', ' ', $target['status_indicator'])); ?>
                                                    </span>
                                                    
                                                    <?php if ($timeline_message): ?>
                                                    <span class="badge bg-<?php echo $timeline_class; ?> px-3 py-2">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo $timeline_message; ?>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- Status Consistency Alert -->
                                            <?php if (!$status_consistent): ?>
                                            <div class="alert alert-warning alert-sm py-1 px-2 mb-0">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <small><?php echo $consistency_message; ?></small>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="row">
                                            <!-- Left Column: Target Details -->
                                            <div class="col-lg-7">
                                                <!-- Target Description -->
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-bold">Target Description</label>
                                                    <div class="border rounded p-3 bg-light">
                                                        <?php echo nl2br(htmlspecialchars($target['target_description'] ?? 'No description provided')); ?>
                                                    </div>
                                                </div>

                                                <!-- Comprehensive Timeline Analysis -->
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-bold">Timeline Analysis & Progress</label>
                                                    <div class="timeline-analysis border rounded p-3 bg-light">
                                                        <?php if ($target_start && $target_end): ?>
                                                            <!-- Timeline Header -->
                                                            <div class="row mb-3">
                                                                <div class="col-6">
                                                                    <div class="timeline-point">
                                                                        <i class="fas fa-play text-success me-2"></i>
                                                                        <strong>Start Date</strong><br>
                                                                        <span class="text-primary"><?php echo $target_start->format('M j, Y'); ?></span>
                                                                        <small class="text-muted d-block"><?php echo $target_start->format('l'); ?></small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="timeline-point">
                                                                        <i class="fas fa-flag-checkered text-danger me-2"></i>
                                                                        <strong>End Date</strong><br>
                                                                        <span class="text-primary"><?php echo $target_end->format('M j, Y'); ?></span>
                                                                        <small class="text-muted d-block"><?php echo $target_end->format('l'); ?></small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Progress Bar -->
                                                            <?php if ($timeline_status == 'active'): ?>
                                                            <div class="mb-3">
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <small class="text-muted">Timeline Progress</small>
                                                                    <small class="text-muted"><?php echo $progress_percentage; ?>% elapsed</small>
                                                                </div>
                                                                <div class="progress" style="height: 8px;">
                                                                    <div class="progress-bar bg-<?php echo $timeline_class; ?>" 
                                                                         style="width: <?php echo $progress_percentage; ?>%"></div>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>

                                                            <!-- Timeline Statistics -->
                                                            <div class="row text-center">
                                                                <div class="col-4">
                                                                    <div class="timeline-stat">
                                                                        <h6 class="text-info mb-1"><?php echo $total_duration; ?></h6>
                                                                        <small class="text-muted">Total Days</small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="timeline-stat">
                                                                        <h6 class="text-<?php echo $timeline_class; ?> mb-1">
                                                                            <?php 
                                                                            if ($timeline_status == 'active') {
                                                                                echo $elapsed_days;
                                                                            } elseif ($timeline_status == 'upcoming') {
                                                                                echo '0';
                                                                            } else {
                                                                                echo $total_duration;
                                                                            }
                                                                            ?>
                                                                        </h6>
                                                                        <small class="text-muted">Days Elapsed</small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="timeline-stat">
                                                                        <h6 class="text-<?php echo $timeline_status == 'active' ? 'warning' : 'muted'; ?> mb-1">
                                                                            <?php 
                                                                            if ($timeline_status == 'active') {
                                                                                echo $days_remaining;
                                                                            } elseif ($timeline_status == 'upcoming') {
                                                                                echo $total_duration;
                                                                            } else {
                                                                                echo '0';
                                                                            }
                                                                            ?>
                                                                        </h6>
                                                                        <small class="text-muted">Days Remaining</small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Reporting Period Overlap Analysis -->
                                                            <?php if ($period_start && $period_end): ?>
                                                            <div class="mt-3 pt-3 border-top">
                                                                <h6 class="text-muted mb-2">
                                                                    <i class="fas fa-calendar-alt me-1"></i>Reporting Period Context
                                                                </h6>
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <?php
                                                                        $period_duration = $period_start->diff($period_end)->days;
                                                                        $target_before_period = $target_end < $period_start;
                                                                        $target_after_period = $target_start > $period_end;
                                                                        $target_spans_period = $target_start <= $period_start && $target_end >= $period_end;
                                                                        $target_within_period = $target_start >= $period_start && $target_end <= $period_end;
                                                                        $target_overlaps_start = $target_start < $period_start && $target_end >= $period_start && $target_end <= $period_end;
                                                                        $target_overlaps_end = $target_start >= $period_start && $target_start <= $period_end && $target_end > $period_end;
                                                                        ?>
                                                                        
                                                                        <small class="text-muted">
                                                                            <strong>Period:</strong> <?php echo $period_start->format('M j'); ?> - <?php echo $period_end->format('M j, Y'); ?> (<?php echo $period_duration; ?> days)<br>
                                                                            <strong>Relationship:</strong> 
                                                                            <?php if ($target_before_period): ?>
                                                                                <span class="text-info">Target completed before this period</span>
                                                                            <?php elseif ($target_after_period): ?>
                                                                                <span class="text-warning">Target starts after this period</span>
                                                                            <?php elseif ($target_spans_period): ?>
                                                                                <span class="text-success">Target spans entire reporting period</span>
                                                                            <?php elseif ($target_within_period): ?>
                                                                                <span class="text-primary">Target contained within reporting period</span>
                                                                            <?php elseif ($target_overlaps_start): ?>
                                                                                <span class="text-warning">Target ends during this period</span>
                                                                            <?php elseif ($target_overlaps_end): ?>
                                                                                <span class="text-warning">Target starts during this period</span>
                                                                            <?php else: ?>
                                                                                <span class="text-muted">No timeline overlap</span>
                                                                            <?php endif; ?>
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <div class="text-center py-3">
                                                                <i class="fas fa-calendar-times text-muted fa-2x mb-2"></i>
                                                                <p class="text-muted mb-0">No timeline specified for this target</p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Right Column: Status & Remarks -->
                                            <div class="col-lg-5">
                                                <!-- Enhanced Status Report -->
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-bold">Status Report & Progress</label>
                                                    <?php if (!empty($target['status_description'])): ?>
                                                        <div class="border rounded p-3 bg-success bg-opacity-10">
                                                            <div class="d-flex align-items-start">
                                                                <i class="fas fa-chart-line text-success me-2 mt-1"></i>
                                                                <div class="flex-grow-1">
                                                                    <?php echo nl2br(htmlspecialchars($target['status_description'])); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="border rounded p-3 bg-light text-center">
                                                            <i class="fas fa-info-circle text-muted me-2"></i>
                                                            <span class="text-muted fst-italic">No status update provided for this target</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Enhanced Remarks Section -->
                                                <?php if (!empty($target['remarks'])): ?>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small fw-bold">Remarks & Additional Notes</label>
                                                    <div class="alert alert-warning border-warning bg-warning bg-opacity-10">
                                                        <div class="d-flex align-items-start">
                                                            <i class="fas fa-sticky-note text-warning me-2 mt-1"></i>
                                                            <div class="flex-grow-1">
                                                                <?php echo nl2br(htmlspecialchars($target['remarks'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>

                                                <!-- Comprehensive Target Summary Card -->
                                                <div class="card bg-info bg-opacity-10 border-info">
                                                    <div class="card-body py-3">
                                                        <h6 class="card-title text-info mb-3">
                                                            <i class="fas fa-analytics me-1"></i>Target Analytics
                                                        </h6>
                                                        
                                                        <!-- Status Analysis -->
                                                        <div class="row mb-3">
                                                            <div class="col-6">
                                                                <div class="text-center">
                                                                    <div class="badge bg-<?php 
                                                                        echo match($target['status_indicator'] ?? 'not_started') {
                                                                            'completed' => 'success',
                                                                            'in_progress' => 'warning', 
                                                                            'delayed' => 'danger',
                                                                            default => 'secondary'
                                                                        };
                                                                    ?> w-100 p-2 mb-1">
                                                                        <?php echo ucwords(str_replace('_', ' ', $target['status_indicator'] ?? 'Not Started')); ?>
                                                                    </div>
                                                                    <small class="text-muted">Current Status</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="text-center">
                                                                    <div class="badge bg-<?php echo $timeline_class; ?> w-100 p-2 mb-1">
                                                                        <?php echo ucwords($timeline_status); ?>
                                                                    </div>
                                                                    <small class="text-muted">Timeline Status</small>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Key Metrics -->
                                                            <div class="row small">
                                                            <div class="col-12 mb-2">
                                                                <div class="d-flex justify-content-between">
                                                                    <span><strong>Target Number:</strong></span>
                                                                        <span class="text-primary"><?php echo !empty($target['target_number']) ? htmlspecialchars($target['target_number']) : '-'; ?></span>
                                                                </div>
                                                            </div>
                                                            <?php if ($target_start && $target_end): ?>
                                                            <div class="col-12 mb-2">
                                                                <div class="d-flex justify-content-between">
                                                                    <span><strong>Duration:</strong></span>
                                                                    <span class="text-primary"><?php echo $total_duration; ?> days</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 mb-2">
                                                                <div class="d-flex justify-content-between">
                                                                    <span><strong>Progress:</strong></span>
                                                                    <span class="text-<?php echo $timeline_class; ?>">
                                                                        <?php 
                                                                        if ($timeline_status == 'active') {
                                                                            echo $progress_percentage . '% elapsed';
                                                                        } elseif ($timeline_status == 'upcoming') {
                                                                            echo 'Not started yet';
                                                                        } elseif ($timeline_status == 'overdue') {
                                                                            echo 'Timeline completed';
                                                                        } else {
                                                                            echo 'No timeline';
                                                                        }
                                                                        ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                            
                                                        </div>

                                                        <!-- Action Items (if any inconsistencies) -->
                                                        <?php if (!$status_consistent): ?>
                                                        <div class="mt-3 pt-2 border-top">
                                                            <small class="text-warning">
                                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                                <strong>Action Required:</strong><br>
                                                                <?php echo $consistency_message; ?>
                                                            </small>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- No Targets -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title m-0">
                                <i class="fas fa-bullseye me-2"></i>Program Targets
                            </h5>
                        </div>
                        <div class="card-body text-center py-4">
                            <i class="fas fa-target fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No Targets Defined</h6>
                            <p class="text-muted mb-0">This submission doesn't have any targets specified.</p>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- No specific submission selected -->
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Select a Submission</h5>
                            <p class="text-muted mb-0">
                                <?php if (!empty($all_submissions)): ?>
                                    Select a submission from the list on the left to view its details.
                                <?php else: ?>
                                    This program doesn't have any finalized submissions to display.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Program Attachments -->
                <?php if (!empty($attachments)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">
                            <i class="fas fa-paperclip me-2"></i>Program Attachments
                            <span class="badge bg-secondary ms-2"><?php echo count($attachments); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="attachments-list">
                            <?php foreach ($attachments as $attachment): ?>
                                <div class="attachment-item d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                                    <div class="attachment-info d-flex align-items-center">
                                        <div class="attachment-icon me-3">
                                            <i class="fas fa-file fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($attachment['file_name'] ?? ''); ?></h6>
                                            <div class="text-muted small">
                                                <span class="me-3"><?php echo $attachment['file_size_formatted']; ?></span>
                                                <?php if (!empty($attachment['upload_date'])): ?>
                                                    <span class="me-3"><?php echo date('M j, Y', strtotime($attachment['upload_date'])); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($attachment['uploaded_by_name'])): ?>
                                                    <span>by <?php echo htmlspecialchars($attachment['uploaded_by_name']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="attachment-actions">
                                        <a href="<?php echo APP_URL; ?>/app/ajax/download_program_attachment.php?id=<?php echo $attachment['attachment_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
    </div>
</main>