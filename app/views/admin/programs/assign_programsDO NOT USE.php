<?php
/**
 * Admin Assign Programs
 * 
 * Allows administrators to assign programs to agencies using a wizard-like interface.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';
require_once ROOT_PATH . 'app/lib/numbering_helpers.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_program'])) {
    $program_name = trim($_POST['program_name']);
    $program_number = trim($_POST['program_number'] ?? '');
    $agency_id = intval($_POST['agency_id']);
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : NULL;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;
    $rating = isset($_POST['rating']) ? $_POST['rating'] : 'not-started';
    $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
    
    // Validation
    $errors = [];
    
    if (empty($program_name)) {
        $errors[] = "Program name is required";
    }
    
    // Validate program_number format if provided
    if (!empty($program_number) && !is_valid_program_number_format($program_number, false)) {
        $errors[] = get_program_number_format_error(false);
    }
    
    if (empty($agency_id)) {
        $errors[] = "Agency is required";
    }
    
    if (empty($errors)) {
        try {
            // Begin transaction
            $conn->begin_transaction();
            
            // Get sector_id based on agency_id
            $sector_query = "SELECT sector_id FROM users WHERE user_id = ?";
            $sector_stmt = $conn->prepare($sector_query);
            $sector_stmt->bind_param("i", $agency_id);
            $sector_stmt->execute();
            $sector_result = $sector_stmt->get_result();
            $sector_row = $sector_result->fetch_assoc();
            $sector_id = $sector_row['sector_id'];
            
            // Process edit permissions
            $edit_permissions = isset($_POST['edit_permissions']) ? $_POST['edit_permissions'] : [];
            
            // Combine permissions and default values in one JSON structure
            $program_settings = [
                'edit_permissions' => $edit_permissions
            ];
            
            $program_settings_json = json_encode($program_settings);
              // Insert program
            $stmt = $conn->prepare("INSERT INTO programs 
                (program_name, program_number, sector_id, owner_agency_id, start_date, end_date, is_assigned, created_by, edit_permissions, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, NOW(), NOW())");
            
            $admin_id = $_SESSION['user_id'];
            
            $stmt->bind_param("ssiissis", 
                $program_name, 
                $program_number,
                $sector_id, 
                $agency_id, 
                $start_date, 
                $end_date,
                $admin_id,
                $program_settings_json
            );
            
            $stmt->execute();
            $program_id = $conn->insert_id;
            
            // Get the current active reporting period
            $period_query = "SELECT period_id FROM reporting_periods WHERE status = 'active' ORDER BY end_date DESC LIMIT 1";
            $period_result = $conn->query($period_query);
            
            if ($period_result->num_rows > 0) {
                $period_row = $period_result->fetch_assoc();
                $period_id = $period_row['period_id'];
                
                // Process targets data from form
                $targets = [];
                if (isset($_POST['target_text']) && is_array($_POST['target_text'])) {
                    foreach ($_POST['target_text'] as $index => $text) {
                        if (!empty($text)) {
                            $targets[] = [
                                'target_text' => $text,
                                'status_description' => isset($_POST['status_description'][$index]) ? $_POST['status_description'][$index] : ''
                            ];
                        }
                    }
                }
                
                // If no targets were provided, add an empty one
                if (empty($targets)) {
                    $targets[] = [
                        'target_text' => '',
                        'status_description' => ''
                    ];
                }
                
                // Prepare content JSON with updated structure
                $content_data = [
                    'rating' => $rating,
                    'targets' => $targets,
                    'remarks' => $remarks
                ];
                
                $content_json = json_encode($content_data);
                
                // Create an initial draft submission record
                $submission_stmt = $conn->prepare("INSERT INTO program_submissions 
                    (program_id, period_id, submitted_by, status, content_json, submission_date, updated_at, is_draft) 
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW(), 1)");
                
                $submission_stmt->bind_param("iiiss", 
                    $program_id,
                    $period_id,
                    $admin_id,
                    $rating, // Use rating as status
                    $content_json
                );
                
                $submission_stmt->execute();
            }            // Create notification for the agency
            $notification_message = "New program '{$program_name}' has been assigned to your agency. Please review and submit it.";
            $action_url = APP_URL . '/app/views/agency/programs/program_details.php?id=' . $program_id;
            $notification_stmt = $conn->prepare("INSERT INTO notifications 
                (user_id, message, type, action_url, read_status) 
                VALUES (?, ?, 'program_assignment', ?, 0)");
                
            $notification_stmt->bind_param("iss", $agency_id, $notification_message, $action_url);
            $notification_stmt->execute();
              // Commit transaction
            $conn->commit();
            
            // Get agency name for logging
            $agency_query = "SELECT a.agency_name 
                           FROM users u 
                           JOIN agency a ON u.agency_id = a.agency_id 
                           WHERE u.user_id = ?";
            $agency_stmt = $conn->prepare($agency_query);
            $agency_stmt->bind_param("i", $agency_id);
            $agency_stmt->execute();
            $agency_result = $agency_stmt->get_result();
            $agency_name = $agency_result->num_rows > 0 ? $agency_result->fetch_assoc()['agency_name'] : 'Unknown Agency';
            
            // Log successful program assignment
            log_audit_action('assign_program', "Program Name: $program_name | Program ID: $program_id | Assigned to: $agency_name", 'success', $_SESSION['user_id']);
            
            // Success message
            $_SESSION['message'] = "Program successfully assigned to agency as a draft. The agency will need to review and submit it.";
            $_SESSION['message_type'] = "success";
            
            // Redirect to programs page
            header("Location: programs.php");
            exit;
              } catch (Exception $e) {
            // Roll back transaction on error
            $conn->rollback();
            
            // Log failed program assignment
            log_audit_action('assign_program_failed', "Program Name: $program_name | Error: " . $e->getMessage(), 'failure', $_SESSION['user_id']);
            
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Get all agencies with their sector information (including both agency and focal users)
$agency_query = "SELECT u.user_id, u.agency_name, s.sector_id, s.sector_name 
                FROM users u 
                JOIN sectors s ON u.sector_id = s.sector_id 
                WHERE u.role IN ('agency', 'focal') 
                ORDER BY u.agency_name";
$agencies_result = $conn->query($agency_query);
$agencies = [];

while ($row = $agencies_result->fetch_assoc()) {
    $agencies[] = $row;
}

// Set page title
$pageTitle = 'Assign Programs';

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/rating_utils.js'
];

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Assign Programs',
    'subtitle' => 'Create and assign programs to agencies step-by-step', // Updated subtitle
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
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-magic me-2"></i> Assign New Program Wizard
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Wizard Progress Indicator -->
                <div class="wizard-progress mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="step-indicator active" id="step-1-indicator">
                            <div class="step-number">1</div>
                            <div class="step-label">Basic Info</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-indicator" id="step-2-indicator">
                            <div class="step-number">2</div>
                            <div class="step-label">Details & Targets</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-indicator" id="step-3-indicator">
                            <div class="step-number">3</div>
                            <div class="step-label">Permissions & Review</div>
                        </div>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-primary" id="wizard-progress-bar" style="width: 33.33%;"></div>
                    </div>
                </div>
                
                <form method="POST" action="<?php echo view_url('admin', 'programs/assign_programs.php'); ?>" id="assignProgramForm">
                    <input type="hidden" id="rating" name="rating" value="<?php echo $_POST['rating'] ?? 'not-started'; ?>">

                    <!-- Step 1: Basic Information -->
                    <div class="wizard-step active" id="step-1">
                        <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Basic Program Information</h6>
                        <div class="mb-3">
                            <label for="program_name" class="form-label">Program Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="program_name" name="program_name" required 
                                  value="<?php echo isset($_POST['program_name']) ? htmlspecialchars($_POST['program_name']) : ''; ?>">
                            <div class="form-text">The name of the program as it will appear in reports and dashboards.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="program_number" class="form-label">Program Number</label>
                            <input type="text" class="form-control" id="program_number" name="program_number" 
                                  value="<?php echo isset($_POST['program_number']) ? htmlspecialchars($_POST['program_number']) : ''; ?>"
                                  pattern="[\w.]+" 
                                  title="Program number can contain letters, numbers, and dots"
                                  placeholder="e.g., 31.1, 31.2A, 31.25.6, 31.2A.3B">
                            <div class="form-text">Optional program identifier with flexible format (letters, numbers, dots).</div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="agency_id" class="form-label">Assign to Agency <span class="text-danger">*</span></label>
                                <select class="form-select" id="agency_id" name="agency_id" required>
                                    <option value="">Select Agency</option>
                                    <?php foreach ($agencies as $agency): ?>
                                        <option value="<?php echo $agency['user_id']; ?>"
                                            <?php echo (isset($_POST['agency_id']) && $_POST['agency_id'] == $agency['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($agency['agency_name']); ?> (<?php echo htmlspecialchars($agency['sector_name']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                      value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>">
                                <div class="form-text">When does/did the program start? (Optional)</div>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                      value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>">
                                <div class="form-text">When is the program expected to end? (Optional)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Program Details & Targets -->
                    <div class="wizard-step" id="step-2">
                        <h6 class="fw-bold mb-3"><i class="fas fa-tasks me-2"></i>Program Details & Targets</h6>
                        <!-- Program Rating -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Program Rating</h6>
                            <p class="text-muted mb-3">How would you rate the overall progress of this program?</p>
                            <div class="rating-pills">
                                <div class="rating-pill target-achieved <?php echo (isset($_POST['rating']) && $_POST['rating'] == 'target-achieved') ? 'active' : ''; ?>" data-rating="target-achieved">
                                    <i class="fas fa-check-circle me-2"></i> Monthly Target Achieved
                                </div>
                                <div class="rating-pill on-track-yearly <?php echo (isset($_POST['rating']) && $_POST['rating'] == 'on-track-yearly') ? 'active' : ''; ?>" data-rating="on-track-yearly">
                                    <i class="fas fa-calendar-check me-2"></i> On Track for Year
                                </div>
                                <div class="rating-pill severe-delay <?php echo (isset($_POST['rating']) && $_POST['rating'] == 'severe-delay') ? 'active' : ''; ?>" data-rating="severe-delay">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Severe Delays
                                </div>
                                <div class="rating-pill not-started <?php echo (!isset($_POST['rating']) || $_POST['rating'] == 'not-started') ? 'active' : ''; ?>" data-rating="not-started">
                                    <i class="fas fa-clock me-2"></i> Not Started
                                </div>
                            </div>
                        </div>
                            
                        <!-- Program Targets -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Program Targets</h6>
                            <p class="text-muted mb-3">Define one or more targets for this program. (Optional)</p>
                            <div id="targets-container">
                                <div class="target-entry">
                                    <div class="mb-3">
                                        <label class="form-label">Target 1</label>
                                        <input type="text" class="form-control target-input" name="target_text[]" 
                                              placeholder="Define a measurable target (e.g., 'Plant 100 trees')"
                                              value="<?php echo htmlspecialchars($_POST['target_text'][0] ?? ''); ?>">
                                        <div class="form-text">Define a specific, measurable target for this program.</div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Status Description</label>
                                        <textarea class="form-control status-description" name="status_description[]" rows="2" 
                                                  placeholder="Describe the current status or progress toward this target"><?php echo htmlspecialchars($_POST['status_description'][0] ?? ''); ?></textarea>
                                        <div class="form-text">Describe the current status or achievement toward this target.</div>
                                    </div>
                                </div>
                                
                                <?php
                                if (isset($_POST['target_text']) && is_array($_POST['target_text']) && count($_POST['target_text']) > 1) {
                                    for ($i = 1; $i < count($_POST['target_text']); $i++) {
                                        if (!empty($_POST['target_text'][$i]) || !empty($_POST['status_description'][$i])) { // Show if either field has data
                                            echo '<div class="target-entry">';
                                            echo '<button type="button" class="btn-close remove-target" aria-label="Remove target"></button>';
                                            echo '<div class="mb-3">';
                                            echo '<label class="form-label">Target ' . ($i + 1) . '</label>';
                                            echo '<input type="text" class="form-control target-input" name="target_text[]" '; 
                                            echo 'value="' . htmlspecialchars($_POST['target_text'][$i]) . '" '; 
                                            echo 'placeholder="Define a measurable target">';
                                            echo '<div class="form-text">Define a specific, measurable target for this program.</div>';
                                            echo '</div>';
                                            echo '<div class="mb-2">';
                                            echo '<label class="form-label">Status Description</label>';
                                            echo '<textarea class="form-control status-description" name="status_description[]" rows="2" '; 
                                            echo 'placeholder="Describe the current status or progress toward this target">' . htmlspecialchars($_POST['status_description'][$i] ?? '') . '</textarea>';
                                            echo '<div class="form-text">Describe the current status or achievement toward this target.</div>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                    }
                                }
                                ?>
                            </div>
                            <button type="button" id="add-target-btn" class="btn btn-outline-secondary add-target-btn">
                                <i class="fas fa-plus-circle me-1"></i> Add Another Target
                            </button>
                        </div>
                        
                        <!-- Remarks -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Additional Remarks</h6>
                            <div class="mb-3">
                                <label for="remarks" class="form-label">Remarks (Optional)</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                         placeholder="Enter any additional notes or context about this program"><?php echo htmlspecialchars($_POST['remarks'] ?? ''); ?></textarea>
                                <div class="form-text">Any additional information that doesn't fit elsewhere.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Permissions & Review -->
                    <div class="wizard-step" id="step-3">
                        <h6 class="fw-bold mb-3"><i class="fas fa-user-shield me-2"></i>Agency Permissions & Review</h6>
                        <!-- Edit Permissions -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Agency Edit Permissions</h6>
                            <p class="text-muted">Control what the assigned agency can edit for this program.</p>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="edit_program_name_perm" name="edit_permissions[]" value="program_name" <?php echo (isset($_POST['edit_permissions']) && in_array('program_name', $_POST['edit_permissions'])) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_program_name_perm">Agency can edit Program Name</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="edit_brief_description" name="edit_permissions[]" value="brief_description" <?php echo (!isset($_POST['edit_permissions']) || (isset($_POST['edit_permissions']) && in_array('brief_description', $_POST['edit_permissions']))) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_brief_description">Agency can edit Brief Description</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="edit_targets" name="edit_permissions[]" value="targets" <?php echo (!isset($_POST['edit_permissions']) || (isset($_POST['edit_permissions']) && in_array('targets', $_POST['edit_permissions']))) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_targets">Agency can edit Targets</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="edit_status_text" name="edit_permissions[]" value="status_text" <?php echo (!isset($_POST['edit_permissions']) || (isset($_POST['edit_permissions']) && in_array('status_text', $_POST['edit_permissions']))) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_status_text">Agency can edit Status Descriptions</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="edit_timeline" name="edit_permissions[]" value="timeline" <?php echo (isset($_POST['edit_permissions']) && in_array('timeline', $_POST['edit_permissions'])) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_timeline">Agency can edit Timeline (Start/End Dates)</label>
                            </div>
                             <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit_remarks" name="edit_permissions[]" value="remarks" <?php echo (!isset($_POST['edit_permissions']) || (isset($_POST['edit_permissions']) && in_array('remarks', $_POST['edit_permissions']))) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_remarks">Agency can edit Remarks</label>
                            </div>
                        </div>

                        <!-- Review Summary -->
                        <div class="review-summary mt-4 pt-3 border-top">
                            <h6 class="fw-bold mb-3"><i class="fas fa-clipboard-check me-2"></i>Review Program Assignment</h6>
                            <div class="alert alert-info"><i class="fas fa-info-circle me-1"></i> Please review all information before assigning the program.</div>
                            <div class="row">                                <div class="col-md-6">
                                    <div class="review-section mb-3">
                                        <h6 class="text-muted mb-1">Program Name:</h6>
                                        <p class="mb-0" id="review-program-name">-</p>
                                    </div>
                                    <div class="review-section mb-3">
                                        <h6 class="text-muted mb-1">Program Number:</h6>
                                        <p class="mb-0" id="review-program-number">-</p>
                                    </div>
                                    <div class="review-section mb-3">
                                        <h6 class="text-muted mb-1">Assigned Agency:</h6>
                                        <p class="mb-0" id="review-agency">-</p>
                                    </div>
                                    <div class="review-section mb-3">
                                        <h6 class="text-muted mb-1">Timeline:</h6>
                                        <p class="mb-0" id="review-timeline">-</p>
                                    </div>
                                    <div class="review-section mb-3">
                                        <h6 class="text-muted mb-1">Program Rating:</h6>
                                        <p class="mb-0" id="review-rating">-</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="review-section mb-3">
                                        <h6 class="text-muted mb-1">Remarks:</h6>
                                        <p class="mb-0" id="review-remarks" style="white-space: pre-wrap;">-</p>
                                    </div>
                                    <div class="review-section mb-3">
                                        <h6 class="text-muted mb-1">Agency Edit Permissions:</h6>
                                        <ul class="list-unstyled mb-0" id="review-permissions"><li>-</li></ul>
                                    </div>
                                </div>
                            </div>
                            <div class="review-section mt-3">
                                <h6 class="text-muted mb-2">Targets:</h6>
                                <div id="review-targets">
                                    <table class="table table-sm table-bordered review-target-table">
                                        <thead class="table-light">
                                            <tr><th>Target</th><th>Status Description</th></tr>
                                        </thead>
                                        <tbody>
                                            <!-- rows injected by JS -->
                                            <tr><td colspan="2" class="text-muted text-center">No targets specified.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wizard Navigation -->
                    <div class="wizard-navigation mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="display: none;">
                                <i class="fas fa-arrow-left me-2"></i> Previous
                            </button>
                            <div class="ms-auto">
                                <button type="button" class="btn btn-primary" id="nextBtn">
                                    Next <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                <button type="submit" name="assign_program" class="btn btn-success" id="assignProgramBtn" style="display: none;">
                                    <i class="fas fa-paper-plane me-1"></i> Assign Program
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Wizard CSS (copied from agency/create_program.php and adapted) -->
<style>
.wizard-progress {
    margin-bottom: 2rem;
}
.step-indicator {
    text-align: center;
    position: relative;
    flex: 1;
}
.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}
.step-indicator.active .step-number {
    background-color: #0d6efd; /* Primary color */
    color: white;
}
.step-indicator.completed .step-number {
    background-color: #198754; /* Success color */
    color: white;
}
.step-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
}
.step-indicator.active .step-label {
    color: #0d6efd;
    font-weight: 600;
}
.step-indicator.completed .step-label {
    color: #198754;
    font-weight: 600;
}
.step-line {
    height: 2px;
    background-color: #e9ecef;
    flex: 1;
    margin: 0 15px;
    margin-top: 20px; /* Adjusted for alignment */
    transition: all 0.3s ease;
}
.step-line.completed {
    background-color: #198754;
}
.wizard-step {
    display: none;
    animation: fadeIn 0.3s ease-in-out;
}
.wizard-step.active {
    display: block;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.review-section {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.75rem;
    margin-bottom: 0.75rem;
}
.review-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}
.review-target-table th, .review-target-table td {
    font-size: 0.9rem;
}
.target-entry {
    position: relative;
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
    background-color: #f8f9fa;
}
.target-entry .btn-close {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
}
/* Styles for rating pills are assumed to be in a global CSS or already handled */
.wizard-content {
    display: none;
}

