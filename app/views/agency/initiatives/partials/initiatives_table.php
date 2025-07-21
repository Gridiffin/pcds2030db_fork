<?php
/**
 * Initiatives Table Partial
 * Contains the initiatives listing table
 */
?>
<!-- Initiatives List -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">
            <i class="fas fa-lightbulb me-2"></i>Your Initiatives
            <span class="badge bg-primary ms-2"><?php echo count($initiatives); ?></span>
        </h5>
        <div class="d-flex align-items-center">
            <div class="text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                Showing initiatives where your agency has programs
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($initiatives)): ?>
            <div class="text-center py-5">
                <i class="fas fa-lightbulb fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No initiatives found</h5>
                <p class="text-muted">
                    <?php if (!empty($search) || $status_filter !== ''): ?>
                        No initiatives match your search criteria.
                    <?php else: ?>
                        Your agency doesn't have any programs assigned to initiatives yet.
                    <?php endif; ?>
                </p>
                <?php if (!empty($search) || $status_filter !== ''): ?>
                    <a href="initiatives.php" class="btn btn-outline-primary">
                        <i class="fas fa-undo me-1"></i>Clear Filters
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if ((isset($_GET['debug']) && $_GET['debug'] == '1') || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
                <pre style="background:#f8f9fa; color:#333; border:1px solid #ccc; padding:1em; margin-bottom:1em;">
                    initiative_name_col: <?php var_dump($initiative_name_col); ?>
                    initiative keys: <?php if (!empty($initiatives)) var_dump(array_keys($initiatives[0])); else echo 'No initiatives.'; ?>
                </pre>
            <?php endif; ?>
            <!-- Table View Only -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Initiative</th>
                            <th class="text-center">Your Programs</th>
                            <th class="text-center">Total Programs</th>
                            <th>Timeline</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($initiatives as $initiative): ?>
                            <tr data-initiative-id="<?php echo isset($initiative[$initiative_id_col]) ? htmlspecialchars($initiative[$initiative_id_col]) : ''; ?>">
                                <td>
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold mb-1">
                                                <?php if (!empty($initiative[$initiative_number_col])): ?>
                                                    <span class="badge bg-primary me-2">
                                                        <?php echo htmlspecialchars($initiative[$initiative_number_col] ?? ''); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($initiative[$initiative_name_col] ?? ''); ?>
                                            </div>
                                            <?php if (!empty($initiative[$initiative_description_col])): ?>
                                                <div class="text-muted small" style="line-height: 1.4;">
                                                    <?php 
                                                    $description = htmlspecialchars($initiative[$initiative_description_col] ?? '');
                                                    echo strlen($description) > 120 ? substr($description, 0, 120) . '...' : $description;
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">
                                        <?php echo isset($initiative['agency_program_count']) ? $initiative['agency_program_count'] : 0; ?> programs included
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        <?php echo isset($initiative['total_program_count']) ? $initiative['total_program_count'] : 0; ?> total
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($initiative[$start_date_col]) || !empty($initiative[$end_date_col])): ?>
                                        <div class="small">
                                            <?php if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])): ?>
                                                <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                                <?php echo date('M j, Y', strtotime($initiative[$start_date_col] ?? '')); ?> - 
                                                <?php echo date('M j, Y', strtotime($initiative[$end_date_col] ?? '')); ?>
                                            <?php elseif (!empty($initiative[$start_date_col])): ?>
                                                <i class="fas fa-play me-1 text-success"></i>
                                                Started: <?php echo date('M j, Y', strtotime($initiative[$start_date_col] ?? '')); ?>
                                            <?php elseif (!empty($initiative[$end_date_col])): ?>
                                                <i class="fas fa-flag-checkered me-1 text-warning"></i>
                                                Due: <?php echo date('M j, Y', strtotime($initiative[$end_date_col] ?? '')); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            <i class="fas fa-calendar-times me-1"></i>
                                            No timeline
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($initiative[$is_active_col])): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="view_initiative.php?id=<?php echo isset($initiative[$initiative_id_col]) ? htmlspecialchars($initiative[$initiative_id_col]) : ''; ?>" 
                                       class="btn btn-outline-primary btn-sm"
                                       title="View Initiative Details">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
