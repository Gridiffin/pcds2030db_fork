<?php
/**
 * Partial for Outcomes Overview
 * 
 * @var array $outcomes_stats Statistics related to outcomes.
 */
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';

// Use pre-fetched outcomes from parent scope when available
$all_outcomes = isset($all_outcomes) ? $all_outcomes : get_all_outcomes();
$admin_chart_outcomes = isset($admin_chart_outcomes)
    ? $admin_chart_outcomes
    : array_values(array_filter($all_outcomes, function($o){
        $t = strtolower($o['type'] ?? '');
        return in_array($t, ['chart','graph']);
    }));
$admin_kpi_outcomes = isset($admin_kpi_outcomes)
    ? $admin_kpi_outcomes
    : array_values(array_filter($all_outcomes, function($o){
        $t = strtolower($o['type'] ?? '');
        return $t === 'kpi';
    }));
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
                <!-- Outcome Graphs and KPIs -->
                <div class="row mt-4 g-4">
                    <div class="col-12">
                        <div class="card h-100">
                            <div class="card-header"><h6 class="m-0"><i class="fas fa-chart-line me-1"></i>Outcome Graphs</h6></div>
                            <div class="card-body">
                                <div id="adminOutcomeGraphs" class="row g-4"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card h-100">
                            <div class="card-header"><h6 class="m-0"><i class="fas fa-gauge-high me-1"></i>KPIs</h6></div>
                            <div class="card-body">
                                <div id="adminKpiOutcomes" class="row g-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
<script>
// Provide data for admin charts/KPIs
window.adminDashboardOutcomes = {
    charts: <?php echo json_encode($admin_chart_outcomes); ?>,
    kpis: <?php echo json_encode($admin_kpi_outcomes); ?>
};

