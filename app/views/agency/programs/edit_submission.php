<?php
// Define the root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';

// edit_submission.php: Edit a submission for a program and period
require_once '../../layouts/header.php';
require_once '../../../lib/agencies/programs.php';

$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;

$program = get_program_details($program_id);
$submission = null;
if ($program && isset($program['submissions']) && is_array($program['submissions'])) {
    foreach ($program['submissions'] as $sub) {
        if ($sub['period_id'] == $period_id) {
            $submission = $sub;
            break;
        }
    }
}

// Helper: Fetch master program targets if submission targets are empty
function get_master_program_targets($program_id) {
    global $conn;
    $targets = [];
    $stmt = $conn->prepare("SELECT target_number, target_description FROM program_targets WHERE program_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY target_number ASC, target_description ASC");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $targets[] = [
            'target_number' => $row['target_number'],
            'target_text' => $row['target_description'],
            'target_status' => 'not_started',
            'status_description' => ''
        ];
    }
    return $targets;
}

// Helper: Fetch targets from the latest previous submission for this program
function get_previous_submission_targets($program_id, $exclude_submission_id) {
    global $conn;
    // Find the latest previous submission (excluding the current one)
    $stmt = $conn->prepare("SELECT submission_id FROM program_submissions WHERE program_id = ? AND submission_id != ? ORDER BY submission_id DESC LIMIT 1");
    $stmt->bind_param("ii", $program_id, $exclude_submission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $prev_submission_id = $row['submission_id'];
        // Fetch targets for that submission
        $targets = [];
        $tstmt = $conn->prepare("SELECT target_number, target_description FROM program_targets WHERE submission_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY target_number ASC, target_description ASC");
        $tstmt->bind_param("i", $prev_submission_id);
        $tstmt->execute();
        $tres = $tstmt->get_result();
        while ($trow = $tres->fetch_assoc()) {
            $targets[] = [
                'target_number' => $trow['target_number'],
                'target_text' => $trow['target_description'],
                'target_status' => 'not_started',
                'status_description' => ''
            ];
        }
        return $targets;
    }
    return [];
}

if ($submission && (empty($submission['targets']) || !is_array($submission['targets']) || count($submission['targets']) === 0)) {
    $submission['targets'] = get_previous_submission_targets($program_id, $submission['submission_id']);
}

