<?php
/**
 * Program Details Toast Notifications
 * 
 * Contains JavaScript for showing toast notifications based on program state.
 */
?>

<!-- Toast Notifications -->
<?php if ($showDraftAlert): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($can_edit): ?>
        showToastWithAction('Draft Submission', 'This program is in draft mode.', 'warning', 10000, {
            text: 'Edit & Submit',
            url: '<?= APP_URL ?>/app/views/agency/programs/edit_program.php?id=<?= $program_id ?>'
        });
        <?php else: ?>
        showToast('Draft Submission', 'This program is in draft mode and pending final submission.', 'warning', 8000);
        <?php endif; ?>
    });
</script>
<?php endif; ?>

<?php if ($has_submissions && !$is_draft): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('Finalized', 'This program\'s latest progress report is finalized.', 'success', 8000);
    });
</script>
<?php endif; ?>
