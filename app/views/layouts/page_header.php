<?php
/**
 * Modern Page Header Component
 * Unified header system for all admin pages
 * 
 * Usage:
 * $header_config = [
 *     'title' => 'Page Title',
 *     'subtitle' => 'Optional subtitle',
 *     'variant' => 'blue', // 'blue' or 'white'
 *     'actions' => [
 *         [
 *             'text' => 'Button Text',
 *             'url' => '#',
 *             'class' => 'btn-light',
 *             'icon' => 'fas fa-plus'
 *         ]
 *     ]
 * ];
 * require_once 'path/to/page_header.php';
 */

// Default configuration
$header_config = $header_config ?? [];

// Extract configuration with defaults
$title = $header_config['title'] ?? ($pageTitle ?? 'Page Title');
$subtitle = $header_config['subtitle'] ?? '';
$variant = $header_config['variant'] ?? 'white';
$actions = $header_config['actions'] ?? [];
$classes = $header_config['classes'] ?? '';
$compact = $header_config['compact'] ?? false;

// Validate variant
$variant = in_array($variant, ['green', 'white', 'blue']) ? $variant : 'white';

// Build CSS classes
$header_classes = ['page-header', "page-header--{$variant}"];
if ($compact) {
    $header_classes[] = 'page-header--compact';
}
if (!empty($classes)) {
    $header_classes[] = $classes;
}
?>

<!-- Modern Page Header -->
<header class="<?php echo implode(' ', $header_classes); ?>" role="banner">
    <div class="page-header__container">
        <div class="page-header__content">
            <div class="page-header__text">
                <h1 class="page-header__title"><?php echo htmlspecialchars($title); ?></h1>
                <?php if (!empty($subtitle)): ?>
                    <p class="page-header__subtitle"><?php echo htmlspecialchars($subtitle); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($actions)): ?>
                <div class="page-header__actions">
                    <?php foreach ($actions as $action): ?>
                        <?php if (isset($action['html'])): ?>
                            <!-- Custom HTML action -->
                            <?php echo $action['html']; ?>
                        <?php else: ?>
                            <!-- Standard button action -->
                            <?php
                            $url = $action['url'] ?? '#';
                            $text = $action['text'] ?? 'Button';
                            $icon = $action['icon'] ?? '';
                            $class = $action['class'] ?? (in_array($variant, ['green', 'blue']) ? 'btn-light' : 'btn-primary');
                            $id = $action['id'] ?? '';
                            $target = $action['target'] ?? '';
                            $onclick = $action['onclick'] ?? '';
                            ?>
                            <a href="<?php echo htmlspecialchars($url); ?>" 
                               <?php if ($id): ?>id="<?php echo htmlspecialchars($id); ?>"<?php endif; ?>
                               <?php if ($target): ?>target="<?php echo htmlspecialchars($target); ?>"<?php endif; ?>
                               <?php if ($onclick): ?>onclick="<?php echo htmlspecialchars($onclick); ?>"<?php endif; ?>
                               class="btn <?php echo htmlspecialchars($class); ?>">
                                <?php if ($icon): ?>
                                    <i class="<?php echo htmlspecialchars($icon); ?> me-1"></i>
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars($text); ?></span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php
// Clear header config to prevent conflicts
unset($header_config, $title, $subtitle, $variant, $actions, $classes, $compact, $header_classes);
?>
