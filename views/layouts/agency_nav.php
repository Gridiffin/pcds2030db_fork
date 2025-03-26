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
            <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="PCDS Logo" height="30" class="me-2">
            <?php echo APP_NAME; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
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
            </ul>
            
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['agency_name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/views/profile.php"><i class="fas fa-user-cog me-1"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Content wrapper -->
<div class="container-fluid px-4 mt-5 pt-4">
