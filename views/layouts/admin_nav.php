<nav class="navbar navbar-expand-lg navbar-light mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo APP_URL; ?>/views/admin/dashboard.php">
            <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="PCDS Logo" height="40" class="me-2">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- User info and logout for mobile view -->
        <div class="d-flex d-lg-none ms-auto me-2">
            <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-danger btn-sm logout-btn">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
        </div>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Center the navigation items -->
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/views/admin/dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'programs.php') !== false ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/views/admin/programs.php">
                        <i class="fas fa-project-diagram me-1"></i> Programs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'sectors.php') !== false ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/views/admin/sectors.php">
                        <i class="fas fa-layer-group me-1"></i> Sectors
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'reporting_periods.php') !== false ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/views/admin/reporting_periods.php">
                        <i class="fas fa-calendar-alt me-1"></i> Periods
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'reports.php') !== false ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/views/admin/reports.php">
                        <i class="fas fa-file-powerpoint me-1"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'users.php') !== false ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/views/admin/users.php">
                        <i class="fas fa-users me-1"></i> Users
                    </a>
                </li>
            </ul>
            
            <!-- User info and logout for desktop (right-aligned) -->
            <div class="ms-auto d-none d-lg-flex align-items-center">
                <div class="user-info me-3 text-end">
                    <small class="text-muted d-block">Logged in as</small>
                    <span class="fw-medium"><?php echo $_SESSION['username']; ?></span>
                </div>
                
                <!-- Prominent logout button -->
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-danger logout-btn">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>
