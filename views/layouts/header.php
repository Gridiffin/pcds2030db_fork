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
    
    <!-- Base Styles -->
    <link href="<?php echo APP_URL; ?>/assets/css/variables.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/base.css" rel="stylesheet">
    
    <!-- Shared Components -->
    <link href="<?php echo APP_URL; ?>/assets/css/custom/shared/components.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/custom/shared/global.css" rel="stylesheet">
    
    <!-- Additional styles from page -->
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
</head>
<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
    </div>
    
    <!-- Main content wrapper -->
    <div class="d-flex flex-column min-vh-100">
        <!-- Content container will be inserted by specific view files -->