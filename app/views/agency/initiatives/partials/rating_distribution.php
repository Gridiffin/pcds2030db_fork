<?php
/**
 * Rating Distribution Chart Partial
 * Contains the program rating distribution chart and statistics
 */
?>
<!-- Program Rating Distribution Chart -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title m-0">
                <i class="fas fa-chart-pie me-2"></i>Program Rating Distribution
            </h5>
            <span class="badge bg-secondary">
                <?php echo count($programs); ?> programs
            </span>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($programs)): ?>
            <?php
            // Calculate rating distribution for initiative programs
            $rating_distribution = [
                'target-achieved' => 0,
                'on-track' => 0,
                'on-track-yearly' => 0,
                'delayed' => 0,
                'severe-delay' => 0,
                'completed' => 0,
                'not-started' => 0
            ];
            
            foreach ($programs as $program) {
                $status = convert_legacy_rating($program['rating'] ?? 'not_started');
                if (isset($rating_distribution[$status])) {
                    $rating_distribution[$status]++;
                } else {
                    $rating_distribution['not-started']++;
                }
            }
            
            $total_programs = count($programs);
            
            // Define display labels and colors
            $rating_config = [
                'target-achieved' => ['label' => 'Target Achieved', 'color' => 'success', 'icon' => 'fas fa-check-circle'],
                'completed' => ['label' => 'Completed', 'color' => 'success', 'icon' => 'fas fa-check-circle'],
                'on-track' => ['label' => 'On Track', 'color' => 'warning', 'icon' => 'fas fa-clock'],
                'on-track-yearly' => ['label' => 'On Track (Yearly)', 'color' => 'warning', 'icon' => 'fas fa-calendar-check'],
                'delayed' => ['label' => 'Delayed', 'color' => 'danger', 'icon' => 'fas fa-exclamation-triangle'],
                'severe-delay' => ['label' => 'Severe Delay', 'color' => 'danger', 'icon' => 'fas fa-exclamation-circle'],
                'not-started' => ['label' => 'Not Started', 'color' => 'secondary', 'icon' => 'fas fa-pause-circle']
            ];
            ?>
            <div class="row">
                <div class="col-lg-6">
                    <div class="chart-container" style="position: relative; height:300px; width:100%">
                        <canvas id="initiativeRatingChart"></canvas>
                        <!-- Hidden element for rating data (used by JavaScript) -->
                        <div id="ratingData" style="display: none;">
                            <?php echo json_encode($rating_distribution); ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="rating-stats">
                        <h6 class="text-muted mb-3">Rating Breakdown</h6>
                        
                        <?php foreach ($rating_config as $status => $config): ?>
                            <?php if ($rating_distribution[$status] > 0): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="<?php echo $config['icon']; ?> me-2 text-<?php echo $config['color']; ?>"></i>
                                    <span><?php echo $config['label']; ?></span>
                                </div>
                                <div>
                                    <span class="badge bg-<?php echo $config['color']; ?> me-2">
                                        <?php echo $rating_distribution[$status]; ?>
                                    </span>
                                    <small class="text-muted">
                                        (<?php echo $total_programs > 0 ? round(($rating_distribution[$status] / $total_programs) * 100) : 0; ?>%)
                                    </small>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-muted text-center py-4">
                <i class="fas fa-chart-pie fa-2x mb-3"></i>
                <div>No programs found to display rating distribution.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
