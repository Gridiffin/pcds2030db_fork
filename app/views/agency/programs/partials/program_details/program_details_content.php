<?php
/**
 * Program Details Content - Main Layout
 * 
 * This file contains the main structure for the program details page.
 * It includes all the modular partials that make up the complete view.
 */
?>

<!-- Include all modals first -->
<?php require_once __DIR__ . '/modals.php'; ?>

<!-- Include toast notifications -->
<?php require_once __DIR__ . '/toast_notifications.php'; ?>

<!-- Enhanced Program Overview -->
<div class="container-fluid">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Program Information Card -->
            <?php require_once __DIR__ . '/program_info_card.php'; ?>

            <!-- Hold Point Management Table (Read-only) -->
            <?php require_once __DIR__ . '/hold_point_history.php'; ?>

            <!-- Quick Actions Section -->
            <?php require_once __DIR__ . '/quick_actions.php'; ?>

            <!-- Submission Timeline -->
            <?php require_once __DIR__ . '/submission_timeline.php'; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Program Statistics -->
            <?php require_once __DIR__ . '/sidebar_stats.php'; ?>

            <!-- Program Attachments -->
            <?php require_once __DIR__ . '/sidebar_attachments.php'; ?>

            <!-- Related Programs -->
            <?php require_once __DIR__ . '/sidebar_related.php'; ?>
        </div>
    </div>
</div>

<?php if (!$is_owner): ?>
<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Note:</strong> You are viewing this program in read-only mode. Only the program's owning agency can submit updates.
</div>
<?php endif; ?>

<!-- JavaScript Configuration -->
<script>
// Pass PHP variables to JavaScript
window.currentUser = {
    id: <?php echo $_SESSION['user_id'] ?? 'null'; ?>,
    agency_id: <?php echo $_SESSION['agency_id'] ?? 'null'; ?>,
    role: '<?php echo $_SESSION['role'] ?? ''; ?>'
};
window.isOwner = <?php echo $is_owner ? 'true' : 'false'; ?>;
window.canEdit = <?php echo $can_edit ? 'true' : 'false'; ?>;
window.programId = <?php echo $program_id; ?>;
window.APP_URL = '<?php echo APP_URL; ?>';
</script>
