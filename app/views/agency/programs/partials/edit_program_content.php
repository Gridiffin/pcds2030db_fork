<?php
/**
 * Content partial for the Edit Program page.
 * 
 * This file contains the HTML form and modals.
 * It is included by the main edit_program.php view.
 */
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Error/Success Messages -->
            <?php if (!empty($message)): ?>
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

            <!-- Simple Program Editing Form -->
            <div class="card shadow-sm mb-4 w-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 me-3">
                            <i class="fas fa-edit me-2"></i>
                            Edit Program Information
                        </h5>
                        <?php 
                        $status = isset($program['status']) ? $program['status'] : 'active';
                        $status_info = get_program_status_info($status);
                        ?>
                        <span id="program-status-badge" class="badge status-badge bg-<?php echo $status_info['class']; ?> py-2 px-3">
                            <i class="icon <?php echo $status_info['icon']; ?> me-1"></i>
                            <?php echo $status_info['label']; ?>
                        </span>
                    </div>
                    <div>
                        <!-- Removed Change Status button -->
                        <button class="btn btn-outline-secondary btn-sm" id="view-status-history-btn">Status History</button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="post" id="editProgramForm">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Program Name -->
                                <div class="mb-4">
                                    <label for="program_name" class="form-label">
                                        Program Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="program_name" 
                                           name="program_name" 
                                           required
                                           placeholder="Enter the program name"
                                           value="<?php echo htmlspecialchars($_POST['program_name'] ?? $program['program_name']); ?>">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This will be the main identifier for your program
                                    </div>
                                </div>

                                <!-- Initiative Selection -->
                                <div class="mb-4">
                                    <label for="initiative_id" class="form-label">
                                        Link to Initiative
                                        <span class="badge bg-secondary ms-1">Optional</span>
                                    </label>
                                    <select class="form-select" id="initiative_id" name="initiative_id">
                                        <option value="">Select an initiative (optional)</option>
                                        <?php foreach ($active_initiatives as $initiative): ?>
                                            <option value="<?php echo $initiative['initiative_id']; ?>"
                                                    <?php echo (isset($_POST['initiative_id']) ? $_POST['initiative_id'] : $program['initiative_id']) == $initiative['initiative_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                                                <?php if ($initiative['initiative_number']): ?>
                                                    (<?php echo htmlspecialchars($initiative['initiative_number']); ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Link this program to a strategic initiative for better organization and reporting
                                    </div>
                                </div>

                                <!-- Program Number -->
                                <div class="mb-4">
                                    <label for="program_number" class="form-label">
                                        Program Number
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="program_number" 
                                           name="program_number" 
                                           placeholder="Select initiative first"
                                           disabled
                                           pattern="[\w.]+"
                                           title="Program number can contain letters, numbers, and dots"
                                           value="<?php echo htmlspecialchars($_POST['program_number'] ?? $program['program_number'] ?? ''); ?>">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <span id="number-help-text">Select an initiative to enable program numbering</span>
                                    </div>
                                    <div id="final-number-display" class="mt-1" style="display: none;">
                                        <small class="text-muted">Final number will be: <span id="final-number-preview"></span></small>
                                    </div>
                                    <div id="number-validation" class="mt-2" style="display: none;">
                                        <small id="validation-message"></small>
                                    </div>
                                </div>

                                <!-- Brief Description -->
                                <div class="mb-4">
                                    <label for="brief_description" class="form-label">Brief Description</label>
                                    <textarea class="form-control" 
                                              id="brief_description" 
                                              name="brief_description"
                                              rows="3"
                                              placeholder="Provide a short summary of the program"><?php echo htmlspecialchars($_POST['brief_description'] ?? $program['program_description'] ?? ''); ?></textarea>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        A brief overview to help identify this program
                                    </div>
                                </div>

                                <!-- Program Rating - Only visible to focal users -->
                                <?php if (is_focal_user()): ?>
                                <div class="mb-4">
                                    <label for="status" class="form-label">
                                        Program Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Select a status</option>
                                        <?php 
                                        $current_status = $_POST['status'] ?? $program['status'] ?? 'active';
                                        $status_options = [
                                            'active' => 'Active',
                                            'on_hold' => 'On Hold',
                                            'completed' => 'Completed',
                                            'delayed' => 'Delayed',
                                            'cancelled' => 'Cancelled'
                                        ];
                                        foreach ($status_options as $value => $label): 
                                        ?>
                                            <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $current_status == $value ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Current status of this program
                                    </div>
                                </div>
                                <?php else: ?>
                                <!-- Hidden status field for non-focal users -->
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($program['status'] ?? 'active'); ?>">
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4">
                                <!-- Timeline Section -->
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            Timeline
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Start Date -->
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">
                                                Start Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="start_date" 
                                                   name="start_date"
                                                   value="<?php echo htmlspecialchars($_POST['start_date'] ?? $program['start_date'] ?? ''); ?>">
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Optional: Set a start date if the program has a specific timeline
                                            </div>
                                        </div>

                                        <!-- End Date -->
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">
                                                End Date
                                            </label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="end_date" 
                                                   name="end_date"
                                                   value="<?php echo htmlspecialchars($_POST['end_date'] ?? $program['end_date'] ?? ''); ?>">
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Optional: Set an end date if the program has a specific timeline
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hold Point Management Section -->
                                <div class="card shadow-sm mt-3" id="holdPointManagementSection" style="<?php echo ($program['status'] ?? '') === 'on_hold' ? '' : 'display:none;'; ?>">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-pause-circle me-2"></i>
                                            Hold Point Management
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="holdPointForm">
                                            <input type="hidden" id="holdPointId" name="hold_point_id">
                                            <div class="mb-3">
                                                <label for="hold_reason" class="form-label">Reason for Hold</label>
                                                <input type="text" class="form-control" id="hold_reason" name="reason" placeholder="Enter the reason for the hold">
                                            </div>
                                            <div class="mb-3">
                                                <label for="hold_remarks" class="form-label">Remarks</label>
                                                <textarea class="form-control" id="hold_remarks" name="hold_remarks" rows="2" placeholder="Additional remarks (optional)"></textarea>
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <button type="button" class="btn btn-primary btn-sm me-2" id="updateHoldPointBtn">Update Hold Point</button>
                                                <button type="button" class="btn btn-danger btn-sm" id="endHoldPointBtn">End Hold Point</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- User Permissions Section - Only shown to program owners, focal users, and admins -->
                                <?php
                                // Check if current user can modify user permissions (must be program owner or focal)
                                $can_modify_permissions = is_focal_user() || is_program_creator($program_id) || is_admin();
                                ?>

                                <?php if ($can_modify_permissions): ?>
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-users me-2"></i>
                                            User Permissions
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Current Status -->
                                        <div class="alert alert-<?php echo $restrict_editors ? 'warning' : 'success'; ?> mb-3">
                                            <i class="fas fa-<?php echo $restrict_editors ? 'lock' : 'unlock'; ?> me-2"></i>
                                            <strong>Current Status:</strong> 
                                            <?php if ($restrict_editors): ?>
                                                Editing restricted to specific users
                                            <?php else: ?>
                                                All agency users can edit
                                            <?php endif; ?>
                                        </div>

                                        <!-- Restrict Editors Toggle -->
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="restrict_editors" name="restrict_editors"
                                                       <?php echo $restrict_editors ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="restrict_editors">
                                                    <strong>Restrict editing to specific users</strong>
                                                </label>
                                            </div>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                When disabled, all agency users can edit. When enabled, only selected users can edit.
                                            </div>
                                        </div>

                                        <!-- User Selection (shown when restrictions are enabled) -->
                                        <div id="userSelectionSection" style="display: <?php echo $restrict_editors ? 'block' : 'none'; ?>;">
                                            <label class="form-label">
                                                <i class="fas fa-user-edit me-1"></i>
                                                Select users who can edit this program:
                                            </label>
                                            
                                            <?php if (!empty($assignable_users)): ?>
                                                <?php foreach ($assignable_users as $user): ?>
                                                    <?php if ($user['user_id'] == $program['created_by'] || $user['user_role'] === 'focal') continue; ?>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6 d-flex flex-column justify-content-center">
                                                            <span class="fw-bold"><?php echo htmlspecialchars($user['fullname'] ?: $user['username']); ?></span>
                                                            <span class="text-muted small"><?php echo htmlspecialchars($user['username']); ?></span>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <select class="form-select" name="user_roles[<?php echo $user['user_id']; ?>]">
                                                                <option value="">No Access</option>
                                                                <option value="viewer" <?php echo ($user['current_role'] === 'viewer') ? 'selected' : ''; ?>>Viewer</option>
                                                                <option value="editor" <?php echo ($user['current_role'] === 'editor') ? 'selected' : ''; ?>>Editor</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                <p class="form-text text-muted mt-2">
                                                    Assign roles to users for this program. <strong>Editor:</strong> Can edit program details and submissions. <strong>Viewer:</strong> Can only view program information.
                                                </p>
                                            <?php else: ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    No assignable users found.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Info Card -->
                                <div class="card shadow-sm mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2"></i>
                                            What You Can Edit
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Program name and description
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-link text-primary me-2"></i>
                                                Initiative linkage
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-hashtag text-info me-2"></i>
                                                Program number
                                            </li>
                                            <li>
                                                <i class="fas fa-calendar text-warning me-2"></i>
                                                Timeline dates
                                            </li>
                                        </ul>
                                        <hr>
                                        <div class="alert alert-info mb-0">
                                            <small>
                                                <i class="fas fa-info-circle me-1"></i>
                                                <strong>Note:</strong> Submissions are managed separately. Use the "Add Submission" button on the program details page to add or edit submissions.
                                            </small>
                                        </div>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Update Program
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Minimal Status History Modal -->
<div class="modal fade" id="statusHistoryModal" tabindex="-1" aria-labelledby="statusHistoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="statusHistoryModalLabel">Program Status History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="status-history-modal-body">
        <!-- Status history will be loaded here by JS -->
      </div>
    </div>
  </div>
</div>
<!-- Minimal Status Edit Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editStatusModalLabel">Change Program Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="edit-status-modal-body">
        <!-- Status edit form will be loaded here by JS -->
      </div>
    </div>
  </div>
</div>

<script>
    // Pass PHP variables to JavaScript
    window.PCDS_VARS = {
        programId: <?php echo json_encode($program_id); ?>,
        APP_URL: '<?php echo APP_URL; ?>'
    };
</script> 