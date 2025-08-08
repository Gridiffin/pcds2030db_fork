<?php
/**
 * Modern Programs Overview Partial
 * 
 * Enhanced design with modern table styling and improved UX
 * Maintains all original functionality
 */
?>

<div class="admin-card-modern admin-fade-in">
    <div class="admin-card-modern-header">
        <h3 class="admin-card-modern-title">
            <div class="admin-card-icon-modern">
                <i class="fas fa-tasks"></i>
            </div>
            Programs Overview
        </h3>
        <span class="admin-badge-modern primary">
            Total: <?php echo $total_programs_count ?? 0; ?>
        </span>
    </div>
    
    <div class="admin-card-modern-content">
        <?php if (empty($recent_programs)): ?>
            <div class="admin-empty-state-modern">
                <div class="admin-empty-icon-modern">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h4 class="admin-empty-title-modern">No Programs Found</h4>
                <p class="admin-empty-description-modern">
                    No programs have been created yet. Start by adding a new program.
                </p>
            </div>
        <?php else: ?>
            <div class="admin-table-modern">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th style="width: 40%">Program Name</th>
                            <th style="width: 25%">Agency</th>
                            <th style="width: 15%">Type</th>
                            <th style="width: 20%">Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($recent_programs, 0, 5) as $program): ?>
                            <tr>
                                <td class="text-truncate" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                    <a href="<?php echo view_url('admin', 'programs/view_program.php', ['id' => $program['program_id']]); ?>" 
                                       class="text-decoration-none fw-medium text-primary">
                                        <?php echo htmlspecialchars($program['program_name']); ?>
                                    </a>
                                </td>
                                <td class="text-truncate" title="<?php echo htmlspecialchars($program['agency_name']); ?>">
                                    <span class="text-muted">
                                        <?php echo htmlspecialchars($program['agency_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $typeClass = isset($program['created_by_admin']) && $program['created_by_admin'] ? 'success' : 'info';
                                    $typeText = isset($program['created_by_admin']) && $program['created_by_admin'] ? 'Assigned' : 'Agency';
                                    ?>
                                    <span class="admin-badge-modern <?php echo $typeClass; ?>">
                                        <?php echo $typeText; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-4">
                <a href="<?php echo view_url('admin', 'programs/programs.php'); ?>" 
                   class="btn btn-outline-primary btn-lg rounded-pill px-4">
                    <i class="fas fa-list me-2"></i> 
                    View All Programs 
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>