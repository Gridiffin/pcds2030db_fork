<?php
/**
 * Admin Assign Programs
 * 
 * Allows administrators to assign programs to agencies.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin_functions.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_program'])) {
    $program_name = trim($_POST['program_name']);
    $description = trim($_POST['description']);
    $agency_id = intval($_POST['agency_id']);
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : NULL;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;
    
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
            
            // Collect default values for the fields
            $default_values = [];
            if (!in_array('target', $edit_permissions) && !empty($_POST['target_value'])) {
                $default_values['target'] = $_POST['target_value'];
            }
            
            if (!in_array('status', $edit_permissions) && !empty($_POST['status_value'])) {
                $default_values['status'] = $_POST['status_value'];
            }
            
            if (!in_array('status_text', $edit_permissions) && !empty($_POST['status_text_value'])) {
                $default_values['status_text'] = $_POST['status_text_value'];
            }
            
            // Combine permissions and default values in one JSON structure
            $program_settings = [
                'edit_permissions' => $edit_permissions,
                'default_values' => $default_values
            ];
            
            $program_settings_json = json_encode($program_settings);
            
            // Insert program
            $stmt = $conn->prepare("INSERT INTO programs 
                (program_name, description, sector_id, owner_agency_id, start_date, end_date, is_assigned, created_by, edit_permissions, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, NOW(), NOW())");
            
            $admin_id = $_SESSION['user_id'];
            
            $stmt->bind_param("ssiissis", 
                $program_name, 
                $description, 
                $sector_id, 
                $agency_id, 
                $start_date, 
                $end_date,
                $admin_id,
                $program_settings_json
            );
            
            $stmt->execute();
            $program_id = $conn->insert_id;
            
            // Create notification for the agency
            $notification_message = "New program '{$program_name}' has been assigned to your agency.";
            $notification_stmt = $conn->prepare("INSERT INTO notifications 
                (user_id, message, type, reference_id, reference_type, created_at, read_status) 
                VALUES (?, ?, 'program_assignment', ?, 'program', NOW(), 0)");
                
            $notification_stmt->bind_param("isi", 
                $agency_id, 
                $notification_message,
                $program_id
            );
            
            $notification_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Success message
            $_SESSION['message'] = "Program successfully assigned to agency.";
            $_SESSION['message_type'] = "success";
            
            // Redirect to programs page
            header("Location: programs.php");
            exit;
            
        } catch (Exception $e) {
            // Roll back transaction on error
            $conn->rollback();
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

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Set up the dashboard header variables
$title = "Assign Programs";
$subtitle = "Create and assign programs to agencies";
$headerStyle = 'light';
$actions = [
    [
        'url' => 'programs.php',
        'text' => 'Back to Programs',
        'icon' => 'fas fa-arrow-left',
        'class' => 'btn-outline-secondary'
    ]
];

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';
?>

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
                
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="program_name" class="form-label">Program Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="program_name" name="program_name" required 
                                   value="<?php echo isset($_POST['program_name']) ? htmlspecialchars($_POST['program_name']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        
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
                        
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-12 mt-4">
                            <h5 class="mb-3">Default Values & Permissions</h5>
                            <p class="text-muted">Set default values for fields and control which ones the agency can edit.</p>
                            
                            <div class="row g-3">
                                <div class="col-md-12 mb-3">
                                    <label for="target_value" class="form-label">Target</label>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" id="target_value" name="target_value" 
                                               placeholder="Define a measurable target for this program"
                                               value="<?php echo isset($_POST['target_value']) ? htmlspecialchars($_POST['target_value']) : ''; ?>">
                                        <div class="input-group-text">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="edit_target" name="edit_permissions[]" value="target" checked>
                                                <label class="form-check-label" for="edit_target">Agency can edit</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="status_value" class="form-label">Status</label>
                                    
                                    <!-- Status Legend -->
                                    <div class="mb-2 d-flex flex-wrap gap-2">
                                        <span class="badge bg-success me-1">Monthly Target Achieved</span>
                                        <span class="badge bg-warning me-1">On Track for Year</span>
                                        <span class="badge bg-danger me-1">Severe Delays</span>
                                        <span class="badge bg-secondary me-1">Not Started</span>
                                    </div>
                                    
                                    <div class="input-group mb-2">
                                        <select class="form-select" id="status_value" name="status_value">
                                            <option value="target-achieved" <?php echo (isset($_POST['status_value']) && $_POST['status_value'] == 'target-achieved') ? 'selected' : ''; ?>>
                                                <span class="status-indicator success"></span>Monthly Target Achieved
                                            </option>
                                            <option value="on-track-yearly" <?php echo (isset($_POST['status_value']) && $_POST['status_value'] == 'on-track-yearly') ? 'selected' : ''; ?>>
                                                <span class="status-indicator warning"></span>On Track for Year
                                            </option>
                                            <option value="severe-delay" <?php echo (isset($_POST['status_value']) && $_POST['status_value'] == 'severe-delay') ? 'selected' : ''; ?>>
                                                <span class="status-indicator danger"></span>Severe Delays
                                            </option>
                                            <option value="not-started" <?php echo (isset($_POST['status_value']) && $_POST['status_value'] == 'not-started') ? 'selected' : ''; ?> selected>
                                                <span class="status-indicator secondary"></span>Not Started
                                            </option>
                                        </select>
                                        <div class="input-group-text">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="edit_status" name="edit_permissions[]" value="status" checked>
                                                <label class="form-check-label" for="edit_status">Agency can edit</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Status Description Field -->
                                <div class="col-md-12 mb-3">
                                    <label for="status_text_value" class="form-label">Status Description</label>
                                    <div class="input-group mb-2">
                                        <textarea class="form-control" id="status_text_value" name="status_text_value" rows="2" placeholder="Describe the current status in detail"><?php echo isset($_POST['status_text_value']) ? htmlspecialchars($_POST['status_text_value']) : ''; ?></textarea>
                                        <div class="input-group-text">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="edit_status_text" name="edit_permissions[]" value="status_text" checked>
                                                <label class="form-check-label" for="edit_status_text">Agency can edit</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="edit_description" name="edit_permissions[]" value="description" checked>
                                        <label class="form-check-label" for="edit_description">Agency can edit Description</label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_timeline" name="edit_permissions[]" value="timeline">
                                        <label class="form-check-label" for="edit_timeline">Agency can edit Timeline (Start/End Dates)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mt-4">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="programs.php" class="btn btn-outline-secondary">Cancel</a>
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

<?php
// Include footer
require_once '../layouts/footer.php';
?>