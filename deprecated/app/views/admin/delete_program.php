<?php
/**
 * Admin Delete Program
 * 
 * Allows administrators to delete programs (both assigned and agency-created).
 */

// Include necessary files
require_once ROOT_PATH . 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/$((includes/db_connect.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/session.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/functions.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/admins/index.php -replace 'includes/', ''))';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Check if program ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid program ID.";
    $_SESSION['message_type'] = "danger";
    header('Location: programs.php');
    exit;
}

$program_id = intval($_GET['id']);

// Get program details
$query = "SELECT p.*, u.agency_name, s.sector_name 
          FROM programs p 
          LEFT JOIN users u ON p.owner_agency_id = u.user_id 
          LEFT JOIN sectors s ON p.sector_id = s.sector_id 
          WHERE p.program_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $program_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Program not found.";
    $_SESSION['message_type'] = "danger";
    header('Location: programs.php');
    exit;
}

$program = $result->fetch_assoc();

// Process deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // First delete any associated submissions
        $delete_submissions = "DELETE FROM program_submissions WHERE program_id = ?";
        $stmt = $conn->prepare($delete_submissions);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        
        // Next delete the program
        $delete_program = "DELETE FROM programs WHERE program_id = ?";
        $stmt = $conn->prepare($delete_program);
        $stmt->bind_param("i", $program_id);
        $result = $stmt->execute();
        
        // Check if deletion was successful
        if (!$result || $conn->affected_rows == 0) {
            throw new Exception("Failed to delete program. No rows affected.");
        }
        
        // Commit transaction
        $conn->commit();
        
        // Success message
        $_SESSION['message'] = "Program successfully deleted.";
        $_SESSION['message_type'] = "success";
        
        // Immediate redirect to the programs page
        echo "<script>window.location.href = 'programs.php';</script>";
        exit;
        
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        echo '<div class="alert alert-danger mt-3">';
        echo '<strong>Error:</strong> ' . $e->getMessage();
        echo '<br>Failed to delete the program. Please try again or contact support.';
        echo '</div>';
    }
}

// Set page title
$pageTitle = 'Delete Program';

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Set up the dashboard header variables
$title = "Delete Program";
$subtitle = "Remove program from system";
$headerStyle = 'danger';
$actions = [
    [
        'url' => 'programs.php',
        'text' => 'Cancel',
        'icon' => 'fas fa-times',
        'class' => 'btn-outline-light'
    ]
];

// Include the dashboard header component
require_once ROOT_PATH . 'app/lib/$((includes/dashboard_header.php -replace 'includes/', ''))';
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title m-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Program
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. Deleting this program will remove all associated data including submissions and reports.
                </div>
                
                <h5 class="mb-3">Program Details:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%;">Program Name</th>
                            <td><?php echo htmlspecialchars($program['program_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><?php echo !empty($program['description']) ? htmlspecialchars($program['description']) : '<em>No description</em>'; ?></td>
                        </tr>
                        <tr>
                            <th>Agency</th>
                            <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Sector</th>
                            <td><?php echo htmlspecialchars($program['sector_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                <?php if (isset($program['is_assigned']) && $program['is_assigned']): ?>
                                    <span class="badge bg-success">Admin Assigned</span>
                                <?php else: ?>
                                    <span class="badge bg-info">Agency Created</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td><?php echo date('F j, Y', strtotime($program['created_at'])); ?></td>
                        </tr>
                    </table>
                </div>
                
                <form method="POST" action="" class="mt-4">
                    <div class="d-flex justify-content-center">
                        <a href="programs.php" class="btn btn-outline-secondary me-3">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" name="delete_program" value="1" class="btn btn-danger" onclick="return confirm('Are you sure you want to permanently delete this program?');">
                            <i class="fas fa-trash-alt me-1"></i> Delete Program
                        </button>
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
