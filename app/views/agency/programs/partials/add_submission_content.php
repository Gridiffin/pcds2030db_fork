<!DOCTYPE html>
<?php
/**
 * Content partial for the Add Submission page.
 *
 * This file contains the HTML markup for the form and informational cards.
 */

if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(dirname(__DIR__))))));
}
require_once PROJECT_ROOT_PATH . '/app/helpers/vite-helpers.php';
?>


<div class="container-fluid">
    <div class="row">
        <div class="col-12">
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
                        // Wait for global functions to be available
                        function waitForToastFunctions() {
                            if (typeof window.showToast === 'function') {
                                showToast('<?= ucfirst($messageType) ?>', <?= json_encode($message) ?>, '<?= $messageType ?>');
                            } else {
                                setTimeout(waitForToastFunctions, 100);
                            }
                        }
                        waitForToastFunctions();
                    });
                </script>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Program Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Program Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Program Name:</strong> <?php echo htmlspecialchars($program['program_name']); ?><br>
                            <strong>Program Number:</strong> <?php echo htmlspecialchars($program['program_number'] ?? 'Not assigned'); ?><br>
                            <strong>Initiative:</strong> 
                            <?php if (!empty($program['initiative_name'])): ?>
                                <?php echo htmlspecialchars($program['initiative_name']); ?>
                                <?php if (!empty($program['initiative_number'])): ?>
                                    (<?php echo htmlspecialchars($program['initiative_number']); ?>)
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not linked</span>
                            <?php endif; ?><br>
                            <strong>Agency:</strong> <?php echo htmlspecialchars($program['agency_name'] ?? 'Unknown'); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Description:</strong> <?php echo htmlspecialchars($program['program_description'] ?? 'No description'); ?><br>
                            <strong>Created:</strong> <?php echo date('M j, Y', strtotime($program['created_at'])); ?><br>
                            <strong>Existing Submissions:</strong> <?php echo count($existing_submissions); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing Submissions Summary -->
            <?php if (!empty($existing_submissions)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Existing Submissions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($existing_submissions as $submission): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($submission['year'] . ' ' . ucfirst($submission['period_type']) . ' ' . $submission['period_number']); ?></td>
                                    <td><?php echo $submission['is_draft'] ? 'Draft' : ($submission['is_submitted'] ? 'Finalized' : 'Unknown'); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $submission['period_type']))); ?>
                                    </td>
                                    <td>
                                        <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $submission['period_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Submission">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Add Submission Form -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Add New Submission
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" id="addSubmissionForm" data-program-id="<?php echo htmlspecialchars($program_id); ?>" data-program-number="<?php echo htmlspecialchars($program['program_number'] ?? ''); ?>">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Reporting Period Selection -->
                                <div class="mb-4">
                                    <label for="period_id" class="form-label">
                                        Reporting Period <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="period_id" name="period_id" required>
                                        <option value="">Select a reporting period</option>
                                        <?php foreach ($reporting_periods as $period): ?>
                                            <?php 
                                            // Check if this period already has a submission
                                            $has_submission = false;
                                            foreach ($existing_submissions as $existing) {
                                                if ($existing['period_id'] == $period['period_id']) {
                                                    $has_submission = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <option value="<?php echo $period['period_id']; ?>"
                                                    <?php echo (isset($_POST['period_id']) && $_POST['period_id'] == $period['period_id']) ? 'selected' : ''; ?>
                                                    data-status="<?php echo $period['status']; ?>"
                                                    <?php echo $has_submission ? 'disabled' : ''; ?>>
                                                <?php echo htmlspecialchars($period['display_name']); ?>
                                                <?php if ($period['status'] == 'open'): ?>
                                                    (Open)
                                                <?php endif; ?>
                                                <?php if ($has_submission): ?>
                                                    (Already has submission)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-calendar me-1"></i>
                                        Select the reporting period for this submission. 
                                        <span class="text-success">Open</span> periods are currently accepting submissions.
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description"
                                              rows="3"
                                              placeholder="Describe the submission for this period"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Provide details about the program's progress during this reporting period
                                    </div>
                                </div>

                                <!-- Targets Section -->
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-bullseye me-2"></i>
                                            Targets
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small mb-3">
                                            Add targets for this submission period. You can add more targets later.
                                        </p>
                                        <div id="targets-container">
                                            <!-- Targets will be added here by JavaScript -->
                                        </div>
                                        <button type="button" id="add-target-btn" class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="fas fa-plus-circle me-1"></i> Add Target
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <!-- Submission Info Card only -->
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Submission Info
                                        </h6>
                                        <ul class="list-unstyled mb-0 small">
                                            <li class="mb-2">
                                                <i class="fas fa-calendar-plus text-primary me-2"></i>
                                                Creates a new submission for the selected period
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-edit text-info me-2"></i>
                                                You can edit this submission later
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-paperclip text-warning me-2"></i>
                                                Add attachments after creating
                                            </li>
                                            <li>
                                                <i class="fas fa-save text-success me-2"></i>
                                                Save as draft or ask focal to submit when ready
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- Attachment Section -->
                                <div class="card shadow-sm mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2">
                                            <i class="fas fa-paperclip me-2"></i>
                                            Attachments
                                        </h6>
                                        <button type="button" id="add-attachment-btn" class="btn btn-outline-secondary btn-sm mb-2">
                                            <i class="fas fa-plus me-1"></i> Add File(s)
                                        </button>
                                        <input type="file" class="form-control d-none" name="attachments[]" id="attachments" multiple>
                                        <div class="form-text mt-1">
                                            You can add files one by one or in batches. Allowed types: PDF, DOCX, XLSX, PNG, JPG, etc.
                                        </div>
                                        <ul id="attachments-list" class="list-unstyled small mt-2"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="view_programs.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                            <div>
                                <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-save me-2"></i>
                                    Save as Draft
                                </button>
                                <!-- Submit button removed -->
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 