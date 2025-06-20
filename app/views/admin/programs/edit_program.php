<?php
/**
 * Admin Edit Program
 * 
 * Allows admin users to edit program details.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admin_functions.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';
require_once ROOT_PATH . 'app/lib/agencies/programs.php'; // Added for program history feature
require_once ROOT_PATH . 'app/lib/audit_log.php';
require_once ROOT_PATH . 'app/lib/agencies/program_attachments.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from query parameter
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($program_id <= 0) {
    $_SESSION['message'] = 'Invalid program ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_programs.php');
    exit;
}

// Initialize variables
$message = '';
$messageType = 'info';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $program_name_form = trim($_POST['program_name'] ?? '');
    $owner_agency_id = intval($_POST['owner_agency_id'] ?? 0);
    $sector_id = intval($_POST['sector_id'] ?? 0);
    $start_date_form = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date_form = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $is_assigned = isset($_POST['is_assigned']) ? 1 : 0;
    $rating_form = isset($_POST['rating']) ? $_POST['rating'] : 'not-started'; // This is the overall program rating/status
    $remarks_form = trim($_POST['remarks'] ?? '');
    $targets_form = [];
    if (isset($_POST['target_text']) && is_array($_POST['target_text'])) {
        foreach ($_POST['target_text'] as $key => $target_text_item) {
            if (!empty($target_text_item)) {
                $targets_form[] = [
                    'target_text' => $target_text_item,
                    'status_description' => $_POST['status_description'][$key] ?? '',
                    // Individual target status might be part of a more complex setup, not directly in this simplified rating
                ];
            }
        }
    }
    
    $edit_permissions = isset($_POST['edit_permissions']) ? $_POST['edit_permissions'] : [];
    $program_settings = [
        'edit_permissions' => $edit_permissions
    ];
    $edit_permissions_json = json_encode($program_settings);

    if (empty($program_name_form)) {
        $message = 'Program name is required.';
        $messageType = 'danger';
    } elseif ($owner_agency_id <= 0) {
        $message = 'Valid owner agency is required.';
        $messageType = 'danger';
    } elseif ($sector_id <= 0) {
        $message = 'Valid sector is required.';
        $messageType = 'danger';
    } else {
        $conn->begin_transaction();
        try {
            // Update program in programs table
            $query_update_program = "UPDATE programs SET 
                      program_name = ?, 
                      owner_agency_id = ?, 
                      sector_id = ?,
                      start_date = ?, 
                      end_date = ?, 
                      is_assigned = ?,
                      edit_permissions = ?,
                      updated_at = NOW()
                      WHERE program_id = ?";
                      
            $stmt_update_program = $conn->prepare($query_update_program);
            $stmt_update_program->bind_param('siissisi', 
                $program_name_form, 
                $owner_agency_id, 
                $sector_id, 
                $start_date_form, 
                $end_date_form, 
                $is_assigned, 
                $edit_permissions_json, 
                $program_id
            );

            if (!$stmt_update_program->execute()) {
                throw new Exception('Failed to update program details: ' . $stmt_update_program->error);
            }
            $stmt_update_program->close();

            // Program details successfully updated in \'programs\' table.
            // Now, create a new submission entry for history.

            // Get the current reporting period
            $current_period_id = null;
            $current_period_data = get_current_reporting_period(); // Assumes this function is robust
            if ($current_period_data && isset($current_period_data['period_id'])) {
                $current_period_id = $current_period_data['period_id'];
            } else {
                // Fallback: Get the latest period if no current active one
                $latest_period_query = "SELECT period_id FROM reporting_periods ORDER BY year DESC, quarter DESC LIMIT 1";
                $latest_stmt = $conn->prepare($latest_period_query);
                if (!$latest_stmt) throw new Exception("Failed to prepare latest period query: " . $conn->error);
                $latest_stmt->execute();
                $latest_result = $latest_stmt->get_result();
                if ($latest_result->num_rows > 0) {
                    $current_period_id = $latest_result->fetch_assoc()['period_id'];
                }
                $latest_stmt->close();
            }

            if (!$current_period_id) {
                // If still no period ID, this is a problem. For now, we might have to skip submission or use a placeholder.
                // Or, decide if admin edits outside a period context should still create a submission.
                // For consistency, let's assume a submission should be created. If period_id is crucial, this needs a policy.
                // For now, let\'s throw an error if no period can be determined, as submissions are tied to periods.
                throw new Exception("Could not determine a valid reporting period for submission history.");
            }
            
            // Content for program_submissions.content_json
            // This snapshot includes the program name and description AS THEY ARE NOW in the \'programs\' table (just updated)
            $content_for_history = [
                'program_name' => $program_name_form, // Name as submitted in this form
                'rating'       => $rating_form,       // Rating/status from the form
                'targets'      => $targets_form,      // Targets from the form
                'remarks'      => $remarks_form       // Remarks from the form
            ];
            $content_json_history = json_encode($content_for_history);
            $admin_id = $_SESSION['user_id'];
            $is_draft_history = 0; // Admin edits are final            // Insert new submission for history
            $query_insert_submission = "INSERT INTO program_submissions (program_id, period_id, submitted_by, 
                                         content_json, is_draft, submission_date) 
                                         VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt_insert_submission = $conn->prepare($query_insert_submission);
            if (!$stmt_insert_submission) {
                 throw new Exception('Failed to prepare submission insert: ' . $conn->error);
            }
            // Note: status column removed as it's no longer used
            $stmt_insert_submission->bind_param('iiisi', 
                $program_id, 
                $current_period_id, 
                $admin_id, 
                $content_json_history, 
                $is_draft_history
            );

            if (!$stmt_insert_submission->execute()) {
                throw new Exception('Failed to insert program submission history: ' . $stmt_insert_submission->error);
            }
            $stmt_insert_submission->close();
              $conn->commit();
            $message = 'Program updated successfully and history recorded.';
            $messageType = 'success';
            
            // Log successful program edit
            log_audit_action(
                'admin_program_edited',
                "Admin edited program '{$program_name_form}' (ID: {$program_id}) - Owner: Agency {$owner_agency_id}, Sector: {$sector_id}",
                'success',
                $_SESSION['user_id']
            );

        } catch (Exception $e) {
            $conn->rollback();
            $message = 'Operation failed: ' . $e->getMessage();
            $messageType = 'danger';
            
            // Log program edit failure
            log_audit_action(
                'admin_program_edit_failed',
                "Admin failed to edit program (ID: {$program_id}): " . $e->getMessage(),
                'failure',
                $_SESSION['user_id']
            );
        }
    }
}

// Fetch program data for form
$query = "SELECT p.*, s.sector_name 
          FROM programs p
          LEFT JOIN sectors s ON p.sector_id = s.sector_id
          WHERE p.program_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $program_id);
$stmt->execute();
$result = $stmt->get_result();
$program = $result->fetch_assoc();
$stmt->close();

if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Get program attachments
$program_attachments = get_program_attachments($program_id);

// Extract edit permissions
$edit_permissions = [];
if (!empty($program['edit_permissions'])) {
    $permissions_data = json_decode($program['edit_permissions'], true);
    if (isset($permissions_data['edit_permissions']) && is_array($permissions_data['edit_permissions'])) {
        $edit_permissions = $permissions_data['edit_permissions'];
    }
}

// Get program edit history
$program_history = get_program_edit_history($program_id);

// Get program submission content
$submission_query = "SELECT content_json, submission_id FROM program_submissions 
                WHERE program_id = ? 
                ORDER BY submission_date DESC LIMIT 1";
$submission_stmt = $conn->prepare($submission_query);
$submission_stmt->bind_param('i', $program_id);
$submission_stmt->execute();
$submission_result = $submission_stmt->get_result();
$current_status = 'not-started';
$current_targets = [];
$submission_id = null;
$remarks = '';

if ($submission_result->num_rows > 0) {
    $submission = $submission_result->fetch_assoc();
    $submission_id = $submission['submission_id'];
    // Process content_json to extract status, targets, remarks
    if (!empty($submission['content_json'])) {
        $content = json_decode($submission['content_json'], true);
        if (isset($content['rating'])) {
            $current_status = $content['rating'];
        }
        if (isset($content['targets']) && is_array($content['targets'])) {
            $current_targets = $content['targets'];
        } else {
            // Legacy format
            $current_targets = [
                [
                    'target_text' => $content['target'] ?? '',
                    // ...other legacy fields...
                ]
            ];
        }
        if (isset($content['remarks'])) {
            $remarks = $content['remarks'];
        }
    }
}

// Fetch list of agencies for owner selection
$agencies_query = "SELECT user_id AS agency_id, agency_name FROM users 
                  WHERE role = 'agency' AND is_active = 1 
                  ORDER BY agency_name ASC";
$agencies_result = $conn->query($agencies_query);
$agencies = [];

if ($agencies_result) {
    while ($row = $agencies_result->fetch_assoc()) {
        $agencies[] = $row;
    }
}

// Fetch list of sectors
$sectors_query = "SELECT sector_id, sector_name FROM sectors ORDER BY sector_name ASC";
$sectors_result = $conn->query($sectors_query);
$sectors = [];

if ($sectors_result) {
    while ($row = $sectors_result->fetch_assoc()) {
        $sectors[] = $row;
    }
}

// Create hidden input for rating
$hidden_rating_input = '<input type="hidden" id="rating" name="rating" value="' . htmlspecialchars($current_status) . '">';

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/rating_utils.js',
    APP_URL . '/assets/js/utilities/program-history.js'
];

// Additional styles
$additionalStyles = '
<link rel="stylesheet" href="' . APP_URL . '/assets/css/components/program-history.css">
<link rel="stylesheet" href="' . APP_URL . '/assets/css/admin/programs.css">
';

// Set page title
$pageTitle = 'Edit Program';

require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Edit Program',
    'subtitle' => 'Modify program details',
    'variant' => 'white',
    'actions' => [
        [
            'text' => 'Back to Programs',
            'url' => APP_URL . '/app/views/admin/programs/programs.php',
            'class' => 'btn-outline-primary',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Include the modern page header
require_once '../../layouts/page_header.php';
?>

<main class="flex-fill">
<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <div><?php echo htmlspecialchars($message); ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">Edit Program: <?php echo htmlspecialchars($program['program_name']); ?></h5>
    </div>
    
    <div class="card-body">
        <form method="post" action="<?php echo view_url('admin/programs', 'edit_program.php?id=' . $program_id); ?>" id="editProgramForm">
            <?php if (isset($program_history['submissions']) && count($program_history['submissions']) > 1): ?>
            <!-- Program History Panel -->
            <div class="mb-4">
                <div class="history-panel-title">
                    <h6 class="fw-bold"><i class="fas fa-history me-2"></i> Program Edit History</h6>
                    <button type="button" class="history-toggle-btn" data-target="programHistoryPanel">
                        <i class="fas fa-history"></i> Show History
                    </button>
                </div>
                
                <div id="programHistoryPanel" class="history-panel" style="display: none;">                        <?php foreach($program_history['submissions'] as $idx => $submission): ?>
                        <div class="history-version">
                            <div class="history-version-info">
                                <strong><?php echo $submission['formatted_date']; ?></strong>
                                <span class="history-version-label"><?php echo $submission['is_draft_label']; ?></span>
                            </div>
                            <?php if ($idx === 0): ?>
                                <div><em>Current version</em></div>
                            <?php else: ?>
                                <div class="small text-muted mb-1">
                                    <?php echo isset($submission['submission_date']) ? 
                                        date('M j, Y g:i A', strtotime($submission['submission_date'])) : 
                                        $submission['formatted_date']; ?>
                                </div>
                                <?php if (isset($submission['period_name'])): ?>
                                <div>Period: <?php echo htmlspecialchars($submission['period_name']); ?></div>
                                <?php endif; ?>
                                <?php if (isset($submission['program_name'])): ?>
                                <div>Name: <?php echo htmlspecialchars($submission['program_name']); ?></div>
                                <?php endif; ?>
                                <?php if (isset($submission['status'])): ?>
                                <div>Status: <?php echo ucfirst($submission['status']); ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
            <div class="row g-3">                <!-- Rating Section -->
                <div class="rating-section mb-4">
                    <h6 class="fw-bold mb-3">Program Rating</h6>
                    <p class="text-muted mb-3">
                        How would you rate the overall progress of this program?
                    </p>
                    
                    <input type="hidden" id="rating" name="rating" value="<?php echo $current_status; ?>">
                    
                    <div class="rating-pills">
                        <div class="rating-pill target-achieved <?php echo ($current_status == 'target-achieved') ? 'active' : ''; ?>" data-rating="target-achieved">
                            <i class="fas fa-check-circle me-2"></i> Monthly Target Achieved
                        </div>
                        <div class="rating-pill on-track-yearly <?php echo ($current_status == 'on-track-yearly') ? 'active' : ''; ?>" data-rating="on-track-yearly">
                            <i class="fas fa-calendar-check me-2"></i> On Track for Year
                        </div>
                        <div class="rating-pill severe-delay <?php echo ($current_status == 'severe-delay') ? 'active' : ''; ?>" data-rating="severe-delay">
                            <i class="fas fa-exclamation-triangle me-2"></i> Severe Delays
                        </div>
                        <div class="rating-pill not-started <?php echo ($current_status == 'not-started' || !$current_status) ? 'active' : ''; ?>" data-rating="not-started">
                            <i class="fas fa-clock me-2"></i> Not Started
                        </div>
                    </div>
                </div>
                    
                <!-- 1. Basic Information Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="program_name" class="form-label">Program Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="program_name" name="program_name" 
                                   value="<?php echo htmlspecialchars($program['program_name']); ?>" required>
                            <?php if (isset($program_history['submissions']) && count($program_history['submissions']) > 1): ?>
                                <?php
                                // Get complete history of program name changes
                                $name_history = get_field_edit_history($program_history['submissions'], 'program_name');
                                
                                if (!empty($name_history)):
                                ?>
                                    <div class="d-flex align-items-center mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary field-history-toggle" 
                                                data-history-target="programNameHistory">
                                            <i class="fas fa-history"></i> Show Name History
                                        </button>
                                    </div>
                                    
                                    <div id="programNameHistory" class="history-complete" style="display: none;">
                                        <h6 class="small text-muted mb-2">Program Name History</h6>
                                        <ul class="history-list">
                                            <?php foreach($name_history as $idx => $item): ?>
                                            <li class="history-list-item">
                                                <div class="history-list-value">
                                                    <?php echo htmlspecialchars($item['value']); ?>
                                                </div>
                                                <div class="history-list-meta">
                                                    <?php echo $item['timestamp']; ?>
                                                    <?php if (isset($item['submission_id']) && $item['submission_id'] > 0): ?>
                                                        <span class="<?php echo ($item['is_draft'] ?? 0) ? 'history-draft-badge' : 'history-final-badge'; ?>">
                                                            <?php echo ($item['is_draft'] ?? 0) ? 'Draft' : 'Final'; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="owner_agency_id" class="form-label">Owner Agency <span class="text-danger">*</span></label>
                            <select class="form-select" id="owner_agency_id" name="owner_agency_id" required>
                                <option value="">Select Agency</option>
                                <?php foreach ($agencies as $agency): ?>
                                    <option value="<?php echo $agency['agency_id']; ?>" <?php echo ($agency['agency_id'] == $program['owner_agency_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($agency['agency_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="sector_id" class="form-label">Sector <span class="text-danger">*</span></label>
                            <select class="form-select" id="sector_id" name="sector_id" required>
                                <option value="">Select Sector</option>
                                <?php foreach ($sectors as $sector): ?>
                                    <option value="<?php echo $sector['sector_id']; ?>" <?php echo ($sector['sector_id'] == $program['sector_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sector['sector_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_assigned" name="is_assigned" 
                                      <?php echo ($program['is_assigned'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_assigned">
                                    Mark as Assigned Program
                                </label>
                                <div class="form-text">Assigned programs are created by admins for agencies.</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo isset($program['start_date']) ? date('Y-m-d', strtotime($program['start_date'])) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="<?php echo isset($program['end_date']) ? date('Y-m-d', strtotime($program['end_date'])) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>                <!-- 2. Program Targets Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">Program Targets</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Define one or more targets for this program, each with its own status description.
                        </p>
                        
                        <?php if (isset($program_history['submissions']) && count($program_history['submissions']) > 1): ?>
                            <div class="d-flex align-items-center mt-2 mb-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary field-history-toggle" 
                                        data-history-target="programTargetsHistory">
                                    <i class="fas fa-history"></i> Show Target History
                                </button>
                            </div>
                            <div id="programTargetsHistory" class="history-complete" style="display: none;">
                                <h6 class="small text-muted mb-2">Program Target History</h6>
                                <?php foreach($program_history['submissions'] as $idx => $submission): ?>
                                    <?php if ($idx > 0 && isset($submission['targets']) && !empty($submission['targets'])): ?>
                                        <div class="target-history-item">
                                            <div class="target-history-header">
                                                <strong><?php echo $submission['formatted_date']; ?></strong>
                                                <span><?php echo $submission['period_name'] ?? ''; ?></span>
                                            </div>
                                            
                                            <?php foreach($submission['targets'] as $t_idx => $target): ?>
                                                <div class="mb-1">
                                                    <strong>Target #<?php echo ($t_idx + 1); ?>:</strong> 
                                                    <?php echo htmlspecialchars($target['target_text'] ?? ''); ?>
                                                </div>
                                                <?php if (!empty($target['status_description'])): ?>
                                                    <div class="mb-1 ps-3">
                                                        <em>Status:</em> <?php echo htmlspecialchars($target['status_description']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div id="targets-container">
                            <?php if (!empty($current_targets)): ?>
                                <?php foreach ($current_targets as $index => $target): ?>
                                    <div class="target-entry">
                                        <?php if ($index > 0): ?>
                                        <button type="button" class="btn-close remove-target" aria-label="Remove target"></button>
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label class="form-label">Target <?php echo $index + 1; ?> *</label>
                                            <input type="text" class="form-control target-input" name="target_text[]" 
                                                    value="<?php echo htmlspecialchars($target['target_text'] ?? ''); ?>" 
                                                    placeholder="Define a measurable target (e.g., 'Plant 100 trees')">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Status Description</label>
                                            <textarea class="form-control status-description" name="status_description[]" rows="2" 
                                                        placeholder="Describe the current status or progress toward this target"><?php echo htmlspecialchars($target['status_description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Default empty target when no targets exist -->
                                <div class="target-entry">
                                    <div class="mb-3">
                                        <label class="form-label">Target 1 *</label>
                                        <input type="text" class="form-control target-input" name="target_text[]" 
                                                placeholder="Define a measurable target (e.g., 'Plant 100 trees')">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Status Description</label>
                                        <textarea class="form-control status-description" name="status_description[]" rows="2" 
                                                    placeholder="Describe the current status or progress toward this target"></textarea>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" id="add-target-btn" class="btn btn-outline-secondary add-target-btn">
                            <i class="fas fa-plus-circle me-1"></i> Add Another Target
                        </button>
                    </div>
                </div>
                  <!-- 3. Remarks and Comments Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">Remarks and Comments</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Additional Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="4" 
                                      placeholder="Add any additional remarks, challenges, or observations about this program..."><?php echo htmlspecialchars($remarks); ?></textarea>
                            <div class="form-text">
                                Optional additional notes or context about this program for the reporting period.
                            </div>
                        </div>
                    </div>
                </div>
                  <!-- 4. Edit Permissions Card -->
                <div class="card shadow-sm mb-4" id="permissions-section">
                    <div class="card-header">
                        <h5 class="card-title m-0">Agency Edit Permissions</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            These settings control what parts of the program the owning agency can edit.
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="edit_program_name" name="edit_permissions[]" value="program_name" 
                                      <?php echo in_array('program_name', $edit_permissions) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_program_name">Agency can edit Program Name</label>
                            </div>
                            
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="edit_brief_description" name="edit_permissions[]" value="brief_description" 
                                      <?php echo in_array('brief_description', $edit_permissions) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_brief_description">Agency can edit Brief Description</label>
                            </div>
                            
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="edit_targets" name="edit_permissions[]" value="targets" 
                                      <?php echo in_array('targets', $edit_permissions) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_targets">Agency can edit Targets</label>
                            </div>
                            
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="edit_status_text" name="edit_permissions[]" value="status_text" 
                                      <?php echo in_array('status_text', $edit_permissions) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_status_text">Agency can edit Status Descriptions</label>
                            </div>
                            
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="edit_rating" name="edit_permissions[]" value="rating" 
                                      <?php echo in_array('rating', $edit_permissions) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_rating">Agency can edit Rating</label>
                            </div>
                            
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit_timeline" name="edit_permissions[]" value="timeline" 
                                      <?php echo in_array('timeline', $edit_permissions) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_timeline">Agency can edit Timeline (Start/End Dates)</label>
                            </div>                        </div>
                    </div>
                </div>
                
                <!-- Program Attachments Section -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="card-title m-0">
                            <i class="fas fa-paperclip me-2"></i>Program Attachments
                        </h6>
                        <span class="badge bg-secondary">
                            <?php echo count($program_attachments); ?> 
                            <?php echo count($program_attachments) === 1 ? 'file' : 'files'; ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>View Only:</strong> This section displays attachments uploaded by the agency. 
                            Admin users can view and download these files but cannot upload or delete them through this interface.
                        </div>
                        
                        <?php if (!empty($program_attachments)): ?>
                            <div class="attachments-list">
                                <?php foreach ($program_attachments as $attachment): ?>
                                    <div class="attachment-item d-flex justify-content-between align-items-center border rounded p-3 mb-3">
                                        <div class="attachment-info d-flex align-items-center">
                                            <div class="attachment-icon me-3">
                                                <i class="fas <?php echo get_file_icon($attachment['mime_type']); ?> fa-2x text-primary"></i>
                                            </div>
                                            <div class="attachment-details">
                                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($attachment['original_filename']); ?></h6>
                                                <div class="attachment-meta text-muted small">
                                                    <span class="me-3">
                                                        <i class="fas fa-hdd me-1"></i>
                                                        <?php echo $attachment['file_size_formatted']; ?>
                                                    </span>
                                                    <span class="me-3">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo date('M j, Y \a\t g:i A', strtotime($attachment['upload_date'])); ?>
                                                    </span>
                                                    <span>
                                                        <i class="fas fa-user me-1"></i>
                                                        <?php echo htmlspecialchars($attachment['uploaded_by'] ?? 'Unknown'); ?>
                                                    </span>
                                                </div>
                                                <?php if (!empty($attachment['description'])): ?>
                                                    <div class="attachment-description mt-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-comment me-1"></i>
                                                            <?php echo htmlspecialchars($attachment['description']); ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="attachment-actions">
                                            <a href="<?php echo APP_URL; ?>/app/ajax/download_program_attachment.php?id=<?php echo $attachment['attachment_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               target="_blank"
                                               title="Download <?php echo htmlspecialchars($attachment['original_filename']); ?>">
                                                <i class="fas fa-download me-1"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-folder-open fa-3x text-muted"></i>
                                </div>
                                <h6 class="text-muted">No Attachments</h6>
                                <p class="text-muted mb-0">This program doesn't have any supporting documents uploaded.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Program
                    </button>
                    <div>
                        <a href="programs.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Rating pills selection
    const ratingPills = document.querySelectorAll('.rating-pill');
    const ratingInput = document.getElementById('rating');
    
    ratingPills.forEach(pill => {
        pill.addEventListener('click', function() {
            // Remove active class from all pills
            ratingPills.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked pill
            this.classList.add('active');
            
            // Update hidden input
            ratingInput.value = this.getAttribute('data-rating');
        });
    });
      // Add target functionality
    const addTargetBtn = document.getElementById('add-target-btn');
    if (addTargetBtn) {
        const targetsContainer = document.getElementById('targets-container');
        
        // Keep track of the highest target number
        let highestTargetNumber = document.querySelectorAll('.target-entry').length;
        
        // Function to update target numbers sequentially
        function updateTargetNumbers() {
            const targetEntries = document.querySelectorAll('.target-entry');
            targetEntries.forEach((entry, index) => {
                const label = entry.querySelector('.form-label');
                if (label && label.textContent.includes('Target')) {
                    label.textContent = `Target ${index + 1} *`;
                }
            });
        }
        
        addTargetBtn.addEventListener('click', function() {
            // Increment the highest target number
            highestTargetNumber++;
            
            const targetEntry = document.createElement('div');
            targetEntry.className = 'target-entry';
            
            const html = `
                <button type="button" class="btn-close remove-target" aria-label="Remove target"></button>
                <div class="mb-3">
                    <label class="form-label">Target ${highestTargetNumber} *</label>
                    <input type="text" class="form-control target-input" name="target_text[]" 
                           placeholder="Define a measurable target (e.g., 'Plant 100 trees')">
                </div>
                <div class="mb-2">
                    <label class="form-label">Status Description</label>
                    <textarea class="form-control status-description" name="status_description[]" rows="2" 
                              placeholder="Describe the current status or progress toward this target"></textarea>
                </div>
            `;
            
            targetEntry.innerHTML = html;
            targetsContainer.appendChild(targetEntry);
            
            // Attach remove event listener to the new target
            const removeBtn = targetEntry.querySelector('.remove-target');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    targetEntry.remove();
                    // Update target numbers after removing
                    updateTargetNumbers();
                });
            }
        });
        
        // Initialize existing remove buttons
        document.querySelectorAll('.remove-target').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.target-entry').remove();
                // Update target numbers after removing
                updateTargetNumbers();
            });
        });
    }
      // Toggle permissions section based on assigned status
    const isAssignedCheckbox = document.getElementById('is_assigned');
    const permissionsSection = document.getElementById('permissions-section');
    
    function togglePermissionsVisibility() {
        permissionsSection.style.display = isAssignedCheckbox.checked ? 'block' : 'none';
    }
    
    // Set initial state
    togglePermissionsVisibility();
    
    // Listen for changes
    isAssignedCheckbox.addEventListener('change', togglePermissionsVisibility);
    
    // Handle date validation
    const startDateField = document.getElementById('start_date');
    const endDateField = document.getElementById('end_date');
    
    if (startDateField && endDateField) {
        endDateField.addEventListener('change', function() {
            if (startDateField.value && this.value) {
                if (new Date(this.value) < new Date(startDateField.value)) {
                    alert('End date cannot be before start date');
                    this.value = '';
                }
            }
        });
    }
    
    // History panel toggle
    const historyToggleBtn = document.querySelector('.history-toggle-btn');
    const programHistoryPanel = document.getElementById('programHistoryPanel');
    
    if (historyToggleBtn && programHistoryPanel) {
        historyToggleBtn.addEventListener('click', function() {
            const isVisible = programHistoryPanel.style.display === 'block';
            programHistoryPanel.style.display = isVisible ? 'none' : 'block';
            this.innerHTML = isVisible ? '<i class="fas fa-history"></i> Show History' : '<i class="fas fa-history"></i> Hide History';
        });
    }
    
    // Form validation
    document.getElementById('editProgramForm').addEventListener('submit', function(e) {
        const programName = document.getElementById('program_name').value;
        const targetInputs = document.querySelectorAll('.target-input');
        let hasFilledTarget = false;
        
        // Validate program name
        if (!programName.trim()) {
            alert('Please enter a program name.');
            e.preventDefault();
            return false;
        }
        
        // Validate at least one target
        targetInputs.forEach(input => {
            if (input.value.trim()) {
                hasFilledTarget = true;
            }
        });
        
        if (!hasFilledTarget) {
            alert('Please add at least one target for this program.');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
});
</script>
</main>

<?php
require_once '../../layouts/footer.php';
?>



