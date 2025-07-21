<?php
// Ensure asset_url function is available
if (!function_exists('asset_url')) {
    // Define PROJECT_ROOT_PATH if not already defined
    if (!defined('PROJECT_ROOT_PATH')) {
        define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
    }
    
    // Include asset helpers to get asset_url function
    if (file_exists(PROJECT_ROOT_PATH . 'lib/asset_helpers.php')) {
        require_once PROJECT_ROOT_PATH . 'app/lib/asset_helpers.php';
    } elseif (file_exists(PROJECT_ROOT_PATH . 'config/config.php')) {
        require_once PROJECT_ROOT_PATH . 'config/config.php';
    } else {
        // Fallback definition if files not found
        function asset_url($type, $file) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
            $base_path = dirname($script_name);
            $base_path = str_replace('\\', '/', $base_path);
            $base_path = rtrim($base_path, '/');
            
            if (strpos($base_path, '/app/') !== false) {
                $base_path = substr($base_path, 0, strpos($base_path, '/app/'));
            }
            
            return $protocol . '://' . $host . $base_path . '/assets/' . $type . '/' . $file;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>PCDS 2030 Dashboard</title>
    
    <!-- Preconnect to Google Fonts for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Google Fonts - Poppins with all needed weights -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo asset_url('img', 'favicon.ico'); ?>" type="image/x-icon">
    
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <!-- Main CSS (imports all component and layout CSS) -->
    <link rel="stylesheet" href="<?php echo asset_url('css', 'main.css'); ?>">
    
    <!-- Simple header CSS -->
    <link rel="stylesheet" href="<?php echo asset_url('css', 'simple-header.css'); ?>">

    <!-- Set global APP_URL JavaScript variable for URL helper functions -->
    <script>
        window.APP_URL = '<?php echo APP_URL; ?>';
    </script>
    <!-- JavaScript URL helper functions -->
    <script src="<?php echo asset_url('js', 'url_helpers.js'); ?>"></script>
    
    <!-- Responsive navbar text handler -->
    <script src="<?php echo asset_url('js', 'responsive-navbar.js'); ?>"></script>
    
    <!-- Additional page-specific styles -->
    <?php if (isset($additionalStyles) && is_array($additionalStyles)): ?>
        <?php foreach($additionalStyles as $style): ?>
            <link href="<?php echo $style; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline page-specific styles -->
    <?php if (isset($inlineStyles)): ?>
        <style>
            <?php echo $inlineStyles; ?>
        </style>
    <?php endif; ?>
    
    <!-- Full-width header style -->
    <style>
        /* Override any potential container margins/paddings that might affect header width */
        body {
            overflow-x: hidden; /* Prevent horizontal scrolling */
        }
        
        /* Fix content wrapper and its containers to use full width */
        .d-flex.flex-column.min-vh-100 {
            width: 100%;
            max-width: 100%;
            overflow-x: visible !important; /* Allow content to use full width */
        }
        
        .content-wrapper {
            width: 100%;
            max-width: 100%;
            overflow-x: visible; /* Allow content to expand properly */
        }
        
        /* Ensure container-fluid uses full width */
        .container-fluid {
            width: 100%;
            max-width: 100%;
        }
    </style>
    
    <!-- Dropdown arrow and navigation styling -->
    <style>
        /* Hide Bootstrap's default dropdown arrow completely */
        .navbar .nav-item.dropdown .dropdown-toggle::after,
        .navbar .nav-item.dropdown .dropdown-toggle.active::after,
        .navbar .nav-item.dropdown .dropdown-toggle[aria-expanded="true"]::after,
        .navbar .nav-link.dropdown-toggle::after,
        .navbar .nav-link.dropdown-toggle.active::after {
            display: none !important; 
            content: none !important;
            border: none !important;
        }

        /* Style for our custom dropdown arrow */
        .nav-dropdown-icon {
            margin-left: 4px;
            margin-right: 0;
            opacity: 0.8;
            transition: transform 0.2s;
        }
        
        /* Only rotate arrow when dropdown is actually open, not just when the nav item is active */
        .dropdown-toggle[aria-expanded="true"] .nav-dropdown-icon {
            transform: rotate(180deg);
        }

        /* Ensure dropdown works correctly */
        .dropdown-menu {
            margin-top: 0.125rem;
            z-index: 1000;
        }
    </style>
    
    <!-- Font-related styling -->
    <style>
        /* Apply Poppins to all elements */
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
    </style>

    <!-- Main JS - Restored inclusion -->
    <script src="<?php echo asset_url('js', 'main.js'); ?>"></script>

</head>
<body class="<?php echo isset($bodyClass) ? htmlspecialchars($bodyClass) : ''; ?><?php if (strpos($_SERVER['REQUEST_URI'], '/app/views/agency/') !== false) echo ' agency-layout'; ?><?php if (strpos($_SERVER['REQUEST_URI'], '/app/views/admin/') !== false) echo ' admin-layout'; ?>">
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>
    
    <!-- Main content wrapper - removed overflow-hidden -->
    <div class="d-flex flex-column min-vh-100">
        <!-- Content container will be inserted by specific view files -->        <div class="content-wrapper<?php if (strpos($_SERVER['REQUEST_URI'], '/app/views/agency/') !== false) echo ' agency-content'; ?><?php if (strpos($_SERVER['REQUEST_URI'], '/app/views/admin/') !== false) echo ' admin-content'; ?>">
            <!-- Wrap agency navigation and header properly -->
            <?php if (strpos($_SERVER['REQUEST_URI'], '/app/views/agency/') !== false): ?>
            <div class="agency-header-wrapper">
                <?php require_once 'agency_nav.php'; ?>
                <!-- Remove or comment out the empty .page-header div to prevent the large green bar -->
                <!-- <div class="page-header">
                    <!-- Page header content -->
                <!-- </div> -->
            </div>
            <?php endif; ?>
            
            <!-- Wrap admin navigation and header properly -->
            <?php if (strpos($_SERVER['REQUEST_URI'], '/app/views/admin/') !== false): ?>
            <div class="admin-header-wrapper">
                <?php require_once 'admin_nav.php'; ?>
            </div>
            <?php endif; ?>
