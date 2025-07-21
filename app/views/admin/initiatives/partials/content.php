<main class="flex-fill">
<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php
// Ensure $search and $status_filter are always defined
if (!isset($search)) $search = '';
if (!isset($status_filter)) $status_filter = '';
?>
<!-- Search and Filter Section -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <?php include __DIR__ . '/search_filter_form.php'; ?>
    </div>
</div>

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
            <?php include __DIR__ . '/initiatives_table.php'; ?>
        <?php endif; ?>
    </div>
</div>
</main> 