<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define the root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';

// submission_info.php: View submission details for a program and period
require_once '../../layouts/header.php';
require_once '../../../lib/agencies/programs.php';

$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;

$program = get_program_details($program_id);
$submission = null;

if (!$program) {
    echo '<div class="alert alert-danger">Program not found or access denied.</div>';
    require_once '../../layouts/footer.php';
    exit;
}

if (isset($program['submissions']) && is_array($program['submissions'])) {
    foreach ($program['submissions'] as $sub) {
        if (isset($sub['period_id']) && $sub['period_id'] == $period_id) {
            $submission = $sub;
            break;
        }
    }
}

if (!$submission) {
    echo '<div class="alert alert-warning">No submission found for this period (period_id=' . htmlspecialchars($period_id) . ').</div>';
    echo '<pre>Program data:\n';
    var_dump($program);
    echo '</pre>';
    require_once '../../layouts/footer.php';
    exit;
}

$pageTitle = 'Submission Information';
require_once '../../layouts/page_header.php';
?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Submission Information
            </h5>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Program</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($program['program_name']); ?></dd>
                <dt class="col-sm-3">Period</dt>
                <dd class="col-sm-9"><?php echo htmlspecialchars($submission['period_type'] . ' ' . $submission['period_number'] . ' ' . $submission['year']); ?></dd>
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9"><?php echo $submission['is_draft'] ? 'Draft' : 'Submitted'; ?></dd>
                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($submission['description'])); ?></dd>
                <dt class="col-sm-3">Targets</dt>
                <dd class="col-sm-9">
                    <?php if (!empty($submission['targets'])): ?>
                        <ul>
                            <?php foreach ($submission['targets'] as $target): ?>
                                <li><?php echo htmlspecialchars($target['target_text'] ?? $target['target'] ?? ''); ?>
                                    <?php if (!empty($target['status_description'])): ?>
                                        <span class="text-muted small"><?php echo htmlspecialchars($target['status_description']); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <span class="text-muted">No targets specified.</span>
                    <?php endif; ?>
                </dd>
                <!-- Add attachments and other fields as needed -->
            </dl>
            <a href="view_programs.php" class="btn btn-outline-secondary mt-3"><i class="fas fa-arrow-left me-1"></i> Back to Programs</a>
        </div>
    </div>
</div>
<?php require_once '../../layouts/footer.php'; ?> 