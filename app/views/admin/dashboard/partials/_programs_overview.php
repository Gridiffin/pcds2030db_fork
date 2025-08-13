<?php
/**
 * Partial for Programs Overview
 * 
 * @var array $recent_programs List of recent programs (all types).
 * @var int $total_programs_count Total count of all programs.
 */
?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Programs Overview</h5>
                <span class="badge bg-secondary">Total: <?php echo $total_programs_count ?? 0; ?></span>
            </div>
            <div class="card-body">
                <div class="p-3 border rounded bg-light">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary me-2" style="min-width: 90px;">Recent</span>
                        <span class="fw-bold">Latest 5 Programs</span>
                    </div>
                    <?php if (empty($recent_programs)): ?>
                        <div class="alert alert-light">
                            <i class="fas fa-info-circle me-2"></i>No programs found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-custom">
                                <thead class="table-light">
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
                                                <a href="<?php echo view_url('admin', 'programs/program_details.php', ['id' => $program['program_id']]); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                                </a>
                                            </td>
                                            <td class="text-truncate" title="<?php echo htmlspecialchars($program['agency_name']); ?>">
                                                <?php echo htmlspecialchars($program['agency_name']); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $typeClass = isset($program['created_by_admin']) && $program['created_by_admin'] ? 'success' : 'info';
                                                $typeText = isset($program['created_by_admin']) && $program['created_by_admin'] ? 'Assigned' : 'Agency';
                                                ?>
                                                <span class="badge bg-<?php echo $typeClass; ?>"><?php echo $typeText; ?></span>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="<?php echo view_url('admin', 'programs/programs.php'); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i> View All Programs <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div> 