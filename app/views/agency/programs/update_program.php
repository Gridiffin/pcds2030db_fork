<?php
/**
 * Update Program
 * 
 * Interface for agency users to update program information.
 */

// Define the root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'lib/audit_log.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$program_id) {
    $_SESSION['message'] = 'Invalid program ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get program details
$program = get_program_details($program_id);

if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get program edit history
$program_history = get_program_edit_history($program_id);

// Check if this program has a finalized (non-draft) submission for the current period
// If it does, redirect to the program details page, as editing is not allowed
if (isset($program['submissions']) && !empty($program['submissions'])) {
    $current_period = get_current_reporting_period();
    foreach ($program['submissions'] as $submission) {
        if (isset($submission['period_id']) && 
            $current_period && 
            $submission['period_id'] == $current_period['period_id'] && 
            (!isset($submission['is_draft']) || $submission['is_draft'] == 0)) {
            // Found a finalized submission for current period - redirect to details page
            $_SESSION['message'] = 'This program has already been finalized for the current reporting period and cannot be edited.';
            $_SESSION['message_type'] = 'info';
            header('Location: program_details.php?id=' . $program_id);
            exit;
        }
    }
}

// Get current reporting period for submissions
$current_period = get_current_reporting_period();

// If no current period, redirect with error
if (!$current_period) {
    $_SESSION['message'] = 'No active reporting period found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Helper function to check if a field is editable for assigned programs
function is_editable($field) {
    global $program, $is_draft, $current_submission;
    
    // If program has a finalized submission, nothing is editable unless reopened by admin
    if (isset($current_submission) && 
        !empty($current_submission) && 
        (!isset($current_submission['is_draft']) || $current_submission['is_draft'] == 0)) {
        return false;
    }
    
    // If not an assigned program, all fields are editable
    if (!isset($program['is_assigned']) || !$program['is_assigned']) {
        return true;
    }
    
    // Otherwise, check edit permissions
    if (!isset($program['edit_permissions'])) {
        return true; // Default to editable if no specific permissions
    }
    
    $permissions = json_decode($program['edit_permissions'], true);
    
    // Check if field is in the editable permissions array
    return isset($permissions['edit_permissions']) && 
           is_array($permissions['edit_permissions']) && 
           in_array($field, $permissions['edit_permissions']);
}

// Helper function to get field value from POST, default, or content
function get_field_value($field, $default = '') {
    if (isset($_POST[$field])) {
        return $_POST[$field];
    }
    
    return $default;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine submission type
    $is_draft = isset($_POST['save_draft']);
    $finalize_draft = isset($_POST['finalize_draft']);    if ($finalize_draft) {
        $submission_id = $_POST['submission_id'] ?? 0;
        // Get current reporting period to ensure we're finalizing the correct submission
        $current_period = get_current_reporting_period();
        
        if ($submission_id && $current_period) {
            global $conn;
            
            // First validate that the submission has content
            $content_check = $conn->prepare("SELECT content_json FROM program_submissions WHERE submission_id = ? AND program_id = ? AND period_id = ?");
            $content_check->bind_param("iii", $submission_id, $program_id, $current_period['period_id']);
            $content_check->execute();
            $content_result = $content_check->get_result();
            
            if ($content_result->num_rows > 0) {
                $content_row = $content_result->fetch_assoc();
                $content_json = $content_row['content_json'];
                  // Validate content
                if (empty($content_json) || $content_json === 'null') {
                    $result = ['error' => 'Cannot finalize submission without content. Please add targets and rating first.'];
                    
                    // Log validation failure
                    log_audit_action(
                        'program_submission_finalization_failed',
                        "Failed to finalize program '{$program['program_name']}' (ID: {$program_id}) - no content",
                        'failure',
                        $_SESSION['user_id']
                    );
                } else {
                    $content_data = json_decode($content_json, true);
                    if (!$content_data || (empty($content_data['targets']) && empty($content_data['target'])) || empty($content_data['rating'])) {
                        $result = ['error' => 'Cannot finalize submission without targets and rating. Please complete the program details first.'];
                        
                        // Log validation failure
                        log_audit_action(
                            'program_submission_finalization_failed',
                            "Failed to finalize program '{$program['program_name']}' (ID: {$program_id}) - missing targets or rating",
                            'failure',
                            $_SESSION['user_id']
                        );
                    } else {
                        // Content is valid, proceed with finalization
                        $stmt = $conn->prepare("UPDATE program_submissions SET is_draft = 0, submission_date = NOW() WHERE submission_id = ? AND program_id = ? AND period_id = ?");
                        $stmt->bind_param("iii", $submission_id, $program_id, $current_period['period_id']);                        if ($stmt->execute() && $stmt->affected_rows > 0) {
                            $result = ['success' => true, 'message' => 'Draft finalized successfully.'];
                            
                            // Log successful finalization
                            log_audit_action(
                                'program_submission_finalized',
                                "Program '{$program['program_name']}' (ID: {$program_id}) submission finalized for period {$current_period['period_id']}",
                                'success',
                                $_SESSION['user_id']
                            );
                        } else {
                            $result = ['error' => 'Failed to finalize draft. Submission may not exist for current period.'];
                            
                            // Log finalization failure
                            log_audit_action(
                                'program_submission_finalization_failed',
                                "Failed to finalize program '{$program['program_name']}' (ID: {$program_id}) submission for period {$current_period['period_id']}",
                                'failure',
                                $_SESSION['user_id']
                            );
                        }
                    }
                }
            } else {
                $result = ['error' => 'Submission not found.'];
            }} else {
            $result = ['error' => 'Invalid submission ID or no active reporting period.'];
        }    } else {
        // Handle save draft functionality - update both program basic info and submission content
        global $conn;
        try {
            $conn->begin_transaction();
              // Get form data
            $program_name = trim($_POST['program_name'] ?? '');
            $brief_description = trim($_POST['brief_description'] ?? '');
            $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
            $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
            $rating = $_POST['rating'] ?? 'not-started';
            $remarks = trim($_POST['remarks'] ?? '');
            $period_id = intval($_POST['period_id'] ?? 0);
            $submission_id = intval($_POST['submission_id'] ?? 0);
            $current_user_id = $_SESSION['user_id'];
            
            // Process targets array
            $targets = [];
            if (isset($_POST['target_text']) && is_array($_POST['target_text'])) {
                $target_texts = $_POST['target_text'];
                $target_status_descriptions = $_POST['target_status_description'] ?? [];
                
                for ($i = 0; $i < count($target_texts); $i++) {
                    $target_text = trim($target_texts[$i]);
                    if (!empty($target_text)) {
                        $targets[] = [
                            'target_text' => $target_text,
                            'status_description' => trim($target_status_descriptions[$i] ?? '')
                        ];
                    }
                }
            }
            
            // 1. Update program basic information (if allowed)
            if (is_editable('program_name') || is_editable('start_date') || is_editable('end_date')) {
                $update_fields = [];
                $update_params = [];
                $param_types = '';
                
                if (is_editable('program_name') && !empty($program_name)) {
                    $update_fields[] = "program_name = ?";
                    $update_params[] = $program_name;
                    $param_types .= 's';
                }
                
                if (is_editable('start_date')) {
                    $update_fields[] = "start_date = ?";
                    $update_params[] = $start_date;
                    $param_types .= 's';
                }
                
                if (is_editable('end_date')) {
                    $update_fields[] = "end_date = ?";
                    $update_params[] = $end_date;
                    $param_types .= 's';
                }
                
                if (!empty($update_fields)) {
                    $update_fields[] = "updated_at = NOW()";
                    $update_params[] = $program_id;
                    $param_types .= 'i';
                    
                    $program_query = "UPDATE programs SET " . implode(', ', $update_fields) . " WHERE program_id = ?";
                    $program_stmt = $conn->prepare($program_query);
                    $program_stmt->bind_param($param_types, ...$update_params);
                    
                    if (!$program_stmt->execute()) {
                        throw new Exception('Failed to update program: ' . $program_stmt->error);
                    }
                }
            }
              // 2. Handle program submission data
            $content_data = [
                'rating' => $rating,
                'targets' => $targets,
                'remarks' => $remarks,
                'brief_description' => $brief_description,
                'program_name' => $program_name
            ];
            $content_json = json_encode($content_data);
              if ($submission_id > 0) {
                // Whenever changes are made, we should insert a new record to preserve change history
                // This is particularly important for brief_description which needs a full change history
                
                // Create new submission instead of updating
                $submission_query = "INSERT INTO program_submissions 
                                   (program_id, period_id, submitted_by, content_json, is_draft, submission_date, updated_at) 
                                   VALUES (?, ?, ?, ?, 1, NOW(), NOW())";
                $submission_stmt = $conn->prepare($submission_query);
                $submission_stmt->bind_param("iiis", $program_id, $period_id, $current_user_id, $content_json);
                
                if (!$submission_stmt->execute()) {
                    throw new Exception('Failed to create submission record: ' . $submission_stmt->error);
                }
            } else {
                // Create new submission
                $submission_query = "INSERT INTO program_submissions 
                                   (program_id, period_id, submitted_by, content_json, is_draft, submission_date, updated_at) 
                                   VALUES (?, ?, ?, ?, 1, NOW(), NOW())";
                $submission_stmt = $conn->prepare($submission_query);
                $submission_stmt->bind_param("iiis", $program_id, $period_id, $current_user_id, $content_json);
                
                if (!$submission_stmt->execute()) {
                    throw new Exception('Failed to create submission: ' . $submission_stmt->error);
                }
            }            $conn->commit();
            $result = ['success' => true, 'message' => 'Program saved as draft successfully.'];
            
            // Log successful draft save
            log_audit_action(
                'program_draft_saved',
                "Program '{$program_name}' (ID: {$program_id}) draft saved for period {$period_id}",
                'success',
                $_SESSION['user_id']
            );
        } catch (Exception $e) {
            $conn->rollback();
            $result = ['error' => 'Failed to save draft: ' . $e->getMessage()];
            
            // Log draft save failure
            log_audit_action(
                'program_draft_save_failed',
                "Failed to save draft for program '{$program['program_name']}' (ID: {$program_id}): " . $e->getMessage(),
                'failure',
                $_SESSION['user_id']
            );
        }
    }
    
    if (isset($result['success'])) {
        // Set success message
        if ($finalize_draft) {
            $_SESSION['message'] = 'Draft finalized successfully.';
        } else if ($is_draft) {
            $_SESSION['message'] = 'Program saved as draft successfully.';
        } else {
            $_SESSION['message'] = 'Program updated successfully.';
        }
        $_SESSION['message_type'] = 'success';
        
        // Redirect to programs page
        header('Location: view_programs.php');
        exit;
    } else {
        $message = $result['error'] ?? 'An error occurred while updating the program.';
        $messageType = 'danger';
    }
}

// Check if the program has a draft submission for the current period
$is_draft = false;
$submission_id = null;

// Check for current submission
if (isset($program['current_submission'])) {
    $current_submission = $program['current_submission'];
    $is_draft = isset($current_submission['is_draft']) && $current_submission['is_draft'] == 1;
    $submission_id = $current_submission['submission_id'] ?? null;
    
    // Process content_json if available
    if (isset($current_submission['content_json']) && is_string($current_submission['content_json'])) {
        $content = json_decode($current_submission['content_json'], true);
          // If we have the new structure with targets array, use it
        if (isset($content['targets']) && is_array($content['targets'])) {
            $targets = $content['targets'];
            $rating = $content['rating'] ?? 'not-started';
            $remarks = $content['remarks'] ?? '';
            $brief_description = $content['brief_description'] ?? '';} else {
            // Legacy data - handle semicolon-separated targets
            $target_text = $content['target'] ?? $current_submission['target'] ?? '';
            $status_description = $content['status_description'] ?? $content['status_text'] ?? $current_submission['status_text'] ?? '';
            
            // Check if targets are semicolon-separated
            if (strpos($target_text, ';') !== false) {
                // Split semicolon-separated targets and status descriptions
                $target_parts = array_map('trim', explode(';', $target_text));
                $status_parts = array_map('trim', explode(';', $status_description));
                
                $targets = [];
                foreach ($target_parts as $index => $target_part) {
                    if (!empty($target_part)) {
                        $targets[] = [
                            'target_text' => $target_part,
                            'status_description' => isset($status_parts[$index]) ? $status_parts[$index] : ''
                        ];
                    }
                }
                
                // Ensure we have at least one target
                if (empty($targets)) {
                    $targets = [['target_text' => '', 'status_description' => '']];
                }
            } else {
                // Single target - create a single target from old structure
                $targets = [
                    [
                        'target_text' => $target_text,
                        'status_description' => $status_description
                    ]
                ];
            }
            
            $rating = $current_submission['status'] ?? 'not-started';
            $remarks = $content['remarks'] ?? '';
        }    } else {
        // Old structure without content_json - handle semicolon-separated targets
        $target_text = $current_submission['target'] ?? '';
        $status_description = $current_submission['status_text'] ?? '';
        
        // Check if targets are semicolon-separated
        if (strpos($target_text, ';') !== false) {
            // Split semicolon-separated targets and status descriptions
            $target_parts = array_map('trim', explode(';', $target_text));
            $status_parts = array_map('trim', explode(';', $status_description));
            
            $targets = [];
            foreach ($target_parts as $index => $target_part) {
                if (!empty($target_part)) {
                    $targets[] = [
                        'target_text' => $target_part,
                        'status_description' => isset($status_parts[$index]) ? $status_parts[$index] : ''
                    ];
                }
            }
            
            // Ensure we have at least one target
            if (empty($targets)) {
                $targets = [['target_text' => '', 'status_description' => '']];
            }
        } else {
            // Single target
            $targets = [
                [
                    'target_text' => $target_text,
                    'status_description' => $status_description
                ]
            ];        }
        
        $rating = $current_submission['status'] ?? 'not-started';
        $remarks = $current_submission['remarks'] ?? '';
        $brief_description = $content['brief_description'] ?? '';
    }
} else {
    // No current submission, initialize empty targets
    $targets = [['target_text' => '', 'status_description' => '']];
    $rating = 'not-started';
    $remarks = '';
    $brief_description = '';
}

// Ensure $brief_description is always defined as a string to prevent warnings and deprecated notices
if (!isset($brief_description) || $brief_description === null) {
    $brief_description = '';
}

// Fallback: If brief_description is still empty, use the value from the program's content_json if not present in the latest submission, since that's where the data is stored.
if (empty($brief_description)) {
    // Try to extract from program['content_json'] if available
    if (!empty($program['content_json'])) {
        $program_content = json_decode($program['content_json'], true);
        if (is_array($program_content) && !empty($program_content['brief_description'])) {
            $brief_description = $program_content['brief_description'];
        }
    }
    // Legacy fallback (if any other fields are used in the future)
    elseif (!empty($program['brief_description'])) {
        $brief_description = $program['brief_description'];
    } elseif (!empty($program['description'])) {
        $brief_description = $program['description'];
    }
}

// Set page title
$pageTitle = 'Update Program';

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/agency/program_management.js',
    APP_URL . '/assets/js/utilities/rating_utils.js',
    APP_URL . '/assets/js/utilities/program-history.js'
];

