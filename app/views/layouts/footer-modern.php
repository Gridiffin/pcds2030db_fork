<?php
/**
 * Modern Footer Component
 * Clean, performance-focused design with forest theme
 * Multi-column layout with responsive stacking
 */

// Ensure required constants are available
if (!defined('APP_NAME')) {
    define('APP_NAME', 'PCDS 2030 Dashboard');
}

if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}

if (!defined('APP_URL')) {
    define('APP_URL', '');
}

// Get current year for copyright
$current_year = date('Y');

// System status check (you can implement actual status checking)
$system_status = 'operational'; // 'operational', 'maintenance', 'issues'
$status_text = [
    'operational' => 'All systems operational',
    'maintenance' => 'Scheduled maintenance',
    'issues' => 'Some services unavailable'
];

// Quick links based on user role
$quick_links = [];
if (isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'agency':
            $quick_links = [
                ['url' => APP_URL . '/app/views/agency/dashboard/dashboard.php', 'label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
                ['url' => APP_URL . '/app/views/agency/programs/view_programs.php', 'label' => 'My Programs', 'icon' => 'fas fa-project-diagram'],
                ['url' => APP_URL . '/app/views/agency/reports/public_reports.php', 'label' => 'Reports', 'icon' => 'fas fa-file-alt'],
                ['url' => APP_URL . '/app/views/agency/users/all_notifications.php', 'label' => 'Notifications', 'icon' => 'fas fa-bell']
            ];
            break;
        case 'admin':
            $quick_links = [
                ['url' => APP_URL . '/app/views/admin/dashboard/dashboard.php', 'label' => 'Admin Dashboard', 'icon' => 'fas fa-tachometer-alt'],
                ['url' => APP_URL . '/app/views/admin/programs/programs.php', 'label' => 'Manage Programs', 'icon' => 'fas fa-cogs'],
                ['url' => APP_URL . '/app/views/admin/users/manage_users.php', 'label' => 'User Management', 'icon' => 'fas fa-users'],
                ['url' => APP_URL . '/app/views/admin/reports/reports.php', 'label' => 'System Reports', 'icon' => 'fas fa-chart-bar']
            ];
            break;
    }
}

// Support links
$support_links = [
    ['url' => '#', 'label' => 'Help Center', 'icon' => 'fas fa-question-circle'],
    ['url' => '#', 'label' => 'Documentation', 'icon' => 'fas fa-book'],
    ['url' => '#', 'label' => 'Contact Support', 'icon' => 'fas fa-headset'],
    ['url' => '#', 'label' => 'System Status', 'icon' => 'fas fa-server']
];

// Legal links
$legal_links = [
    ['url' => '#', 'label' => 'Privacy Policy'],
    ['url' => '#', 'label' => 'Terms of Service'],
    ['url' => '#', 'label' => 'Accessibility']
];
?>

<footer class="footer-modern" role="contentinfo" aria-label="Site footer">
    <div class="footer-container">
        <div class="footer-content">
            <!-- Brand & Description Section -->
            <div class="footer-section">
                <div class="footer-brand">
                    <i class="fas fa-leaf footer-brand-icon" aria-hidden="true"></i>
                    <h2 class="footer-brand-text"><?php echo APP_NAME; ?></h2>
                </div>
                <p class="footer-section-description">
                    Empowering sustainable development through comprehensive program management 
                    and outcome tracking. Building a better future for all communities.
                </p>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-copyright">
                <p>&copy; <?php echo $current_year; ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>

            <div class="footer-legal">
                <?php foreach ($legal_links as $link): ?>
                <a href="<?php echo $link['url']; ?>" class="legal-link">
                    <?php echo $link['label']; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="footer-version">
                <span class="version-label">Version</span>
                <span class="version-badge">
                    <?php echo defined('APP_VERSION') ? APP_VERSION : '1.0.0'; ?>
                </span>
            </div>

            <div class="footer-status">
                <span class="status-indicator" 
                      style="background-color: <?php echo $system_status === 'operational' ? 'var(--color-success)' : ($system_status === 'maintenance' ? 'var(--color-warning)' : 'var(--color-danger)'); ?>"
                      aria-hidden="true"></span>
                <span class="status-text">
                    <?php echo $status_text[$system_status]; ?>
                </span>
            </div>
        </div>
    </div>
</footer>

<!-- Include main toast component -->
<?php if (file_exists(__DIR__ . '/main_toast.php')): ?>
    <?php include_once __DIR__ . '/main_toast.php'; ?>
<?php endif; ?>

<!-- Footer JavaScript (minimal - main scripts handled in base.php) -->
<script>
// Modern footer specific functionality
document.addEventListener('DOMContentLoaded', function() {
    // Performance monitoring (optional)
    if ('performance' in window && 'measure' in performance) {
        performance.mark('footer-initialized');
    }
    
    // Footer-specific enhancements
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Add analytics tracking for social media clicks if needed
            const platform = this.getAttribute('title');
            console.log('Social media click:', platform);
        });
    });
});
</script>

</body>
</html>