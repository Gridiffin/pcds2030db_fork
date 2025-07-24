<?php
/**
 * Program Details Content Partial
 * Main content for the program details page
 */
?>
<!-- Main Content -->
<main class="flex-fill">
<div class="container-fluid">
    <!-- Toast Notifications for Alerts -->
    <?php if ($alert_flags['show_draft_alert']): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for global functions to be available
            function waitForToastFunctions() {
                <?php if ($can_edit): ?>
                if (typeof window.showToastWithAction === 'function') {
                    showToastWithAction('Draft Submission', 'This program is in draft mode.', 'warning', 10000, {
                        text: 'Edit & Submit',
                        url: '<?= APP_URL ?>/app/views/agency/programs/edit_program.php?id=<?= $program['program_id'] ?>'
                    });
                } else {
                    setTimeout(waitForToastFunctions, 100);
                }
                <?php else: ?>
                if (typeof window.showToast === 'function') {
                    showToast('Draft Submission', 'This program is in draft mode and pending final submission.', 'warning', 8000);
                } else {
                    setTimeout(waitForToastFunctions, 100);
                }
                <?php endif; ?>
            }
            waitForToastFunctions();
        });
    </script>
    <?php endif; ?>

    <?php if ($alert_flags['show_finalized_alert']): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for global functions to be available
            function waitForToastFunctions() {
                if (typeof window.showToast === 'function') {
                    showToast('Finalized', 'This program\'s latest progress report is finalized.', 'success', 8000);
                } else {
                    setTimeout(waitForToastFunctions, 100);
                }
            }
            waitForToastFunctions();
        });
    </script>
    <?php endif; ?>

    <!-- Error/Success Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Wait for global functions to be available
                function waitForToastFunctions() {
                    if (typeof window.showToast === 'function') {
                        showToast('<?= ucfirst($_SESSION['message_type']) ?>', '<?= $_SESSION['message'] ?>', '<?= $_SESSION['message_type'] ?>');
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
            <?php require_once __DIR__ . '/program_overview.php'; ?>
            
            <!-- Quick Actions Section (replaces targets) -->
            <?php if ($can_edit): ?>
                <?php require_once __DIR__ . '/program_actions.php'; ?>
            <?php else: ?>
                <!-- Read-only Actions Notice -->
                <div class="card read-only-actions-notice shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Program Actions
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="text-muted">
                            <i class="fas fa-lock fa-2x mb-3 opacity-50"></i>
                            <h6>Read-Only Access</h6>
                            <p class="mb-0">You are viewing this program in read-only mode. Only the program's owning agency can perform actions.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php require_once __DIR__ . '/program_timeline.php'; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <?php require_once __DIR__ . '/program_sidebar.php'; ?>
        </div>
    </div>

</div>

<!-- Modals -->
<?php require_once __DIR__ . '/program_modals.php'; ?>

<!-- JavaScript Variables for Enhanced Functionality -->
<script>
    window.programId = <?php echo json_encode($program['program_id']); ?>;
    window.isOwner = <?php echo json_encode($is_owner); ?>;
    window.canEdit = <?php echo json_encode($can_edit); ?>;
    window.APP_URL = <?php echo json_encode(APP_URL); ?>;
</script>
</main>