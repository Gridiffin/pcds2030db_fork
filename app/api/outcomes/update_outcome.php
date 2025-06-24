<?php
/**
 * Update Outcome Details API
 * 
 * Updates outcome details including the is_cumulative flag
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../../config/config.php';
require_once '../../lib/db_connect.php';
require_once '../../lib/session.php';
require_once '../../lib/functions.php';
require_once '../../lib/audit_log.php';

// Set content type
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Verify user is admin (only admins can modify outcome structure)
if (!is_admin()) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['error' => 'Permission denied. Only admin users can modify outcomes.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
$detail_id = isset($input['detail_id']) ? intval($input['detail_id']) : null;
$detail_name = isset($input['detail_name']) ? trim($input['detail_name']) : null;
$is_cumulative = isset($input['is_cumulative']) ? (bool)$input['is_cumulative'] : false;

if (!$detail_id || !$detail_name) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: detail_id, detail_name']);
    exit;
}

try {
    // Check if outcome exists
    $check_query = "SELECT detail_id, detail_name, is_cumulative FROM outcomes_details WHERE detail_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $detail_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['error' => 'Outcome not found']);
        exit;
    }
    
    $existing_outcome = $result->fetch_assoc();
    
    // Update the outcome
    $update_query = "UPDATE outcomes_details 
                    SET detail_name = ?, is_cumulative = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE detail_id = ?";
    
    $stmt = $conn->prepare($update_query);
    $cumulative_int = $is_cumulative ? 1 : 0;
    $stmt->bind_param("sii", $detail_name, $cumulative_int, $detail_id);
    
    if ($stmt->execute()) {
        // Log the change
        $changes = [];
        if ($existing_outcome['detail_name'] !== $detail_name) {
            $changes[] = "name: '{$existing_outcome['detail_name']}' → '$detail_name'";
        }
        if ($existing_outcome['is_cumulative'] != $cumulative_int) {
            $old_cum = $existing_outcome['is_cumulative'] ? 'cumulative' : 'non-cumulative';
            $new_cum = $is_cumulative ? 'cumulative' : 'non-cumulative';
            $changes[] = "type: '$old_cum' → '$new_cum'";
        }
        
        if (!empty($changes)) {
            log_audit_action($_SESSION['user_id'], 'outcome_update', 
                          "Updated outcome '$detail_name' (ID: $detail_id): " . implode(', ', $changes), 
                          'success');
        }
        
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Outcome updated successfully',
            'outcome' => [
                'detail_id' => $detail_id,
                'detail_name' => $detail_name,
                'is_cumulative' => $is_cumulative
            ]
        ]);
    } else {
        throw new Exception("Failed to update outcome: " . $stmt->error);
    }

} catch (Exception $e) {
    ob_end_clean();
    error_log("Error updating outcome: " . $e->getMessage());
    
    log_audit_action($_SESSION['user_id'], 'outcome_update_error', 
                  "Failed to update outcome (ID: $detail_id): " . $e->getMessage(), 
                  'error');
    
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update outcome']);
}
?>
