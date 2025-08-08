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
    <div class="admin-stat-card-modern primary admin-fade-in" role="article" aria-labelledby="stat-users-title">
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
    </div>

    <!-- Programs On Track Card -->
    <div class="admin-stat-card-modern success admin-fade-in">
        <div class="admin-stat-icon-modern">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="admin-stat-value-modern">
            <?php echo $submission_stats['on_track_programs'] ?? 0; ?>
        </div>
        <div class="admin-stat-title-modern">Programs On Track</div>
        <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
        <div class="admin-stat-subtitle-modern">
            <i class="fas fa-chart-line"></i>
            <span><?php echo round(($submission_stats['on_track_programs'] / $submission_stats['total_programs']) * 100); ?>% of total</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Programs Delayed Card -->
    <div class="admin-stat-card-modern warning admin-fade-in">
        <div class="admin-stat-icon-modern">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="admin-stat-value-modern">
            <?php echo $submission_stats['delayed_programs'] ?? 0; ?>
        </div>
        <div class="admin-stat-title-modern">Programs Delayed</div>
        <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
        <div class="admin-stat-subtitle-modern">
            <i class="fas fa-chart-line"></i>
            <span><?php echo round(($submission_stats['delayed_programs'] / $submission_stats['total_programs']) * 100); ?>% of total</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Overall Completion Card -->
    <div class="admin-stat-card-modern info admin-fade-in">
        <div class="admin-stat-icon-modern">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="admin-stat-value-modern">
            <?php echo $submission_stats['completion_percentage'] ?? 0; ?>%
        </div>
        <div class="admin-stat-title-modern">Overall Completion</div>
        <div class="admin-progress-modern">
            <div class="admin-progress-bar-modern" 
                 style="width: <?php echo $submission_stats['completion_percentage'] ?? 0; ?>%">
            </div>
        </div>
    </div>
</div>