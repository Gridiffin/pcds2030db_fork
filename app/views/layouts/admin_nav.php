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
$is_initiative_page = (strpos($current_uri, '/initiatives/') !== false);

// Fix: Only one nav item should have 'active' at a time
$is_programs_active = $is_program_page;
$is_users_active = $is_user_page;
$is_outcomes_active = $is_outcome_page;
$is_initiatives_active = $is_initiative_page;

// Check if current page is report-related
$is_report_page = $current_page == 'generate_reports.php';

// Check if current page is settings related (including users)
$is_settings_page = in_array($current_page, ['reporting_periods.php', 'audit_log.php', 'manage_periods.php', 'system_settings.php', 'manage_users.php', 'add_user.php']);
?>

<!-- Main Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid px-4">        <a class="navbar-brand" href="<?php echo APP_URL; ?>/app/views/admin/dashboard/dashboard.php"
           data-full-text="<?php echo APP_NAME; ?>" 
           data-short-text="PCDS 2030 Dashboard"
           data-ultra-short-text="PCDS Dashboard">
            <span class="brand-text"><?php echo APP_NAME; ?></span>
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
                  <!-- Initiatives Dropdown -->
                <li class="nav-item dropdown">
                    <button class="nav-link dropdown-toggle btn <?php if ($is_initiatives_active) echo 'active'; ?>" data-bs-toggle="dropdown" aria-expanded="false" type="button">
                        <i class="fas fa-lightbulb me-1"></i> Initiatives <i class="fas fa-caret-down nav-dropdown-icon"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'manage_initiatives.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/initiatives/manage_initiatives.php">
                                <i class="fas fa-list me-1"></i> Manage Initiatives
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'create.php' && $is_initiative_page) echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/initiatives/create.php">
                                <i class="fas fa-plus me-1"></i> Create Initiative
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Outcomes Dropdown -->
                <li class="nav-item dropdown">
                    <button class="nav-link dropdown-toggle btn <?php if ($is_outcomes_active) echo 'active'; ?>" data-bs-toggle="dropdown" aria-expanded="false" type="button">
                        <i class="fas fa-chart-line me-1"></i> Outcomes <i class="fas fa-caret-down nav-dropdown-icon"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'manage_outcomes.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/outcomes/manage_outcomes.php">
                                <i class="fas fa-list me-1"></i> Manage Outcomes
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'create_outcome_details.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/outcomes/create_outcome_details.php">
                                <i class="fas fa-chart-line me-1"></i> Create Outcome Details
                            </a>                        </li>
                    </ul>
                </li>
            </ul>

            <div class="d-flex align-items-center ms-auto">
                <!-- Reports (Icon Only) -->
                <div class="nav-item me-3">
                    <a class="nav-link p-0 <?php if ($is_report_page) echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/reports/generate_reports.php" title="Reports">
                        <i class="fas fa-file-alt"></i>
                    </a>
                </div>
                
                <!-- Settings Dropdown -->
                <div class="nav-item dropdown me-3">
                    <button class="nav-link p-0 dropdown-toggle btn text-dark <?php if ($is_settings_page) echo 'active'; ?>" data-bs-toggle="dropdown" aria-expanded="false" title="Settings" type="button">
                        <i class="fas fa-cog"></i>
                        <i class="fas fa-caret-down nav-dropdown-icon small"></i>
                    </button>                    <ul class="dropdown-menu dropdown-menu-end">
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
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'manage_users.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/users/manage_users.php">
                                <i class="fas fa-users me-1"></i> Manage Users
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php if ($current_page == 'add_user.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/app/views/admin/users/add_user.php">
                                <i class="fas fa-user-plus me-1"></i> Add User
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

/* Icon-only Reports button styling */
.nav-item .nav-link[title="Reports"] {
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    transition: background-color 0.15s ease-in-out;
}

.nav-item .nav-link[title="Reports"]:hover {
    background-color: rgba(0,0,0,0.05);
}

.nav-item .nav-link[title="Reports"].active {
    background-color: var(--bs-primary);
    color: white;
}

/* Settings dropdown improvements */
.dropdown-menu .dropdown-divider {
    margin: 0.5rem 0;
}

/* Responsive behavior for Reports text */
@media (max-width: 991.98px) {
    .nav-item .nav-link[title="Reports"] {
        padding: 0.5rem 1rem;
    }
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
