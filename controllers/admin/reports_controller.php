<?php
/**
 * Admin Reports Controller
 * 
 * Handles report generation requests.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin_functions.php';

// Verify user is admin
if (!is_admin()) {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Handle different actions
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'generate_report':
        // Extract period ID
        $period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : 0;
        
        // Generate report
        $result = generate_report($period_id);
        
        // Return result as JSON
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
