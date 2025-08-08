<?php
/**
 * Partial for Outcomes Overview
 * 
 * @var array $outcomes_stats Statistics related to outcomes.
 */
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';
$outcomes = get_all_outcomes();
// Only show graph outcomes; exclude KPI or other types
$outcomes = array_values(array_filter($outcomes, function($o){
    $t = strtolower($o['type'] ?? '');
    return $t === 'chart' || $t === 'graph';
}));
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
                    <div class="col-12">
                        <div id="adminOutcomeGraphsContainer" class="row g-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
<?php if (!empty($outcomes)): ?>
<script>
// Provide outcomes to JS (only necessary fields)
window.adminDashboardOutcomes = {
    charts: <?php echo json_encode(array_map(function($o){
        return [
            'id' => $o['id'] ?? null,
            'code' => $o['code'] ?? null,
            'title' => $o['title'] ?? ($o['code'] ?? 'Outcome'),
            'data' => $o['data'] ?? null
        ];
    }, $outcomes)); ?>
};

(function renderAdminOutcomes(){
    const graphContainer = document.getElementById('adminOutcomeGraphsContainer');
    if (!graphContainer) return;
    const data = window.adminDashboardOutcomes || {charts: []};
    let tries = 0; const max = 50;
    function ensureChartJsAndRender(){
        if (typeof Chart === 'undefined') {
            tries++; if (tries < max) return setTimeout(ensureChartJsAndRender, 100);
        }
        renderCharts();
    }
    function renderCharts(){
        const charts = Array.isArray(data.charts) ? data.charts : [];
        if (!charts.length) {
            graphContainer.innerHTML = '<div class="text-muted py-3 text-center">No graph outcomes to display</div>';
            return;
        }
        graphContainer.innerHTML = '';
        charts.forEach((o, idx) => {
            const col = document.createElement('div');
            col.className = 'col-lg-6';
            col.innerHTML = `
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">${escapeHtml(o.title || o.code || 'Outcome')}</h6>
                        <div style="position:relative;height:260px"><canvas id="adminOutcomeChart_${idx}"></canvas></div>
                    </div>
                </div>`;
            graphContainer.appendChild(col);
            try {
                const series = transformOutcomeToSeries(o);
                if (series && typeof Chart !== 'undefined') {
                    const ctx = col.querySelector('canvas').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: { labels: series.labels, datasets: series.datasets },
                        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } }, scales:{ y:{ beginAtZero:true } } }
                    });
                } else {
                    col.querySelector('.card-body').insertAdjacentHTML('beforeend', '<div class="text-muted">No data</div>');
                }
            } catch(e) { console.error('Admin outcome render failed', e); }
        });
    }
    function escapeHtml(str){
        return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]));
    }
    function transformOutcomeToSeries(outcome){
        const d = outcome.data || {};
        // Tabular { rows, columns }
        if (d && Array.isArray(d.rows) && Array.isArray(d.columns) && d.columns.length){
            const labels = (d.rows || []).map(r => r.month || r.label || r.date || '');
            const datasets = (d.columns || [])
                .filter(col => col !== 'month')
                .map((col, i) => ({
                    label: String(col),
                    data: (d.rows || []).map(r => Number(r[col]) || 0),
                    borderColor: chartColor(i),
                    backgroundColor: 'transparent',
                    tension: 0.3
                }));
            return { labels, datasets };
        }
        // Simple points { points: [{x,y}] }
        if (Array.isArray(d.points)){
            const labels = d.points.map(p => p.x);
            const datasets = [{ label: outcome.title || 'Series', data: d.points.map(p => Number(p.y)||0), borderColor: chartColor(0), backgroundColor:'transparent', tension:0.3 }];
            return { labels, datasets };
        }
        // Fallback values array { values: [...] }
        if (Array.isArray(d.values)){
            const labels = d.values.map((_,i)=> String(i+1));
            const datasets = [{ label: outcome.title || 'Series', data: d.values.map(v => Number(v)||0), borderColor: chartColor(0), backgroundColor:'transparent', tension:0.3 }];
            return { labels, datasets };
        }
        return null;
    }
    function chartColor(i){
        const palette = ['#11998e','#2f80ed','#f2994a','#eb5757','#9b51e0','#27ae60'];
        return palette[i % palette.length];
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureChartJsAndRender);
    } else {
        ensureChartJsAndRender();
    }
})();
</script>
<?php endif; ?> 