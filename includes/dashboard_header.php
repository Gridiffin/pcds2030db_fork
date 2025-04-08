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

// Use dark as default background (which means primary-color background)
$background = $background ?? 'dark';
?>

<!-- Consistent styling with admin dashboard for all pages using this component -->
<div class="page-header pb-10" style="padding-top: 40px; margin-bottom: 2rem; background-color: var(--primary-color); color: white;">
    <div class="container-fluid">
        <div class="row align-items-center">
            <?php if (!empty($breadcrumbs)): ?>
            <div class="col-12 mb-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php" class="text-white">Home</a></li>
                        <?php foreach ($breadcrumbs as $index => $crumb): ?>
                            <?php if ($index === count($breadcrumbs) - 1): ?>
                                <li class="breadcrumb-item active text-white-50" aria-current="page"><?php echo $crumb['text']; ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?php echo $crumb['url']; ?>" class="text-white"><?php echo $crumb['text']; ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            </div>
            <?php endif; ?>
            
            <div class="col">
                <div class="page-title">
                    <h3 style="color: white;"><?php echo htmlspecialchars($title); ?></h3>
                    <?php if (isset($subtitle) && !empty($subtitle)): ?>
                        <p class="text-subtitle text-white">
                            <?php echo htmlspecialchars($subtitle); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isset($actions) && !empty($actions)): ?>
                <div class="col-auto">
                    <?php foreach ($actions as $action): ?>
                        <button <?php if(isset($action['id'])): ?>id="<?php echo $action['id']; ?>"<?php endif; ?> 
                           class="btn <?php echo $action['class'] ?? 'btn-primary'; ?> <?php echo $action['size'] ?? ''; ?>">
                            <?php if (isset($action['icon'])): ?>
                                <i class="<?php echo $action['icon']; ?> me-1"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($action['text']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
