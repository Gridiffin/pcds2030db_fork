<?php
/**
 * Admin Footer Component
 * Modern, informative footer specifically designed for admin interface
 */

// Get system information
$system_info = [
    'version' => '2.1.0', // TODO: Get from config
    'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : 'production',
    'last_backup' => date('Y-m-d H:i:s'), // TODO: Get actual last backup time
    'uptime' => '99.9%', // TODO: Calculate actual uptime
    'database_status' => 'healthy', // TODO: Check actual database status
    'cache_status' => 'active' // TODO: Check actual cache status
];

// Helper functions - implement these based on your system
if (!function_exists('get_total_users_count')) {
    function get_total_users_count() {
        global $conn;
        try {
            if ($conn) {
                $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE active = 1");
                if ($result) {
                    $row = $result->fetch_assoc();
                    return $row['count'];
                }
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('get_active_programs_count')) {
    function get_active_programs_count() {
        global $conn;
        try {
            if ($conn) {
                $result = $conn->query("SELECT COUNT(*) as count FROM programs");
                if ($result) {
                    $row = $result->fetch_assoc();
                    return $row['count'];
                }
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('get_total_submissions_count')) {
    function get_total_submissions_count() {
        global $conn;
        try {
            if ($conn) {
                $result = $conn->query("SELECT COUNT(*) as count FROM submissions");
                if ($result) {
                    $row = $result->fetch_assoc();
                    return $row['count'];
                }
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('get_system_alerts_count')) {
    function get_system_alerts_count() {
        // TODO: Implement system alerts counting
        return 0;
    }
}

// Get statistics (after functions are defined)
$stats = [
    'total_users' => function_exists('get_total_users_count') ? get_total_users_count() : 0,
    'active_programs' => function_exists('get_active_programs_count') ? get_active_programs_count() : 0,
    'total_submissions' => function_exists('get_total_submissions_count') ? get_total_submissions_count() : 0,
    'system_alerts' => function_exists('get_system_alerts_count') ? get_system_alerts_count() : 0
];

// Quick links for admin
$quick_links = [
    [
        'title' => 'User Management',
        'url' => APP_URL . '/app/views/admin/users/manage_users.php',
        'icon' => 'fas fa-users'
    ],
    [
        'title' => 'System Logs',
        'url' => APP_URL . '/app/views/admin/settings/audit_log.php', 
        'icon' => 'fas fa-file-alt'
    ],
    [
        'title' => 'Programs',
        'url' => APP_URL . '/app/views/admin/programs/programs.php',
        'icon' => 'fas fa-project-diagram'
    ],
    [
        'title' => 'Outcomes',
        'url' => APP_URL . '/app/views/admin/outcomes/manage_outcomes.php',
        'icon' => 'fas fa-bullseye'
    ],
    [
        'title' => 'Generate Reports',
        'url' => APP_URL . '/app/views/admin/reports/generate_reports.php',
        'icon' => 'fas fa-chart-bar'
    ]
];
?>

<footer class="admin-footer">
    <div class="admin-footer-theme-accent"></div>
    <div class="admin-footer-container">
        <div class="admin-footer-main">
            <!-- System Information -->
            <div class="admin-footer-system">
                <h4 class="admin-footer-section-title">
                    <i class="fas fa-server"></i>
                    System Status
                </h4>
                <div class="admin-footer-system-info">
                    <div class="admin-footer-system-item">
                        <span class="admin-footer-system-label">Version</span>
                        <span class="admin-footer-system-value">
                            <span class="admin-footer-status-indicator"></span>
                            v<?php echo $system_info['version']; ?>
                        </span>
                    </div>
                    <div class="admin-footer-system-item">
                        <span class="admin-footer-system-label">Environment</span>
                        <span class="admin-footer-system-value">
                            <span class="admin-footer-status-indicator <?php echo $system_info['environment'] === 'production' ? '' : 'warning'; ?>"></span>
                            <?php echo ucfirst($system_info['environment']); ?>
                        </span>
                    </div>
                    <div class="admin-footer-system-item">
                        <span class="admin-footer-system-label">Database</span>
                        <span class="admin-footer-system-value">
                            <span class="admin-footer-status-indicator <?php echo $system_info['database_status'] === 'healthy' ? '' : 'error'; ?>"></span>
                            <?php echo ucfirst($system_info['database_status']); ?>
                        </span>
                    </div>
                    <div class="admin-footer-system-item">
                        <span class="admin-footer-system-label">Uptime</span>
                        <span class="admin-footer-system-value">
                            <span class="admin-footer-status-indicator"></span>
                            <?php echo $system_info['uptime']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="admin-footer-links">
                <h4 class="admin-footer-section-title">
                    <i class="fas fa-external-link-alt"></i>
                    Quick Links
                </h4>
                <div class="admin-footer-links-grid">
                    <?php foreach ($quick_links as $link): ?>
                        <a href="<?php echo $link['url']; ?>" class="admin-footer-link">
                            <i class="<?php echo $link['icon']; ?>"></i>
                            <?php echo $link['title']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Statistics -->
            <div class="admin-footer-stats">
                <h4 class="admin-footer-section-title">
                    <i class="fas fa-chart-bar"></i>
                    System Statistics
                </h4>
                <div class="admin-footer-stats-grid">
                    <div class="admin-footer-stat-item">
                        <div class="admin-footer-stat-value"><?php echo number_format($stats['total_users']); ?></div>
                        <div class="admin-footer-stat-label">Users</div>
                    </div>
                    <div class="admin-footer-stat-item">
                        <div class="admin-footer-stat-value"><?php echo number_format($stats['active_programs']); ?></div>
                        <div class="admin-footer-stat-label">Programs</div>
                    </div>
                    <div class="admin-footer-stat-item">
                        <div class="admin-footer-stat-value"><?php echo number_format($stats['total_submissions']); ?></div>
                        <div class="admin-footer-stat-label">Submissions</div>
                    </div>
                    <div class="admin-footer-stat-item">
                        <div class="admin-footer-stat-value"><?php echo $stats['system_alerts']; ?></div>
                        <div class="admin-footer-stat-label">Alerts</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="admin-footer-bottom">
            <div class="admin-footer-copyright">
                <i class="fas fa-copyright"></i>
                <span><?php echo date('Y'); ?> PCDS 2030 Administration Panel. All rights reserved.</span>
            </div>
            
            <div class="admin-footer-build-info">
                <div class="admin-footer-build-item">
                    <i class="fas fa-code-branch"></i>
                    <span>Build: <?php echo substr(md5(time()), 0, 8); ?></span>
                </div>
                <div class="admin-footer-build-item">
                    <i class="fas fa-clock"></i>
                    <span>Last Updated: <?php echo date('M j, Y'); ?></span>
                </div>
                <div class="admin-footer-build-item">
                    <i class="fas fa-user-shield"></i>
                    <span>Admin: <?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Administrator'); ?></span>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time clock update
    function updateClock() {
        const now = new Date();
        const timeElements = document.querySelectorAll('.admin-footer-current-time');
        timeElements.forEach(element => {
            element.textContent = now.toLocaleTimeString();
        });
    }
    
    // Update clock every second if time elements exist
    if (document.querySelector('.admin-footer-current-time')) {
        setInterval(updateClock, 1000);
        updateClock(); // Initial call
    }

    // Status indicator animation
    const indicators = document.querySelectorAll('.admin-footer-status-indicator');
    indicators.forEach(indicator => {
        if (indicator.classList.contains('error')) {
            indicator.style.animationDuration = '1s';
        }
    });

    // Quick link hover effects
    const quickLinks = document.querySelectorAll('.admin-footer-link');
    quickLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
});
</script>