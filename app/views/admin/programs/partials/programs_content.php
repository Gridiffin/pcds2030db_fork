<main class="flex-fill">
    <!-- Error/Success Messages -->
    <?php if (!empty($message)): ?>
        <?php
        // Check if this is a notification-related message that should not be shown as a toast
        $notification_keywords = ['New program', 'created by', 'System Administrator', 'notification'];
        $is_notification_message = false;
        foreach ($notification_keywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                $is_notification_message = true;
                break;
            }
        }
        
        // Only show toast if it's not a notification-related message
        if (!$is_notification_message):
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?= ucfirst($messageType) ?>', <?= json_encode($message) ?>, '<?= $messageType ?>');
            });
        </script>
        <?php endif; ?>
    <?php endif; ?>

<?php
// Include finalized programs modern box layout for admin view
require_once __DIR__ . '/_finalized_programs_modern.php';
require_once __DIR__ . '/_modals.php';
?>

<script>
// Pass necessary PHP variables to JavaScript.
window.currentUserRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
window.currentPeriodId = '<?php echo $period_id ?? ''; ?>';
</script>
</main>
