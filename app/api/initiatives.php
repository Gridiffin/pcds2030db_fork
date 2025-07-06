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

// Load database configuration
$config = include '../config/db_names.php';
if (!$config || !isset($config['tables']['initiatives'])) {
    die('Config not loaded or missing initiatives table definition.');
}

// Extract table and column names
$initiativesTable = $config['tables']['initiatives'];
$programsTable = $config['tables']['programs'];
$usersTable = $config['tables']['users'];

// Initiative columns
$initiativeIdCol = $config['columns']['initiatives']['id'];
$initiativeNameCol = $config['columns']['initiatives']['name'];
$initiativeDescriptionCol = $config['columns']['initiatives']['description'];
$initiativeStartDateCol = $config['columns']['initiatives']['start_date'];
$initiativeEndDateCol = $config['columns']['initiatives']['end_date'];
$initiativeIsActiveCol = $config['columns']['initiatives']['is_active'];
$initiativeCreatedByCol = $config['columns']['initiatives']['created_by'];
$initiativeCreatedAtCol = $config['columns']['initiatives']['created_at'];
$initiativeUpdatedAtCol = $config['columns']['initiatives']['updated_at'];

// Program columns
$programInitiativeIdCol = $config['columns']['programs']['initiative_id'];

// User columns
$userIdCol = $config['columns']['users']['id'];
$userUsernameCol = $config['columns']['users']['username'];

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
    global $conn, $initiativesTable, $usersTable, $programsTable;
    global $initiativeIdCol, $initiativeNameCol, $initiativeDescriptionCol, $initiativeStartDateCol, $initiativeEndDateCol, $initiativeIsActiveCol, $initiativeCreatedByCol, $initiativeCreatedAtCol;
    global $userUsernameCol, $userIdCol, $programInitiativeIdCol;
    
    $initiative_id = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if ($initiative_id) {
        // Get specific initiative
        $sql = "SELECT i.*, u.{$userUsernameCol} as created_by_name 
                FROM {$initiativesTable} i 
                LEFT JOIN {$usersTable} u ON i.{$initiativeCreatedByCol} = u.{$userIdCol} 
                WHERE i.{$initiativeIdCol} = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $initiative_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($initiative = $result->fetch_assoc()) {
            // Get program count for this initiative
            $count_sql = "SELECT COUNT(*) as program_count FROM {$programsTable} WHERE {$programInitiativeIdCol} = ?";
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
        $sql = "SELECT i.*, u.{$userUsernameCol} as created_by_name,
                       (SELECT COUNT(*) FROM {$programsTable} p WHERE p.{$programInitiativeIdCol} = i.{$initiativeIdCol}) as program_count
                FROM {$initiativesTable} i 
                LEFT JOIN {$usersTable} u ON i.{$initiativeCreatedByCol} = u.{$userIdCol} 
                WHERE i.{$initiativeIsActiveCol} = 1
                ORDER BY i.{$initiativeCreatedAtCol} DESC";
        
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
    global $conn, $initiativesTable, $usersTable;
    global $initiativeNameCol, $initiativeDescriptionCol, $initiativeStartDateCol, $initiativeEndDateCol, $initiativeCreatedByCol, $initiativeIdCol;
    global $userUsernameCol, $userIdCol;
    
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
    
    // Prepare data
    $initiative_name = trim($input['initiative_name']);
    $initiative_description = isset($input['initiative_description']) ? trim($input['initiative_description']) : null;
    $start_date = isset($input['start_date']) ? $input['start_date'] : null;
    $end_date = isset($input['end_date']) ? $input['end_date'] : null;
    $created_by = $_SESSION['user_id'];
    
    // Insert new initiative
    $sql = "INSERT INTO {$initiativesTable} ({$initiativeNameCol}, {$initiativeDescriptionCol}, {$initiativeStartDateCol}, {$initiativeEndDateCol}, {$initiativeCreatedByCol}) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $initiative_name, $initiative_description, $start_date, $end_date, $created_by);
    
    if ($stmt->execute()) {
        $initiative_id = $conn->insert_id;
        
        // Return the created initiative
        $get_sql = "SELECT i.*, u.{$userUsernameCol} as created_by_name 
                    FROM {$initiativesTable} i 
                    LEFT JOIN {$usersTable} u ON i.{$initiativeCreatedByCol} = u.{$userIdCol} 
                    WHERE i.{$initiativeIdCol} = ?";
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
    global $conn, $initiativesTable, $usersTable, $programsTable;
    global $initiativeIdCol, $initiativeNameCol, $initiativeDescriptionCol, $initiativeStartDateCol, $initiativeEndDateCol, $initiativeIsActiveCol, $initiativeUpdatedAtCol;
    global $userUsernameCol, $userIdCol, $programInitiativeIdCol;
    
    if (!$input || !isset($input['initiative_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Initiative ID is required']);
        return;
    }
    
    $initiative_id = intval($input['initiative_id']);
    
    // Check if this is a toggle status action
    if (isset($input['action']) && $input['action'] === 'toggle_status') {
        // Include initiative functions
        require_once '../lib/initiative_functions.php';
        
        $result = toggle_initiative_status($initiative_id);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Initiative status updated successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => $result['error']]);
        }
        return;
    }
    
    // Check if initiative exists
    $check_sql = "SELECT {$initiativeIdCol} FROM {$initiativesTable} WHERE {$initiativeIdCol} = ? AND {$initiativeIsActiveCol} = 1";
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
        $update_fields[] = "{$initiativeNameCol} = ?";
        $values[] = trim($input['initiative_name']);
        $types .= "s";
    }
    
    if (isset($input['initiative_description'])) {
        $update_fields[] = "{$initiativeDescriptionCol} = ?";
        $values[] = trim($input['initiative_description']);
        $types .= "s";
    }
    
    if (isset($input['start_date'])) {
        $update_fields[] = "{$initiativeStartDateCol} = ?";
        $values[] = $input['start_date'];
        $types .= "s";
    }
    
    if (isset($input['end_date'])) {
        $update_fields[] = "{$initiativeEndDateCol} = ?";
        $values[] = $input['end_date'];
        $types .= "s";
    }
    
    if (empty($update_fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid fields to update']);
        return;
    }
    
    // Add updated_at
    $update_fields[] = "{$initiativeUpdatedAtCol} = CURRENT_TIMESTAMP";
    
    // Add initiative_id for WHERE clause
    $values[] = $initiative_id;
    $types .= "i";
    
    $sql = "UPDATE {$initiativesTable} SET " . implode(", ", $update_fields) . " WHERE {$initiativeIdCol} = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        // Return updated initiative
        $get_sql = "SELECT i.*, u.{$userUsernameCol} as created_by_name,
                           (SELECT COUNT(*) FROM {$programsTable} p WHERE p.{$programInitiativeIdCol} = i.{$initiativeIdCol}) as program_count
                    FROM {$initiativesTable} i 
                    LEFT JOIN {$usersTable} u ON i.{$initiativeCreatedByCol} = u.{$userIdCol} 
                    WHERE i.{$initiativeIdCol} = ?";
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
    global $conn, $initiativesTable, $programsTable;
    global $initiativeIdCol, $initiativeIsActiveCol, $initiativeUpdatedAtCol;
    global $programInitiativeIdCol;
    
    if (!$input || !isset($input['initiative_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Initiative ID is required']);
        return;
    }
    
    $initiative_id = intval($input['initiative_id']);
    
    // Check if initiative exists and has programs
    $check_sql = "SELECT i.{$initiativeIdCol}, 
                         (SELECT COUNT(*) FROM {$programsTable} p WHERE p.{$programInitiativeIdCol} = i.{$initiativeIdCol}) as program_count
                  FROM {$initiativesTable} i 
                  WHERE i.{$initiativeIdCol} = ? AND i.{$initiativeIsActiveCol} = 1";
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
    $sql = "UPDATE {$initiativesTable} SET {$initiativeIsActiveCol} = 0, {$initiativeUpdatedAtCol} = CURRENT_TIMESTAMP WHERE {$initiativeIdCol} = ?";
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
