<?php
/**
 * Dashboard Header Component
 * 
 * Provides a consistent header for dashboard pages with proper text contrast
 * 
 * Variables:
 * - $title: Main title text
 * - $subtitle: Subtitle text (optional)
 * - $actions: Array of action buttons (optional)
 * - $background: 'dark' or 'light' (default 'dark')
 */

// Default values
$title = $title ?? ($pageTitle ?? 'Dashboard');
$subtitle = $subtitle ?? '';
$actions = $actions ?? [];
$breadcrumbs = $breadcrumbs ?? [];

// Use dark as default background 
$background = $background ?? 'dark';
$background_class = $background === 'light' ? 'light-background' : '';
$text_class = $background === 'light' ? 'text-on-light' : 'text-on-dark';
?>

<div class="page-header <?php echo $background_class; ?>">
    <div class="container-fluid px-4">
        <div class="row align-items-center">
            <?php if (!empty($breadcrumbs)): ?>
            <div class="col-12 mb-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php">Home</a></li>
                        <?php foreach ($breadcrumbs as $index => $crumb): ?>
                            <?php if ($index === count($breadcrumbs) - 1): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $crumb['text']; ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['text']; ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            </div>
            <?php endif; ?>
            
            <div class="col-md-6 page-title">
                <h3><?php echo htmlspecialchars($title); ?></h3>
                <?php if (isset($subtitle) && !empty($subtitle)): ?>
                    <p class="text-subtitle <?php echo $text_class; ?>">
                        <?php echo htmlspecialchars($subtitle); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <?php if (isset($actions) && !empty($actions)): ?>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <?php foreach ($actions as $action): ?>
                        <a href="<?php echo $action['url']; ?>" 
                           class="btn <?php echo $action['class'] ?? 'btn-primary'; ?> <?php echo $action['size'] ?? ''; ?>">
                            <?php if (isset($action['icon'])): ?>
                                <i class="<?php echo $action['icon']; ?> me-1"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($action['text']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
