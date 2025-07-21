<?php
/**
 * View Reports Content
 * Main content for the view reports page
 */

// Configure modern page header
$header_config = [
    'title' => 'View Reports',
    'subtitle' => 'Access and download reports for your programs and sector',
    'variant' => 'green',
    'actions' => []
];

// Include modern page header
require_once PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php';
?>

<!-- Reports Content -->
<section class="section reports-container">
    <div class="container-fluid">
        <div class="reports-content">
            
            <!-- Filter Section -->
            <?php require_once __DIR__ . '/reports_filter.php'; ?>
            
            <!-- Reports List Section -->
            <?php require_once __DIR__ . '/reports_list.php'; ?>
            
            <!-- Reports Info Section -->
            <?php require_once __DIR__ . '/reports_info.php'; ?>
            
        </div>
    </div>
</section>

<?php if (!empty($infoMessage)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.showToast === 'function') {
            window.showToast('Info', <?= json_encode($infoMessage) ?>, 'info');
        }
    });
</script>
<?php endif; ?>