// Additional styles
$additionalStyles = '
<link rel="stylesheet" href="' . APP_URL . '/assets/css/components/program-history.css">
';

// Include header (which contains the DOCTYPE declaration)
require_once dirname(__DIR__, 2) . '/layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Update Program',
    'subtitle' => htmlspecialchars($program['program_name']) . " - " . 
                htmlspecialchars($current_period['name'] ?? '') . 
                " (" . date('M j, Y', strtotime($current_period['start_date'])) . " - " . 
                date('M j, Y', strtotime($current_period['end_date'])) . ")",
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'view_programs.php',
            'text' => 'Back to Programs',
            'icon' => 'fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Include modern page header
require_once dirname(__DIR__, 2) . '/layouts/page_header.php';

?>

<?php
// Include any draft notification banner if this is a draft
                if ($is_draft): ?>
                <div class="draft-banner mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Draft Mode:</strong>  This program submission is currently saved as a draft. You can continue editing or submit the final version.
                </div>
                <?php endif; ?>

                <?php if (isset($program_history['submissions']) && count($program_history['submissions']) > 1): ?>
                <!-- Program History Panel -->
                <div class="mb-4">
                    <div class="history-panel-title">
                        <h6 class="fw-bold"><i class="fas fa-history me-2"></i> Program Edit History</h6>
                        <button type="button" class="history-toggle-btn" data-target="programHistoryPanel">
                            <i class="fas fa-history"></i> Show History
                        </button>
                    </div>
                    
                    <div id="programHistoryPanel" class="history-panel" style="display: none;">
                        <?php foreach($program_history['submissions'] as $idx => $submission): ?>
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
                            
                            <?php if (isset($submission['target'])): ?>
                            <div>Target: <?php echo htmlspecialchars($submission['target']); ?></div>
                            <?php endif; ?>
                            
                            <?php if (isset($submission['achievement'])): ?>
                            <div>Achievement: <?php echo htmlspecialchars($submission['achievement']); ?></div>
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

                <!-- Program Update Form -->
                <form id="updateProgramForm" method="post">
                    <input type="hidden" name="period_id" value="<?php echo $current_period['period_id']; ?>">
                    <?php if ($submission_id): ?>
                    <input type="hidden" name="submission_id" value="<?php echo $submission_id; ?>">
                    <?php endif; ?>
                    
                    <!-- Rating Section -->
                    <div class="rating-section mb-4">
                        <h6 class="fw-bold mb-3">Program Rating</h6>
                        <p class="text-muted mb-3">
                            How would you rate the overall progress of this program?
                        </p>
                        
                        <input type="hidden" id="rating" name="rating" value="<?php echo $rating; ?>">
                        
                        <div class="rating-pills">
                            <div class="rating-pill target-achieved <?php echo ($rating == 'target-achieved') ? 'active' : ''; ?> <?php echo (!is_editable('rating')) ? 'disabled' : ''; ?>" data-rating="target-achieved">
                                <i class="fas fa-check-circle me-2"></i> Monthly Target Achieved
                            </div>
                            <div class="rating-pill on-track-yearly <?php echo ($rating == 'on-track-yearly') ? 'active' : ''; ?> <?php echo (!is_editable('rating')) ? 'disabled' : ''; ?>" data-rating="on-track-yearly">
                                <i class="fas fa-calendar-check me-2"></i> On Track for Year
                            </div>
                            <div class="rating-pill severe-delay <?php echo ($rating == 'severe-delay') ? 'active' : ''; ?> <?php echo (!is_editable('rating')) ? 'disabled' : ''; ?>" data-rating="severe-delay">
                                <i class="fas fa-exclamation-triangle me-2"></i> Severe Delays
                            </div>
                            <div class="rating-pill not-started <?php echo ($rating == 'not-started' || !$rating) ? 'active' : ''; ?> <?php echo (!is_editable('rating')) ? 'disabled' : ''; ?>" data-rating="not-started">
                                <i class="fas fa-clock me-2"></i> Not Started
                            </div>
                        </div>
                        
                        <?php if ($program['is_assigned'] && !is_editable('rating')): ?>
                            <div class="form-text">Rating was set by an administrator and cannot be changed.</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- 1. Basic Information Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title m-0">Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="program_name" class="form-label">Program Name *</label>
                                <input type="text" class="form-control" id="program_name" name="program_name" required
                                        value="<?php echo htmlspecialchars($program['program_name']); ?>"
                                        <?php echo (!is_editable('program_name')) ? 'readonly' : ''; ?>>
                                <?php if ($program['is_assigned'] && !is_editable('program_name')): ?>
                                    <div class="form-text">Program name was set by an administrator and cannot be changed.</div>
                                <?php endif; ?>
                                
                                <?php if (isset($program_history['submissions']) && count($program_history['submissions']) > 1): ?>
                                    <?php
                                    // Get complete history of program name changes
                                    $name_history = get_field_edit_history($program_history['submissions'], 'program_name');
                                    ?>
                                    <?php
                                    // Show history panel if there is at least one entry in $name_history
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
                                <?php endif; ?>                            </div>
                            
                            <div class="mb-3">
                                <label for="brief_description" class="form-label">Brief Description</label>
                                <textarea class="form-control" id="brief_description" name="brief_description" rows="3" 
                                        placeholder="Provide a short summary of the program"
                                        <?php echo (!is_editable('brief_description')) ? 'readonly' : ''; ?>><?php echo htmlspecialchars($brief_description); ?></textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    A brief overview to help identify this program
                                </div>
                                <?php if ($program['is_assigned'] && !is_editable('brief_description')): ?>
                                    <div class="form-text">Brief description was set by an administrator and cannot be changed.</div>
                                <?php endif; ?>
                                
                                <?php if (isset($program_history['submissions']) && count($program_history['submissions']) > 1): ?>
                                    <?php
                                    // Get complete history of brief description changes
                                    $description_history = get_field_edit_history($program_history['submissions'], 'brief_description');
                                    
                                    if (!empty($description_history)):
                                    ?>
                                        <div class="d-flex align-items-center mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary field-history-toggle" 
                                                    data-history-target="briefDescriptionHistory">
                                                <i class="fas fa-history"></i> Show Description History
                                            </button>
                                        </div>
                                        <div id="briefDescriptionHistory" class="history-complete" style="display: none;">
                                            <h6 class="small text-muted mb-2">Brief Description History</h6>
                                            <ul class="history-list">
                                                <?php foreach($description_history as $idx => $item): ?>
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
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                            value="<?php echo get_field_value('start_date', $program['start_date'] ? date('Y-m-d', strtotime($program['start_date'])) : ''); ?>"
                                            <?php echo (!is_editable('timeline')) ? 'readonly' : ''; ?>>
                                    <?php if ($program['is_assigned'] && !is_editable('timeline')): ?>
                                        <div class="form-text">Start date was set by an administrator and cannot be changed.</div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                            value="<?php echo get_field_value('end_date', $program['end_date'] ? date('Y-m-d', strtotime($program['end_date'])) : ''); ?>"
                                            <?php echo (!is_editable('timeline')) ? 'readonly' : ''; ?>>
                                    <?php if ($program['is_assigned'] && !is_editable('timeline')): ?>
                                        <div class="form-text">End date was set by an administrator and cannot be changed.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                      <!-- 2. Program Targets Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title m-0">Program Targets</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Define one or more targets for this program, each with its own status description.
                            </p>
                            
                            <?php if (isset($program_history['submissions']) && count($program_history['submissions']) > 1): ?>
                                <?php
                                // Get complete history of program targets
                                $targets_history = get_field_edit_history($program_history['submissions'], 'targets');
                                
                                if (!empty($targets_history)):
                                ?>
                                    <div class="d-flex align-items-center mt-2 mb-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary field-history-toggle" 
                                                data-history-target="programTargetsHistory">
                                            <i class="fas fa-history"></i> Show Target History
                                        </button>
                                    </div>
                                    <div id="programTargetsHistory" class="history-complete" style="display: none;">
                                        <h6 class="small text-muted mb-2">Program Target History</h6>
                                        <ul class="history-list">
                                            <?php foreach($targets_history as $idx => $item): ?>
                                            <li class="history-list-item">
                                                <div class="history-list-value">
                                                    <?php 
                                                    if (is_array($item['value'])):
                                                        foreach($item['value'] as $target_idx => $target): 
                                                            echo '<strong>Target ' . ($target_idx + 1) . ':</strong> ' . 
                                                                 htmlspecialchars($target['target_text'] ?? $target['text'] ?? '') . '<br>';
                                                        endforeach;
                                                    else:
                                                        echo htmlspecialchars($item['value']);
                                                    endif;
                                                    ?>
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
                            
                            <div id="targets-container">
                                <?php 
                                $canEditTargets = is_editable('targets');
                                
                                foreach ($targets as $index => $target): 
                                    $target_text = $target['target_text'] ?? '';
                                    $status_description = $target['status_description'] ?? '';
                                    $canDelete = $index > 0; // Only allow deleting additional targets
                                ?>
                                <div class="target-entry">
                                    <?php if ($canDelete && $canEditTargets): ?>
                                    <button type="button" class="btn-close remove-target" aria-label="Remove target"></button>
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label class="form-label">Target <?php echo $index + 1; ?> *</label>
                                        <input type="text" class="form-control target-input" name="target_text[]" 
                                                value="<?php echo htmlspecialchars($target_text); ?>" 
                                                placeholder="Define a measurable target (e.g., 'Plant 100 trees')"
                                                <?php echo ($canEditTargets) ? '' : 'readonly'; ?>>
                                        <?php if (!$canEditTargets && $index === 0): ?>
                                        <div class="form-text">Targets were set by an administrator and cannot be changed.</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Status Description</label>
                                        <textarea class="form-control status-description" name="target_status_description[]" rows="2" 
                                                    placeholder="Describe the current status or progress toward this target"
                                                    <?php echo (is_editable('status_text')) ? '' : 'readonly'; ?>><?php echo htmlspecialchars($status_description); ?></textarea>
                                        <?php if (!is_editable('status_text') && $index === 0): ?>
                                        <div class="form-text">Status descriptions were set by an administrator and cannot be changed.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if ($canEditTargets): ?>
                            <button type="button" id="add-target-btn" class="btn btn-outline-secondary add-target-btn">
                                <i class="fas fa-plus-circle me-1"></i> Add Another Target
                            </button>
                            <?php endif; ?>
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
                                          placeholder="Add any additional remarks, challenges, or observations about this program..."
                                          <?php echo (is_editable('remarks')) ? '' : 'readonly'; ?>><?php echo htmlspecialchars($remarks); ?></textarea>
                                <?php if ($program['is_assigned'] && !is_editable('remarks')): ?>
                                    <div class="form-text">Remarks were set by an administrator and cannot be changed.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                              <!-- Form Actions -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <?php if ($is_draft): ?>
                                <button type="submit" name="save_draft" class="btn btn-secondary me-2">
                                    <i class="fas fa-save me-1"></i> Save Draft
                                </button>
                            <?php else: ?>
                                <button type="submit" name="save_draft" class="btn btn-secondary me-2">
                                    <i class="fas fa-save me-1"></i> Save as Draft
                                </button>
                                <button type="submit" name="submit_program" class="btn btn-primary">
                                    <i class="fas fa-check-circle me-1"></i> Update Program
                                </button>
                            <?php endif; ?>
                        </div>
                        <div>
                            <a href="view_programs.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>                    </div>
                </form>

