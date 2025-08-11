<?php
/**
 * Modern Outcomes Overview Partial
 * 
 * Streamlined design focused on key metrics and chart display
 * Maintains all original functionality with enhanced UX
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
?>

<div class="admin-card-modern admin-fade-in">
    <div class="admin-card-modern-header">
        <h3 class="admin-card-modern-title">
            <div class="admin-card-icon-modern">
                <i class="fas fa-chart-line"></i>
            </div>
            Outcomes Overview
        </h3>
    </div>
    
    <div class="admin-card-modern-content">
        <!-- Key Metrics Grid -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="text-center p-3 bg-light rounded-3">
                    <div class="h2 text-primary mb-1">
                        <?php echo $outcomes_stats['total_outcomes']; ?>
                    </div>
                    <div class="small text-muted">Total Outcomes</div>
                </div>
            </div>
        </div>

        <!-- Management Actions -->
        <div class="d-grid gap-2 mb-4">
            <a href="<?php echo view_url('admin', 'outcomes/manage_outcomes.php'); ?>" 
               class="btn btn-outline-primary rounded-pill">
                <i class="fas fa-cogs me-2"></i>Manage Outcomes
            </a>
        </div>

        <!-- Recent Activity -->
        <?php if (!empty($outcomes_stats['recent_outcomes'])): ?>
            <div class="border-top pt-3">
                <h6 class="text-muted mb-3">
                    <i class="fas fa-clock me-2"></i>Recent Activity
                </h6>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($outcomes_stats['recent_outcomes'], 0, 3) as $outcome): ?>
                        <div class="list-group-item px-0 py-2 border-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-medium small">
                                        <?php echo htmlspecialchars($outcome['table_name']); ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?php echo htmlspecialchars($outcome['sector_name'] ?? 'Unknown Sector'); ?>
                                    </div>
                                </div>
                                <span class="admin-badge-modern <?php echo $outcome['is_draft'] ? 'warning' : 'success'; ?> small">
                                    <?php echo $outcome['is_draft'] ? 'Draft' : 'Live'; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Chart Container -->
        <div class="border-top pt-3 mt-3">
            <h6 class="text-muted mb-3">
                <i class="fas fa-chart-area me-2"></i>Outcome Graphs
            </h6>
            <div id="adminOutcomeGraphsCompact" style="min-height: 200px;">
                <!-- Charts will be rendered here -->
            </div>
        </div>

    </div>
</div>

<!-- Enhanced Chart Rendering Script -->
<script>
// Compact admin outcomes rendering
window.adminDashboardOutcomesCompact = {
    charts: <?php echo json_encode($admin_chart_outcomes); ?>
};

(function initCompactAdminOutcomes(){
    const graphRoot = document.getElementById('adminOutcomeGraphsCompact');
    const data = window.adminDashboardOutcomesCompact || {charts:[]};
    let tries = 0; const max = 50;
    
    function ensureAndRender(){
        if (typeof Chart === 'undefined') { 
            tries++; 
            if (tries < max) return setTimeout(ensureAndRender, 100); 
        }
        
        if (graphRoot){
            if (!data.charts || data.charts.length === 0){
                graphRoot.innerHTML = '<div class="text-muted text-center py-4"><i class="fas fa-chart-line fa-2x mb-2 opacity-50"></i><br>No outcome graphs available</div>';
            } else {
                // Show only first 2 charts in compact view
                const chartsToShow = data.charts.slice(0, 2);
                graphRoot.innerHTML = '<div class="row g-3"></div>';
                const rowContainer = graphRoot.querySelector('.row');
                
                chartsToShow.forEach((outcome, idx) => {
                    const col = document.createElement('div');
                    col.className = 'col-12';
                    col.innerHTML = `
                        <div class="border rounded-3 p-3 chart-clickable" title="Click to view outcomes">
                            <h6 class="mb-2 text-truncate">${escapeHtml(outcome.title || outcome.code || 'Outcome')}</h6>
                            <div style="position:relative;height:150px">
                                <canvas id="compactAdminChart_${idx}"></canvas>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-mouse-pointer me-1"></i>Click to view details
                                </small>
                            </div>
                        </div>`;
                    rowContainer.appendChild(col);
                    
                    try {
                        const series = transformOutcomeToSeries(outcome);
                        if (series && typeof Chart !== 'undefined'){
                            const ctx = col.querySelector('canvas').getContext('2d');
                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: series.labels,
                                    datasets: series.datasets
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false }
                                    },
                                    scales: {
                                        x: { display: false },
                                        y: { 
                                            beginAtZero: true,
                                            ticks: { font: { size: 10 } }
                                        }
                                    },
                                    elements: {
                                        point: { radius: 2 },
                                        line: { tension: 0.4 }
                                    },
                                    onClick: function(event, elements) {
                                        // Redirect to view outcomes page when chart is clicked
                                        window.location.href = '<?php echo view_url('admin', 'outcomes/manage_outcomes.php'); ?>';
                                    },
                                    onHover: function(event, elements) {
                                        // Change cursor to pointer on hover
                                        event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
                                    }
                                }
                            });
                        }
                    } catch(e) {
                        console.error('Compact chart render failed', e);
                    }
                });
                
                if (data.charts.length > 2) {
                    const moreDiv = document.createElement('div');
                    moreDiv.className = 'text-center mt-3';
                    moreDiv.innerHTML = `
                        <a href="<?php echo view_url('admin', 'outcomes/manage_outcomes.php'); ?>" 
                           class="btn btn-sm btn-outline-secondary rounded-pill">
                            <i class="fas fa-plus me-1"></i>View ${data.charts.length - 2} More
                        </a>`;
                    graphRoot.appendChild(moreDiv);
                }
            }
        }

    }
    
    function escapeHtml(str){ 
        return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s])); 
    }
    
    function chartColor(i){ 
        const palette = ['#11998e','#2f80ed','#f2994a','#eb5757','#9b51e0','#27ae60']; 
        return palette[i % palette.length]; 
    }
    
    function transformOutcomeToSeries(outcome){
        const d = outcome.data || {};
        if (d && Array.isArray(d.rows) && Array.isArray(d.columns) && d.columns.length){
            const labels = d.rows.map(r => r.month || r.label || r.date || '');
            const datasets = d.columns.map((col,i) => ({
                label: String(col), 
                data: d.rows.map(r => Number(r[col]) || 0), 
                borderColor: chartColor(i), 
                backgroundColor: 'transparent', 
                tension: 0.3
            }));
            return { labels, datasets };
        }
        if (Array.isArray(d.points)){
            return { 
                labels: d.points.map(p => p.x), 
                datasets: [{ 
                    label: outcome.title || 'Series', 
                    data: d.points.map(p => Number(p.y) || 0), 
                    borderColor: chartColor(0), 
                    backgroundColor: 'transparent', 
                    tension: 0.3 
                }] 
            };
        }
        if (Array.isArray(d.values)){
            return { 
                labels: d.values.map((_, i) => String(i + 1)), 
                datasets: [{ 
                    label: outcome.title || 'Series', 
                    data: d.values.map(v => Number(v) || 0), 
                    borderColor: chartColor(0), 
                    backgroundColor: 'transparent', 
                    tension: 0.3 
                }] 
            };
        }
        return null;
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureAndRender);
    } else {
        ensureAndRender();
    }
})();
</script>

<style>
.chart-clickable {
    transition: all 0.2s ease;
    cursor: pointer;
}

.chart-clickable:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    border-color: #0d6efd !important;
}

.chart-clickable canvas {
    cursor: pointer;
}
</style>