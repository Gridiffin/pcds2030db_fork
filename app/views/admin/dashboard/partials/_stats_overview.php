<?php
/**
 * Partial for Stats Overview
 * 
 * @var array $submission_stats Statistics related to submissions.
 */
?>
<div data-period-content="stats_section">
    <div class="row">
        <!-- Agencies Reporting Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card primary">
                <div class="card-body">
                    <div class="icon-container">
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                    <div class="stat-card-content">
                        <div class="stat-title">Users Reporting</div>
                        <div class="stat-value">
                            <?php echo $submission_stats['agencies_reported'] ?? 0; ?>/<?php echo $submission_stats['total_agencies'] ?? 0; ?>
                        </div>
                        <div class="stat-subtitle">
                            <i class="fas fa-check me-1"></i>
                            <?php echo $submission_stats['agencies_reported'] ?? 0; ?> Users Reported
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Programs On Track Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card warning">
                <div class="card-body">
                    <div class="icon-container">
                        <i class="fas fa-calendar-check stat-icon"></i>
                    </div>
                    <div class="stat-card-content">
                        <div class="stat-title">Programs On Track</div>
                        <div class="stat-value">
                            <?php echo $submission_stats['on_track_programs'] ?? 0; ?>
                        </div>
                        <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
                        <div class="stat-subtitle">
                            <i class="fas fa-chart-line me-1"></i>
                            <?php echo round(($submission_stats['on_track_programs'] / $submission_stats['total_programs']) * 100); ?>% of total
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Programs Delayed Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card danger">
                <div class="card-body">
                    <div class="icon-container">
                        <i class="fas fa-exclamation-triangle stat-icon"></i>
                    </div>
                    <div class="stat-card-content">
                        <div class="stat-title">Programs Delayed</div>
                        <div class="stat-value">
                            <?php echo $submission_stats['delayed_programs'] ?? 0; ?>
                        </div>
                        <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
                        <div class="stat-subtitle">
                            <i class="fas fa-chart-line me-1"></i>
                            <?php echo round(($submission_stats['delayed_programs'] / $submission_stats['total_programs']) * 100); ?>% of total
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Completion Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card success">
                <div class="card-body">
                    <div class="icon-container">
                        <i class="fas fa-clipboard-list stat-icon"></i>
                    </div>
                    <div class="stat-card-content">
                        <div class="stat-title">Overall Completion</div>
                        <div class="stat-value">
                            <?php echo $submission_stats['completion_percentage'] ?? 0; ?>%
                        </div>
                        <div class="stat-subtitle progress mt-2" style="height: 10px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: <?php echo $submission_stats['completion_percentage'] ?? 0; ?>%"
                                 aria-valuenow="<?php echo $submission_stats['completion_percentage'] ?? 0; ?>" 
                                 aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 