.wizard-content.active {
    display: block;
}

.progress-container {
    margin-bottom: 20px;
}

.progress-bar {
    display: flex;
    justify-content: space-between;
    list-style-type: none;
    padding: 0;
    margin: 0;
    border-bottom: 2px solid #ccc; /* Add a line under the steps */
}

.progress-step {
    text-align: center;
    flex-grow: 1;
    padding-bottom: 10px; /* Space for the step name */
    position: relative; /* For the dot */
    color: #ccc; /* Default color for inactive steps */
}

.progress-step.active {
    font-weight: bold;
    color: #007bff; /* Active step color */
    border-bottom: 2px solid #007bff; /* Highlight active step */
    margin-bottom: -2px; /* Align with the container border */
}
    
.progress-step.completed {
    color: #28a745; /* Completed step color */
    border-bottom: 2px solid #28a745; /* Highlight completed step */
    margin-bottom: -2px; /* Align with the container border */
}

.progress-step .dot {
    display: block;
    width: 10px;
    height: 10px;
    background-color: #ccc; /* Default dot color */
    border-radius: 50%;
    margin: 0 auto 5px; /* Center the dot and space from text */
}

.progress-step.active .dot {
    background-color: #007bff; /* Active dot color */
}
    
.progress-step.completed .dot {
    background-color: #28a745; /* Completed dot color */
}