<script>
function showToast(title, message, type = 'info', duration = 5000) {
    if (window.showToast) {
        window.showToast(title, message, type, duration);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Rating pills selection
    const ratingPills = document.querySelectorAll('.rating-pill:not(.disabled)');
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
                    <div class="form-text">Define a specific, measurable target for this program.</div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Status Description</label>
                    <textarea class="form-control status-description" name="target_status_description[]" rows="2" 
                              placeholder="Describe the current status or progress toward this target"></textarea>
                    <div class="form-text">Describe the current status or achievement toward this target.</div>
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
    }
    
    // Initialize existing remove buttons
    document.querySelectorAll('.remove-target').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.target-entry').remove();
            // Update target numbers after removing
            updateTargetNumbers();
        });
    });
    
    // Form validation
    document.getElementById('updateProgramForm').addEventListener('submit', function(e) {
        const programName = document.getElementById('program_name').value;
        const targetInputs = document.querySelectorAll('.target-input');
        let hasFilledTarget = false;
        
        // Validate program name
        if (!programName.trim()) {
            showToast('Validation Error', 'Please enter a program name.', 'danger');
            e.preventDefault();
            return false;
        }
        
        // For finalize/submit actions, validate at least one target
        if (e.submitter && (e.submitter.name === 'submit_program' || e.submitter.name === 'finalize_draft')) {
            targetInputs.forEach(input => {
                if (input.value.trim()) {
                    hasFilledTarget = true;
                }
            });
            
            if (!hasFilledTarget) {
                showToast('Validation Error', 'Please add at least one target for this program.', 'danger');
                e.preventDefault();
                return false;
            }
        }
        
        return true;
    });
});
</script>

<?php
// Include footer
require_once dirname(__DIR__, 2) . '/layouts/footer.php';
