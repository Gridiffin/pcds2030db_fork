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
 * - $headerStyle: 'primary' or 'light' (default 'primary')
 * - $breadcrumbs: Array of breadcrumb items (optional)
 */

// Default values
$title = $title ?? ($pageTitle ?? 'Dashboard');
$subtitle = $subtitle ?? '';
$actions = $actions ?? [];
$breadcrumbs = $breadcrumbs ?? [];

// Header style: 'primary' (blue) or 'light' (white)
$headerStyle = $headerStyle ?? 'primary';
$headerClass = ($headerStyle === 'light') ? 'page-header-light' : 'page-header-primary';
?>

<!-- Standardized page header using CSS classes -->
<div class="<?php echo $headerClass; ?>">
    <div class="container-fluid p-0">
        <?php if (!empty($breadcrumbs)): ?>
        <div class="row mb-2">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php" class="<?php echo $headerStyle === 'light' ? 'text-primary' : 'text-white'; ?>">Home</a></li>
                        <?php foreach ($breadcrumbs as $index => $crumb): ?>
                            <?php if ($index === count($breadcrumbs) - 1): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $crumb['text']; ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?php echo $crumb['url']; ?>" class="<?php echo $headerStyle === 'light' ? 'text-primary' : 'text-white'; ?>"><?php echo $crumb['text']; ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="row align-items-center">
            <div class="col">
                <div class="page-title">
                    <h3><?php echo htmlspecialchars($title); ?></h3>
                    <?php if (isset($subtitle) && !empty($subtitle)): ?>
                        <p><?php echo htmlspecialchars($subtitle); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isset($actions) && !empty($actions)): ?>
                <div class="col-auto">
                    <?php foreach ($actions as $action): ?>
                        <?php if (isset($action['url'])): ?>
                            <!-- Render as anchor tag if URL is provided -->
                            <a href="<?php echo $action['url']; ?>" 
                                <?php if(isset($action['id'])): ?>id="<?php echo $action['id']; ?>"<?php endif; ?> 
                                class="btn <?php echo $action['class'] ?? ($headerStyle === 'light' ? 'btn-primary' : 'btn-light'); ?> <?php echo $action['size'] ?? ''; ?>">
                                <?php if (isset($action['icon'])): ?>
                                    <i class="<?php echo $action['icon']; ?> me-1"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($action['text']); ?>
                            </a>
                        <?php else: ?>
                            <!-- Render as button if no URL -->
                            <button <?php if(isset($action['id'])): ?>id="<?php echo $action['id']; ?>"<?php endif; ?> 
                                class="btn <?php echo $action['class'] ?? ($headerStyle === 'light' ? 'btn-primary' : 'btn-light'); ?> <?php echo $action['size'] ?? ''; ?>">
                                <?php if (isset($action['icon'])): ?>
                                    <i class="<?php echo $action['icon']; ?> me-1"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($action['text']); ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
