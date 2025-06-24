<?php
/**
 * Initiatives Management API
 * 
 * Handles CRUD operations for initiatives
 * Supports: GET (list/single), POST (create), PUT (update), DELETE (delete)
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    ob_end_clean();
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied. Only admin users can access this API.']);
    exit;
}

// Clear any buffered output and set JSON header
ob_end_clean();
header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($method) {
        case 'GET':
            handleGet();
            break;
            
        case 'POST':
            handlePost($input);
            break;
            
        case 'PUT':
            handlePut($input);
            break;
            
        case 'DELETE':
            handleDelete($input);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

/**
 * Handle GET requests - list all initiatives or get specific initiative
 */
function handleGet() {
    global $conn;
    
    $initiative_id = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if ($initiative_id) {
        // Get specific initiative
        $sql = "SELECT i.*, u.username as created_by_name 
                FROM initiatives i 
                LEFT JOIN users u ON i.created_by = u.user_id 
                WHERE i.initiative_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $initiative_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($initiative = $result->fetch_assoc()) {
            // Get program count for this initiative
            $count_sql = "SELECT COUNT(*) as program_count FROM programs WHERE initiative_id = ?";
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param("i", $initiative_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_data = $count_result->fetch_assoc();
            
            $initiative['program_count'] = $count_data['program_count'];
            
            echo json_encode(['success' => true, 'data' => $initiative]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Initiative not found']);
        }
    } else {
        // Get all initiatives
        $sql = "SELECT i.*, u.username as created_by_name,
                       (SELECT COUNT(*) FROM programs p WHERE p.initiative_id = i.initiative_id) as program_count
                FROM initiatives i 
                LEFT JOIN users u ON i.created_by = u.user_id 
                WHERE i.is_active = 1
                ORDER BY i.created_at DESC";
        
        $result = $conn->query($sql);
        $initiatives = [];
        
        while ($row = $result->fetch_assoc()) {
            $initiatives[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $initiatives]);
    }
}

/**
 * Handle POST requests - create new initiative
 */
function handlePost($input) {
    global $conn;
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input data']);
        return;
    }
    
    // Validate required fields
    $required_fields = ['initiative_name'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Prepare data    $initiative_name = trim($input['initiative_name']);
    $initiative_description = isset($input['initiative_description']) ? trim($input['initiative_description']) : null;
    $start_date = isset($input['start_date']) ? $input['start_date'] : null;
    $end_date = isset($input['end_date']) ? $input['end_date'] : null;
    $created_by = $_SESSION['user_id'];
    
    // Insert new initiative
    $sql = "INSERT INTO initiatives (initiative_name, initiative_description, start_date, end_date, created_by) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $initiative_name, $initiative_description, $start_date, $end_date, $created_by);
    
    if ($stmt->execute()) {
        $initiative_id = $conn->insert_id;
        
        // Return the created initiative
        $get_sql = "SELECT i.*, u.username as created_by_name 
                    FROM initiatives i 
                    LEFT JOIN users u ON i.created_by = u.user_id 
                    WHERE i.initiative_id = ?";
        $get_stmt = $conn->prepare($get_sql);
        $get_stmt->bind_param("i", $initiative_id);
        $get_stmt->execute();
        $result = $get_stmt->get_result();
        $initiative = $result->fetch_assoc();
        $initiative['program_count'] = 0; // New initiative has no programs yet
        
        echo json_encode(['success' => true, 'message' => 'Initiative created successfully', 'data' => $initiative]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create initiative']);
    }
}

/**
 * Handle PUT requests - update existing initiative
 */
function handlePut($input) {
    global $conn;
    
    if (!$input || !isset($input['initiative_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Initiative ID is required']);
        return;
    }
    
    $initiative_id = intval($input['initiative_id']);
    
    // Check if initiative exists
    $check_sql = "SELECT initiative_id FROM initiatives WHERE initiative_id = ? AND is_active = 1";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $initiative_id);
    $check_stmt->execute();
    if (!$check_stmt->get_result()->fetch_assoc()) {
        http_response_code(404);
        echo json_encode(['error' => 'Initiative not found']);
        return;
    }
    
    // Build update query dynamically
    $update_fields = [];
    $values = [];
    $types = "";
    
    if (isset($input['initiative_name']) && !empty(trim($input['initiative_name']))) {
        $update_fields[] = "initiative_name = ?";
        $values[] = trim($input['initiative_name']);
        $types .= "s";
    }
      if (isset($input['initiative_description'])) {
        $update_fields[] = "initiative_description = ?";
        $values[] = trim($input['initiative_description']);
        $types .= "s";
    }
    
    if (isset($input['start_date'])) {
        $update_fields[] = "start_date = ?";
        $values[] = $input['start_date'];
        $types .= "s";
    }
    
    if (isset($input['end_date'])) {
        $update_fields[] = "end_date = ?";
        $values[] = $input['end_date'];
        $types .= "s";
    }
    
    if (empty($update_fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid fields to update']);
        return;
    }
    
    // Add updated_at
    $update_fields[] = "updated_at = CURRENT_TIMESTAMP";
    
    // Add initiative_id for WHERE clause
    $values[] = $initiative_id;
    $types .= "i";
    
    $sql = "UPDATE initiatives SET " . implode(", ", $update_fields) . " WHERE initiative_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        // Return updated initiative
        $get_sql = "SELECT i.*, u.username as created_by_name,
                           (SELECT COUNT(*) FROM programs p WHERE p.initiative_id = i.initiative_id) as program_count
                    FROM initiatives i 
                    LEFT JOIN users u ON i.created_by = u.user_id 
                    WHERE i.initiative_id = ?";
        $get_stmt = $conn->prepare($get_sql);
        $get_stmt->bind_param("i", $initiative_id);
        $get_stmt->execute();
        $result = $get_stmt->get_result();
        $initiative = $result->fetch_assoc();
        
        echo json_encode(['success' => true, 'message' => 'Initiative updated successfully', 'data' => $initiative]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update initiative']);
    }
}

/**
 * Handle DELETE requests - soft delete initiative
 */
function handleDelete($input) {
    global $conn;
    
    if (!$input || !isset($input['initiative_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Initiative ID is required']);
        return;
    }
    
    $initiative_id = intval($input['initiative_id']);
    
    // Check if initiative exists and has programs
    $check_sql = "SELECT i.initiative_id, 
                         (SELECT COUNT(*) FROM programs p WHERE p.initiative_id = i.initiative_id) as program_count
                  FROM initiatives i 
                  WHERE i.initiative_id = ? AND i.is_active = 1";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $initiative_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if (!$initiative = $result->fetch_assoc()) {
        http_response_code(404);
        echo json_encode(['error' => 'Initiative not found']);
        return;
    }
    
    if ($initiative['program_count'] > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Cannot delete initiative that has programs assigned to it']);
        return;
    }
    
    // Soft delete the initiative
    $sql = "UPDATE initiatives SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE initiative_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $initiative_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Initiative deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete initiative']);
    }
}
?>
