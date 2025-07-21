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
    
    <!-- Bento Grid Dashboard -->
    <section class="section">
        <div class="container-fluid">
            <!-- Bento Grid Layout -->
            <div class="bento-grid">
                <!-- Total Programs Card -->
                <div class="bento-card size-3x1 primary">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            Total Programs
                        </h3>
                    </div>
                    <div class="bento-card-content text-center">
                        <div class="display-4 fw-bold mb-2"><?php echo $stats['total']; ?></div>
                        <p class="mb-0 opacity-75">Active programs in your portfolio</p>
                    </div>
                </div>

                <!-- On Track Programs Card -->
                <div class="bento-card size-3x1 success">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            On Track
                        </h3>
                    </div>
                    <div class="bento-card-content text-center">
                        <div class="display-4 fw-bold mb-2"><?php echo $stats['on-track']; ?></div>
                        <p class="mb-0 opacity-75">
                            <?php echo $stats['total'] > 0 ? round(($stats['on-track'] / $stats['total']) * 100) : 0; ?>% of total
                        </p>
                    </div>
                </div>

                <!-- Delayed Programs Card -->
                <div class="bento-card size-3x1 warning">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            Delayed
                        </h3>
                    </div>
                    <div class="bento-card-content text-center">
                        <div class="display-4 fw-bold mb-2"><?php echo $stats['delayed']; ?></div>
                        <p class="mb-0 opacity-75">
                            <?php echo $stats['total'] > 0 ? round(($stats['delayed'] / $stats['total']) * 100) : 0; ?>% of total
                        </p>
                    </div>
                </div>

                <!-- Completed Programs Card -->
                <div class="bento-card size-3x1 info">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-trophy"></i>
                            </div>
                            Completed
                        </h3>
                    </div>
                    <div class="bento-card-content text-center">
                        <div class="display-4 fw-bold mb-2"><?php echo $stats['completed']; ?></div>
                        <p class="mb-0 opacity-75">
                            <?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>% of total
                        </p>
                    </div>
                </div>

                <!-- Program Rating Chart -->
                <div class="bento-card size-6x2">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: #667eea;">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            Program Rating Distribution
                        </h3>
                    </div>
                    <div class="bento-card-content">
                        <div class="chart-container" style="position: relative; height: 300px; width: 100%">
                            <canvas id="programRatingChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Program Updates -->
                <div class="bento-card size-6x2">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: #11998e;">
                                <i class="fas fa-clock"></i>
                            </div>
                            Recent Program Updates
                        </h3>
                        <span class="badge bg-primary" id="programCount"><?php echo count($recentUpdates); ?></span>
                    </div>
                    <div class="bento-card-content">
                        <?php if (empty($recentUpdates)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                <p>No recent program updates found.</p>
                                <a href="submit_program_data.php" class="btn btn-primary btn-sm">
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
                            
                            <div class="bento-card-footer">
                                <a href="../programs/view_programs.php" class="btn btn-outline-primary">
                                    View All Programs <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="bento-card size-3x1">
                    <div class="bento-card-header">
                        <h3 class="bento-card-title">
                            <div class="bento-card-icon" style="background: #4facfe;">
                                <i class="fas fa-bolt"></i>
                            </div>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="bento-card-content">
                        <div class="d-grid gap-2">
                            <a href="../programs/create_program.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create Program
                            </a>
                            <a href="../programs/add_submission.php" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-2"></i>Submit Data
                            </a>
                            <a href="../outcomes/submit_outcomes.php" class="btn btn-outline-success">
                                <i class="fas fa-upload me-2"></i>Submit Outcomes
                            </a>
                            <a href="../reports/view_reports.php" class="btn btn-outline-info">
                                <i class="fas fa-chart-bar me-2"></i>View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
