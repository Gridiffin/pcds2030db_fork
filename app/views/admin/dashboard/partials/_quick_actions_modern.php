<?php
/**
 * Modern Quick Actions Partial
 * 
 * Enhanced UI with hover effects and smooth animations
 * Maintains all original functionality and links
 */

// Get current period status for contextual actions
$periodOpen = isset($current_period) && isset($current_period['status']) && $current_period['status'] === 'open';
$periodId = $current_period['period_id'] ?? 0;
?>

<div class="admin-card-modern admin-fade-in">
    <div class="admin-card-modern-header">
        <h3 class="admin-card-modern-title">
            <div class="admin-card-icon-modern">
                <i class="fas fa-bolt"></i>
            </div>
            Quick Actions
        </h3>
    </div>
    
    <div class="admin-card-modern-content">
        <div class="admin-quick-actions-modern" role="navigation" aria-label="Quick Actions">
            <!-- Manage Periods -->
            <a href="<?php echo view_url('admin', 'periods/reporting_periods.php'); ?>" 
               class="admin-action-card-modern"
               aria-describedby="action-periods-desc">
                <div class="admin-action-icon-modern <?php echo $periodOpen ? 'warning' : 'success'; ?>" aria-hidden="true">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h4 class="admin-action-title-modern">Manage Periods</h4>
                <span class="sr-only" id="action-periods-desc">
                    Navigate to reporting periods management page
                </span>
            </a>

            <!-- Generate Reports -->
            <a href="<?php echo view_url('admin', 'reports/generate_reports.php'); ?>" 
               class="admin-action-card-modern">
                <div class="admin-action-icon-modern primary">
                    <i class="fas fa-file-powerpoint"></i>
                </div>
                <h4 class="admin-action-title-modern">Generate Reports</h4>
            </a>

            <!-- Add New User -->
            <a href="<?php echo view_url('admin', 'users/add_user.php'); ?>" 
               class="admin-action-card-modern">
                <div class="admin-action-icon-modern info">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h4 class="admin-action-title-modern">Add New User</h4>
            </a>
        </div>
    </div>
</div>