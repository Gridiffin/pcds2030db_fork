<?php
/**
 * Admin Navbar Component
 * Modern, responsive navbar specifically designed for admin interface
 */

// Get current user information
$current_user = $_SESSION['user'] ?? null;
$user_name = $current_user['name'] ?? ($_SESSION['fullname'] ?? 'Administrator');
$user_role = $current_user['role'] ?? 'admin';

// Get user initials for avatar
$user_initials = '';
if (isset($_SESSION['fullname'])) {
    $names = explode(' ', $_SESSION['fullname']);
    $user_initials = substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : '');
} else {
    $user_initials = substr($_SESSION['username'] ?? 'AD', 0, 2);
}
$user_initials = strtoupper($user_initials);

// Get current page for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_path = $_SERVER['REQUEST_URI'];

// Navigation items
$nav_items = [
    [
        'title' => 'Dashboard',
        'url' => APP_URL . '/app/views/admin/dashboard/dashboard.php',
        'icon' => 'fas fa-tachometer-alt',
        'active' => strpos($current_path, '/admin/dashboard/') !== false
    ],
    [
        'title' => 'Programs',
        'url' => APP_URL . '/app/views/admin/programs/programs.php',
        'icon' => 'fas fa-project-diagram',
        'active' => strpos($current_path, '/admin/programs/') !== false
    ],
    [
        'title' => 'Outcomes',
        'url' => APP_URL . '/app/views/admin/outcomes/manage_outcomes.php',
        'icon' => 'fas fa-bullseye',
        'active' => strpos($current_path, '/admin/outcomes/') !== false
    ],
    [
        'title' => 'Initiatives',
        'url' => APP_URL . '/app/views/admin/initiatives/manage_initiatives.php',
        'icon' => 'fas fa-lightbulb',
        'active' => strpos($current_path, '/admin/initiatives/') !== false
    ],
    [
        'title' => 'Reports',
        'url' => APP_URL . '/app/views/admin/reports/generate_reports.php',
        'icon' => 'fas fa-chart-bar',
        'active' => strpos($current_path, '/admin/reports/') !== false
    ],
    [
        'title' => 'Settings',
        'url' => '#',
        'icon' => 'fas fa-cog',
        'dropdown' => [
            [
                'title' => 'Users',
                'url' => APP_URL . '/app/views/admin/users/manage_users.php',
                'icon' => 'fas fa-users'
            ],
            [
                'title' => 'Audit Log',
                'url' => APP_URL . '/app/views/admin/settings/audit_log.php',
                'icon' => 'fas fa-history'
            ],
            [
                'title' => 'Reporting Periods',
                'url' => APP_URL . '/app/views/admin/periods/reporting_periods.php',
                'icon' => 'fas fa-calendar'
            ]
        ],
        'active' => strpos($current_path, '/admin/settings/') !== false || 
                   strpos($current_path, '/admin/periods/') !== false ||
                   strpos($current_path, '/admin/users/') !== false
    ]
];

// Helper function to get user initials
if (!function_exists('get_user_initials')) {
    function get_user_initials($name) {
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            return strtoupper(substr($name, 0, 2));
        }
    }
}

// Get notification count (you can implement this based on your notification system)
$notification_count = 0; // TODO: Implement notification counting
?>

