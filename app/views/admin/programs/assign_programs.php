<?php
/**
 * Admin Assign Programs
 * 
 * Allows administrators to assign programs to agencies.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_program'])) {
    $program_name = trim($_POST['program_name']);
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
                (program_name, sector_id, owner_agency_id, start_date, end_date, is_assigned, created_by, edit_permissions, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 1, ?, ?, NOW(), NOW())");
            
            $admin_id = $_SESSION['user_id'];
            
            $stmt->bind_param("siissis", 
                $program_name, 
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
            $agency_query = "SELECT agency_name FROM users WHERE user_id = ?";
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

// Get all agencies with their sector information
$agency_query = "SELECT u.user_id, u.agency_name, s.sector_id, s.sector_name 
                FROM users u 
                JOIN sectors s ON u.sector_id = s.sector_id 
                WHERE u.role = 'agency' 
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
    'subtitle' => 'Create and assign programs to agencies',
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
                <h5 class="card-title m-0">Assign New Program</h5>
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
                
                <form method="POST" action="<?php echo view_url('admin', 'programs/assign_programs.php'); ?>" id="assignProgramForm">
                    <div class="row g-3">
                        <!-- Basic Information -->
                        <div class="col-md-12 mb-4">
                            <h6 class="fw-bold mb-3">Basic Information</h6>
                            <div class="mb-3">
                                <label for="program_name" class="form-label">Program Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="program_name" name="program_name" required 
                                      value="<?php echo isset($_POST['program_name']) ? htmlspecialchars($_POST['program_name']) : ''; ?>">
                                <div class="form-text">The name of the program as it will appear in reports and dashboards.</div>
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
                                    <div class="form-text">When does/did the program start?</div>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                          value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>">
                                    <div class="form-text">When is the program expected to end?</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Program Rating -->
                        <div class="col-md-12 mb-4">
                            <h6 class="fw-bold mb-3">Program Rating</h6>
                            <p class="text-muted mb-3">
                                How would you rate the overall progress of this program?
                            </p>
                            
                            <input type="hidden" id="rating" name="rating" value="<?php echo $_POST['rating'] ?? 'not-started'; ?>">
                            
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
                        <div class="col-md-12 mb-4">
                            <h6 class="fw-bold mb-3">Program Targets</h6>
                            <p class="text-muted mb-3">
                                Define one or more targets for this program, each with its own status description.
                            </p>
                            
                            <div id="targets-container">
                                <div class="target-entry">
                                    <div class="mb-3">
                                        <label class="form-label">Target 1 *</label>
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
                                // Restore additional targets from previous form submission if any
                                if (isset($_POST['target_text']) && is_array($_POST['target_text']) && count($_POST['target_text']) > 1) {
                                    for ($i = 1; $i < count($_POST['target_text']); $i++) {
                                        if (!empty($_POST['target_text'][$i])) {
                                            echo '<div class="target-entry">';
                                            echo '<button type="button" class="btn-close remove-target" aria-label="Remove target"></button>';
                                            echo '<div class="mb-3">';
                                            echo '<label class="form-label">Target ' . ($i + 1) . ' *</label>';
                                            echo '<input type="text" class="form-control target-input" name="target_text[]" ';
                                            echo 'value="' . htmlspecialchars($_POST['target_text'][$i]) . '" ';
                                            echo 'placeholder="Define a measurable target (e.g., \'Plant 100 trees\')">';
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
                        <div class="col-md-12 mb-4">
                            <h6 class="fw-bold mb-3">Additional Remarks</h6>
                            <div class="mb-3">
                                <label for="remarks" class="form-label">Remarks (Optional)</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                         placeholder="Enter any additional notes or context about this program"><?php echo htmlspecialchars($_POST['remarks'] ?? ''); ?></textarea>
                                <div class="form-text">Any additional information that doesn't fit elsewhere.</div>
                            </div>
                        </div>
                        
                        <!-- Edit Permissions -->
                        <div class="col-md-12 mb-4">
                            <h6 class="fw-bold mb-3">Agency Edit Permissions</h6>
                            <div class="mb-3">
                                                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="edit_targets" name="edit_permissions[]" value="targets" checked>
                                    <label class="form-check-label" for="edit_targets">Agency can edit Targets</label>
                                </div>                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="edit_status_text" name="edit_permissions[]" value="status_text" checked>
                                    <label class="form-check-label" for="edit_status_text">Agency can edit Status Descriptions</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="edit_brief_description" name="edit_permissions[]" value="brief_description" checked>
                                    <label class="form-check-label" for="edit_brief_description">Agency can edit Brief Description</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="edit_timeline" name="edit_permissions[]" value="timeline">
                                    <label class="form-check-label" for="edit_timeline">Agency can edit Timeline (Start/End Dates)</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="col-md-12 mt-4">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="programs.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Cancel
                                </a>
                                <button type="submit" name="assign_program" class="btn btn-primary">
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
    const targetsContainer = document.getElementById('targets-container');
    
    // Keep track of the highest target number used
    let highestTargetNumber = document.querySelectorAll('.target-entry').length;
    
    // Function to update target numbers sequentially
    function updateTargetNumbers() {
        const targetEntries = document.querySelectorAll('.target-entry');
        targetEntries.forEach((entry, index) => {
            const label = entry.querySelector('.form-label');
            if (label) {
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
                <textarea class="form-control status-description" name="status_description[]" rows="2" 
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
    
    // Initialize existing remove buttons
    document.querySelectorAll('.remove-target').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.target-entry').remove();
            // Update target numbers after removing
            updateTargetNumbers();
        });
    });
    
    // Form validation
    document.getElementById('assignProgramForm').addEventListener('submit', function(e) {
        const programName = document.getElementById('program_name').value;
        const agencyId = document.getElementById('agency_id').value;
        const targetInputs = document.querySelectorAll('.target-input');
        let hasFilledTarget = false;
        
        // Validate program name
        if (!programName.trim()) {
            alert('Please enter a program name.');
            e.preventDefault();
            return false;
        }
        
        // Validate agency selection
        if (!agencyId) {
            alert('Please select an agency.');
            e.preventDefault();
            return false;
        }
        
        // Not requiring targets for admin assignments - they can be added later by agency
        
        return true;
    });
    
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
});
</script>
</main>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>


