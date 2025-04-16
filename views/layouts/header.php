<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>PCDS 2030 Dashboard</title>
    
    <!-- Preconnect to Google Fonts for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts - Nunito with all needed weights -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo APP_URL; ?>/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Main CSS (imports all component and layout CSS) -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/main.css">
    
    <!-- Simple header CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/simple-header.css">
    
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
</head>
<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>
    
    <!-- Main content wrapper - removed overflow-hidden -->
    <div class="d-flex flex-column min-vh-100">
        <!-- Content container will be inserted by specific view files -->