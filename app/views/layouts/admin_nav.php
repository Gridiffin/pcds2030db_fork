<?php
/**
 * Admin Navigation
 * 
 * Main navigation menu for admin users.
 */

// Get current page and URI
$current_page = basename($_SERVER['PHP_SELF']);
$current_uri = $_SERVER['REQUEST_URI'];

// Improved section detection for highlighting nav tabs
$is_program_page = (strpos($current_uri, '/programs/') !== false);
$is_user_page = (strpos($current_uri, '/users/') !== false);
$is_outcome_page = (strpos($current_uri, '/outcomes/') !== false);

// Fix: Only one nav item should have 'active' at a time
$is_programs_active = $is_program_page;
$is_users_active = $is_user_page;
$is_outcomes_active = $is_outcome_page;

// Check if current page is report-related
$is_report_page = $current_page == 'generate_reports.php';

// Check if current page is settings related
$is_settings_page = in_array($current_page, ['reporting_periods.php', 'audit_log.php', 'manage_periods.php', 'system_settings.php']);
?>

<!-- Main Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="<?php echo APP_URL; ?>/app/views/admin/dashboard/dashboard.php">
            <?php echo APP_NAME; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav mx-auto">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'dashboard.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/dashboard/dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                
                <!-- Programs Dropdown -->
                <li class="nav-item dropdown">
                    <button class="nav-link dropdown-toggle btn <?php if ($is_programs_active) echo 'active'; ?>" data-bs-toggle="dropdown" aria-expanded="false" type="button">
                        <i class="fas fa-project-diagram me-1"></i> Programs <i class="fas fa-caret-down nav-dropdown-icon"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'programs.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/programs/programs.php">
                                <i class="fas fa-list me-1"></i> All Programs
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'assign_programs.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/programs/assign_programs.php">
                                <i class="fas fa-tasks me-1"></i> Assign Programs
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Users Dropdown -->
                <li class="nav-item dropdown">
                    <button class="nav-link dropdown-toggle btn <?php if ($is_users_active) echo 'active'; ?>" data-bs-toggle="dropdown" aria-expanded="false" type="button">
                        <i class="fas fa-users me-1"></i> Users <i class="fas fa-caret-down nav-dropdown-icon"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'manage_users.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/users/manage_users.php">
                                <i class="fas fa-user-cog me-1"></i> Manage Users
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'add_user.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/users/add_user.php">
                                <i class="fas fa-user-plus me-1"></i> Add User
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Outcomes Link -->
                <li class="nav-item">
                    <a class="nav-link <?php if ($is_outcomes_active) echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/outcomes/manage_outcomes.php">
                        <i class="fas fa-chart-line me-1"></i> Outcomes
                    </a>
                </li>
                
                <!-- Reports -->
                <li class="nav-item">
                    <a class="nav-link <?php if ($is_report_page) echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/reports/generate_reports.php">
                        <i class="fas fa-file-alt me-1"></i> Reports
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center ms-auto">
                <!-- Settings Dropdown -->
                <div class="nav-item dropdown me-3">
                    <button class="nav-link p-0 dropdown-toggle btn text-dark <?php if ($is_settings_page) echo 'active'; ?>" data-bs-toggle="dropdown" aria-expanded="false" title="Settings" type="button">
                        <i class="fas fa-cog"></i>
                        <i class="fas fa-caret-down nav-dropdown-icon small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'system_settings.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/settings/system_settings.php">
                                <i class="fas fa-sliders-h me-1"></i> System Settings
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'reporting_periods.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/periods/reporting_periods.php">
                                <i class="fas fa-calendar-alt me-1"></i> Reporting Periods
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'audit_log.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/audit/audit_log.php">
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

<style>
.nav-link.btn {
    background: none;
    border: none;
    padding: 0.5rem 1rem;
}
.nav-link.btn:hover,
.nav-link.btn:focus {
    background: rgba(0,0,0,0.05);
}
.nav-link.btn.active {
    color: var(--bs-navbar-active-color);
}
.navbar-nav .dropdown-menu { 
    margin-top: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dropdowns using Bootstrap 5's API
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'))
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl, {
            autoClose: true
        })
    })
})
</script>
