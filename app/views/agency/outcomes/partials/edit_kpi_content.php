<?php
/**
 * Edit KPI Content Partial - Agency Version
 * 
 * Content partial for editing KPI outcome details in agency area
 */

// Ensure variables are available from parent view
if (!isset($outcome_id) || !isset($outcome)) {
    return;
}
?>

<div class="container-fluid px-4 py-4">
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"> <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success_message) ?> </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"> <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error_message) ?> </div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Edit KPI Outcome</h5>
            <p class="text-muted mb-0 small">Outcome ID: <?= $outcome_id ?> | Code: <?= htmlspecialchars($outcome['code']) ?></p>
        </div>
        <div class="card-body">
            <form id="editKpiForm" method="post" action="">
                <div class="mb-3">
                    <label for="kpiTitleInput" class="form-label">KPI Title</label>
                    <input type="text" class="form-control" id="kpiTitleInput" name="title" required value="<?= htmlspecialchars($outcome['title']) ?>" />
                </div>
                <div class="mb-3">
                    <label for="kpiDescriptionInput" class="form-label">KPI Description</label>
                    <textarea class="form-control" id="kpiDescriptionInput" name="description" rows="3"><?= htmlspecialchars($outcome['description']) ?></textarea>
                </div>
                <!-- KPI Data Table Section -->
                <?php $kpi_data = is_array($outcome['data']) ? $outcome['data'] : json_decode($outcome['data'], true); ?>
                <?php if (!empty($kpi_data)): ?>
                    <?php foreach ($kpi_data as $idx => $item): ?>
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">Description</label>
                                <input type="text" class="form-control" name="data[<?= $idx ?>][description]" value="<?= htmlspecialchars($item['description'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Value</label>
                                <input type="text" class="form-control" name="data[<?= $idx ?>][value]" value="<?= htmlspecialchars($item['value'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control" name="data[<?= $idx ?>][unit]" value="<?= htmlspecialchars($item['unit'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Extra</label>
                                <input type="text" class="form-control" name="data[<?= $idx ?>][extra]" value="<?= htmlspecialchars($item['extra'] ?? '') ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning text-center my-4">
                        <i class="fas fa-exclamation-circle me-2"></i> No KPI data available for this outcome.
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-end gap-2">
                    <a href="view_outcome.php?id=<?= $outcome_id ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 