<?php
/**
 * Resubmit Outcome
 * 
 * Allows admins to resubmit an unsubmitted/draft outcome
 */

require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get metric ID from URL
$metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 0;

if (!$metric_id) {
    $_SESSION['error_message'] = "Invalid outcome ID.";
    header('Location: manage_outcomes.php');
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Update the outcome status to submitted
    $update_query = "UPDATE sector_outcomes_data SET is_draft = 0, submitted_by = ?, updated_at = NOW() WHERE metric_id = ?";
    $stmt = $conn->prepare($update_query);
    
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt->bind_param("ii", $user_id, $metric_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating outcome: " . $stmt->error);
    }
    
    // Get the outcome data for history recording
    $outcome = get_outcome_data_for_display($metric_id);
    if (!$outcome) {
        throw new Exception("Could not retrieve outcome data");
    }
    
    // Record in history
    if (!record_outcome_history(
        $outcome['id'],
        $metric_id,
        $outcome['data_json'],
        'resubmit',
        'submitted',
        $user_id,
        'Outcome resubmitted by admin'
    )) {
        throw new Exception("Failed to record outcome history");
    }
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['success_message'] = "Outcome successfully resubmitted.";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error_message'] = "Error resubmitting outcome: " . $e->getMessage();
}

// Redirect back to the manage outcomes page
header('Location: manage_outcomes.php');
exit;
?>
