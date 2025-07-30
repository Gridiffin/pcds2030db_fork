<?php
/**
 * Create Program Page
 * Allows agency users to create new programs
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}


// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/programs.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_permissions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/numbering_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_validation.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect program data
    $program_data = [
        'program_name' => $_POST['program_name'] ?? '',
        'program_number' => $_POST['program_number'] ?? '',
        'brief_description' => $_POST['brief_description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'initiative_id' => !empty($_POST['initiative_id']) ? intval($_POST['initiative_id']) : null,
        'restrict_editors' => isset($_POST['restrict_editors']) ? 1 : 0,
        'assigned_editors' => isset($_POST['assigned_editors']) ? $_POST['assigned_editors'] : []
    ];
    
    // Validate program data
    $validation = validate_program_data($program_data);
    
    if ($validation['success']) {
        // Create new program
        $result = create_simple_program($program_data);
        
        if (isset($result['success']) && $result['success'] && isset($result['program_id'])) {
            $program_id = $result['program_id'];
            
            // Set editor restrictions if enabled
            if ($program_data['restrict_editors']) {
                set_program_editor_restrictions($program_id, true);
                
                // Assign selected users as editors
                if (!empty($program_data['assigned_editors'])) {
                    foreach ($program_data['assigned_editors'] as $user_id) {
                        assign_user_to_program($program_id, intval($user_id), 'editor', 'Assigned during program creation');
                    }
                }
            }
            
            // Set success message and show modal prompt
            $message = $result['message'];
            $messageType = 'success';
            $created_program_id = $program_id;
            $showSuccessModal = true;
        } else {
            $message = $result['error'] ?? 'An error occurred while creating the program.';
            $messageType = 'danger';
        }
    } else {
        $message = $validation['message'];
        $messageType = 'danger';
    }
}

// Set page title and body class
$pageTitle = 'Create New Program';
$bodyClass = 'create-program-page';

// Configure modern page header
$header_config = [
    'title' => 'Create New Program',
    'subtitle' => 'Create a new program template. Add progress reports for specific periods when ready.',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'view_programs.php',
            'text' => 'Back to Programs',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Set CSS and JS bundles
$cssBundle = 'agency-create-program'; // Vite bundle for create program page
$jsBundle = 'agency-create-program';

// Set content file
$contentFile = 'partials/create_program_content.php';

// Display success/error message
if (!empty($message)) {
    // Check if this is a notification-related message that should not be shown as a toast
    $notification_keywords = ['New program', 'created by', 'System Administrator', 'notification'];
    $is_notification_message = false;
    foreach ($notification_keywords as $keyword) {
        if (stripos($message, $keyword) !== false) {
            $is_notification_message = true;
            break;
        }
    }
    
    // Only show toast if it's not a notification-related message
    if (!$is_notification_message) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('" . ucfirst($messageType) . "', " . json_encode($message) . ", '$messageType');
            });
        </script>";
    }
}

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
