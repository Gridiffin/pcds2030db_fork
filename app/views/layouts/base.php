<?php
/**
 * Base Layout
 * 
 * Central layout file that handles dynamic asset injection and common HTML structure.
 * Replaces the old header.php pattern with a more flexible approach.
 * 
 * Usage:
 * $cssBundle = 'initiatives'; // Will load dist/css/initiatives.bundle.css
 * $jsBundle = 'initiatives';  // Will load dist/js/initiatives.bundle.js
 * $pageTitle = 'Initiatives';
 * $contentFile = __DIR__ . '/partials/content.php'; // Optional: for partial-based content
 * require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
 */

// Ensure PROJECT_ROOT_PATH is defined
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include config.php to get APP_URL and other constants
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Include necessary functions
require_once PROJECT_ROOT_PATH . 'app/lib/asset_helpers.php';

// APP_URL is now available from config.php - no need to define BASE_URL

// Set default values if not provided
$pageTitle = $pageTitle ?? 'PCDS 2030 Dashboard';
$cssBundle = $cssBundle ?? null;
$jsBundle = $jsBundle ?? null;
$contentFile = $contentFile ?? null;
$additionalScripts = $additionalScripts ?? [];
$additionalStyles = $additionalStyles ?? [];


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - PCDS 2030 Dashboard</title>
    
    <!-- Preconnect to Google Fonts for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts - Poppins with all needed weights -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo asset_url('images', 'favicon.ico'); ?>" type="image/x-icon">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    
    <!-- 🚫 INDIVIDUAL CSS FILES REMOVED - USING VITE BUNDLES ONLY -->
    <!-- CSS Bundle - extracted from JS imports by Vite -->
    <?php if ($cssBundle): ?>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/dist/css/<?php echo htmlspecialchars($cssBundle); ?>.bundle.css">
    <?php endif; ?>
    
    <!-- Additional CSS Files -->
    <?php foreach ($additionalStyles as $style): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($style); ?>">
    <?php endforeach; ?>
</head>
<body class="d-flex flex-column min-vh-100<?php echo isset($bodyClass) ? ' ' . htmlspecialchars($bodyClass) : ''; ?><?php echo isset($pageClass) ? ' ' . htmlspecialchars($pageClass) : ''; ?>">
    <?php
    // Include appropriate navigation based on user role
    if (function_exists('is_admin') && is_admin()) {
        // Use modern admin navbar
        require_once PROJECT_ROOT_PATH . 'app/views/layouts/admin-navbar-modern.php';
    } elseif (function_exists('is_agency') && is_agency()) {
        // Use modern navbar for agency users
        require_once PROJECT_ROOT_PATH . 'app/views/layouts/navbar-modern.php';
    }
    
    // Include page header if not explicitly disabled
    // Initialize header_config with defaults if not set
    if (!isset($disable_page_header) && file_exists(PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php')) {
        if (!isset($header_config)) {
            $header_config = [
                'title' => $pageTitle ?? 'Page Title',
                'subtitle' => '',
                'breadcrumb' => []
            ];
        }
        require_once PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php';
    }
    ?>
    
    <!-- Main Content -->
    <?php if ($contentFile && file_exists($contentFile)): ?>
        <?php require_once $contentFile; ?>
    <?php else: ?>
        <!-- Content will be rendered inline if no contentFile specified -->
    <?php endif; ?>
    
    <!-- Toast Container -->
    <?php if (file_exists(PROJECT_ROOT_PATH . 'app/views/layouts/main_toast.php')): ?>
        <?php require_once PROJECT_ROOT_PATH . 'app/views/layouts/main_toast.php'; ?>
    <?php endif; ?>
    
    <!-- Footer -->
    <?php if (file_exists(PROJECT_ROOT_PATH . 'app/views/layouts/footer-modern.php')): ?>
        <?php require_once PROJECT_ROOT_PATH . 'app/views/layouts/footer-modern.php'; ?>
    <?php elseif (file_exists(PROJECT_ROOT_PATH . 'app/views/layouts/footer.php')): ?>
        <?php require_once PROJECT_ROOT_PATH . 'app/views/layouts/footer.php'; ?>
    <?php endif; ?>
    
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <!-- Chart.js - Ensure it's always loaded before dashboard scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <!-- 🚫 INDIVIDUAL JS FILES REMOVED - USING VITE BUNDLES ONLY -->
    <!-- All JavaScript utilities now loaded through vite bundles -->
    
    <!-- Core App JavaScript -->
    <script>
        // Handle preloader
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.classList.add('preloader-hide');
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 300);
            }
        });
    </script>
    
    <!-- JS Bundle - loads after all other scripts -->
    <?php if ($jsBundle): ?>
    <script type="module" src="<?php echo APP_URL; ?>/dist/js/<?php echo htmlspecialchars($jsBundle); ?>.bundle.js"></script>
    <?php endif; ?>
    
    <!-- Additional page-specific scripts -->
    <?php if (isset($additionalScripts) && is_array($additionalScripts)): ?>
        <?php foreach($additionalScripts as $script): ?>
            <?php if (strpos($script, 'http') === 0 || strpos($script, '//') === 0): ?>
                <!-- External script -->
                <script src="<?php echo $script; ?>"></script>
            <?php elseif (strpos($script, 'asset_url') !== false || strpos($script, 'APP_URL') !== false): ?>
                <!-- Script already using helper functions -->
                <script src="<?php echo $script; ?>"></script>
            <?php else: ?>
                <!-- Convert relative path to asset_url -->
                <?php
                    // Extract path parts
                    $pathParts = explode('/', $script);
                    $filename = array_pop($pathParts);
                    $directory = implode('/', $pathParts);
                    // Remove 'assets/' prefix if present
                    $directory = str_replace('assets/', '', $directory);
                ?>
                <?php if (function_exists('asset_url')): ?>
                <script src="<?php echo asset_url($directory, $filename); ?>"></script>
                <?php else: ?>
                <script src="<?php echo APP_URL; ?>/assets/<?php echo $script; ?>"></script>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Inline page-specific scripts -->
    <?php if (isset($inlineScripts)): ?>
    <script>
        <?php echo $inlineScripts; ?>
    </script>
    <?php endif; ?>

    <!-- Fallback: Force Bootstrap dropdown re-initialization if needed -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof bootstrap !== 'undefined' && typeof bootstrap.Dropdown !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(dropdownToggleEl) {
                if (!bootstrap.Dropdown.getInstance(dropdownToggleEl)) {
                    new bootstrap.Dropdown(dropdownToggleEl);
                }
            });
        }
    });
    </script>

    <!-- Toast container for notifications -->
    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" aria-atomic="true"></div>

</body>
</html>
