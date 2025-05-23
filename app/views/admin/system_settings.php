<?php
/**
 * System Settings
 * 
 * Admin page for configuring system-wide settings.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['multi_sector'])) {
        $multi_sector = ($_POST['multi_sector'] === '1');
        $result = update_multi_sector_setting($multi_sector);
        
        if (isset($result['error'])) {
            $message = $result['error'];
            $messageType = 'danger';
        } elseif (isset($result['warning'])) {
            $message = $result['warning'];
            $messageType = 'warning';
        } elseif (isset($result['success'])) {
            $message = $result['message'];
            $messageType = 'success';
        }
    }
}

// Get current settings state
$multi_sector_enabled = get_multi_sector_setting();

// Set page title
$pageTitle = 'System Settings';

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Set up the page header variables for dashboard_header.php
$title = "System Settings";
$subtitle = "Configure system-wide settings";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = [];

// Include the dashboard header component
require_once ROOT_PATH . 'app/lib/dashboard_header.php';
?>

<div class="container-fluid px-4 py-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- System Settings Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">General Settings</h5>
        </div>
        <div class="card-body">
            <form method="post">
                <!-- Multi-Sector Toggle -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h6 class="mb-1">Multi-Sector Mode</h6>
                        <p class="text-muted mb-2">
                            Enable or disable multi-sector functionality. When enabled, the dashboard will display all sectors.
                            When disabled, the dashboard will focus exclusively on the Forestry sector.
                        </p>
                        
                        <div class="form-check form-switch mt-3">
                            <!-- Hidden field to ensure the form value is always sent -->
                            <input type="hidden" name="multi_sector" value="0">
                            
                            <input type="checkbox" class="form-check-input" id="multiSectorToggle" name="multi_sector" value="1"
                                <?php echo $multi_sector_enabled ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="multiSectorToggle">
                                <?php echo $multi_sector_enabled ? 'Enabled - All Sectors Visible' : 'Disabled - Forestry Sector Only'; ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Current Status</h6>
                                <span class="badge bg-<?php echo $multi_sector_enabled ? 'success' : 'secondary'; ?> mb-2">
                                    <?php echo $multi_sector_enabled ? 'Enabled' : 'Disabled'; ?>
                                </span>
                                <p class="small mb-0">
                                    <?php echo $multi_sector_enabled 
                                        ? 'All sectors are currently visible in the dashboard.' 
                                        : 'Dashboard is focused on the Forestry sector only.'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Changing this setting will affect the entire system. All users may need to refresh their browsers to see the changes.
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Settings
                </button>
            </form>
        </div>
    </div>
    
    <!-- Other System Information Card -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title m-0">System Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Application Version:</strong> <?php echo APP_VERSION; ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>MySQL Version:</strong> <?php echo $conn->server_info; ?></p>
                </div>
            </div>
            
            <div class="alert alert-secondary mt-3">
                <h6><i class="fas fa-flag me-2"></i>Implementation Scope</h6>
                <p class="mb-0">
                    <?php if ($multi_sector_enabled): ?>
                        This system is configured for all government sectors, including Forestry, Health, Education, and more.
                        Agency users can view data across sectors.
                    <?php else: ?>
                        This system is currently configured for the <strong>Forestry sector only</strong>. The following agencies are included:
                        <ul class="mb-0 mt-2">
                            <li>Forestry Department</li>
                            <li>Sarawak Forestry Corporation (SFC)</li>
                            <li>Sarawak Timber Industry Development Corporation (STIDC)</li>
                        </ul>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Update label text when toggle changes
document.addEventListener('DOMContentLoaded', function() {
    const toggleSwitch = document.getElementById('multiSectorToggle');
    if (toggleSwitch) {
        toggleSwitch.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (this.checked) {
                label.textContent = 'Enabled - All Sectors Visible';
            } else {
                label.textContent = 'Disabled - Forestry Sector Only';
            }
        });
    }
});
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>

