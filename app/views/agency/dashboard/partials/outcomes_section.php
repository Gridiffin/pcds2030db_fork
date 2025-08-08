<?php
/**
 * Dashboard Outcomes Section Partial
 */
?>

<!-- Outcomes Section Wrapper -->
<section class="outcomes-section mb-4">
    <div class="section-header d-flex align-items-center mb-2">
        <i class="fas fa-bullseye me-2 text-primary"></i>
        <h2 class="h4 fw-bold mb-0">Outcomes</h2>
    </div>
    <div class="outcomes-description mb-3 text-muted">
        Review your agency's outcomes, submissions, and recent activity. Use the cards below to explore outcome status and actions.
    </div>
    
    <section class="section">
        <div class="container-fluid">
            <div class="bento-grid">
                <!-- Outcomes Overview -->
                <div class="bento-card size-6x2">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: #f093fb;">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            Outcomes Overview
                        </h3>
                    </div>
                    <div class="bento-card-content">
                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="text-center p-3 bg-primary text-white rounded">
                                    <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                    <div class="h4 mb-0"><?php echo $outcomes_stats['total_outcomes']; ?></div>
                                    <small>Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 bg-success text-white rounded">
                                    <i class="fas fa-check-square fa-2x mb-2"></i>
                                    <div class="h4 mb-0"><?php echo isset($outcomes_stats['submitted_outcomes']) ? $outcomes_stats['submitted_outcomes'] : 0; ?></div>
                                    <small>Submitted</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 bg-warning text-white rounded">
                                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                                    <div class="h4 mb-0"><?php echo isset($outcomes_stats['draft_outcomes']) ? $outcomes_stats['draft_outcomes'] : 0; ?></div>
                                    <small>Drafts</small>
                                </div>
                            </div>
                        </div>
                        <div class="bento-card-footer">
                            <div class="bento-card-actions">
                                <a href="../outcomes/submit_outcomes.php" class="btn btn-success">
                                    <i class="fas fa-upload me-1"></i> Submit Outcomes
                                </a>
                                <a href="../outcomes/create_outcome_flexible.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Create New
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Outcomes Activity -->
                <div class="bento-card size-3x2">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: #2c3e50;">
                                <i class="fas fa-history"></i>
                            </div>
                            Recent Activity
                        </h3>
                    </div>
                    <div class="bento-card-content">
                        <?php if (empty($outcomes_stats['recent_outcomes'])): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No recent outcomes activity found.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($outcomes_stats['recent_outcomes'], 0, 5) as $outcome): ?>
                                    <div class="list-group-item px-0 py-2 border-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($outcome['table_name']); ?></h6>
                                                <small class="text-muted"><?php echo date('M j, Y', strtotime($outcome['updated_at'])); ?></small>
                                            </div>
                                            <span class="badge-modern badge-<?php echo $outcome['is_draft'] ? 'warning' : 'success'; ?>-modern">
                                                <?php echo $outcome['is_draft'] ? 'Draft' : 'Submitted'; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
