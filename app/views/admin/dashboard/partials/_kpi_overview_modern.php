<?php
/**
 * Modern KPI Overview Partial (Admin)
 * Separate card for KPI outcomes, mirroring agency dashboard KPI rendering
 */

require_once ROOT_PATH . 'app/lib/admins/outcomes.php';

// Use pre-fetched outcomes from parent scope when available
$all_outcomes = isset($all_outcomes) ? $all_outcomes : get_all_outcomes();
$admin_kpi_outcomes = isset($admin_kpi_outcomes)
    ? $admin_kpi_outcomes
    : array_values(array_filter($all_outcomes, function($o){
        $t = strtolower($o['type'] ?? '');
        return $t === 'kpi';
    }));
?>

<div class="admin-card-modern admin-fade-in">
    <div class="admin-card-modern-header">
        <h3 class="admin-card-modern-title">
            <div class="admin-card-icon-modern">
                <i class="fas fa-gauge-high"></i>
            </div>
            KPIs
        </h3>
    </div>

    <div class="admin-card-modern-content">
        <div id="adminKpiOutcomesContainer" class="row g-3"></div>
    </div>
</div>

<script>
(function renderAdminKpis(){
    const container = document.getElementById('adminKpiOutcomesContainer');
    if (!container) return;
    const kpis = <?php echo json_encode($admin_kpi_outcomes); ?>;
    const outcomeViewBaseUrl = '<?php echo view_url('admin', 'outcomes/view_outcome.php'); ?>';
    if (!Array.isArray(kpis) || kpis.length === 0) {
        container.innerHTML = '<div class="col-12 text-muted py-3 text-center">No KPI outcomes to display</div>';
        return;
    }
    container.innerHTML = '';
    kpis.forEach((o) => {
        const items = Array.isArray(o.data) ? o.data : (Array.isArray(o.data?.items) ? o.data.items : (o.data ? [o.data] : []));
        if (!items.length) return;
        // Header per KPI group with inline View Outcome button
        const header = document.createElement('div');
        header.className = 'col-12';
        header.innerHTML = `
            <div class="d-flex align-items-center mt-1 mb-1">
                <div class="small text-muted">${escapeHtml(o.title || o.code || 'KPI')}</div>
                <a href="${outcomeViewBaseUrl}?id=${encodeURIComponent(o.id)}" class="btn btn-sm btn-outline-primary rounded-pill ms-2">
                    <i class="fas fa-eye me-1"></i>View Outcome
                </a>
            </div>`;
        container.appendChild(header);
        items.forEach((entry) => {
            const valueRaw = entry?.value ?? entry?.current ?? entry?.kpi ?? null;
            const unit = entry?.unit ?? entry?.suffix ?? '';
            const extra = entry?.extra ? ` <span class=\"d-block small text-muted\">${escapeHtml(entry.extra)}</span>` : '';
            const desc = entry?.description ? `<div class=\"small text-muted\">${escapeHtml(entry.description)}</div>` : '';
            const formatted = formatValue(valueRaw);
            const card = document.createElement('div');
            card.className = 'col-sm-6 col-md-4 col-lg-3';
            card.innerHTML = `
                <div class=\"card-modern card-stat-modern text-center h-100\">
                    <div class=\"card-body-modern\">
                        <div class=\"card-icon-modern text-forest-medium mb-2\"><i class=\"fas fa-bullseye\"></i></div>
                        <div class=\"card-stat-number-modern\">${escapeHtml(formatted)}${unit ? ' ' + escapeHtml(unit) : ''}</div>
                        ${desc}
                        ${extra}
                    </div>
                </div>`;
            // No per-card action button; action is in the header
            container.appendChild(card);
        });
    });

    function escapeHtml(str){
        return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]));
    }
    function formatValue(v){
        if (v === null || v === undefined) return '-';
        if (typeof v === 'number') return v.toLocaleString();
        const num = Number(v);
        return Number.isFinite(num) ? num.toLocaleString() : String(v);
    }
})();
</script>


