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

// Include necessary functions
require_once PROJECT_ROOT_PATH . 'app/lib/asset_helpers.php';

// Define base URL for asset paths
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $base_path = dirname($script_name);
    $base_path = str_replace('\\', '/', $base_path);
    $base_path = rtrim($base_path, '/');
    
    // Remove /app/views/... from path to get project root
    if (strpos($base_path, '/app/') !== false) {
        $base_path = substr($base_path, 0, strpos($base_path, '/app/'));
    }
    
    define('BASE_URL', $protocol . '://' . $host . $base_path);
}

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Base CSS -->
    <link rel="stylesheet" href="<?php echo asset_url('css', 'main.css'); ?>">
    
    <!-- Dynamic CSS Bundle (Vite) -->
    <?php if ($cssBundle): ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/dist/css/<?php echo htmlspecialchars($cssBundle); ?>.bundle.css">
    <?php endif; ?>
    
    <!-- Additional CSS Files -->
    <?php foreach ($additionalStyles as $style): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($style); ?>">
    <?php endforeach; ?>
    
    <!-- Chart.js (if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="d-flex flex-column min-vh-100<?php echo isset($bodyClass) ? ' ' . htmlspecialchars($bodyClass) : ''; ?><?php echo isset($pageClass) ? ' ' . htmlspecialchars($pageClass) : ''; ?>">
    <?php
    // Include appropriate navigation based on user role
    if (function_exists('is_admin') && is_admin()) {
        require_once PROJECT_ROOT_PATH . 'app/views/layouts/admin_nav.php';
    } elseif (function_exists('is_agency') && is_agency()) {
        require_once PROJECT_ROOT_PATH . 'app/views/layouts/agency_nav.php';
    }
    
    // Include page header if it exists
    if (isset($header_config) && file_exists(PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php')) {
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
    <?php if (file_exists(PROJECT_ROOT_PATH . 'app/views/layouts/footer.php')): ?>
        <?php require_once PROJECT_ROOT_PATH . 'app/views/layouts/footer.php'; ?>
    <?php endif; ?>
    
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Dynamic JS Bundle (Vite) -->
    <?php if ($jsBundle): ?>
    <script type="module" src="<?php echo BASE_URL; ?>/dist/js/<?php echo htmlspecialchars($jsBundle); ?>.bundle.js"></script>
    <?php endif; ?>
    
    <!-- Additional JavaScript Files -->
    <?php foreach ($additionalScripts as $script): ?>
    <script src="<?php echo htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
</body>
</html>
