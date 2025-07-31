<?php
// Start session FIRST before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * AJAX endpoint for hierarchical program numbering
 * Returns the next available program number for an initiative
 */

require_once '../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/numbering_helpers.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data (handle both JSON and form-encoded)
$input = [];
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($content_type, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

if (!isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Action is required']);
    exit;
}

$action = $input['action'];

try {
    switch ($action) {
        case 'get_next_number':
            handleGetNextNumber($input);
            break;
            
        case 'validate_number':
            handleValidateNumber($input);
            break;
            
        case 'check_program_number_availability':
            handleCheckProgramNumberAvailability($input);
            break;
            
        case 'preview_bulk_assignment':
            handlePreviewBulkAssignment($input);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Get the next available program number for an initiative
 */
function handleGetNextNumber($input) {
    if (!isset($input['initiative_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Initiative ID is required']);
        return;
    }
    
    $initiative_id = intval($input['initiative_id']);
    
    if ($initiative_id <= 0) {
        echo json_encode([
            'success' => true,
            'program_number' => null,
            'message' => 'No initiative selected'
        ]);
        return;
    }
    
    $next_number = generate_next_program_number($initiative_id);
    
    if ($next_number) {
        echo json_encode([
            'success' => true,
            'program_number' => $next_number,
            'message' => 'Next available number generated'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Could not generate program number. Initiative may not have a number assigned.'
        ]);
    }
}

/**
 * Validate a program number format and availability
 */
function handleValidateNumber($input) {
    if (!isset($input['program_number'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Program number is required']);
        return;
    }
    
    $program_number = trim($input['program_number']);
    $initiative_id = isset($input['initiative_id']) ? intval($input['initiative_id']) : null;
    $exclude_program_id = isset($input['exclude_program_id']) ? intval($input['exclude_program_id']) : null;
    
    // Validate format
    $format_validation = validate_program_number_format($program_number, $initiative_id);
    
    if (!$format_validation['valid']) {
        echo json_encode([
            'valid' => false,
            'error' => $format_validation['message']
        ]);
        return;
    }
    
    // Check availability
    $is_available = is_program_number_available($program_number, $exclude_program_id);
    
    if (!$is_available) {
        echo json_encode([
            'valid' => false,
            'error' => 'This program number is already in use'
        ]);
        return;
    }
      echo json_encode([
        'valid' => true,
        'message' => 'Program number is valid and available'
    ]);
}

/**
 * Check program number availability (for real-time validation)
 */
function handleCheckProgramNumberAvailability($input) {
    if (!isset($input['program_number'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Program number is required']);
        return;
    }
    
    $program_number = trim($input['program_number']);
    $initiative_id = isset($input['initiative_id']) && !empty($input['initiative_id']) ? intval($input['initiative_id']) : null;
    $exclude_program_id = isset($input['exclude_program_id']) && !empty($input['exclude_program_id']) ? intval($input['exclude_program_id']) : null;
    
    // Basic format validation using centralized function
    if (!is_valid_program_number_format($program_number, false)) {
        echo json_encode([
            'available' => false,
            'message' => get_program_number_format_error(false)
        ]);
        return;
    }
    
    // Advanced format validation if initiative is linked
    if ($initiative_id) {
        $format_validation = validate_program_number_format($program_number, $initiative_id);
        if (!$format_validation['valid']) {
            echo json_encode([
                'available' => false,
                'message' => $format_validation['message']
            ]);
            return;
        }
    }
    
    // Check availability
    $is_available = is_program_number_available($program_number, $exclude_program_id);
    
    echo json_encode([
        'available' => $is_available,
        'message' => $is_available ? 'Program number is available' : 'This program number is already in use.'
    ]);
}

/**
 * Preview what numbers would be assigned in bulk assignment
 */
function handlePreviewBulkAssignment($input) {
    if (!isset($input['program_ids']) || !isset($input['initiative_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Program IDs and Initiative ID are required']);
        return;
    }
    
    $program_ids = $input['program_ids'];
    $initiative_id = intval($input['initiative_id']);
    
    if (!is_array($program_ids) || empty($program_ids)) {
        echo json_encode([
            'success' => true,
            'assignments' => [],
            'message' => 'No programs to assign'
        ]);
        return;
    }
    
    global $conn;
    
    // Get initiative number
    $initiative_query = "SELECT initiative_number, initiative_name FROM initiatives WHERE initiative_id = ?";
    $stmt = $conn->prepare($initiative_query);
    $stmt->bind_param("i", $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $initiative = $result->fetch_assoc();
    
    if (!$initiative || !$initiative['initiative_number']) {
        echo json_encode([
            'success' => false,
            'error' => 'Initiative not found or has no number assigned'
        ]);
        return;
    }
    
    // Get program details
    $placeholders = str_repeat('?,', count($program_ids) - 1) . '?';
    $programs_query = "SELECT program_id, program_name, program_number 
                       FROM programs 
                       WHERE program_id IN ($placeholders)
                       ORDER BY program_name";
    
    $stmt = $conn->prepare($programs_query);
    $stmt->bind_param(str_repeat('i', count($program_ids)), ...$program_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $assignments = [];
    $sequence = 1;
    
    // Get the next starting sequence number
    $existing_programs = get_initiative_programs_with_numbers($initiative_id);
    if (!empty($existing_programs)) {
        foreach ($existing_programs as $existing) {
            if ($existing['program_number'] && preg_match('/\.(\d+)$/', $existing['program_number'], $matches)) {
                $sequence = max($sequence, intval($matches[1]) + 1);
            }
        }
    }
    
    while ($program = $result->fetch_assoc()) {
        $new_number = $initiative['initiative_number'] . '.' . $sequence;
        
        $assignments[] = [
            'program_id' => $program['program_id'],
            'program_name' => $program['program_name'],
            'current_number' => $program['program_number'],
            'new_number' => $new_number
        ];
        
        $sequence++;
    }
    
    echo json_encode([
        'success' => true,
        'initiative_name' => $initiative['initiative_name'],
        'initiative_number' => $initiative['initiative_number'],
        'assignments' => $assignments,
        'total_programs' => count($assignments)
    ]);
}
?>
