<?php
/**
 * Edit Program - Refactored
 * 
 * Interface for agency users to edit program basic information.
 * Follows best practices with modular structure, base layout, and Vite bundling.
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
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/program_status_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/notifications_core.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$program_id) {
    $_SESSION['message'] = 'Invalid program ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get program details
$program = get_program_details($program_id);

if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Check if user can edit this program using new permission system
if (!can_edit_program($program_id)) {
    $_SESSION['message'] = 'You do not have permission to edit this program.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// For legacy compatibility
$is_owner = is_program_owner($program_id);

// Get active initiatives for dropdown
$active_initiatives = get_initiatives_for_select(true);

// Get users in assigned agencies for user assignment
$assignable_users = get_assignable_users_for_program($program_id);

// Get current user assignments for this program
$current_user_assignments = get_program_assigned_users($program_id);

// Check if program has editor restrictions
$restrict_editors = program_has_editor_restrictions($program_id);

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Track changes for notification context
    $changes = [];
    $original_program = $program; // Current program data from earlier fetch
    
    $program_data = [
        'program_id' => $program_id,
        'program_name' => $_POST['program_name'] ?? '',
        'program_number' => $_POST['program_number'] ?? '',
        'brief_description' => $_POST['brief_description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'initiative_id' => !empty($_POST['initiative_id']) ? intval($_POST['initiative_id']) : null,
        'rating' => $_POST['rating'] ?? 'not_started'
    ];
    
    // Track changes for meaningful notifications
    if (($original_program['program_name'] ?? '') !== $program_data['program_name']) {
        $changes['program_name'] = $program_data['program_name'];
    }
    if (($original_program['program_number'] ?? '') !== $program_data['program_number']) {
        $changes['program_number'] = $program_data['program_number'];
    }
    if (($original_program['brief_description'] ?? '') !== $program_data['brief_description']) {
        $changes['brief_description'] = 'updated';
    }
    if (($original_program['start_date'] ?? '') !== $program_data['start_date']) {
        $changes['start_date'] = $program_data['start_date'];
    }
    if (($original_program['end_date'] ?? '') !== $program_data['end_date']) {
        $changes['end_date'] = $program_data['end_date'];
    }
    if (($original_program['initiative_id'] ?? null) !== $program_data['initiative_id']) {
        $changes['initiative'] = 'updated';
    }
    if (($original_program['rating'] ?? '') !== $program_data['rating']) {
        $changes['rating'] = $program_data['rating'];
    }
    
    // Update program using simplified function
    $result = update_simple_program($program_data);
    
    if (isset($result['success']) && $result['success']) {
        // Handle user role assignments
        $user_roles = isset($_POST['user_roles']) ? $_POST['user_roles'] : [];
        
        // Only process user assignments if user has permission to modify permissions
        if (is_focal_user() || is_program_creator($program_id) || is_admin()) {
            $new_restrict_editors = isset($_POST['restrict_editors']) ? 1 : 0;
            set_program_editor_restrictions($program_id, $new_restrict_editors);

            if ($new_restrict_editors) {
                // Remove all current user assignments
                foreach ($current_user_assignments as $assignment) {
                    remove_user_from_program($program_id, $assignment['user_id']);
                }
                // Add new assignments based on user_roles array
                foreach ($user_roles as $user_id => $role) {
                    if (!empty($role) && in_array($role, ['editor', 'viewer'])) {
                        assign_user_to_program($program_id, intval($user_id), $role, 'Updated during program edit');
                    }
                }
            } else {
                // Remove all user assignments when restrictions are disabled
                foreach ($current_user_assignments as $assignment) {
                    remove_user_from_program($program_id, $assignment['user_id']);
                }
            }
        }
        
        // Send program edit notifications
        if (!empty($changes) && function_exists('notify_program_edited')) {
            notify_program_edited($program_id, $_SESSION['user_id'], $changes);
        }
        
        // Set success message and show redirecting modal
        $message = $result['message'];
        $messageType = 'success';
        
        // Add JavaScript to show redirecting modal
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                // Show success message first
                showToast('Success', '" . addslashes($result['message']) . "', 'success');
                
                // Show redirecting modal after a short delay
                setTimeout(function() {
                    const redirectModal = document.createElement('div');
                    redirectModal.innerHTML = `
                        <div class='modal fade' tabindex='-1'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-body text-center py-4'>
                                        <i class='fas fa-check-circle text-success' style='font-size: 3rem;'></i>
                                        <h5 class='mt-3'>Program Updated Successfully!</h5>
                                        <p class='text-muted'>Redirecting to programs page...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(redirectModal);
                    const tempModal = new bootstrap.Modal(redirectModal.querySelector('.modal'));
                    tempModal.show();
                    
                    // Redirect after 2 seconds
                    setTimeout(function() {
                        window.location.href = 'view_programs.php';
                    }, 2000);
                }, 1000);
            });
        </script>";
        
        // Don't exit - let the page render with the message
    } else {
        $message = $result['error'] ?? 'An error occurred while updating the program.';
        $messageType = 'danger';
    }
}

// -- Refactored Page Setup --

$pageTitle = 'Edit Program';
$pageClass = 'agency-edit-program-page'; // A specific class for this page
$cssBundle = 'agency-edit-program'; // Vite bundle for edit program page
$jsBundle = 'agency-edit-program';

// Configure modern page header
$header_config = [
    'title' => 'Edit Program',
    'subtitle' => 'Edit your program\'s basic information',
    'variant' => 'white'
];

// The content will be rendered by this file
$contentFile = __DIR__ . '/partials/edit_program_content.php';

// Include the base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
?> 