<nav class="admin-navbar" id="adminNavbar">
    <div class="admin-navbar-theme-accent"></div>
    <div class="admin-navbar-container">
        <!-- Brand Section -->
        <a href="<?php echo APP_URL; ?>/app/views/admin/dashboard/dashboard.php" class="admin-navbar-brand">
            <div class="admin-navbar-logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="admin-navbar-title">
                <div class="admin-navbar-title-main">PCDS 2030</div>
                <div class="admin-navbar-title-sub">Admin Portal</div>
            </div>
        </a>

        <!-- Navigation Menu -->
        <ul class="admin-navbar-nav" id="adminNavbarNav">
            <?php foreach ($nav_items as $item): ?>
                <li class="admin-navbar-nav-item <?php echo isset($item['dropdown']) ? 'admin-navbar-dropdown' : ''; ?>">
                    <?php if (isset($item['dropdown'])): ?>
                        <!-- Dropdown Menu -->
                        <div class="admin-navbar-nav-link admin-navbar-dropdown-toggle <?php echo $item['active'] ? 'active' : ''; ?>">
                            <i class="<?php echo $item['icon']; ?>"></i>
                            <span><?php echo $item['title']; ?></span>
                        </div>
                        <div class="admin-navbar-dropdown-menu">
                            <?php foreach ($item['dropdown'] as $dropdown_item): ?>
                                <a href="<?php echo $dropdown_item['url']; ?>" class="admin-navbar-dropdown-item">
                                    <i class="<?php echo $dropdown_item['icon']; ?>"></i>
                                    <?php echo $dropdown_item['title']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Regular Link -->
                        <a href="<?php echo $item['url']; ?>" class="admin-navbar-nav-link <?php echo $item['active'] ? 'active' : ''; ?>">
                            <i class="<?php echo $item['icon']; ?>"></i>
                            <span><?php echo $item['title']; ?></span>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Right Section -->
        <div class="admin-navbar-user">
            <!-- Notifications -->
            <?php if ($notification_count > 0): ?>
                <div class="admin-navbar-notifications">
                    <button class="admin-navbar-notifications-btn" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="admin-navbar-notifications-badge"><?php echo $notification_count; ?></span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- User Info -->
            <div class="admin-navbar-user-info">
                <div class="admin-navbar-user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="admin-navbar-user-role"><?php echo ucfirst(htmlspecialchars($user_role)); ?></div>
            </div>

            <!-- User Avatar Dropdown -->
            <div class="admin-navbar-dropdown">
                <div class="admin-navbar-dropdown-toggle admin-navbar-user-dropdown" title="User Menu">
                    <div class="admin-navbar-user-avatar">
                        <?php echo $user_initials; ?>
                    </div>
                </div>
                <div class="admin-navbar-dropdown-menu">
                    <a href="<?php echo APP_URL; ?>/logout.php" class="admin-navbar-dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>

            <!-- Mobile Toggle -->
            <button class="admin-navbar-mobile-toggle" id="adminNavbarToggle" aria-label="Toggle navigation">
                <span class="admin-navbar-mobile-toggle-line"></span>
                <span class="admin-navbar-mobile-toggle-line"></span>
                <span class="admin-navbar-mobile-toggle-line"></span>
            </button>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const mobileToggle = document.getElementById('adminNavbarToggle');
    const navbarNav = document.getElementById('adminNavbarNav');
    const navbar = document.getElementById('adminNavbar');
    
    if (mobileToggle && navbarNav) {
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.toggle('active');
            navbarNav.classList.toggle('show');
            
            // Prevent body scroll when mobile menu is open
            if (navbarNav.classList.contains('show')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
    }

    // Dropdown functionality
    const dropdownToggles = document.querySelectorAll('.admin-navbar-dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const dropdown = this.closest('.admin-navbar-dropdown');
            const isInMobileNav = this.closest('.admin-navbar-nav');
            
            // Close other dropdowns (except when in mobile nav and this is a nav dropdown)
            document.querySelectorAll('.admin-navbar-dropdown.show').forEach(otherDropdown => {
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('show');
            
            // For mobile nav dropdowns, ensure proper scrolling if needed
            if (isInMobileNav && dropdown.classList.contains('show')) {
                setTimeout(() => {
                    const dropdownMenu = dropdown.querySelector('.admin-navbar-dropdown-menu');
                    if (dropdownMenu) {
                        dropdownMenu.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }, 300);
            }
        });
        
        // Add touch event for better mobile responsiveness
        toggle.addEventListener('touchend', function(e) {
            // Prevent the click event from firing after touchend
            e.preventDefault();
            this.click();
        });
    });

    // Close mobile menu and dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        // Close dropdowns
        if (!e.target.closest('.admin-navbar-dropdown')) {
            document.querySelectorAll('.admin-navbar-dropdown.show').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
        
        // Close mobile menu if clicking outside navbar
        if (!e.target.closest('.admin-navbar') && navbarNav && navbarNav.classList.contains('show')) {
            navbarNav.classList.remove('show');
            mobileToggle.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Close mobile menu when clicking on nav links
    const navLinks = document.querySelectorAll('.admin-navbar-nav-link:not(.admin-navbar-dropdown-toggle)');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (navbarNav && navbarNav.classList.contains('show')) {
                navbarNav.classList.remove('show');
                mobileToggle.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            // Reset mobile menu state on larger screens
            if (navbarNav) {
                navbarNav.classList.remove('show');
            }
            if (mobileToggle) {
                mobileToggle.classList.remove('active');
            }
            document.body.style.overflow = '';
        }
    });

    // Navbar scroll effect
    let lastScrollY = window.scrollY;
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        lastScrollY = window.scrollY;
    });

    // Touch support for better mobile experience
    let touchStartY = 0;
    document.addEventListener('touchstart', function(e) {
        touchStartY = e.touches[0].clientY;
    });

    document.addEventListener('touchend', function(e) {
        const touchEndY = e.changedTouches[0].clientY;
        const touchDiff = touchStartY - touchEndY;
        
        // Close mobile menu on significant downward swipe
        if (touchDiff < -50 && navbarNav && navbarNav.classList.contains('show')) {
            navbarNav.classList.remove('show');
            mobileToggle.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Add body class for navbar padding
    document.body.classList.add('has-admin-navbar');

    // Improve keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Close mobile menu and dropdowns on Escape key
            if (navbarNav && navbarNav.classList.contains('show')) {
                navbarNav.classList.remove('show');
                mobileToggle.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            document.querySelectorAll('.admin-navbar-dropdown.show').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
});
</script>