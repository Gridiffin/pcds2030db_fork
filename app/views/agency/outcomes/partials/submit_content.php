<?php
/**
 * Submit Outcomes Content Partial
 * Content section for the outcomes submission/management page
 */

// Include page header with configuration
require_once PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php';
?>

<div class="outcomes-submit-container">
    
    <!-- Outcomes Grid -->
    <div id="outcomes-grid" class="outcomes-grid">
        <?php if (!empty($outcomes)): ?>
            <?php foreach ($outcomes as $outcome): ?>
                <div class="outcome-card" data-outcome-id="<?= htmlspecialchars($outcome['id']) ?>">
                    <div class="outcome-card-header">
                        <div class="outcome-code"><?= htmlspecialchars($outcome['code']) ?></div>
                        <h3 class="outcome-title"><?= htmlspecialchars($outcome['title']) ?></h3>
                    </div>
                    <div class="outcome-card-body">
                        <p class="outcome-description">
                            <?= htmlspecialchars($outcome['description'] ?: 'No description available.') ?>
                        </p>
                        <div class="outcome-meta">
                            <span class="outcome-type type-<?= htmlspecialchars($outcome['type']) ?>">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $outcome['type']))) ?>
                            </span>
                            <span class="outcome-updated">
                                Updated <?= (new DateTime($outcome['updated_at']))->format('M j, Y') ?>
                            </span>
                        </div>
                        <div class="outcome-actions">
                            <a href="view_outcome.php?id=<?= htmlspecialchars($outcome['id']) ?>" 
                               class="outcome-action-btn btn-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <a href="<?= $outcome['type'] === 'kpi' ? 'edit_kpi.php' : 'edit_outcome.php' ?>?id=<?= htmlspecialchars($outcome['id']) ?>" 
                               class="outcome-action-btn btn-warning ms-2">
                                <i class="fas fa-edit"></i> Edit <?= $outcome['type'] === 'kpi' ? 'KPI' : 'Outcome' ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="outcomes-empty">
                <i class="fas fa-chart-bar"></i>
                <h3>No Outcomes Available</h3>
                <p>No outcomes found. Outcomes are created by administrators and contain the data metrics that agencies can view.</p>
                <div class="outcomes-empty-actions">
                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript data for module -->
<script>
    // Pass data to JavaScript for dynamic functionality
    window.outcomesData = <?= json_encode($outcomes ?? []) ?>;
</script>