(function initAdminOutcomes(){
    const graphRoot = document.getElementById('adminOutcomeGraphs');
    const kpiRoot = document.getElementById('adminKpiOutcomes');
    const data = window.adminDashboardOutcomes || {charts:[], kpis:[]};
    let tries = 0; const max = 50;
    function ensureAndRender(){
        if (typeof Chart === 'undefined') { tries++; if (tries < max) return setTimeout(ensureAndRender, 100); }
        // Graphs
        if (graphRoot){
            if (!data.charts || data.charts.length === 0){
                graphRoot.innerHTML = '<div class="text-muted py-3 text-center">No outcome graphs to display</div>';
            } else {
                graphRoot.innerHTML = '';
                const chartElems = [];
                data.charts.forEach((o, idx) => {
                    const col = document.createElement('div');
                    col.className = 'col-md-6 col-lg-4';
                    col.innerHTML = '<div class="card h-100"><div class="card-body">'
                        + '<h6 class="card-title mb-2">' + escapeHtml(o.title || o.code || 'Outcome') + '</h6>'
                        + '<div style="position:relative;height:220px"><canvas id="adminOutcomeChart_'+idx+'" data-chart-index="'+idx+'"></canvas></div>'
                        + '</div></div>';
                    graphRoot.appendChild(col);
                    try {
                        const series = transformOutcomeToSeries(o);
                        if (series){
                            const canvas = col.querySelector('canvas');
                            canvas._series = series; // attach for lazy render
                            chartElems.push(canvas);
                        } else {
                            col.querySelector('.card-body').insertAdjacentHTML('beforeend','<div class="text-muted">No data</div>');
                        }
                    } catch(e){ console.error('Admin outcome render failed', e); }
                });
                // Lazy render charts when they become visible to reduce main-thread jank
                if (chartElems.length){
                    const chartsMap = new Map();
                    const renderChart = (canvas) => {
                        if (!canvas || chartsMap.has(canvas)) return;
                        const series = canvas._series;
                        if (!series || typeof Chart === 'undefined') return;
                        const ctx = canvas.getContext('2d');
                        const chart = new Chart(ctx, { type:'line', data:{ labels: series.labels, datasets: series.datasets }, options:{
                            responsive:true, maintainAspectRatio:false,
                            animation: { duration: 300 },
                            plugins:{ legend:{ position:'bottom' } },
                            scales:{ y:{ beginAtZero:true } }
                        }});
                        chartsMap.set(canvas, chart);
                    };
                    if ('IntersectionObserver' in window){
                        const io = new IntersectionObserver((entries) => {
                            entries.forEach(entry => { if (entry.isIntersecting){ renderChart(entry.target); io.unobserve(entry.target); } });
                        }, { root:null, rootMargin:'100px', threshold:0.01 });
                        chartElems.forEach(c => io.observe(c));
                    } else {
                        // Fallback: stagger rendering
                        let i = 0; const step = () => { if (i < chartElems.length){ renderChart(chartElems[i++]); setTimeout(step, 50); } }; step();
                    }
                }
            }
        }
        // KPIs
        if (kpiRoot){
            if (!data.kpis || data.kpis.length === 0){
                kpiRoot.innerHTML = '<div class="text-muted py-3 text-center">No KPI outcomes to display</div>';
            } else {
                kpiRoot.innerHTML = '';
                data.kpis.forEach((o) => {
                    const items = Array.isArray(o.data) ? o.data : (Array.isArray(o.data?.items) ? o.data.items : (o.data ? [o.data] : []));
                    if (!items.length) return;
                    const header = document.createElement('div');
                    header.className = 'col-12';
                    header.innerHTML = '<div class="small text-muted mt-1 mb-1">' + escapeHtml(o.title || o.code || 'KPI') + '</div>';
                    kpiRoot.appendChild(header);
                    items.forEach((entry) => {
                        const valueRaw = entry && (entry.value ?? entry.current ?? entry.kpi);
                        const unit = entry && (entry.unit ?? entry.suffix) || '';
                        const extra = entry && entry.extra ? ' <span class="d-block small text-muted">' + escapeHtml(entry.extra) + '</span>' : '';
                        const desc = entry && entry.description ? '<div class="small text-muted">' + escapeHtml(entry.description) + '</div>' : '';
                        const formatted = formatValue(valueRaw);
                        const card = document.createElement('div');
                        card.className = 'col-sm-6 col-md-4 col-lg-3';
                        card.innerHTML = '<div class="card h-100 text-center">'
                            + '<div class="card-body">'
                            + '<div class="text-forest-medium mb-2"><i class="fas fa-bullseye"></i></div>'
                            + '<div class="h4">' + escapeHtml(formatted) + (unit ? ' ' + escapeHtml(unit) : '') + '</div>'
                            + desc + extra
                            + '</div></div>';
                        kpiRoot.appendChild(card);
                    });
                });
            }
        }
    }
    function escapeHtml(str){ return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s])); }
    function formatValue(v){ if (v===null||v===undefined) return '-'; if (typeof v==='number') return v.toLocaleString(); const n=Number(v); return Number.isFinite(n)?n.toLocaleString():String(v); }
    function chartColor(i){ const p=['#11998e','#2f80ed','#f2994a','#eb5757','#9b51e0','#27ae60']; return p[i%p.length]; }
    function transformOutcomeToSeries(outcome){
        const d = outcome.data || {};
        if (d && Array.isArray(d.rows) && Array.isArray(d.columns) && d.columns.length){
            const labels = d.rows.map(r => r.month || r.label || r.date || '');
            const datasets = d.columns.map((col,i)=>({ label:String(col), data:d.rows.map(r=>Number(r[col])||0), borderColor:chartColor(i), backgroundColor:'transparent', tension:0.3 }));
            return { labels, datasets };
        }
        if (Array.isArray(d.points)){
            return { labels: d.points.map(p=>p.x), datasets: [{ label: outcome.title || 'Series', data: d.points.map(p=>Number(p.y)||0), borderColor: chartColor(0), backgroundColor:'transparent', tension:0.3 }] };
        }
        if (Array.isArray(d.values)){
            return { labels: d.values.map((_,i)=>String(i+1)), datasets: [{ label: outcome.title || 'Series', data: d.values.map(v=>Number(v)||0), borderColor: chartColor(0), backgroundColor:'transparent', tension:0.3 }] };
        }
        return null;
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ensureAndRender); else ensureAndRender();
})();
</script>