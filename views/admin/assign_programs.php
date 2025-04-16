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
            
            // Insert program
            $stmt = $conn->prepare("INSERT INTO programs 
                (program_name, description, sector_id, owner_agency_id, start_date, end_date, is_assigned, created_by, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, 1, ?, NOW(), NOW())");
            
            $admin_id = $_SESSION['user_id'];
            
            $stmt->bind_param("ssiissi", 
                $program_name, 
                $description, 
                $sector_id, 
                $agency_id, 
                $start_date, 
                $end_date,
                $admin_id
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
                        
                        <div class="col-md-12">
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