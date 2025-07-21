<?php
/**
 * View Submissions Content - Main wrapper for all content
 * Uses base.php layout pattern for consistent structure
 */
?>

<main class="flex-fill">
    <div class="view-submissions-content">
        <div class="container-fluid py-4">
            <!-- Error/Success Messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            <?php endif; ?>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <?php require_once __DIR__ . '/submission_overview.php'; ?>
                    <?php require_once __DIR__ . '/targets_section.php'; ?>
                    <?php require_once __DIR__ . '/attachments_section.php'; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <?php require_once __DIR__ . '/program_summary_sidebar.php'; ?>
                    <?php require_once __DIR__ . '/period_info_sidebar.php'; ?>
                    <?php require_once __DIR__ . '/quick_actions_sidebar.php'; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- JavaScript configuration - Anti-pattern fix (Bug #27) -->
<script>
// Pass PHP variables to JavaScript for use in ES6 modules
window.programId = <?php echo $program_id; ?>;
window.periodId = <?php echo $period_id; ?>;
window.submissionId = <?php echo $submission['submission_id']; ?>;
window.APP_URL = '<?php echo APP_URL; ?>';
window.canEdit = <?php echo $can_edit ? 'true' : 'false'; ?>;
window.isOwner = <?php echo $is_owner ? 'true' : 'false'; ?>;
</script>
