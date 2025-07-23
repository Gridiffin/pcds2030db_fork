<?php
/**
 * Admin View: Programs
 *
 * This file is the main view for the admin programs page.
 * It ensures the controller has run to provide data, then includes the necessary partials.
 */

// Define the project root path correctly by navigating up from the current file's directory.
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include the main config file which defines global constants like APP_URL.
require_once PROJECT_ROOT_PATH . 'app/config/config.php';

// Ensure the controller has run and prepared the data. If not, load it.
if (!isset($programs_with_drafts)) {
    require_once PROJECT_ROOT_PATH . 'app/controllers/AdminProgramsController.php';
}

// Additional scripts for this page.
$additionalScripts = [
    APP_URL . '/assets/js/admin/programs/programs.js',
];

// Include header.
require_once PROJECT_ROOT_PATH . 'app/views/layouts/header.php';

// Configure modern page header.
$header_config = [
    'title' => $pageTitle ?? 'Admin Programs',
    'subtitle' => 'View and manage programs across all agencies',
    'variant' => 'blue'
];

// Include modern page header.
require_once PROJECT_ROOT_PATH . 'app/views/layouts/page_header.php';
?>

<!-- Toast Notification for Program Creation/Deletion -->
<?php if (!empty($message)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('<?= ucfirst($messageType) ?>', <?= json_encode($message) ?>, '<?= $messageType ?>');
        });
    </script>
<?php endif; ?>

<div class="mb-3">
    <a href="bulk_assign_initiatives.php" class="btn btn-outline-secondary">
        <i class="fas fa-link me-1"></i> Bulk Assign Initiatives
    </a>
</div>

<?php
// Include the partials for each program table.
require_once __DIR__ . '/partials/_draft_programs_table.php';
require_once __DIR__ . '/partials/_finalized_programs_table.php';
require_once __DIR__ . '/partials/_template_programs_table.php';
require_once __DIR__ . '/partials/_modals.php';
?>

<?php
// Include footer.
require_once PROJECT_ROOT_PATH . 'app/views/layouts/footer.php';
?>

<script>
// Pass necessary PHP variables to JavaScript.
window.currentUserRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
window.currentPeriodId = '<?php echo $period_id ?? ''; ?>';
</script>