.btn-wizard {
    margin-top: 10px;
}
.summary-section {
    margin-bottom: 15px;
}
.summary-section h5 {
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
    margin-bottom: 10px;
}
.rating-pills {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}
.rating-pills .rating-pill {
    padding: 8px 15px;
    border: 1px solid #ccc;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.rating-pills .rating-pill.selected {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}
.target-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.target-item input {
    flex-grow: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Wizard state
    let currentStep = 1;
    const totalSteps = 3;
    
    // Elements
    const wizardForm = document.getElementById('assignProgramForm');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const assignProgramBtn = document.getElementById('assignProgramBtn');
    const progressBar = document.getElementById('wizard-progress-bar');
    const ratingInput = document.getElementById('rating'); // Already exists for rating pills

    // Attach submit handler to the form itself for more robust handling
    if (wizardForm) {
        wizardForm.addEventListener('submit', function(event) {
            // Check if the submission was triggered by the assignProgramBtn
            // document.activeElement might be the button if it was clicked.
            const clickedButton = document.activeElement;
            
            if (clickedButton && clickedButton.id === 'assignProgramBtn') {
                // Ensure the 'assign_program' signal is present in the form data
                // This is crucial because a direct form.submit() call might not include button names.
                // However, if the event is a default submit from the button, its name should be included.
                // To be safe, especially if we were to add event.preventDefault() and form.submit() later,
                // let's ensure the flag is there.

                let hiddenSubmitFlag = wizardForm.querySelector('input[type="hidden"][name="assign_program"]');
                if (!hiddenSubmitFlag) {
                    // If the button itself has name="assign_program", this hidden input might be redundant
                    // or could conflict if not handled carefully.
                    // For now, let's assume the button's name should be sent.
                    // If issues persist, this is where we'd add the hidden input.
                }
                // No event.preventDefault() here, let the natural submit happen for now.
                // The issue might be elsewhere if the button's name isn't being sent.
            }
        });
    }


    // Rating pills selection (existing logic)
    const ratingPills = document.querySelectorAll('.rating-pill');
    ratingPills.forEach(pill => {
        pill.addEventListener('click', function() {
            ratingPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            ratingInput.value = this.getAttribute('data-rating');
        });
    });

    // Add target functionality (existing logic, slightly adapted)
    const addTargetBtn = document.getElementById('add-target-btn');
    const targetsContainer = document.getElementById('targets-container');
    let highestTargetNumber = document.querySelectorAll('.target-entry').length;

    function updateTargetNumbers() {
        const targetEntries = targetsContainer.querySelectorAll('.target-entry');
        targetEntries.forEach((entry, index) => {
            const label = entry.querySelector('.form-label:first-child'); // Target the first label for numbering
            if (label) {
                label.textContent = `Target ${index + 1}` + (entry.querySelector('.target-input[required]') ? ' *' : '');
            }
        });
    }

    if(addTargetBtn) {
        addTargetBtn.addEventListener('click', function() {
            highestTargetNumber = targetsContainer.querySelectorAll('.target-entry').length + 1;
            const targetEntry = document.createElement('div');
            targetEntry.className = 'target-entry';
            const newIndex = highestTargetNumber -1; // for array indexing if needed for POST

            targetEntry.innerHTML = `
                <button type="button" class="btn-close remove-target" aria-label="Remove target"></button>
                <div class="mb-3">
                    <label class="form-label">Target ${highestTargetNumber}</label>
                    <input type="text" class="form-control target-input" name="target_text[]" 
                           placeholder="Define a measurable target">
                    <div class="form-text">Define a specific, measurable target for this program.</div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Status Description</label>
                    <textarea class="form-control status-description" name="status_description[]" rows="2" 
                              placeholder="Describe the current status or progress toward this target"></textarea>
                    <div class="form-text">Describe the current status or achievement toward this target.</div>
                </div>
            `;
            targetsContainer.appendChild(targetEntry);
            targetEntry.querySelector('.remove-target').addEventListener('click', function() {
                this.closest('.target-entry').remove();
                updateTargetNumbers();
            });
            updateTargetNumbers(); // Renumber after adding
        });
    }

    document.querySelectorAll('.remove-target').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.target-entry').remove();
            updateTargetNumbers();
        });
    });

    function showStep(step) {
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
        document.getElementById(`step-${step}`).classList.add('active');
        updateStepIndicators(step);
        updateNavigationButtons(step);
        updateProgressBar();
        if (step === totalSteps) {
            updateReviewSummary();
        }
    }

    function updateStepIndicators(step) {
        for (let i = 1; i <= totalSteps; i++) {
            const indicator = document.getElementById(`step-${i}-indicator`);
            const line = indicator.nextElementSibling;
            indicator.classList.remove('active', 'completed');
            if (line && line.classList.contains('step-line')) line.classList.remove('completed');

            if (i < step) {
                indicator.classList.add('completed');
                if (line && line.classList.contains('step-line')) line.classList.add('completed');
            } else if (i === step) {
                indicator.classList.add('active');
            }
        }
    }

    function updateNavigationButtons(step) {
        prevBtn.style.display = step > 1 ? 'inline-block' : 'none';
        nextBtn.style.display = step < totalSteps ? 'inline-block' : 'none';
        assignProgramBtn.style.display = step === totalSteps ? 'inline-block' : 'none';
    }

    function updateProgressBar() {
        const progress = ((currentStep -1) / (totalSteps-1)) * 100;
        progressBar.style.width = `${progress}%`;
         // Special case for first step to show some progress
        if (currentStep === 1) progressBar.style.width = '33.33%';
        else if (currentStep === totalSteps) progressBar.style.width = '100%';
        else progressBar.style.width = `${(currentStep / totalSteps) * 100}%`;
    }

    function collectFormData() {
        const formData = new FormData(wizardForm);
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (key.endsWith('[]')) { // Handle array fields like targets and permissions
                if (!data[key.slice(0,-2)]) data[key.slice(0,-2)] = [];
                data[key.slice(0,-2)].push(value);
            } else {
                data[key] = value;
            }
        }
        return data;
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'Not set';
        try {
            const date = new Date(dateStr + 'T00:00:00'); // Ensure local timezone interpretation
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        } catch (e) {
            return 'Invalid Date';
        }
    }
    
    function escapeHtml(text) {
        if (typeof text !== 'string') {
            return '';
        }
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }    function updateReviewSummary() {
        const data = collectFormData();
        document.getElementById('review-program-name').textContent = data.program_name || '-';
        document.getElementById('review-program-number').textContent = data.program_number || 'Not specified';
        const agencySelect = document.getElementById('agency_id');
        document.getElementById('review-agency').textContent = agencySelect.options[agencySelect.selectedIndex]?.text || '-';
        
        let timeline = 'Not set';
        if (data.start_date && data.end_date) timeline = `${formatDate(data.start_date)} - ${formatDate(data.end_date)}`;
        else if (data.start_date) timeline = `Starts: ${formatDate(data.start_date)}`;
        else if (data.end_date) timeline = `Ends: ${formatDate(data.end_date)}`;
        document.getElementById('review-timeline').textContent = timeline;

        const activeRatingPill = document.querySelector('.rating-pill.active');
        document.getElementById('review-rating').textContent = activeRatingPill ? activeRatingPill.textContent.trim() : (data.rating || 'Not Started');
        
        document.getElementById('review-remarks').textContent = data.remarks || 'No remarks provided.';

        const permissionsList = document.getElementById('review-permissions');
        permissionsList.innerHTML = ''; // Clear previous
        const selectedPermissions = data.edit_permissions || [];
        if (selectedPermissions.length > 0) {
            selectedPermissions.forEach(perm => {
                const li = document.createElement('li');
                // Get label text for permission
                const permLabel = document.querySelector(`label[for^=\"edit_${perm.replace(/_perm$/, '')}\"]`);
                li.textContent = permLabel ? permLabel.textContent.replace('Agency can edit ', '') : perm;
                permissionsList.appendChild(li);
            });
        } else {
            permissionsList.innerHTML = '<li>Agency cannot edit any fields.</li>';
        }

        const reviewTargetsTableBody = document.querySelector('#review-targets tbody');
        reviewTargetsTableBody.innerHTML = ''; // Clear previous
        const targets = data.target_text || [];
        const statuses = data.status_description || [];
        if (targets.length > 0 && targets.some(t => t.trim() !== '')) {
            targets.forEach((target, index) => {
                if (target.trim() !== '' || (statuses[index] && statuses[index].trim() !== '')) {
                    const row = reviewTargetsTableBody.insertRow();
                    row.insertCell().textContent = escapeHtml(target.trim() || '-');
                    row.insertCell().textContent = escapeHtml(statuses[index] ? statuses[index].trim() : '-');
                }
            });
        } else {
            reviewTargetsTableBody.innerHTML = '<tr><td colspan="2" class="text-muted text-center">No targets specified.</td></tr>';
        }
    }

    function validateStep(step) {
        const stepElement = document.getElementById(`step-${step}`);
        const requiredFields = stepElement.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                alert(`${field.previousElementSibling?.textContent?.replace('*', '').trim() || field.name} is required.`);
                field.focus();
                isValid = false;
                return; // exit forEach early
            }
        });
        if (!isValid) return false;

        if (step === 1) { // Basic Info
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                alert('End date cannot be before start date.');
                document.getElementById('end_date').focus();
                isValid = false;
            }
        }
        // No specific validation for step 2 (Details & Targets) or step 3 (Permissions & Review) beyond required fields
        return isValid;
    }

    if(nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        });
    }

    if(prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });
    }

    if(assignProgramBtn && wizardForm) {
        assignProgramBtn.addEventListener('click', function(event) {
            // This listener is primarily for any pre-submission JS logic if needed.
            // The actual submission is handled by the button's type="submit".

            // If we find that the button's name="assign_program" is NOT being sent,
            // then we would do the preventDefault() and manual submission with hidden input here.

            // For now, let's test if simply ensuring the button is clicked is enough.
            // The problem description suggests the form *is* submitting (page refresh).
            // So, the issue is likely that $_POST['assign_program'] is not set on the server.

            // Let's try the explicit handling:
            event.preventDefault(); // Stop the default submit action of the button

            // Optional: final validation pass
            // if (!validateStep(1) || !validateStep(2) || !validateStep(3)) {
            //     alert('Please ensure all required fields are filled correctly across all steps.');
            //     // Show the first invalid step
            //     if (!validateStep(1)) showStep(1);
            //     else if (!validateStep(2)) showStep(2);
            //     else if (!validateStep(3)) showStep(3); // Should update review summary first
            //     return;
            // }

            let hiddenSubmitFlag = wizardForm.querySelector('input[type="hidden"][name="assign_program_hidden_trigger"]');
            if (!hiddenSubmitFlag) {
                hiddenSubmitFlag = document.createElement('input');
                hiddenSubmitFlag.type = 'hidden';
                // We need the PHP to see $_POST['assign_program']
                // The button itself is <button type="submit" name="assign_program" ...>
                // If we preventDefault() and call wizardForm.submit(), the button's name is NOT sent.
                // So, we MUST add a hidden input with that name.
                hiddenSubmitFlag.name = 'assign_program'; 
                hiddenSubmitFlag.value = 'true'; // Value can be anything, isset() just checks for presence
                wizardForm.appendChild(hiddenSubmitFlag);
            } else {
                // If it somehow already exists (e.g. from a previous click attempt)
                hiddenSubmitFlag.value = 'true';
            }
            
            // Submit the form programmatically
            wizardForm.submit();
        });
    }

    // Initialize first step
    showStep(currentStep);
    updateButtons();
    updateProgressBar();
});
</script>
</main>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>


