<?php
/**
 * Related Programs Partial
 * Shows all programs associated with this initiative
 */
?>
<!-- Related Programs -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="card-title m-0">
            <i class="fas fa-tasks me-2"></i>Related Programs
            <span class="badge bg-secondary ms-2"><?php echo count($programs); ?></span>
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($programs)): ?>
            <div class="programs-list" style="max-height: 500px; overflow-y: auto;">
                <?php
                // Set ownership flag for each program
                $current_agency_id = $_SESSION['agency_id'] ?? null;
                foreach ($programs as &$program) {
                    $program['is_owned_by_agency'] = ($program['agency_id'] == $current_agency_id);
                }
                unset($program); // break reference
                ?>
                <?php foreach ($programs as $program): ?>
                    <div class="program-item mb-3 <?php echo $program['is_owned_by_agency'] ? 'owned' : 'other-agency'; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="fw-medium mb-1">
                                    <?php if (!empty($program['program_number'])): ?>
                                        <span class="badge bg-info me-2" style="font-size: 0.7em;">
                                            <?php echo htmlspecialchars($program['program_number']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                </div>
                                <div class="small text-muted mb-2">
                                    <i class="fas fa-building me-1"></i>
                                    <?php echo htmlspecialchars($program['agency_name']); ?>
                                </div>
                                <?php if ($program['is_owned_by_agency']): ?>
                                    <span class="badge bg-primary mb-2" style="font-size: 0.7em;">
                                        <i class="fas fa-star me-1"></i>Your Program
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="ms-2">
                                <?php
                                // Use the rating helper to render the status badge
                                echo get_rating_badge($program['rating'] ?? 'not_started');
                                ?>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <?php if (!$program['is_owned_by_agency']): ?>
                                <span class="text-muted small me-2"><i class="fas fa-lock"></i> View-only (other agency)</span>
                            <?php endif; ?>
                            <a href="../programs/program_details.php?id=<?php echo (int)$program['program_id']; ?>" class="btn btn-outline-primary btn-sm ms-auto">
                                <i class="fas fa-eye me-1"></i> View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-muted text-center py-4">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <div>No programs found under this initiative.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
