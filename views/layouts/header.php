<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>PCDS 2030 Dashboard</title>
    
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
        /* Override any potential container margins/paddings that might affect header width */
        body {
            overflow-x: hidden; /* Prevent horizontal scrolling */
        }
        
        .content-wrapper {
            overflow-x: hidden; /* Ensure no horizontal scrollbars */
            max-width: 100%;
            width: 100%;
            padding-left: 0;
            padding-right: 0;
        }
    </style>
</head>
<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>
    
    <!-- Main content wrapper -->
    <div class="d-flex flex-column min-vh-100 overflow-hidden">
        <!-- Content container will be inserted by specific view files -->