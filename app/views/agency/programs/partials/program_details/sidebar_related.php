<?php
/**
 * Sidebar Related Programs
 * 
 * Displays programs related to the same initiative.
 */
?>

<?php if (!empty($related_programs)): ?>
<div class="card shadow-sm">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-link me-2"></i>Related Programs
        </h6>
    </div>
    <div class="card-body">
        <?php foreach ($related_programs as $rel_prog): ?>
            <div class="mb-2 p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <span class="fw-semibold"><?php echo htmlspecialchars($rel_prog['program_name']); ?></span>
                    <div class="text-muted small mt-1">
                        <?php echo htmlspecialchars($rel_prog['program_number']); ?>
                        &bull;
                        <?php echo htmlspecialchars($rel_prog['agency_name']); ?>
                        <?php if ($rel_prog['agency_id'] == ($_SESSION['agency_id'] ?? null)): ?>
                            <span class="badge bg-success ms-2"><i class="fas fa-star me-1"></i>Your Program</span>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="program_details.php?id=<?php echo (int)$rel_prog['program_id']; ?>" class="btn btn-outline-primary btn-sm btn-icon ms-2" title="View Details">
                    <i class="fas fa-eye"></i>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
