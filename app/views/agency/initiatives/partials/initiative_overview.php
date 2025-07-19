<?php
/**
 * Initiative Overview Partial
 * Contains the initiative header with title and meta information
 */
?>
<!-- Initiative Overview Section -->
<div class="container-fluid">
    <div class="initiative-overview">
        <div class="initiative-title">
            <i class="fas fa-leaf"></i>
            <?php echo htmlspecialchars($initiative[$initiative_name_col]); ?>
            <?php if (!empty($initiative[$initiative_number_col])): ?>
                <span class="badge bg-primary ms-3" style="font-size: 0.6em; padding: 0.5rem 1rem; vertical-align: middle;">
                    #<?php echo htmlspecialchars($initiative[$initiative_number_col]); ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="initiative-meta">
            <div class="meta-item">
                <i class="fas fa-calendar"></i>
                <span>
                    <?php 
                    if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                        echo date('Y-m-d', strtotime($initiative[$start_date_col])) . ' to ' . date('Y-m-d', strtotime($initiative[$end_date_col]));
                        
                        // Calculate duration in years
                        $start = new DateTime($initiative[$start_date_col]);
                        $end = new DateTime($initiative[$end_date_col]);
                        $interval = $start->diff($end);
                        $years = $interval->y + ($interval->m / 12) + ($interval->d / 365);
                        echo ' (' . round($years, 1) . ' years)';
                    } else {
                        echo 'Timeline not specified';
                    }
                    ?>
                </span>
            </div>
            <div class="meta-item">
                <i class="fas fa-clock"></i>
                <span>
                    <?php 
                    if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                        $start = new DateTime($initiative[$start_date_col]);
                        $end = new DateTime($initiative[$end_date_col]);
                        $now = new DateTime();
                        
                        $total_duration = $start->diff($end);
                        $elapsed = $start->diff($now);
                        
                        $total_days = $total_duration->days;
                        $elapsed_days = $elapsed->days;
                        
                        $elapsed_years = round($elapsed_days / 365, 1);
                        $remaining_years = round(($total_days - $elapsed_days) / 365, 1);
                        
                        echo $elapsed_years . ' years elapsed, ' . $remaining_years . ' years remaining';
                    } else {
                        echo 'Timeline not available';
                    }
                    ?>
                </span>
            </div>
            <div class="meta-item">
                <span class="badge" style="background-color: <?php echo $health_color; ?>;">
                    Health Score: <?php echo $health_score; ?>% - <?php echo $health_description; ?>
                </span>
            </div>
        </div>
    </div>
</div>
