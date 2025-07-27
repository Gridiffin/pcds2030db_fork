<?php
/**
 * Admin Select View Submission Period Content
 * Content for selecting which submission to view
 */
?>

<main>
    <!-- Hero Section -->
    <div class="hero-section bg-light border-bottom mb-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-1 text-primary">
                        <i class="fas fa-eye me-2"></i>Select Submission to View
                    </h6>
                    <p class="mb-0 text-muted">
                        Choose which submission period you want to view for this program
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <!-- Program Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">
                            <i class="fas fa-info-circle me-2"></i>Program Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Program</label>
                                <div class="fw-medium">
                                    <?php if (!empty($program['program_number'])): ?>
                                        <span class="badge bg-info me-2"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted">Agency</label>
                                <div class="fw-medium">
                                    <i class="fas fa-building me-2 text-primary"></i>
                                    <?php echo htmlspecialchars($agency_info['agency_name']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Submissions -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title m-0">
                            <i class="fas fa-file-alt me-2"></i>Available Submissions
                            <span class="badge bg-primary ms-2"><?php echo count($all_submissions); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($all_submissions)): ?>
                            <div class="row">
                                <?php foreach ($all_submissions as $sub): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card border h-100 hover-card">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="fas fa-file-check fa-2x text-success mb-2"></i>
                                                    <h6 class="card-title"><?php echo htmlspecialchars($sub['period_display']); ?></h6>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Finalized Submission
                                                    </span>
                                                </div>
                                                
                                                <?php if (!empty($sub['submitted_at'])): ?>
                                                    <div class="mb-3">
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            Submitted: <?php echo date('M j, Y', strtotime($sub['submitted_at'])); ?>
                                                        </small>
                                                        <?php if (!empty($sub['submitted_by_name'])): ?>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-user me-1"></i>
                                                                by <?php echo htmlspecialchars($sub['submitted_by_name']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="d-grid">
                                                    <a href="view_submissions.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $sub['period_id']; ?>" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-eye me-2"></i>
                                                        View Submission
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Latest Submission Quick Access -->
                            <div class="alert alert-info border-0 mt-4">
                                <div class="d-flex">
                                    <div class="alert-icon me-3">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading">Latest Submission</h6>
                                        <p class="mb-2">
                                            <strong><?php echo htmlspecialchars($all_submissions[0]['period_display']); ?></strong>
                                            <?php if (!empty($all_submissions[0]['submitted_at'])): ?>
                                                - Submitted <?php echo date('M j, Y', strtotime($all_submissions[0]['submitted_at'])); ?>
                                            <?php endif; ?>
                                        </p>
                                        <a href="view_submissions.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $all_submissions[0]['period_id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-2"></i>View Latest Submission
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Submissions Available</h5>
                                <p class="text-muted mb-0">There are no finalized submissions for this program.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="card-title m-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="program_details.php?id=<?php echo $program_id; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-info-circle me-2"></i>Program Details
                            </a>
                            
                            <a href="edit_submission.php?program_id=<?php echo $program_id; ?>" class="btn btn-outline-warning">
                                <i class="fas fa-edit me-2"></i>Edit Submission
                            </a>
                            
                            <a href="programs.php" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-2"></i>All Programs
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Information -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h6 class="card-title m-0">
                            <i class="fas fa-info-circle me-2"></i>Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info border-0">
                            <div class="d-flex">
                                <div class="alert-icon me-2">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <div>
                                    <small>
                                        <strong>Note:</strong><br>
                                        • Only finalized submissions are shown<br>
                                        • All submissions are read-only in this view<br>
                                        • Use "Edit Submission" to modify submissions
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.hover-card {
    transition: all 0.2s ease;
}

.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.alert-icon {
    flex-shrink: 0;
}
</style>