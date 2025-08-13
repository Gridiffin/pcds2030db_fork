<?php
/**
 * Partial for Quick Actions
 * 
 * @var array $current_period The current reporting period.
 */
?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0 text-white"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row justify-content-center text-center g-4">
                    <?php
                    // Get current period status for contextual actions
                    $periodOpen = isset($current_period) && isset($current_period['status']) && $current_period['status'] === 'open';
                    $periodId = $current_period['period_id'] ?? 0;
                    ?>
                    <div class="col-lg-3 col-md-4 col-6">
                        <a href="<?php echo view_url('admin', 'periods/reporting_periods.php'); ?>" class="btn <?php echo $periodOpen ? 'btn-outline-danger' : 'btn-outline-success'; ?> w-100 d-flex flex-column align-items-center justify-content-center quick-action-btn <?php echo $periodOpen ? 'border-danger' : 'border-success'; ?>">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                            <span class="mt-2"><?php echo $periodOpen ? 'Manage Periods' : 'Manage Periods'; ?></span>
                        </a>
                    </div>

                    <div class="col-lg-3 col-md-4 col-6">
                        <a href="<?php echo view_url('admin', 'reports/generate_reports.php'); ?>" class="btn btn-outline-primary w-100 d-flex flex-column align-items-center justify-content-center quick-action-btn border-primary">
                            <i class="fas fa-file-powerpoint fa-2x"></i>
                            <span class="mt-2">Generate Reports</span>
                        </a>
                    </div>

                    <div class="col-lg-3 col-md-4 col-6">
                        <a href="<?php echo view_url('admin', 'users/add_user.php'); ?>" class="btn btn-outline-info w-100 d-flex flex-column align-items-center justify-content-center quick-action-btn border-info">
                            <i class="fas fa-user-plus fa-2x"></i>
                            <span class="mt-2">Add New User</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 