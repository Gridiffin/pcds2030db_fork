<?php
/**
 * Modern Stats Overview Partial
 * 
 * Enhanced UI with modern card design and smooth animations
 * Maintains all original functionality
 */
?>

<!-- Modern Statistics Cards -->
<div class="admin-bento-stats" role="region" aria-label="Dashboard Statistics">
    <!-- Users Reporting Card -->
    <div class="admin-stat-card-modern primary admin-fade-in admin-stat-clickable" 
         role="button" 
         tabindex="0"
         data-stat-type="agencies_reported"
         data-stat-value="<?php echo $submission_stats['agencies_reported'] ?? 0; ?>/<?php echo $submission_stats['total_agencies'] ?? 0; ?>"
         data-period-id="<?php echo $period_id ?? ''; ?>"
         aria-label="Click to view detailed list of reporting users">
        <div class="admin-stat-icon-modern" aria-hidden="true">
            <i class="fas fa-users"></i>
        </div>
        <div class="admin-stat-value-modern" aria-label="<?php echo $submission_stats['agencies_reported'] ?? 0; ?> out of <?php echo $submission_stats['total_agencies'] ?? 0; ?> users reporting">
            <?php echo $submission_stats['agencies_reported'] ?? 0; ?>/<?php echo $submission_stats['total_agencies'] ?? 0; ?>
        </div>
        <div class="admin-stat-title-modern" id="stat-users-title">Users Reporting</div>
        <div class="admin-stat-subtitle-modern">
            <i class="fas fa-check" aria-hidden="true"></i>
            <span><?php echo $submission_stats['agencies_reported'] ?? 0; ?> Users Reported</span>
        </div>
        <div class="admin-stat-click-hint">
            <small class="text-white-50"><i class="fas fa-mouse-pointer me-1"></i>Click for details</small>
        </div>
    </div>

    <!-- Programs On Track Card -->
    <div class="admin-stat-card-modern success admin-fade-in admin-stat-clickable" 
         role="button" 
         tabindex="0"
         data-stat-type="on_track_programs"
         data-stat-value="<?php echo $submission_stats['on_track_programs'] ?? 0; ?>"
         data-period-id="<?php echo $period_id ?? ''; ?>"
         aria-label="Click to view detailed list of on-track programs">
        <div class="admin-stat-icon-modern" aria-hidden="true">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="admin-stat-value-modern">
            <?php echo $submission_stats['on_track_programs'] ?? 0; ?>
        </div>
        <div class="admin-stat-title-modern">Programs On Track</div>
        <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
        <div class="admin-stat-subtitle-modern">
            <i class="fas fa-chart-line" aria-hidden="true"></i>
            <span><?php echo round(($submission_stats['on_track_programs'] / $submission_stats['total_programs']) * 100); ?>% of total</span>
        </div>
        <?php endif; ?>
        <div class="admin-stat-click-hint">
            <small class="text-white-50"><i class="fas fa-mouse-pointer me-1"></i>Click for details</small>
        </div>
    </div>

    <!-- Programs Delayed Card -->
    <div class="admin-stat-card-modern warning admin-fade-in admin-stat-clickable" 
         role="button" 
         tabindex="0"
         data-stat-type="delayed_programs"
         data-stat-value="<?php echo $submission_stats['delayed_programs'] ?? 0; ?>"
         data-period-id="<?php echo $period_id ?? ''; ?>"
         aria-label="Click to view detailed list of delayed programs">
        <div class="admin-stat-icon-modern" aria-hidden="true">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="admin-stat-value-modern">
            <?php echo $submission_stats['delayed_programs'] ?? 0; ?>
        </div>
        <div class="admin-stat-title-modern">Programs Delayed</div>
        <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
        <div class="admin-stat-subtitle-modern">
            <i class="fas fa-chart-line" aria-hidden="true"></i>
            <span><?php echo round(($submission_stats['delayed_programs'] / $submission_stats['total_programs']) * 100); ?>% of total</span>
        </div>
        <?php endif; ?>
        <div class="admin-stat-click-hint">
            <small class="text-white-50"><i class="fas fa-mouse-pointer me-1"></i>Click for details</small>
        </div>
    </div>

    <!-- Monthly Target Achieved Card -->
    <div class="admin-stat-card-modern info admin-fade-in admin-stat-clickable" 
         role="button" 
         tabindex="0"
         data-stat-type="monthly_target_achieved"
         data-stat-value="<?php echo $submission_stats['monthly_target_achieved_programs'] ?? 0; ?>"
         data-period-id="<?php echo $period_id ?? ''; ?>"
         aria-label="Click to view detailed list of programs with monthly target achieved">
        <div class="admin-stat-icon-modern" aria-hidden="true">
            <i class="fas fa-bullseye"></i>
        </div>
        <div class="admin-stat-value-modern">
            <?php echo $submission_stats['monthly_target_achieved_programs'] ?? 0; ?>
        </div>
        <div class="admin-stat-title-modern">Monthly Target Achieved</div>
        <div class="admin-stat-subtitle-modern">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <span><?php echo $submission_stats['monthly_target_achieved_programs'] ?? 0; ?> Programs</span>
        </div>
        <div class="admin-stat-click-hint">
            <small class="text-white-50"><i class="fas fa-mouse-pointer me-1"></i>Click for details</small>
        </div>
    </div>
</div>