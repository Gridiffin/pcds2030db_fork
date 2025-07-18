<?php
/**
 * Manage Users Page
 * 
 * Admin interface for managing user accounts.
 * Using standard Bootstrap modals with fixes.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Manage Users';

// Process form submissions
$message = '';
$message_type = '';

// Check if there's a message in the session and use it
if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    
    // If show_toast_only is set, we'll only show the toast notification
    $show_toast_only = isset($_SESSION['show_toast_only']) && $_SESSION['show_toast_only'];
    
    // Clear the message from session after using it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    if (isset($_SESSION['show_toast_only'])) {
        unset($_SESSION['show_toast_only']);
    }
}

// Handle user actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Check if this is an AJAX request
        $is_ajax = isset($_POST['ajax_request']) && $_POST['ajax_request'] == '1';
        
        $result = [];
        
        switch ($_POST['action']) {
            case 'add_user':
                $result = add_user($_POST);
                if (isset($result['success'])) {
                    $message = 'User added successfully.';
                    $message_type = 'success';
                    
                    // Store in session for redirect
                    $_SESSION['message'] = $message;
                    $_SESSION['message_type'] = $message_type;
                    
                    // Redirect to clear the form and prevent resubmission
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $message = $result['error'] ?? 'Failed to add user.';
                    $message_type = 'danger';
                }
                break;

            case 'edit_user':
                $result = update_user($_POST);
                if (isset($result['success'])) {
                    $message = 'User updated successfully.';
                    $message_type = 'success';
                } else {
                    $message = $result['error'] ?? 'Failed to update user.';
                    $message_type = 'danger';
                }
                break;            case 'delete_user':
                // Redirect to process_user.php which handles the actual deletion
                $_SESSION['user_id_to_delete'] = $_POST['user_id'];
                header('Location: ../../../handlers/admin/process_user.php?action=delete_user&user_id=' . $_POST['user_id']);
                exit;
        }
        
        // If this was an AJAX request, return JSON response instead of setting message variables
        if ($is_ajax) {
            header('Content-Type: application/json');
            if ($message_type === 'success') {
                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                echo json_encode(['error' => $message]);
            }
            exit;
        }
    }
}

// Get all users and separate them by role
$all_users = get_all_users();
$admin_users = array_filter($all_users, function($user) {
    return $user['role'] === 'admin';
});
$agency_users = array_filter($all_users, function($user) {
    return $user['role'] === 'agency' || $user['role'] === 'focal';
});

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/admin/user_form_manager.js',
    APP_URL . '/assets/js/admin/user_table_manager.js',
    APP_URL . '/assets/js/admin/simple_users.js'
];

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'User Management',
    'subtitle' => 'Create and manage user accounts for the system',
    'variant' => 'green',
    'actions' => [
        [
            'text' => 'Add New User',
            'url' => APP_URL . '/app/views/admin/users/add_user.php',
            'class' => 'btn-light',
            'icon' => 'fas fa-user-plus'
        ]
    ]
];

// Include the modern page header
require_once '../../layouts/page_header.php';
?>

<!-- Make APP_URL and other data available to JavaScript -->
<script>
    // Define APP_URL for JavaScript to fix the "APP_URL is not defined" error
    window.APP_URL = '<?php echo APP_URL; ?>';
    
    // Store any success/error messages for toast notifications - always use toast for AJAX responses
    window.pageMessages = {
        message: '<?php echo addslashes($message); ?>',
        type: '<?php echo $message_type; ?>',
        // Always use toast for ajax responses or when explicitly requested
        useToast: <?php echo (!empty($message) && (isset($show_toast_only) && $show_toast_only)) ? 'true' : 'false'; ?>
    };
</script>

<script src="<?php echo APP_URL; ?>/assets/js/admin/manage_users.js"></script>

<?php if (!empty($message) && empty($show_toast_only)): ?>
    <div class="alert alert-forest alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> alert-icon"></i>
            <div><?php echo $message; ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>    </div>
<?php endif; ?>

<!-- User Management Content -->
<main class="flex-fill">
    <div id="userTablesWrapper">
        <?php
        // Render Admin Users Table
        $users = $admin_users;
        $tableTitle = 'Admin Users';
        $roleType = 'admin';
        include '_user_table.php';
        // Render Agency Users Table
        $users = $agency_users;
        $tableTitle = 'Agency Users';
        $roleType = 'agency';
        include '_user_table.php';
        ?>
    </div> <!-- End of userTablesWrapper -->
</main>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>