<?php
/**
 * Admin Navigation
 * 
 * Main navigation menu for admin users.
 */

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Main Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="<?php echo APP_URL; ?>/views/admin/dashboard.php">
            <?php echo APP_NAME; ?>
            <span class="badge bg-primary ms-2">Admin</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'dashboard.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'programs.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/programs.php">
                        <i class="fas fa-project-diagram me-1"></i> Programs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'manage_users.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/manage_users.php">
                        <i class="fas fa-users me-1"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'manage_metrics.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/manage_metrics.php">
                        <i class="fas fa-chart-line me-1"></i> Metrics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'reporting_periods.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/admin/reporting_periods.php">
                        <i class="fas fa-calendar-alt me-1"></i> Periods
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center ms-auto">
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
