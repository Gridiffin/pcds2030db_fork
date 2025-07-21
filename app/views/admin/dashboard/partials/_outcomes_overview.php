<?php
/**
 * Partial for Outcomes Overview
 * 
 * @var array $outcomes_stats Statistics related to outcomes.
 */
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
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                <h4><?php echo $outcomes_stats['total_outcomes']; ?></h4>
                                <p class="mb-0">Total Outcomes</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-check-square fa-3x mb-3"></i>
                                <h4><?php echo $outcomes_stats['total_outcomes']; ?></h4>
                                <p class="mb-0">Total Outcomes</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-file-alt fa-3x mb-3"></i>
                                <h4><?php echo $outcomes_stats['draft_outcomes'] ?? 0; ?></h4>
                                <p class="mb-0">Drafts</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-building fa-3x mb-3"></i>
                                <h4><?php echo $outcomes_stats['sectors_with_outcomes'] ?? 0; ?></h4>
                                <p class="mb-0">Sectors</p>
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
                            <p class="text-muted mb-3">View, edit, and manage all outcomes data across sectors</p>
                            <div class="d-flex gap-2">
                                <a href="<?php echo view_url('admin', 'outcomes/manage_outcomes.php'); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-cogs me-1"></i> Manage Outcomes
                                </a>
                                <a href="<?php echo view_url('admin', 'outcomes/create_outcome_flexible.php'); ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-plus-circle me-1"></i> Create New
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
            </div>
        </div>
    </div>
</div> 