<?php
/**
 * KPI Display Partial
 * Displays KPI-type outcome data
 */
?>

<?php if (!empty($outcome['data']) && is_array($outcome['data'])): ?>
    <div class="outcomes-section">
        <div class="outcomes-section-header">
            <h3>
                <i class="fas fa-chart-bar me-2"></i>Key Performance Indicators
            </h3>
        </div>
        <div class="outcomes-section-content">
            <div class="row g-3">
                <?php foreach ($outcome['data'] as $item): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="h5 text-muted mb-2">
                                    <?= htmlspecialchars($item['description'] ?? 'KPI') ?>
                                </div>
                                <div class="display-6 fw-bold text-primary mb-2">
                                    <?= isset($item['value']) ? htmlspecialchars($item['value']) : '—' ?>
                                    <?php if (!empty($item['unit'])): ?>
                                        <span class="fs-5 text-muted ms-1"><?= htmlspecialchars($item['unit']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($item['extra'])): ?>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars($item['extra']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="outcomes-section">
        <div class="outcomes-section-content">
            <div class="outcome-empty-state">
                <i class="fas fa-chart-bar"></i>
                <h4>No KPI Data Available</h4>
                <p>No key performance indicators have been configured for this outcome. Please contact your administrator.</p>
            </div>
        </div>
    </div>
<?php endif; ?>
