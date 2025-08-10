<main class="flex-fill">
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content Column -->
            <div class="col-lg-8 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-lightbulb me-2"></i><?php echo htmlspecialchars($initiative[$initiative_name_col] ?? 'Initiative'); ?></h5>
                    <?php if (!empty($initiative[$initiative_number_col])): ?>
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($initiative[$initiative_number_col]); ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($initiative[$initiative_description_col] ?? 'No description provided.')); ?></p>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="small text-muted">Start Date</div>
                            <div><?php echo htmlspecialchars($initiative[$start_date_col] ?? '—'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">End Date</div>
                            <div><?php echo htmlspecialchars($initiative[$end_date_col] ?? '—'); ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Status</div>
                            <?php 
                            $status = $initiative[$initiative_status_col] ?? 'inactive';
                            $badge_class = ($status == 'active') ? 'success' : 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $badge_class; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!empty($associated_programs)): ?>
                    <div class="p-3 rounded bg-light d-flex align-items-center gap-3">
                        <div class="display-6 mb-0 fw-semibold text-primary">
                            <?php echo count($associated_programs); ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Associated Programs</div>
                            <div class="small text-muted">Programs linked to this initiative</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-layer-group me-2"></i>Associated Programs (<?php echo count($associated_programs ?? []); ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($associated_programs)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-layer-group fa-2x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No programs associated with this initiative.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Program</th>
                                        <th class="text-center">Number</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Agency</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($associated_programs as $program): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                // Use correct column names from the function
                                                $program_name = $program[$programNameCol] ?? $program['program_name'] ?? 'Unknown Program';
                                                echo htmlspecialchars($program_name); 
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                $program_number = $program[$programNumberCol] ?? $program['program_number'] ?? 'N/A';
                                                ?>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($program_number); ?></span>
                                            </td>
                                            <td class="text-center"><span class="badge bg-success">Active</span></td>
                                            <td class="text-center small text-muted">
                                                <?php 
                                                $agency_name = $program[$agencyNameCol] ?? $program['agency_name'] ?? 'Unknown Agency';
                                                echo htmlspecialchars($agency_name);
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </div>

            <!-- Sidebar Column -->
            <div class="col-lg-4 mb-4">
                <!-- Initiative Details Card -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Initiative Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted small">Created</div>
                                <div class="fw-semibold">
                                    <?php 
                                    $created_date = $initiative[$created_at_col] ?? null;
                                    echo $created_date ? date('M j, Y', strtotime($created_date)) : 'Unknown'; 
                                    ?>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Last Updated</div>
                                <div class="fw-semibold">
                                    <?php 
                                    $updated_date = $initiative[$updated_at_col] ?? null;
                                    echo $updated_date ? date('M j, Y', strtotime($updated_date)) : 'Unknown'; 
                                    ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Status</div>
                                <div class="fw-semibold">
                                    <?php 
                                    $status = $initiative[$initiative_status_col] ?? 'inactive';
                                    $badge_class = ($status == 'active') ? 'success' : 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="edit.php?id=<?php echo $initiative_id; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-2"></i>Edit Initiative
                            </a>
                            <a href="manage_initiatives.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-list me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
