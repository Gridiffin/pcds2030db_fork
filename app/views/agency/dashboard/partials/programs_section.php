<?php
/**
 * Dashboard Programs Section Partial
 */
?>

<!-- Programs Section Wrapper -->
<section class="programs-section mb-4">
    <div class="section-header d-flex align-items-center mb-2">
        <i class="fas fa-clipboard-list me-2 text-primary"></i>
        <h2 class="h4 fw-bold mb-0">Programs</h2>
    </div>
    <div class="programs-description mb-3 text-muted">
        View your agency's programs, their progress, and recent updates. Use the cards below to explore program status and activity.
    </div>
    
    <!-- Programs Dashboard -->
    <div class="container-fluid">
        <!-- Statistics Row -->
        <div class="row g-4 mb-4">
            <!-- Total Programs Card -->
            <div class="col-lg-3 col-md-6">
                <div class="card-modern card-stat-modern">
                    <div class="card-body-modern text-center">
                        <div class="card-icon-modern text-forest-deep mb-3">
                            <i class="fas fa-clipboard-list fa-2x"></i>
                        </div>
                        <div class="card-stat-number-modern"><?php echo $stats['total']; ?></div>
                        <div class="card-stat-label-modern">Total Programs</div>
                        <div class="card-stat-change-modern text-muted">Active programs in your portfolio</div>
                    </div>
                </div>
            </div>

            <!-- On Track Programs Card -->
            <div class="col-lg-3 col-md-6">
                <div class="card-modern card-stat-modern">
                    <div class="card-body-modern text-center">
                        <div class="card-icon-modern text-success mb-3">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                        <div class="card-stat-number-modern text-success"><?php echo $stats['on-track']; ?></div>
                        <div class="card-stat-label-modern">On Track</div>
                        <div class="card-stat-change-modern card-stat-change-positive-modern">
                            <?php echo $stats['total'] > 0 ? round(($stats['on-track'] / $stats['total']) * 100) : 0; ?>% of total
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delayed Programs Card -->
            <div class="col-lg-3 col-md-6">
                <div class="card-modern card-stat-modern">
                    <div class="card-body-modern text-center">
                        <div class="card-icon-modern text-warning mb-3">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div class="card-stat-number-modern text-warning"><?php echo $stats['delayed']; ?></div>
                        <div class="card-stat-label-modern">Delayed</div>
                        <div class="card-stat-change-modern text-muted">
                            <?php echo $stats['total'] > 0 ? round(($stats['delayed'] / $stats['total']) * 100) : 0; ?>% of total
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Programs Card -->
            <div class="col-lg-3 col-md-6">
                <div class="card-modern card-stat-modern">
                    <div class="card-body-modern text-center">
                        <div class="card-icon-modern text-info mb-3">
                            <i class="fas fa-trophy fa-2x"></i>
                        </div>
                        <div class="card-stat-number-modern text-info"><?php echo $stats['completed']; ?></div>
                        <div class="card-stat-label-modern">Completed</div>
                        <div class="card-stat-change-modern card-stat-change-positive-modern">
                            <?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>% of total
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="row g-4">
            <!-- Program Rating Chart -->
            <div class="col-lg-8">
                <div class="card-modern card-elevated-modern h-100">
                    <div class="card-header-modern">
                        <h3 class="card-title-modern">
                            <div class="card-icon-modern text-forest-medium">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            Program Rating Distribution
                        </h3>
                    </div>
                    <div class="card-body-modern">
                        <div class="chart-container" style="position: relative; height: 300px; width: 100%">
                            <canvas id="programRatingChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="col-lg-4">
                <div class="card-modern card-elevated-modern h-100">
                    <div class="card-header-modern">
                        <h3 class="card-title-modern">
                            <div class="card-icon-modern text-forest-deep">
                                <i class="fas fa-bolt"></i>
                            </div>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="card-body-modern">
                        <div class="d-grid gap-3">
                            <a href="../programs/create_program.php" class="btn-modern btn-primary-modern">
                                <i class="fas fa-plus me-2"></i>Create Program
                            </a>
                            <a href="../programs/add_submission.php" class="btn-modern btn-outline-primary-modern">
                                <i class="fas fa-edit me-2"></i>Submit Data
                            </a>
                            <a href="../outcomes/submit_outcomes.php" class="btn-modern btn-outline-success-modern">
                                <i class="fas fa-upload me-2"></i>Submit Outcomes
                            </a>
                            <a href="../reports/view_reports.php" class="btn-modern btn-outline-info-modern">
                                <i class="fas fa-chart-bar me-2"></i>View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Program Updates -->
        <div class="row g-4 mt-1">
            <div class="col-12">
                <div class="card-modern card-elevated-modern">
                    <div class="card-header-modern">
                        <div class="d-flex align-items-center justify-content-between">
                            <h3 class="card-title-modern mb-0">
                                <div class="card-icon-modern text-forest-light">
                                    <i class="fas fa-clock"></i>
                                </div>
                                Recent Program Updates
                            </h3>
                            <span class="badge bg-forest-medium text-white" id="programCount"><?php echo count($recentUpdates); ?></span>
                        </div>
                    </div>
                    <div class="card-body-modern">
                        <?php if (empty($recentUpdates)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                <p class="mb-3">No recent program updates found.</p>
                                <a href="submit_program_data.php" class="btn-modern btn-primary-modern btn-sm-modern">
                                    <i class="fas fa-edit me-1"></i> Update Program Data
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="sortable" data-sort="name">
                                                Program Name <i class="fas fa-sort ms-1"></i>
                                            </th>
                                            <th class="sortable" data-sort="rating">
                                                Rating <i class="fas fa-sort ms-1"></i>
                                            </th>
                                            <th class="sortable" data-sort="date">
                                                Last Updated <i class="fas fa-sort ms-1"></i>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="dashboardProgramsTable">
                                        <?php foreach ($recentUpdates as $program): 
                                            $program_type = isset($program['is_assigned']) && $program['is_assigned'] ? 'assigned' : 'created';
                                            $program_type_label = $program_type === 'assigned' ? 'Assigned' : 'Agency-Created';
                                            $is_draft = isset($program['is_draft']) && $program['is_draft'] == 1;
                                            $is_new_assigned = $program_type === 'assigned' && !isset($program['rating']);
                                        ?>
                                            <tr data-program-type="<?php echo $program_type; ?>" 
                                               class="<?php echo ($is_draft || $is_new_assigned) ? 'draft-program' : ''; ?>">
                                                <td>
                                                    <div class="fw-medium">
                                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                                        <?php if ($is_draft || $is_new_assigned): ?>
                                                            <span class="badge bg-secondary ms-1">Draft</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="small text-muted program-type-indicator">
                                                        <i class="fas fa-<?php echo $program_type === 'assigned' ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                                        <?php echo $program_type_label; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $rating = $program['rating'] ?? 'not-started';
                                                    $rating_class = 'secondary';
                                                    
                                                    switch($rating) {
                                                        case 'on-track':
                                                        case 'on-track-yearly':
                                                            $rating_class = 'warning';
                                                            break;
                                                        case 'delayed':
                                                        case 'severe-delay':
                                                            $rating_class = 'danger';
                                                            break;
                                                        case 'completed':
                                                        case 'target-achieved':
                                                            $rating_class = 'success';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $rating_class; ?>">
                                                        <?php echo ucfirst(str_replace('-', ' ', $rating)); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo isset($program['updated_at']) && $program['updated_at'] ? date('M j, Y', strtotime($program['updated_at'])) : 'N/A'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="card-footer-modern">
                                <a href="../programs/view_programs.php" class="btn-modern btn-outline-primary-modern">
                                    View All Programs <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
