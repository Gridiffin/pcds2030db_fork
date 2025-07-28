<?php
/**
 * Admin Delete Program
 * 
 * Allows administrators to delete programs (both assigned and agency-created).
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/agencies/program_permissions.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Check if program ID is provided
$program_id = intval($_GET['id'] ?? $_POST['program_id'] ?? 0);
$current_period_id = intval($_GET['period_id'] ?? $_POST['period_id'] ?? 0);

if (!isset($_GET['id']) && !isset($_POST['program_id'])) {
    $_SESSION['message'] = "Invalid program ID.";
    $_SESSION['message_type'] = "danger";
    header('Location: programs.php' . ($current_period_id ? '?period_id=' . $current_period_id : ''));
    exit;
}

if ($program_id === 0) {
    $_SESSION['message'] = "Invalid program ID provided.";
    $_SESSION['message_type'] = "danger";
    header('Location: programs.php' . ($current_period_id ? '?period_id=' . $current_period_id : ''));
    exit;
}

// Get program details
$query = "SELECT p.*, a.agency_name 
          FROM programs p 
          LEFT JOIN agency a ON p.agency_id = a.agency_id 
          WHERE p.program_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $program_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Log failed deletion attempt - program not found
    log_audit_action('delete_program_failed', "Program ID: $program_id | Error: Program not found", 'failure', $_SESSION['user_id']);
    
    $_SESSION['message'] = "Program not found.";
    $_SESSION['message_type'] = "danger";
    header('Location: programs.php');
    exit;
}

$program = $result->fetch_assoc();

// Check role-based permissions: only admin, focal users, or program creators can delete
$can_delete = is_admin();

// If not an admin, check if user is focal or program creator
if (!$can_delete) {
    $can_delete = is_focal_user() || is_program_creator($program_id);
}

if (!$can_delete) {
    // Log failed deletion attempt - unauthorized
    log_audit_action('delete_program_failed', "Program ID: $program_id | Error: Unauthorized access - user not admin, focal, or program creator", 'failure', $_SESSION['user_id']);
    
    $_SESSION['message'] = 'You do not have permission to delete this program. Only admins, focal users, and program creators can delete programs.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php' . ($current_period_id ? '?period_id=' . $current_period_id : ''));
    exit;
}

// Process deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for confirmation
    if (isset($_POST['confirm_delete'])) {
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
                // It's possible the program had no submissions, so don't error if only program is deleted.
                // We should check if the program itself was deleted.
                $check_program_exists_query = "SELECT program_id FROM programs WHERE program_id = ?";
                $check_stmt = $conn->prepare($check_program_exists_query);
                $check_stmt->bind_param("i", $program_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                if ($check_result->num_rows > 0) {
                    throw new Exception("Failed to delete program. The program still exists.");
                }
            }
              // Commit transaction
            $conn->commit();
            
            // Log successful deletion
            log_audit_action('delete_program', "Program Name: {$program['program_name']} | Program ID: $program_id | Owner: {$program['agency_name']}", 'success', $_SESSION['user_id']);
            
            // Success message
            $_SESSION['message'] = "Program '{$program['program_name']}' successfully deleted.";
            $_SESSION['message_type'] = "success";
            
            // Ensure session data is written before redirecting
            session_write_close();
            
            // Redirect to the programs page, maintaining the period_id if present
            $redirect_url = 'programs.php';
            if ($current_period_id) {
                $redirect_url .= '?period_id=' . $current_period_id;
            }
            header('Location: ' . $redirect_url);
            exit;
              } catch (Exception $e) {
            // Roll back transaction on error
            $conn->rollback();
            
            // Log failed deletion attempt
            log_audit_action('delete_program_failed', "Program Name: {$program['program_name']} | Program ID: $program_id | Error: " . $e->getMessage(), 'failure', $_SESSION['user_id']);
            
            $_SESSION['message'] = 'Error: ' . $e->getMessage() . '<br>Failed to delete the program. Please try again or contact support.';
            $_SESSION['message_type'] = "danger";
            // Redirect back to programs page or show error on current page
            $redirect_url = 'programs.php';
            if ($current_period_id) {
                $redirect_url .= '?period_id=' . $current_period_id;
            }
             header('Location: ' . $redirect_url);
            exit;
        }
    } else {
        // If not confirmed, redirect back or show error
        $_SESSION['message'] = "Deletion not confirmed.";
        $_SESSION['message_type'] = "warning";
        $redirect_url = 'programs.php';
        if ($current_period_id) {
            $redirect_url .= '?period_id=' . $current_period_id;
        }
        header('Location: ' . $redirect_url);
        exit;
    }
} else {
    // If GET request, it means direct access to delete_program.php without POST, which is not the new flow.
    // Redirect to programs page as the confirmation is now handled by JS.
    $_SESSION['message'] = "Invalid access method for deletion.";
    $_SESSION['message_type'] = "warning";
    $redirect_url = 'programs.php';
    if ($current_period_id) {
        $redirect_url .= '?period_id=' . $current_period_id;
    }
    header('Location: ' . $redirect_url);
    exit;
}

// Set page title - This part is no longer reached if POSTing directly.
// $pageTitle = 'Delete Program: ' . htmlspecialchars($program['program_name']);

// Include header - This part is no longer reached.
// include PROJECT_ROOT_PATH . 'app/views/layouts/header.php';
?>

<!-- HTML for confirmation page is no longer needed here as it's handled by JS confirm dialog -->

<?php
// Include footer - This part is no longer reached.
// include PROJECT_ROOT_PATH . 'app/views/layouts/footer.php';
?>


