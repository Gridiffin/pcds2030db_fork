<main class="flex-fill">
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
        <div class="col-xl-8 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-lightbulb me-2"></i>Initiative Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="initiativeForm">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="initiative_name" class="form-label">Initiative Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="initiative_name" name="initiative_name" value="<?php echo htmlspecialchars($form_data['initiative_name'] ?? ''); ?>" required>
                                <div class="form-text">Enter a clear, descriptive name for the initiative</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="initiative_number" class="form-label">Initiative Number</label>
                                <input type="text" class="form-control" id="initiative_number" name="initiative_number" value="<?php echo htmlspecialchars($form_data['initiative_number'] ?? ''); ?>">
                                <div class="form-text">Optional unique reference (e.g., PCDS-I-001)</div>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="initiative_description" class="form-label">Description</label>
                                <textarea class="form-control" id="initiative_description" name="initiative_description" rows="4"><?php echo htmlspecialchars($form_data['initiative_description'] ?? ''); ?></textarea>
                                <div class="form-text">Provide a brief description of the initiative</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($form_data['start_date'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($form_data['end_date'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select" id="is_active" name="is_active">
                                    <option value="1" <?php echo (isset($form_data['is_active']) ? ($form_data['is_active'] == 1 ? 'selected' : '') : 'selected'); ?>>Active</option>
                                    <option value="0" <?php echo (isset($form_data['is_active']) && $form_data['is_active'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="manage_initiatives.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Cancel</a>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Create Initiative</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
