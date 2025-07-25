<?php
/**
 * View Submissions Content Partial
 * Main content for the view submissions page
 */
?>
<!-- Main Content -->
<main>
<div class="container-fluid">
    <!-- Error/Success Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Wait for global functions to be available
                function waitForToastFunctions() {
                    if (typeof window.showToast === 'function') {
                        showToast('<?= ucfirst($_SESSION['message_type']) ?>', <?= json_encode($_SESSION['message']) ?>, '<?= $_SESSION['message_type'] ?>');
                    } else {
                        setTimeout(waitForToastFunctions, 100);
                    }
                }
                waitForToastFunctions();
            });
        </script>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <?php require_once __DIR__ . '/submission_overview.php'; ?>
            <?php require_once __DIR__ . '/submission_targets.php'; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <?php require_once __DIR__ . '/submission_sidebar.php'; ?>
        </div>
    </div>
</div>
</main>