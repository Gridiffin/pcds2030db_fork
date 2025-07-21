<?php
/**
 * Partial for Programs Overview
 * 
 * @var array $assigned_programs List of assigned programs.
 * @var array $agency_programs List of agency-created programs.
 * @var int $assigned_count Count of assigned programs.
 * @var int $agency_count Count of agency-created programs.
 */
?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Programs Overview</h5>
            </div>
            <div class="card-body">
                <div class="row gx-4 gy-4">
                    <!-- Assigned Programs Section -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded h-100 bg-light">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-success me-2" style="min-width: 90px;">Assigned</span>
                                <span class="fw-bold">Latest Assigned Programs</span>
                                <span class="badge bg-secondary ms-auto">Total: <?php echo $assigned_count; ?></span>
                            </div>
                            <?php if (empty($assigned_programs)): ?>
                                <div class="alert alert-light">
                                    <i class="fas fa-info-circle me-2"></i>No assigned programs found.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-custom" style="table-layout: fixed; width: 100%; min-width: 500px;">
                                        <colgroup>
                                            <col style="width: 45%">
                                            <col style="width: 30%">
                                            <col style="width: 25%">
                                        </colgroup>
                                        <thead class="table-light">
                                            <tr>
                                                <th>Program Name</th>
                                                <th>Agency</th>
                                                <th>Created Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assigned_programs as $program): ?>
                                                <tr>
                                                    <td class="text-truncate" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                                        <a href="<?php echo view_url('admin', 'programs/view_program.php', ['id' => $program['program_id']]); ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($program['program_name']); ?>
                                                        </a>
                                                    </td>
                                                    <td class="text-truncate" title="<?php echo htmlspecialchars($program['agency_name']); ?>">
                                                        <?php echo htmlspecialchars($program['agency_name']); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-2">
                                    <a href="<?php echo view_url('admin', 'programs/programs.php', ['program_type' => 'assigned']); ?>" class="btn btn-sm btn-outline-success">
                                        View All Assigned Programs <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Agency Created Programs Section -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded h-100 bg-light">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-info me-2" style="min-width: 90px;">Agency</span>
                                <span class="fw-bold">Latest Agency-Created Programs</span>
                                <span class="badge bg-secondary ms-auto">Total: <?php echo $agency_count; ?></span>
                            </div>
                            <?php if (empty($agency_programs)): ?>
                                <div class="alert alert-light">
                                    <i class="fas fa-info-circle me-2"></i>No agency-created programs found.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-custom" style="table-layout: fixed; width: 100%; min-width: 500px;">
                                        <colgroup>
                                            <col style="width: 45%">
                                            <col style="width: 30%">
                                            <col style="width: 25%">
                                        </colgroup>
                                        <thead class="table-light">
                                            <tr>
                                                <th>Program Name</th>
                                                <th>Agency</th>
                                                <th>Created Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($agency_programs as $program): ?>
                                                <tr>
                                                    <td class="text-truncate" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                                        <a href="<?php echo view_url('admin', 'programs/view_program.php', ['id' => $program['program_id']]); ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($program['program_name']); ?>
                                                        </a>
                                                    </td>
                                                    <td class="text-truncate" title="<?php echo htmlspecialchars($program['agency_name']); ?>">
                                                        <?php echo htmlspecialchars($program['agency_name']); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-2">
                                    <a href="<?php echo view_url('admin', 'programs/programs.php', ['program_type' => 'agency']); ?>" class="btn btn-sm btn-outline-info">
                                        View All Agency Programs <i class="fas fa-arrow-right ms-1"></i>
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