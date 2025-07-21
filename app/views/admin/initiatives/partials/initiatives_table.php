<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>Initiative</th>
                <th class="text-center">Total Programs</th>
                <th>Timeline</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $initiatives as $initiative): ?>
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
                        <span class="badge bg-secondary">
                            <?php echo isset($initiative['program_count']) ? $initiative['program_count'] : 0; ?> total
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
                        <a href="edit.php?id=<?php echo isset($initiative[$initiative_id_col]) ? htmlspecialchars($initiative[$initiative_id_col]) : ''; ?>" 
                           class="btn btn-outline-primary btn-sm me-1"
                           title="Edit Initiative">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="view_initiative.php?id=<?php echo isset($initiative[$initiative_id_col]) ? htmlspecialchars($initiative[$initiative_id_col]) : ''; ?>" 
                           class="btn btn-outline-primary btn-sm"
                           title="View Initiative Details">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div> 