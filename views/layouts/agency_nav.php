<?php
/**
 * Agency Navigation
 * 
 * Main navigation menu for agency users.
 */

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Main Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="<?php echo APP_URL; ?>/views/agency/dashboard.php">
            <?php echo APP_NAME; ?>
            <span class="badge bg-success ms-2">Agency</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'dashboard.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/agency/dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'view_programs.php' || $current_page == 'create_program.php' || $current_page == 'update_program.php' || $current_page == 'program_details.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/agency/view_programs.php">
                        <i class="fas fa-project-diagram me-1"></i> My Programs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'view_all_sectors.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/agency/view_all_sectors.php">
                        <i class="fas fa-globe me-1"></i> All Sectors
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if ($current_page == 'submit_metrics.php') echo 'active'; ?>" href="<?php echo APP_URL; ?>/views/agency/submit_metrics.php">
                        <i class="fas fa-chart-line me-1"></i> Metrics
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center ms-auto">
                <div class="user-info me-3 text-dark">
                    <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['agency_name']); ?>
                </div>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline-danger btn-sm logout-btn">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Content wrapper -->
<div class="container-fluid px-4 content-wrapper"> <!-- Ensure content-wrapper styles are applied -->
