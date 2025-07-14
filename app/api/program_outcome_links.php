<?php
/**
 * Program-Outcome Links Management API
 * 
 * Handles linking and unlinking programs to outcomes
 * Supports: GET (list links), POST (create link), DELETE (remove link)
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/admins/index.php';

// Verify user is admin or agency user
if (!is_admin() && !is_agency()) {
    ob_end_clean();
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied. Only admin and agency users can access this API.']);
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
 * Handle GET requests - get program-outcome links
 */
function handleGet() {
    global $conn;
    
    $program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : null;
    $outcome_id = isset($_GET['outcome_id']) ? intval($_GET['outcome_id']) : null;
    
    if ($program_id) {
        // Get all outcomes linked to a specific program
        $sql = "SELECT pol.link_id, pol.program_id, pol.outcome_id, pol.created_at,
                       od.title, od.detail_json, od.is_cumulative,
                       u.username as created_by_name
                FROM program_outcome_links pol
                JOIN outcomes od ON pol.outcome_id = od.id
                LEFT JOIN users u ON pol.created_by = u.user_id
                WHERE pol.program_id = ?
                ORDER BY pol.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $links = [];
        while ($row = $result->fetch_assoc()) {
            $links[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $links]);
        
    } elseif ($outcome_id) {
        // Get all programs linked to a specific outcome
        $sql = "SELECT pol.link_id, pol.program_id, pol.outcome_id, pol.created_at,
                       p.program_name, p.program_number,
                       u.username as created_by_name
                FROM program_outcome_links pol
                JOIN programs p ON pol.program_id = p.program_id
                LEFT JOIN users u ON pol.created_by = u.user_id
                WHERE pol.outcome_id = ?
                ORDER BY pol.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $outcome_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $links = [];
        while ($row = $result->fetch_assoc()) {
            $links[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $links]);
        
    } else {
        // Get all program-outcome links (for admin overview)
        if (!is_admin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Permission denied. Only admin users can view all links.']);
            return;
        }
        
        $sql = "SELECT pol.link_id, pol.program_id, pol.outcome_id, pol.created_at,
                       p.program_name, p.program_number,
                       od.title,
                       u.username as created_by_name
                FROM program_outcome_links pol
                JOIN programs p ON pol.program_id = p.program_id
                JOIN outcomes od ON pol.outcome_id = od.id
                LEFT JOIN users u ON pol.created_by = u.user_id
                ORDER BY pol.created_at DESC";
        
        $result = $conn->query($sql);
        $links = [];
        
        while ($row = $result->fetch_assoc()) {
            $links[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $links]);
    }
}

/**
 * Handle POST requests - create new program-outcome link
 */
function handlePost($input) {
    global $conn;
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input data']);
        return;
    }
    
    // Validate required fields
    if (empty($input['program_id']) || empty($input['outcome_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Both program_id and outcome_id are required']);
        return;
    }
    
    $program_id = intval($input['program_id']);
    $outcome_id = intval($input['outcome_id']);
    $created_by = $_SESSION['user_id'];
    
    // Verify program exists
    $check_program_sql = "SELECT program_id FROM programs WHERE program_id = ?";
    $check_program_stmt = $conn->prepare($check_program_sql);
    $check_program_stmt->bind_param("i", $program_id);
    $check_program_stmt->execute();
    if (!$check_program_stmt->get_result()->fetch_assoc()) {
        http_response_code(404);
        echo json_encode(['error' => 'Program not found']);
        return;
    }
    
    // Verify outcome exists
    $check_outcome_sql = "SELECT id FROM outcomes WHERE id = ?";
    $check_outcome_stmt = $conn->prepare($check_outcome_sql);
    $check_outcome_stmt->bind_param("i", $outcome_id);
    $check_outcome_stmt->execute();
    if (!$check_outcome_stmt->get_result()->fetch_assoc()) {
        http_response_code(404);
        echo json_encode(['error' => 'Outcome not found']);
        return;
    }
    
    // Check if link already exists
    $check_link_sql = "SELECT link_id FROM program_outcome_links WHERE program_id = ? AND outcome_id = ?";
    $check_link_stmt = $conn->prepare($check_link_sql);
    $check_link_stmt->bind_param("ii", $program_id, $outcome_id);
    $check_link_stmt->execute();
    if ($check_link_stmt->get_result()->fetch_assoc()) {
        http_response_code(409);
        echo json_encode(['error' => 'Link already exists between this program and outcome']);
        return;
    }
    
    // Create the link
    $sql = "INSERT INTO program_outcome_links (program_id, outcome_id, created_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $program_id, $outcome_id, $created_by);
    
    if ($stmt->execute()) {
        $link_id = $conn->insert_id;
        
        // Return the created link with details
        $get_sql = "SELECT pol.link_id, pol.program_id, pol.outcome_id, pol.created_at,
                           p.program_name, p.program_number,
                           od.title, od.is_cumulative,
                           u.username as created_by_name
                    FROM program_outcome_links pol
                    JOIN programs p ON pol.program_id = p.program_id
                    JOIN outcomes od ON pol.outcome_id = od.id
                    LEFT JOIN users u ON pol.created_by = u.user_id
                    WHERE pol.link_id = ?";
        
        $get_stmt = $conn->prepare($get_sql);
        $get_stmt->bind_param("i", $link_id);
        $get_stmt->execute();
        $result = $get_stmt->get_result();
        $link = $result->fetch_assoc();
        
        echo json_encode(['success' => true, 'message' => 'Program-outcome link created successfully', 'data' => $link]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create program-outcome link']);
    }
}

/**
 * Handle DELETE requests - remove program-outcome link
 */
function handleDelete($input) {
    global $conn;
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input data']);
        return;
    }
    
    // Support deletion by link_id or by program_id + outcome_id
    if (isset($input['link_id'])) {
        $link_id = intval($input['link_id']);
        
        // Check if link exists
        $check_sql = "SELECT link_id FROM program_outcome_links WHERE link_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $link_id);
        $check_stmt->execute();
        if (!$check_stmt->get_result()->fetch_assoc()) {
            http_response_code(404);
            echo json_encode(['error' => 'Link not found']);
            return;
        }
        
        // Delete the link
        $sql = "DELETE FROM program_outcome_links WHERE link_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $link_id);
        
    } elseif (isset($input['program_id']) && isset($input['outcome_id'])) {
        $program_id = intval($input['program_id']);
        $outcome_id = intval($input['outcome_id']);
        
        // Check if link exists
        $check_sql = "SELECT link_id FROM program_outcome_links WHERE program_id = ? AND outcome_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $program_id, $outcome_id);
        $check_stmt->execute();
        if (!$check_stmt->get_result()->fetch_assoc()) {
            http_response_code(404);
            echo json_encode(['error' => 'Link not found']);
            return;
        }
        
        // Delete the link
        $sql = "DELETE FROM program_outcome_links WHERE program_id = ? AND outcome_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $program_id, $outcome_id);
        
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Either link_id or both program_id and outcome_id are required']);
        return;
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Program-outcome link removed successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to remove program-outcome link']);
    }
}
?>