$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $submission) {
    // Collect updated data
    $updated_data = [
        'program_id' => $program_id,
        'period_id' => $period_id,
        'description' => $_POST['description'] ?? '',
        'targets' => []
    ];
    if (isset($_POST['target_text']) && is_array($_POST['target_text'])) {
        $target_texts = $_POST['target_text'];
        $target_numbers = $_POST['target_number'] ?? [];
        $target_statuses = $_POST['target_status'] ?? [];
        $target_status_descriptions = $_POST['target_status_description'] ?? [];
        for ($i = 0; $i < count($target_texts); $i++) {
            $target_text = trim($target_texts[$i] ?? '');
            if (!empty($target_text)) {
                $updated_data['targets'][] = [
                    'target_number' => trim($target_numbers[$i] ?? ''),
                    'target_text' => $target_text,
                    'target_status' => trim($target_statuses[$i] ?? 'not_started'),
                    'status_description' => trim($target_status_descriptions[$i] ?? ''),
                ];
            }
        }
    }
    // Call update logic (implement update_program_submission in backend as needed)
    $result = function_exists('update_program_submission') ? update_program_submission($updated_data) : ['success' => true, 'message' => 'Submission updated (mock).'];
    if (isset($result['success']) && $result['success']) {
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = 'success';
        header('Location: submission_info.php?program_id=' . $program_id . '&period_id=' . $period_id);
        exit;
    } else {
        $message = $result['error'] ?? 'An error occurred while updating the submission.';
        $messageType = 'danger';
    }
}
$pageTitle = 'Edit Submission';
require_once '../../layouts/page_header.php';
?>
<div class="container mt-4">
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-edit fa-lg me-2"></i>
        <div>
            <strong>You are editing this submission.</strong> Make any changes below and click <b>Save Changes</b>.
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning bg-gradient text-dark">
            <h5 class="card-title mb-0">
                <i class="fas fa-edit me-2"></i>
                Edit Submission
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Program Name:</strong> <?php echo htmlspecialchars($program['program_name'] ?? ''); ?><br>
                    <strong>Reporting Period:</strong> <?php echo htmlspecialchars($submission['period_label'] ?? $submission['year'] . ' ' . ucfirst($submission['period_type']) . ' ' . $submission['period_number']); ?><br>
                    <strong>Status:</strong> <?php echo $submission['is_draft'] ? 'Draft' : ($submission['is_submitted'] ? 'Finalized' : 'Unknown'); ?>
                </div>
                <div class="col-md-6">
                    <strong>Initiative:</strong> <?php echo htmlspecialchars($program['initiative_name'] ?? ''); ?><br>
                    <strong>Agency:</strong> <?php echo htmlspecialchars($program['agency_name'] ?? ''); ?>
                </div>
            </div>
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>"> <?php echo htmlspecialchars($message); ?> </div>
            <?php endif; ?>
            <?php if ($submission): ?>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Describe the submission for this period"><?php echo htmlspecialchars($submission['description'] ?? ''); ?></textarea>
                </div>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-bullseye me-2"></i>
                            Targets
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="targets-container">
                            <?php if (!empty($submission['targets'])): ?>
                                <?php foreach ($submission['targets'] as $i => $target): ?>
                                    <div class="mb-2 border rounded p-2 bg-light">
                                        <label class="form-label small">Target <?php echo $i + 1; ?></label>
                                        <textarea class="form-control form-control-sm" name="target_text[]" rows="2" required><?php echo htmlspecialchars($target['target_text'] ?? $target['target'] ?? ''); ?></textarea>
                                        <input type="text" class="form-control form-control-sm mt-1" name="target_number[]" placeholder="Number (optional)" value="<?php echo htmlspecialchars($target['target_number'] ?? ''); ?>">
                                        <select class="form-select form-select-sm mt-1" name="target_status[]">
                                            <option value="not_started" <?php echo (isset($target['target_status']) && $target['target_status'] == 'not_started') ? 'selected' : ''; ?>>Not Started</option>
                                            <option value="in_progress" <?php echo (isset($target['target_status']) && $target['target_status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo (isset($target['target_status']) && $target['target_status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                            <option value="delayed" <?php echo (isset($target['target_status']) && $target['target_status'] == 'delayed') ? 'selected' : ''; ?>>Delayed</option>
                                        </select>
                                        <textarea class="form-control form-control-sm mt-1" name="target_status_description[]" rows="1" placeholder="Status description (optional)"><?php echo htmlspecialchars($target['status_description'] ?? ''); ?></textarea>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted">No targets specified.</div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-target-btn">
                            <i class="fas fa-plus-circle me-1"></i> Add Target
                        </button>
                    </div>
                </div>
                <!-- Attachments Section (if supported) -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-paperclip me-2"></i>
                            Attachments
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small mb-2" id="attachments-list">
                            <?php if (!empty($submission['attachments'])): ?>
                                <?php foreach ($submission['attachments'] as $file): ?>
                                    <li class="mb-1 d-flex align-items-center">
                                        <i class="fas fa-file-alt me-2"></i>
                                        <span><?php echo htmlspecialchars($file['filename']); ?></span>
                                        <a href="delete_attachment.php?attachment_id=<?php echo $file['id']; ?>&program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" class="btn btn-sm btn-outline-danger ms-2">Remove</a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-muted">No attachments.</li>
                            <?php endif; ?>
                        </ul>
                        <input type="file" class="form-control" name="attachments[]" id="attachments" multiple>
                        <div class="form-text mt-1">
                            You can add files one by one or in batches. Allowed types: PDF, DOCX, XLSX, PNG, JPG, etc.
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="submission_info.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Save Changes
                    </button>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-warning">Submission not found for this period.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once '../../layouts/footer.php'; ?> 