<main class="flex-fill">
<!-- Toast Notification for Program Creation/Deletion -->
<?php if (!empty($message)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('<?= ucfirst($messageType) ?>', <?= json_encode($message) ?>, '<?= $messageType ?>');
        });
    </script>
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
