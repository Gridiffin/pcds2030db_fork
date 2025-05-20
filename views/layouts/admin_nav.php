<?php
/**
 * Admin Navigation
 * 
 * Main navigation menu for admin users.
 */

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// Check if current page is program-related
$is_program_page = in_array($current_page, ['programs.php', 'view_program.php', 'assign_programs.php', 'delete_program.php']);

// Check if current page is report-related
$is_report_page = $current_page == 'generate_reports.php';

// Check if current page is user management related
$is_user_page = in_array($current_page, ['manage_users.php', 'add_user.php', 'edit_user.php']);

// Check if current page is metric related
$is_outcome_page = in_array($current_page, ['manage_metrics.php', 'edit_metric.php', 'view_metric.php']);

// Check if current page is settings related
$is_settings_page = in_array($current_page, ['reporting_periods.php', 'audit_log.php', 'manage_periods.php', 'system_settings.php']);

// Check if audit log is active
$audit_log = $current_page == 'audit_log.php';
?>



<!-- Main Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="<?php echo APP_URL; ?>/views/admin/dashboard.php">
            <?php echo APP_NAME; ?>
            
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'dashboard.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                
                <!-- Programs Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php if ($is_program_page) echo 'active'; ?>" href="javascript:void(0)" id="programsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="<?php echo $is_program_page ? 'true' : 'false'; ?>">
                        <i class="fas fa-project-diagram me-1"></i> Programs <i class="fas fa-caret-down nav-dropdown-icon"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="programsDropdown">
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'programs.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/programs.php">
                                <i class="fas fa-list me-1"></i> All Programs
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'assign_programs.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/assign_programs.php">
                                <i class="fas fa-tasks me-1"></i> Assign Programs
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Users Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php if ($is_user_page) echo 'active'; ?>" href="javascript:void(0)" id="usersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="<?php echo $is_user_page ? 'true' : 'false'; ?>">
                        <i class="fas fa-users me-1"></i> Users <i class="fas fa-caret-down nav-dropdown-icon"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="usersDropdown">
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'manage_users.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/manage_users.php">
                                <i class="fas fa-user-cog me-1"></i> Manage Users
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'add_user.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/add_user.php">
                                <i class="fas fa-user-plus me-1"></i> Add User
                            </a>
                        </li>
                    </ul>
                </li>
                  <!-- Outcomes (Direct Link) -->
                <li class="nav-item">
                    <a class="nav-link <?php if ($is_outcome_page) echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/manage_metrics.php">
                        <i class="fas fa-chart-line me-1"></i> Outcomes
                    </a>
                </li>
                
                <!-- Reports -->
                <li class="nav-item">
                    <a class="nav-link <?php if ($is_report_page) echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/generate_reports.php">
                        <i class="fas fa-file-alt me-1"></i> Reports
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center ms-auto">
                <!-- Settings Dropdown (Icon only) -->
                <div class="nav-item dropdown me-3">
                    <a class="nav-link p-0 dropdown-toggle text-dark <?php if ($is_settings_page) echo 'active'; ?>" href="javascript:void(0)" id="settingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="<?php echo $is_settings_page ? 'true' : 'false'; ?>" title="Settings">
                        <i class="fas fa-cog"></i>
                        <i class="fas fa-caret-down nav-dropdown-icon small"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="settingsDropdown">
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'system_settings.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/system_settings.php">
                                <i class="fas fa-sliders-h me-1"></i> System Settings
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'reporting_periods.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/reporting_periods.php">
                                <i class="fas fa-calendar-alt me-1"></i> Reporting Periods
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'audit_log.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/audit_log.php">
                                <i class="fas fa-clipboard-list me-1"></i> Audit Log
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="user-info me-3 text-dark">
                    <i class="fas fa-user-shield me-1"></i> Administrator
                </div>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline-danger btn-sm logout-btn">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Content wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page content will be inserted here -->
