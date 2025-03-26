<!-- Agency User Navigation Header -->
<header class="main-header bg-white shadow-sm py-2">
    <div class="d-flex justify-content-between align-items-center px-3">
        <!-- LEFT SIDE: Logo -->
        <div class="header-left">
            <a href="<?php echo APP_URL; ?>/views/agency/dashboard.php" class="logo-link">
                <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="PCDS Logo" height="40">
            </a>
        </div>
        
        <!-- CENTER: Navigation links -->
        <nav class="header-center">
            <ul class="nav-list">
                <?php
                $menu_items = [
                    ['dashboard.php', 'Dashboard', 'tachometer-alt'],
                    ['submit_program_data.php', 'Submit Data', 'edit'],
                    ['submit_metrics.php', 'Metrics', 'chart-line'],
                    ['view_programs.php', 'Programs', 'project-diagram'],
                    ['create_program.php', 'Create Program', 'plus-circle'], // Added new menu item
                    ['view_reports.php', 'Reports', 'file-powerpoint'],
                    ['view_all_sectors.php', 'All Sectors', 'sitemap']
                ];
                
                foreach ($menu_items as $item):
                    $is_active = strpos($_SERVER['PHP_SELF'], '/'.$item[0]) !== false;
                ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" 
                       href="<?php echo APP_URL; ?>/views/agency/<?php echo $item[0]; ?>">
                        <i class="fas fa-<?php echo $item[2]; ?>"></i> <?php echo $item[1]; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        
        <!-- RIGHT SIDE: Logout button -->
        <div class="header-right">
            <div class="d-flex align-items-center">
                <span class="badge bg-secondary me-3 agency-badge">
                    <?php echo get_sector_name($_SESSION['sector_id'] ?? 0); ?>
                </span>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-danger btn-sm d-flex align-items-center">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout (<?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>)
                </a>
            </div>
        </div>
        
        <!-- Mobile Navigation Toggle Button -->
        <button class="nav-toggle d-lg-none">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>

<!-- JavaScript for mobile navigation toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navToggle = document.querySelector('.nav-toggle');
        const headerCenter = document.querySelector('.header-center');
        
        if (navToggle && headerCenter) {
            navToggle.addEventListener('click', function() {
                headerCenter.classList.toggle('show');
            });
        }
    });
</script>

<!-- Main content container -->
<div class="container-fluid px-4 py-4">
