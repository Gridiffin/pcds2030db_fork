<main class="flex-fill">
    <div class="container-fluid">
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'danger' ? 'exclamation-circle' : 'info-circle'); ?> me-2"></i>
                    <div><?php echo htmlspecialchars($message); ?></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Main Form Column -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-lightbulb me-2"></i>Initiative Details</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="initiativeForm">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="initiative_name" class="form-label">Initiative Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="initiative_name" name="initiative_name" value="<?php echo htmlspecialchars($form_data[$initiative_name_col] ?? $form_data['initiative_name'] ?? ''); ?>" required>
                                    <div class="form-text">Enter a clear, descriptive name for the initiative</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="initiative_number" class="form-label">Initiative Number</label>
                                    <input type="text" class="form-control" id="initiative_number" name="initiative_number" value="<?php echo htmlspecialchars($form_data[$initiative_number_col] ?? $form_data['initiative_number'] ?? ''); ?>">
                                    <div class="form-text">Optional unique reference (e.g., PCDS-I-001)</div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="initiative_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="initiative_description" name="initiative_description" rows="4"><?php echo htmlspecialchars($form_data[$initiative_description_col] ?? $form_data['initiative_description'] ?? ''); ?></textarea>
                                    <div class="form-text">Provide a brief description of the initiative</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($form_data[$start_date_col] ?? $form_data['start_date'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($form_data[$end_date_col] ?? $form_data['end_date'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo (isset($form_data[$initiative_status_col]) ? ($form_data[$initiative_status_col] == 'active' ? 'selected' : '') : 'selected'); ?>>Active</option>
                                        <option value="inactive" <?php echo (isset($form_data[$initiative_status_col]) && $form_data[$initiative_status_col] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="manage_initiatives.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Cancel</a>
                                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Update Initiative</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="col-lg-4 mb-4">
                <!-- Initiative Info Card -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Initiative Info
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
                                <div class="text-muted small">Associated Programs</div>
                                <div class="fw-semibold">
                                    <span class="badge bg-primary"><?php echo count($associated_programs ?? []); ?> programs</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Associated Programs Card -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="fas fa-layer-group me-2"></i>Associated Programs</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($associated_programs)): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-layer-group fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0 small">No programs are currently linked to this initiative.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($associated_programs as $program): ?>
                                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small">
                                                <?php 
                                                $program_name = $program[$programNameCol] ?? $program['program_name'] ?? 'Unknown Program';
                                                echo htmlspecialchars($program_name); 
                                                ?>
                                            </div>
                                            <div class="text-muted small">
                                                <?php 
                                                $agency_name = $program[$agencyNameCol] ?? $program['agency_name'] ?? 'Unknown Agency';
                                                echo htmlspecialchars($agency_name);
                                                ?>
                                            </div>
                                        </div>
                                        <div>
                                            <?php 
                                            $program_number = $program[$programNumberCol] ?? $program['program_number'] ?? null;
                                            if ($program_number): ?>
                                                <span class="badge bg-secondary small"><?php echo htmlspecialchars($program_number); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="card shadow-sm mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="view_initiative.php?id=<?php echo $initiative_id; ?>" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-eye me-2"></i>View Initiative
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash me-2"></i>Delete Initiative
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <div class="d-flex">
                            <div class="alert-icon me-3">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h6 class="alert-heading">Warning: This action cannot be undone!</h6>
                                <p class="mb-2">You are about to permanently delete:</p>
                                <ul class="mb-0">
                                    <li><strong><?php echo htmlspecialchars($initiative[$initiative_name_col] ?? 'Unknown Initiative'); ?></strong></li>
                                    <?php if (!empty($associated_programs)): ?>
                                        <li><?php echo count($associated_programs); ?> associated program(s) will be unlinked</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <p class="mb-0 text-muted">
                        Are you sure you want to delete this initiative? This action will permanently remove the initiative from the system.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="initiative_id" value="<?php echo $initiative_id; ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Delete Initiative
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>