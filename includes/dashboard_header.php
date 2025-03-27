<?php
/**
 * Dashboard Header Component
 * 
 * Provides a consistent header for dashboard pages with customizable elements.
 * 
 * @param string $title The main title to display
 * @param string $subtitle Optional subtitle to display
 * @param array $actions Optional array of action buttons to display
 * @param array $breadcrumbs Optional breadcrumbs array with 'text' and 'url' keys
 */

// Default values
$title = $title ?? ($pageTitle ?? 'Dashboard');
$subtitle = $subtitle ?? '';
$actions = $actions ?? [];
$breadcrumbs = $breadcrumbs ?? [];
?>

<div class="dashboard-header mb-4">
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
        
        <div class="col">
            <div class="dashboard-title">
                <h1 class="h2 mb-0"><?php echo $title; ?></h1>
                <?php if (!empty($subtitle)): ?>
                    <p class="text-muted mb-0"><?php echo $subtitle; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($actions)): ?>
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <?php foreach ($actions as $action): ?>
                        <?php if (isset($action['url'])): ?>
                            <a href="<?php echo $action['url']; ?>" class="btn <?php echo $action['class'] ?? 'btn-primary'; ?>">
                                <?php if (isset($action['icon'])): ?>
                                    <i class="<?php echo $action['icon']; ?> me-1"></i>
                                <?php endif; ?>
                                <?php echo $action['text']; ?>
                            </a>
                        <?php else: ?>
                            <button type="button" id="<?php echo $action['id'] ?? ''; ?>" class="btn <?php echo $action['class'] ?? 'btn-primary'; ?>"
                                <?php if (isset($action['data'])): foreach ($action['data'] as $key => $value): ?>
                                    data-<?php echo $key; ?>="<?php echo htmlspecialchars($value); ?>"
                                <?php endforeach; endif; ?>>
                                <?php if (isset($action['icon'])): ?>
                                    <i class="<?php echo $action['icon']; ?> me-1"></i>
                                <?php endif; ?>
                                <?php echo $action['text']; ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
