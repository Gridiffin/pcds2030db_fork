<?php
/**
 * Initiative Metrics Partial
 * Contains the three metric cards showing timeline progress, health score, and status
 */
?>
<!-- Core Initiative Metrics -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="metric-card text-center">
            <div class="metric-value" style="color: #ffc107;">
                <?php 
                // Calculate timeline progress percentage
                if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                    $start = new DateTime($initiative[$start_date_col]);
                    $end = new DateTime($initiative[$end_date_col]);
                    $now = new DateTime();
                    
                    $total_duration = $start->diff($end)->days;
                    $elapsed = $start->diff($now)->days;
                    
                    if ($elapsed < 0) {
                        $progress = 0;
                    } elseif ($elapsed > $total_duration) {
                        $progress = 100;
                    } else {
                        $progress = round(($elapsed / $total_duration) * 100);
                    }
                    
                    echo $progress . '%';
                } else {
                    echo 'N/A';
                }
                ?>
            </div>
            <div class="metric-label">Initiative Timeline Progress</div>
            <div class="metric-sublabel">
                <i class="fas fa-hourglass-half"></i>
                <?php 
                if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                    $start = new DateTime($initiative[$start_date_col]);
                    $end = new DateTime($initiative[$end_date_col]);
                    $now = new DateTime();
                    
                    $total_years = round($start->diff($end)->days / 365, 1);
                    $elapsed_years = round($start->diff($now)->days / 365, 1);
                    
                    // Ensure elapsed doesn't exceed total
                    if ($elapsed_years > $total_years) {
                        $elapsed_years = $total_years;
                    }
                    
                    echo $elapsed_years . ' of ' . $total_years . ' years completed';
                } else {
                    echo 'Timeline not available';
                }
                ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="metric-card text-center">
            <div class="health-score-circle" style="background: conic-gradient(<?php echo $health_color; ?> 0deg <?php echo ($health_score * 3.6); ?>deg, #e9ecef <?php echo ($health_score * 3.6); ?>deg 360deg);">
                <div class="health-score-inner">
                    <div class="health-score-value"><?php echo $health_score; ?></div>
                    <div class="health-score-label">Health</div>
                </div>
            </div>
            <div class="metric-label d-flex align-items-center justify-content-center">
                Overall Initiative Health
                <span class="ms-2" tabindex="0" data-bs-toggle="popover" data-bs-trigger="focus hover" data-bs-placement="top" data-bs-html="true" title="How is Health Calculated?" data-bs-content="The health score is an average of all program statuses under this initiative.<br><br><strong>Scoring:</strong><br>- <strong>Completed:</strong> 100<br>- <strong>Active:</strong> 75<br>- <strong>On Hold:</strong> 50<br>- <strong>Delayed:</strong> 25<br>- <strong>Cancelled:</strong> 10<br><br>Higher scores mean better overall program performance.">
                    <i class="fas fa-info-circle text-secondary" style="cursor:pointer;"></i>
                </span>
            </div>
            <div class="health-description" style="color: <?php echo $health_color; ?>;">
                <i class="fas fa-check-circle"></i>
                <?php echo $health_description; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="metric-card text-center">
            <div class="status-active">
                <?php if ($initiative[$is_active_col]): ?>
                    ACTIVE
                <?php else: ?>
                    INACTIVE
                <?php endif; ?>
            </div>
            <div class="metric-label">Current Status</div>
            <div class="status-programs">
                <i class="fas fa-star"></i>
                <?php echo $initiative['agency_program_count']; ?> programs included
            </div>
        </div>
    </div>
</div>
