<?php
/**
 * Submit/Unsubmit Outcome AJAX Handler
 * 
 * Handles admin actions to submit or unsubmit outcomes
 */

require_once '../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Set JSON header
header('Content-Type: application/json');

// Verify user is an admin
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['metric_id']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$metric_id = intval($input['metric_id']);
$action = $input['action'];

if (!in_array($action, ['submit', 'unsubmit'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

try {
    // Set the new draft status based on action
    $is_draft = ($action === 'submit') ? 0 : 1;
    $submitted_by = ($action === 'submit') ? $_SESSION['user_id'] : null;
    
    // Update the outcome in the database
    $query = "UPDATE sector_outcomes_data SET is_draft = ?, submitted_by = ?, updated_at = CURRENT_TIMESTAMP WHERE metric_id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("iii", $is_draft, $submitted_by, $metric_id);
    $success = $stmt->execute();
    
    if (!$success) {
        throw new Exception("Failed to update outcome: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No outcome found with the specified ID");
    }
    
    $stmt->close();
    
    // Record the action in outcome history if the function exists
    if (function_exists('record_outcome_history')) {
        // Get the outcome record ID
        $record_query = "SELECT id FROM sector_outcomes_data WHERE metric_id = ?";
        $record_stmt = $conn->prepare($record_query);
        $record_stmt->bind_param("i", $metric_id);
        $record_stmt->execute();
        $record_result = $record_stmt->get_result();
        
        if ($record_row = $record_result->fetch_assoc()) {
            $record_id = $record_row['id'];
            $status = ($action === 'submit') ? 'submitted' : 'draft';
            $description = ($action === 'submit') ? 'Outcome submitted by admin' : 'Outcome unsubmitted by admin';
            
            record_outcome_history($record_id, $metric_id, '', $action, $status, $_SESSION['user_id'], $description);
        }
        
        $record_stmt->close();
    }
    
    $action_text = ($action === 'submit') ? 'submitted' : 'unsubmitted';
    echo json_encode([
        'success' => true, 
        'message' => "Outcome {$action_text} successfully"
    ]);
    
} catch (Exception $e) {
    error_log("Submit outcome error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
