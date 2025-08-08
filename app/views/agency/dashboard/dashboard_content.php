<?php
/**
 * Dashboard Content
 * 
 * Main content file for the agency dashboard using base.php layout
 */

// This file is included by base.php as the $contentFile
?>

<!-- Main Content -->
<main>
    <div class="container-fluid">
        <!-- Initiatives Section -->
        <?php require_once __DIR__ . '/partials/initiatives_section.php'; ?>

        <!-- Outcomes (under Initiatives) -->
        <section class="mb-4">
            <div class="container-fluid">
                <div class="row g-4">
                    <!-- Individual Graph Outcomes -->
                    <div class="col-12">
                        <div class="card-modern card-elevated-modern">
                            <div class="card-header-modern">
                                <h3 class="card-title-modern">
                                    <div class="card-icon-modern text-forest-medium">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    Outcome Graphs
                                </h3>
                            </div>
                            <div class="card-body-modern">
                                <div id="outcomeGraphsContainer" class="row g-4"></div>
                            </div>
                        </div>
                    </div>
                    <!-- KPI Outcomes -->
                    <div class="col-12">
                        <div class="card-modern card-elevated-modern">
                            <div class="card-header-modern">
                                <h3 class="card-title-modern">
                                    <div class="card-icon-modern text-forest-medium">
                                        <i class="fas fa-gauge-high"></i>
                                    </div>
                                    KPIs
                                </h3>
                            </div>
                            <div class="card-body-modern">
                                <div id="kpiOutcomesContainer" class="row g-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Programs Section -->
        <?php require_once __DIR__ . '/partials/programs_section.php'; ?>

        <!-- Outcomes Section -->
        <?php require_once __DIR__ . '/partials/outcomes_section.php'; ?>
    </div>
</main>

<!-- Pass chart data to JavaScript -->
<script>
    // Initialize chart with data
    window.programRatingChartData = {
        labels: <?php echo json_encode($chartData['labels']); ?>,
        data: <?php echo json_encode($chartData['data']); ?>
    };
    // Provide raw outcomes for individual rendering
    window.dashboardOutcomes = { 
        charts: <?php echo json_encode($chartOutcomes); ?>,
        kpis: <?php echo json_encode($kpiOutcomes); ?>
    };
    
    // Pass current period ID for AJAX requests
    window.currentPeriodId = <?php echo json_encode($period_id); ?>;

    // Render individual outcomes (line charts) after Chart.js is ready
    (function renderIndividualOutcomes(){
        const graphContainer = document.getElementById('outcomeGraphsContainer');
        const kpiContainer = document.getElementById('kpiOutcomesContainer');
        if (!graphContainer && !kpiContainer) return;
        const data = window.dashboardOutcomes || {charts: [], kpis: []};
        let tries = 0; const max = 50;
        function ensureChartJsAndRender(){
            if (typeof Chart === 'undefined') {
                tries++; if (tries < max) return setTimeout(ensureChartJsAndRender, 100);
            }
            // Render chart outcomes
            if (graphContainer) {
                if (!data.charts || data.charts.length === 0) {
                    graphContainer.innerHTML = '<div class="text-muted py-3 text-center">No graph outcomes to display</div>';
                } else {
                    graphContainer.innerHTML = '';
                    data.charts.forEach((o, idx) => {
                        const col = document.createElement('div');
                        col.className = 'col-lg-6';
                        col.innerHTML = `
                            <div class="card-modern h-100">
                                <div class="card-header-modern"><h4 class="h6 mb-0">${escapeHtml(o.title || o.code || 'Outcome')}</h4></div>
                                <div class="card-body-modern">
                                    <div style="position:relative;height:260px"><canvas id="outcomeChart_${idx}"></canvas></div>
                                </div>
                            </div>`;
                        graphContainer.appendChild(col);
                        try {
                            const series = transformOutcomeToSeries(o);
                            if (series && typeof Chart !== 'undefined') {
                                const ctx = col.querySelector('canvas').getContext('2d');
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: series.labels,
                                        datasets: series.datasets
                                    },
                                    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom'}}, scales:{ y:{ beginAtZero:true } } }
                                });
                            } else {
                                col.querySelector('.card-body-modern').innerHTML = '<div class="text-muted">No data</div>';
                            }
                        } catch(e) {
                            console.error('Outcome render failed', e);
                        }
                    });
                }
            }
            // Render KPI outcomes
            if (kpiContainer) {
                if (!data.kpis || data.kpis.length === 0) {
                    kpiContainer.innerHTML = '<div class="text-muted py-3 text-center">No KPI outcomes to display</div>';
                } else {
                    kpiContainer.innerHTML = '';
                    data.kpis.forEach((o) => {
                        const items = Array.isArray(o.data) ? o.data : (Array.isArray(o.data?.items) ? o.data.items : (o.data ? [o.data] : []));
                        if (!items.length) return;
                        // Create a header row for each KPI group
                        const header = document.createElement('div');
                        header.className = 'col-12';
                        header.innerHTML = `<div class="small text-muted mt-1 mb-1">${escapeHtml(o.title || o.code || 'KPI')}</div>`;
                        kpiContainer.appendChild(header);
                        items.forEach((entry) => {
                            const valueRaw = entry?.value ?? entry?.current ?? entry?.kpi ?? null;
                            const unit = entry?.unit ?? entry?.suffix ?? '';
                            const extra = entry?.extra ? ` <span class="d-block small text-muted">${escapeHtml(entry.extra)}</span>` : '';
                            const desc = entry?.description ? `<div class="small text-muted">${escapeHtml(entry.description)}</div>` : '';
                            const formatted = formatValue(valueRaw);
                            const card = document.createElement('div');
                            card.className = 'col-sm-6 col-md-4 col-lg-3';
                            card.innerHTML = `
                                <div class="card-modern card-stat-modern text-center h-100">
                                    <div class="card-body-modern">
                                        <div class="card-icon-modern text-forest-medium mb-2"><i class="fas fa-bullseye"></i></div>
                                        <div class="card-stat-number-modern">${escapeHtml(formatted)}${unit ? ' ' + escapeHtml(unit) : ''}</div>
                                        ${desc}
                                        ${extra}
                                    </div>
                                </div>`;
                            kpiContainer.appendChild(card);
                        });
                    });
                }
            }
        }
        function escapeHtml(str){
            return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]));
        }
        function formatValue(v){
            if (v === null || v === undefined) return '-';
            if (typeof v === 'number') return v.toLocaleString();
            const num = Number(v);
            return Number.isFinite(num) ? num.toLocaleString() : String(v);
        }
        function transformOutcomeToSeries(outcome){
            // Support common structures: {data:{rows:[{month, year:value},...] , columns:[years]}} or arrays of points
            const d = outcome.data || {};
            // Case 1: tabular rows/columns
            if (d && Array.isArray(d.rows) && Array.isArray(d.columns) && d.columns.length){
                const labels = d.rows.map(r => r.month || r.label || r.date || '');
                const datasets = d.columns.map((col, i) => ({
                    label: String(col),
                    data: d.rows.map(r => Number(r[col]) || 0),
                    borderColor: chartColor(i),
                    backgroundColor: 'transparent',
                    tension: 0.3
                }));
                return { labels, datasets };
            }
            // Case 2: simple series: {points:[{x,y}]}
            if (Array.isArray(d.points)){
                const labels = d.points.map(p => p.x);
                const datasets = [{ label: outcome.title || 'Series', data: d.points.map(p => Number(p.y)||0), borderColor: chartColor(0), backgroundColor:'transparent', tension:0.3 }];
                return { labels, datasets };
            }
            // Case 3: fallback single array
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
