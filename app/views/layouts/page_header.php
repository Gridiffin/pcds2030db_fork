<?php
/**
 * Modern Page Header Component
 * Simplified header system with title, subtitle, and breadcrumb
 * 
 * Usage:
 * $header_config = [
 *     'title' => 'Page Title',              // Required: Main page title
 *     'subtitle' => 'Optional subtitle',    // Optional: Secondary text below title
 *     'breadcrumb' => [                     // Optional: Breadcrumb navigation items
 *         [
 *             'text' => 'Home',             // Can also use 'label' for backward compatibility
 *             'url' => '/index.php'         // Can also use 'link' for backward compatibility
 *         ],
 *         [
 *             'text' => 'Current Page',
 *             'url' => null                 // No URL for current page
 *         ]
 *     ],
 *     'classes' => '',                      // Optional: Additional CSS classes
 *     'showBreadcrumb' => true,            // Optional: Whether to show breadcrumb (default: true)
 *     'showSubtitle' => true,              // Optional: Whether to show subtitle (default: true)
 *     'titleTag' => 'h1',                  // Optional: HTML tag for title (default: h1)
 *     'theme' => 'light'                   // Optional: Theme variant (light, dark, primary, secondary)
 * ];
 * require_once 'path/to/page_header.php';
 * 
 * Backward Compatibility:
 * - Supports legacy $headerTitle and $headerSubtitle variables
 * - Supports legacy $breadcrumbItems array format
 */

// Default configuration
$header_config = $header_config ?? [];

// Extract configuration with defaults
$title = $header_config['title'] ?? ($pageTitle ?? 'Page Title');
$subtitle = $header_config['subtitle'] ?? '';

// Handle breadcrumb configuration with backward compatibility
$breadcrumb = [];
if (isset($header_config['breadcrumb'])) {
    $breadcrumb = $header_config['breadcrumb'];
} elseif (isset($breadcrumbItems)) {
    // Legacy breadcrumb format support
    $breadcrumb = array_map(function($item) {
        return [
            'text' => $item['text'] ?? $item['label'] ?? '',
            'url' => $item['url'] ?? $item['link'] ?? null
        ];
    }, $breadcrumbItems);
}

// Additional CSS classes
$classes = $header_config['classes'] ?? '';

// Support for legacy header configuration
if (empty($title) && isset($headerTitle)) {
    $title = $headerTitle;
}
if (empty($subtitle) && isset($headerSubtitle)) {
    $subtitle = $headerSubtitle;
}

// Additional configuration options with defaults
$showBreadcrumb = $header_config['showBreadcrumb'] ?? true;
$showSubtitle = $header_config['showSubtitle'] ?? true;
$titleTag = $header_config['titleTag'] ?? 'h1';
$subtitleHtml = $header_config['subtitle_html'] ?? false;

// Build CSS classes
$header_classes = ['page-header'];
if (!empty($classes)) {
    $header_classes[] = $classes;
}

// Add theme class if specified
if (isset($header_config['theme']) && in_array($header_config['theme'], ['light', 'dark', 'primary', 'secondary'])) {
    $header_classes[] = 'page-header--' . $header_config['theme'];
}
?>

<header class="<?php echo htmlspecialchars(implode(' ', $header_classes)); ?>">
    <div class="container">
        <div class="row">
            <!-- Breadcrumb (Left-aligned) -->
            <?php if (!empty($breadcrumb) && $showBreadcrumb): ?>
            <div class="col-12">
                <nav aria-label="breadcrumb" class="page-header__breadcrumb">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumb as $index => $item): ?>
                            <?php 
                            // Validate breadcrumb item
                            if (!isset($item['text']) && !isset($item['label'])) {
                                continue; // Skip invalid items
                            }
                            
                            // Get text from either 'text' or 'label' key (backward compatibility)
                            $itemText = $item['text'] ?? $item['label'] ?? '';
                            
                            // Get URL from either 'url' or 'link' key (backward compatibility)
                            $itemUrl = $item['url'] ?? $item['link'] ?? null;
                            
                            // Determine if this is the last item
                            $isLast = $index === count($breadcrumb) - 1;
                            ?>
                            <?php if ($index > 0): ?>
                                <li class="breadcrumb-separator" aria-hidden="true">/</li>
                            <?php endif; ?>
                            <li class="breadcrumb-item<?php echo $isLast ? ' active' : ''; ?>"<?php echo $isLast ? ' aria-current="page"' : ''; ?>>
                                <?php if (!$isLast && !empty($itemUrl)): ?>
                                    <a href="<?php echo htmlspecialchars($itemUrl); ?>"><?php echo htmlspecialchars($itemText); ?></a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($itemText); ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            </div>
            <?php endif; ?>
            
            <!-- Title, Subtitle and Actions -->
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <!-- Title and Subtitle (Left side) -->
                    <div class="page-header__content">
                        <<?php echo $titleTag; ?> class="page-header__title"><?php echo htmlspecialchars($title); ?></<?php echo $titleTag; ?>>
                        <?php if (!empty($subtitle) && $showSubtitle): ?>
                            <p class="page-header__subtitle">
                                <?php
                                if (!empty($header_config['subtitle_html']) || $subtitleHtml) {
                                    echo $subtitle; // Output raw HTML
                                } else {
                                    echo htmlspecialchars($subtitle);
                                }
                                ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Actions (Right side) -->
                    <?php if (!empty($header_config['actions'])): ?>
                    <div class="page-header__actions d-flex align-items-center gap-2 flex-wrap">
                        <?php foreach ($header_config['actions'] as $action): ?>
                            <?php if (isset($action['html'])): ?>
                                <!-- Custom HTML action (e.g., badges) -->
                                <?php echo $action['html']; ?>
                            <?php elseif (isset($action['text']) || isset($action['icon'])): ?>
                                <!-- Button action -->
                                <?php
                                $actionUrl = $action['url'] ?? '#';
                                $actionClass = 'btn ' . ($action['class'] ?? 'btn-primary');
                                $actionId = isset($action['id']) ? ' id="' . htmlspecialchars($action['id']) . '"' : '';
                                $actionText = $action['text'] ?? '';
                                $actionIcon = $action['icon'] ?? '';
                                $actionOnclick = isset($action['onclick']) ? ' onclick="' . htmlspecialchars($action['onclick']) . '"' : '';
                                $actionTarget = isset($action['target']) ? ' target="' . htmlspecialchars($action['target']) . '"' : '';
                                ?>
                                <a href="<?php echo htmlspecialchars($actionUrl); ?>" 
                                   class="<?php echo htmlspecialchars($actionClass); ?>"<?php echo $actionId; ?><?php echo $actionOnclick; ?><?php echo $actionTarget; ?>>
                                    <?php if ($actionIcon): ?>
                                        <i class="<?php echo htmlspecialchars($actionIcon); ?><?php echo $actionText ? ' me-2' : ''; ?>"></i>
                                    <?php endif; ?>
                                    <?php if ($actionText): ?>
                                        <?php echo htmlspecialchars($actionText); ?>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>

<?php
// Clear header config to prevent conflicts
unset(
    $header_config, 
    $title, 
    $subtitle, 
    $breadcrumb, 
    $classes, 
    $header_classes, 
    $showBreadcrumb, 
    $showSubtitle, 
    $titleTag,
    $subtitleHtml
);
?>
