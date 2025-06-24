<?php
/**
 * AJAX endpoint for program numbering operations
 * Handles requests for generating and validating hierarchical program numbers
 */

header('Content-Type: application/json');

// Include necessary files
require_once '../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/numbering_helpers.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get the action from POST data
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_next_number':
        handle_get_next_number();
        break;
    
    case 'validate_number':
        handle_validate_number();
        break;
    
    case 'preview_number':
        handle_preview_number();
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

/**
 * Get the next available program number for an initiative
 */
function handle_get_next_number() {
    $initiative_id = intval($_POST['initiative_id'] ?? 0);
    
    if (!$initiative_id) {
        echo json_encode([
            'success' => false,
            'program_number' => null,
            'message' => 'No initiative selected'
        ]);
        return;
    }
    
    $program_number = generate_next_program_number($initiative_id);
    
    if ($program_number) {
        echo json_encode([
            'success' => true,
            'program_number' => $program_number,
            'message' => 'Number generated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Could not generate program number. Check if initiative has a valid number.'
        ]);
    }
}

/**
 * Validate a manually entered program number
 */
function handle_validate_number() {
    $program_number = trim($_POST['program_number'] ?? '');
    $initiative_id = intval($_POST['initiative_id'] ?? 0);
    $exclude_program_id = intval($_POST['exclude_program_id'] ?? 0);
    
    if (empty($program_number)) {
        echo json_encode([
            'valid' => true,
            'message' => 'Empty number is allowed'
        ]);
        return;
    }
    
    // Validate format
    $format_validation = validate_program_number_format($program_number, $initiative_id);
    
    if (!$format_validation['valid']) {
        echo json_encode($format_validation);
        return;
    }
    
    // Check availability
    $is_available = is_program_number_available($program_number, $exclude_program_id);
    
    if (!$is_available) {
        echo json_encode([
            'valid' => false,
            'message' => 'This program number is already in use'
        ]);
        return;
    }
    
    echo json_encode([
        'valid' => true,
        'message' => 'Program number is valid and available'
    ]);
}

/**
 * Preview what the program number would be for a given initiative
 */
function handle_preview_number() {
    global $conn;
    
    $initiative_id = intval($_POST['initiative_id'] ?? 0);
    
    if (!$initiative_id) {
        echo json_encode([
            'success' => false,
            'preview' => null,
            'message' => 'No initiative selected'
        ]);
        return;
    }
    
    // Get initiative details
    $initiative_query = "SELECT initiative_name, initiative_number FROM initiatives WHERE initiative_id = ?";
    $stmt = $conn->prepare($initiative_query);
    $stmt->bind_param("i", $initiative_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $initiative = $result->fetch_assoc();
    
    if (!$initiative) {
        echo json_encode([
            'success' => false,
            'preview' => null,
            'message' => 'Initiative not found'
        ]);
        return;
    }
    
    if (!$initiative['initiative_number']) {
        echo json_encode([
            'success' => false,
            'preview' => null,
            'message' => 'Initiative does not have a number assigned'
        ]);
        return;
    }
    
    $next_number = generate_next_program_number($initiative_id);
    
    echo json_encode([
        'success' => true,
        'preview' => $next_number,
        'initiative_name' => $initiative['initiative_name'],
        'initiative_number' => $initiative['initiative_number'],
        'message' => 'Preview generated successfully'
    ]);
}
?>
