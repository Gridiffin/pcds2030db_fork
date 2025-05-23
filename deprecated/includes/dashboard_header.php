<?php
/**
 * Simple Dashboard Header Component
 */

// Default values
$title = $title ?? ($pageTitle ?? 'Dashboard');
$subtitle = $subtitle ?? '';
$actions = $actions ?? [];
$headerStyle = $headerStyle ?? 'primary';
$headerClass = $headerClass ?? ''; // Added support for additional header classes
?>

<!-- Simple full-width header -->
<div class="simple-header <?php echo $headerStyle; ?> <?php echo $headerClass; ?>">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="header-title"><?php echo htmlspecialchars($title); ?></h3>
                <?php if (!empty($subtitle)): ?>
                    <p class="header-subtitle"><?php echo htmlspecialchars($subtitle); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($actions)): ?>
                <div class="col-auto">
                    <?php foreach ($actions as $action): ?>
                        <?php if (isset($action['html'])): ?>
                            <?php echo $action['html']; ?>
                        <?php else: ?>
                            <a href="<?php echo $action['url'] ?? '#'; ?>" 
                                <?php if(isset($action['id'])): ?>id="<?php echo $action['id']; ?>"<?php endif; ?> 
                                class="btn <?php echo $action['class'] ?? 'btn-light border border-primary text-primary'; ?>">
                                <?php if (isset($action['icon'])): ?>
                                    <i class="fas <?php echo $action['icon']; ?> me-1"></i>
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars($action['text'] ?? ''); ?></span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
