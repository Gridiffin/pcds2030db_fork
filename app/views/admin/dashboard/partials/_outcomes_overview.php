<?php
/**
 * Partial for Outcomes Overview
 * 
 * @var array $outcomes_stats Statistics related to outcomes.
 */
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';
$outcomes = get_all_outcomes();
?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Outcomes Overview</h5>
            </div>
            <div class="card-body">
                <div class="row gx-4 gy-4">
                    <!-- Outcomes Statistics Cards -->
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-primary text-dark h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-clipboard-list fa-3x mb-3 text-dark"></i>
                                <h4 class="text-dark"><?php echo $outcomes_stats['total_outcomes']; ?></h4>
                                <p class="mb-0 text-dark">Total Outcomes</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-success text-dark h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-check-square fa-3x mb-3 text-dark"></i>
                                <h4 class="text-dark"><?php echo $outcomes_stats['total_outcomes']; ?></h4>
                                <p class="mb-0 text-dark">Total Outcomes</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-file-alt fa-3x mb-3 text-dark"></i>
                                <h4 class="text-dark"><?php echo $outcomes_stats['draft_outcomes'] ?? 0; ?></h4>
                                <p class="mb-0 text-dark">Drafts</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-info text-dark h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-building fa-3x mb-3 text-dark"></i>
                                <h4 class="text-dark"><?php echo $outcomes_stats['sectors_with_outcomes'] ?? 0; ?></h4>
                                <p class="mb-0 text-dark">Sectors</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Outcomes Actions -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="p-3 border rounded h-100 bg-light">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-primary me-2" style="min-width: 90px;">Manage</span>
                                <span class="fw-bold">Outcomes Management</span>
                            </div>
                            <p class="text-muted mb-3">View and manage all outcomes data across sectors</p>
                            <div class="d-flex gap-2">
                                <a href="<?php echo view_url('admin', 'outcomes/manage_outcomes.php'); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-cogs me-1"></i> Manage Outcomes
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="p-3 border rounded h-100 bg-light">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-info me-2" style="min-width: 90px;">Activity</span>
                                <span class="fw-bold">Recent Outcomes Activity</span>
                            </div>
                            <?php if (empty($outcomes_stats['recent_outcomes'])): ?>
                                <div class="alert alert-light">
                                    <i class="fas fa-info-circle me-2"></i>No recent outcomes activity found.
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach (array_slice($outcomes_stats['recent_outcomes'], 0, 3) as $outcome): ?>
                                        <div class="list-group-item px-0 py-2 border-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($outcome['table_name']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($outcome['sector_name'] ?? 'Unknown Sector'); ?></small>
                                                </div>
                                                <span class="badge bg-<?php echo $outcome['is_draft'] ? 'warning' : 'success'; ?>">
                                                    <?php echo $outcome['is_draft'] ? 'Draft' : 'Submitted'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center mt-2">
                                    <a href="<?php echo view_url('admin', 'outcomes/outcome_history.php'); ?>" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-history me-1"></i> View All Activity <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Outcome Line Charts -->
                <div class="row mt-4">
                    <?php foreach ($outcomes as $outcome): ?>
                        <?php
                        $data = $outcome['data'] ?? [];
                        $columns = $data['columns'] ?? [];
                        $rows = $data['rows'] ?? [];
                        $has_data = !empty($columns) && !empty($rows);
                        ?>
                        <?php if ($has_data): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-2"><?php echo htmlspecialchars($outcome['title']); ?></h6>
                                    <div class="chart-canvas-container">
                                        <canvas id="outcomeChart_<?php echo $outcome['id']; ?>" class="chart-canvas" height="200"></canvas>
                                    </div>
                                    <pre style="font-size:12px; background:#f8f9fa; border:1px solid #eee; padding:8px; margin-top:10px; max-height:200px; overflow:auto;">
Columns:
<?php print_r($columns); ?>
Rows:
<?php print_r($rows); ?>
</pre>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div> 
<?php if (!empty($outcomes)): ?>
<script src="<?php echo asset_url('js/charts', 'outcomes-chart.js'); ?>"></script>
<script>
<?php foreach ($outcomes as $outcome):
    $data = $outcome['data'] ?? [];
    $columns = $data['columns'] ?? [];
    $rows = $data['rows'] ?? [];
    $has_data = !empty($columns) && !empty($rows);
    if (!$has_data) continue;
    // Prepare labels (months) and datasets (years)
    $labels = array_map(function($row) { return $row['month'] ?? ''; }, $rows);
    $datasets = [];
    foreach ($columns as $year) {
        if ($year === 'month') continue;
        $datasets[] = [
            'label' => $year,
            'data' => array_map(function($row) use ($year) { return isset($row[$year]) ? floatval($row[$year]) : null; }, $rows),
            'fill' => false,
            'borderColor' => 'rgba(54, 162, 235, 1)',
            'tension' => 0.1
        ];
    }
?>
(function() {
    var ctx = document.getElementById('outcomeChart_<?php echo $outcome['id']; ?>').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: <?php echo json_encode($datasets); ?>
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: { x: { display: true }, y: { display: true } }
        }
    });
})();
<?php endforeach; ?>
</script>
<?php endif; ?> 