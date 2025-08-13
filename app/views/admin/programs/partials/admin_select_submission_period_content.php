<?php
/**
 * Admin Select Submission Period Content
 * Content for selecting which reporting period to edit
 */
?>

<main>
    <!-- Hero Section -->
    <div class="hero-section bg-light border-bottom mb-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="mb-1 text-primary">
                        <i class="fas fa-calendar-alt me-2"></i>Select Reporting Period
                    </h6>
                    <p class="mb-0 text-muted">
                        Choose which submission period you want to edit for this program
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
                                    <?php echo htmlspecialchars($program['program_name'] ?? 'Unknown Program'); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted">Agency</label>
                                <div class="fw-medium">
                                    <i class="fas fa-building me-2 text-primary"></i>
                                    <?php echo htmlspecialchars($agency_info['agency_name'] ?? 'Unknown Agency'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Reporting Periods -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title m-0">
                            <i class="fas fa-calendar-alt me-2"></i>Available Reporting Periods
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($all_periods)): ?>
                            <div class="row">
                                <?php foreach ($all_periods as $period): ?>
                                    <?php 
                                    $has_submission = false;
                                    $submission_status = '';
                                    
                                    // Check if there's a submission for this period
                                    foreach ($latest_by_period as $submission) {
                                        if ($submission['period_id'] == $period['period_id']) {
                                            $has_submission = true;
                                            $submission_status = $submission['is_draft'] ? 'Draft' : 'Finalized';
                                            break;
                                        }
                                    }
                                    ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card border h-100 hover-card">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                                                    <h6 class="card-title"><?php echo htmlspecialchars($period['period_display']); ?></h6>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <?php if ($has_submission): ?>
                                                        <span class="badge bg-<?php echo $submission_status == 'Draft' ? 'warning' : 'success'; ?>">
                                                            <i class="fas fa-<?php echo $submission_status == 'Draft' ? 'edit' : 'check-circle'; ?> me-1"></i>
                                                            <?php echo $submission_status; ?> Submission
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-plus me-1"></i>
                                                            No Submission
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="d-grid">
                                                    <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period['period_id']; ?>" 
                                                       class="btn btn-<?php echo $has_submission ? 'primary' : 'outline-primary'; ?>">
                                                        <i class="fas fa-<?php echo $has_submission ? 'edit' : 'plus'; ?> me-2"></i>
                                                        <?php echo $has_submission ? 'Edit Submission' : 'Create Submission'; ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Reporting Periods Available</h5>
                                <p class="text-muted mb-0">There are no reporting periods configured for this program.</p>
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
                            
                            <a href="view_submissions.php?program_id=<?php echo $program_id; ?>" class="btn btn-outline-info">
                                <i class="fas fa-eye me-2"></i>View Submissions
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
                                        <strong>Tips:</strong><br>
                                        • Select a period with existing submission to edit it<br>
                                        • Select a period without submission to create a new one<br>
                                        • All submissions will be marked as finalized when saved
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