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
    
    <!-- Preload critical fonts to avoid FOUT (Flash of Unstyled Text) -->
    <link rel="preload" href="https://fonts.gstatic.com/s/poppins/v20/pxiEyp8kv8JHgFVrJJfecg.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="https://fonts.gstatic.com/s/poppins/v20/pxiByp8kv8JHgFVrLEj6Z1xlFQ.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="https://fonts.gstatic.com/s/poppins/v20/pxiByp8kv8JHgFVrLCz7Z1xlFQ.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo asset_url('fonts/fontawesome', 'fa-solid-900.woff2'); ?>" as="font" type="font/woff2" crossorigin>
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
        /* Ensure Nunito font is applied globally with proper fallbacks */
        html, body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }
        
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
    
    <!-- Local fonts fallback CSS to avoid failed CDN downloads -->
    <style>
        /* Nunito font local fallback */
        @font-face {
            font-family: 'Nunito';
            font-style: normal;
            font-weight: 400;
            src: local('Nunito Regular'), local('Nunito-Regular'),
                 url('<?php echo APP_URL; ?>/assets/fonts/nunito/nunito-v26-latin-regular.woff2') format('woff2');
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Nunito';
            font-style: normal;
            font-weight: 500;
            src: local('Nunito Medium'), local('Nunito-Medium'),
                 url('<?php echo APP_URL; ?>/assets/fonts/nunito/nunito-v26-latin-500.woff2') format('woff2');
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Nunito';
            font-style: normal;
            font-weight: 600;
            src: local('Nunito SemiBold'), local('Nunito-SemiBold'),
                 url('<?php echo APP_URL; ?>/assets/fonts/nunito/nunito-v26-latin-600.woff2') format('woff2');
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Nunito';
            font-style: normal;
            font-weight: 700;
            src: local('Nunito Bold'), local('Nunito-Bold'),
                 url('<?php echo APP_URL; ?>/assets/fonts/nunito/nunito-v26-latin-700.woff2') format('woff2');
            font-display: swap;
        }
        
        /* Font Awesome local fallback */
        @font-face {
            font-family: 'Font Awesome 5 Free';
            font-style: normal;
            font-weight: 900;
            src: local('Font Awesome 5 Free Solid'), local('FontAwesome5Free-Solid'),
                 url('<?php echo APP_URL; ?>/assets/fonts/fontawesome/fa-solid-900.woff2') format('woff2');
            font-display: swap;
        }
    </style>

    <!-- Main JS - Restored inclusion -->
    <script src="<?php echo asset_url('js', 'main.js'); ?>"></script>

</head>
<body class="<?php echo isset($bodyClass) ? htmlspecialchars($bodyClass) : ''; ?>">
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>
    
    <!-- Main content wrapper - removed overflow-hidden -->
    <div class="d-flex flex-column min-vh-100">
        <!-- Content container will be inserted by specific view files -->
        <div class="content-wrapper">
            <!-- Wrap agency navigation and header properly -->
            <div class="agency-header-wrapper">
                <?php require_once 'agency_nav.php'; ?>
                <!-- Remove or comment out the empty .page-header div to prevent the large green bar -->
                <!-- <div class="page-header">
                    <!-- Page header content -->
                <!-- </div> -->
            </div>
            <!-- Toast notification container (for all pages) -->
            <div id="toast-container" class="toast-container"></div>