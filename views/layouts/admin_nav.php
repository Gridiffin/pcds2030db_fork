<!-- Brand new header with completely separate elements -->
<header class="main-header bg-white shadow-sm py-2">
    <div class="d-flex justify-content-between align-items-center px-3">
        <!-- LEFT SIDE: Logo -->
        <div class="header-left">
            <a href="<?php echo APP_URL; ?>/views/admin/dashboard.php" class="logo-link">
                <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="PCDS Logo" height="40">
            </a>
        </div>
        
        <!-- CENTER: Navigation links -->
        <nav class="header-center">
            <ul class="nav-list">
                <?php
                $menu_items = [
                    ['dashboard.php', 'Dashboard', 'tachometer-alt'],
                    ['programs.php', 'Programs', 'project-diagram'],
                    ['sectors.php', 'Sectors', 'layer-group'],
                    ['reporting_periods.php', 'Periods', 'calendar-alt'],
                    ['reports.php', 'Reports', 'file-powerpoint'],
                    ['manage_users.php', 'Users', 'users']
                ];
                
                foreach ($menu_items as $item):
                    $is_active = strpos($_SERVER['PHP_SELF'], '/'.$item[0]) !== false;
                ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" 
                       href="<?php echo APP_URL; ?>/views/admin/<?php echo $item[0]; ?>">
                        <i class="fas fa-<?php echo $item[2]; ?>"></i> <?php echo $item[1]; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        
        <!-- RIGHT SIDE: Logout button -->
        <div class="header-right">
            <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-danger btn-sm d-flex align-items-center">
                <i class="fas fa-sign-out-alt me-2"></i> Logout (<?php echo $_SESSION['username']; ?>)
            </a>
        </div>
    </div>
</header>

<!-- Main content container -->
<div class="container-fluid px-4 py-4">
