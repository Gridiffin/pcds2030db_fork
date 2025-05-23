<?php
/**
 * Manage Users Page
 * 
 * Admin interface for managing user accounts.
 * Using standard Bootstrap modals with fixes.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

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
                header('Location: ../../handlers/admin/process_user.php?action=delete_user&user_id=' . $_POST['user_id']);
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
    return $user['role'] === 'agency';
});

// Get all sectors for dropdown
$sectors = get_all_sectors();

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/admin/toast_manager.js', 
    APP_URL . '/assets/js/admin/user_form_manager.js',
    APP_URL . '/assets/js/admin/user_table_manager.js',
    APP_URL . '/assets/js/admin/users.js'  // Make sure this file actually exists
];

// Pass sectors data directly to JavaScript
$sectorsJson = json_encode($sectors);

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Set up the page header variables
$title = "User Management";
$subtitle = "Create and manage user accounts for the system";
$headerStyle = 'light'; // Use light style
$actions = [
    [
        'url' => APP_URL . '/app/views/admin/add_user.php',
        'text' => 'Add New User',
        'icon' => 'fas fa-user-plus',
        'class' => 'btn-light border border-primary text-primary'
    ]
];

// Include dashboard header component
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
?>

<!-- Make APP_URL and other data available to JavaScript -->
<script>
    // Define APP_URL for JavaScript to fix the "APP_URL is not defined" error
    window.APP_URL = '<?php echo APP_URL; ?>';
    
    // Make sectors data available to the users.js script
    window.sectorsData = <?php echo $sectorsJson; ?>;
    
    // Store any success/error messages for toast notifications - always use toast for AJAX responses
    window.pageMessages = {
        message: '<?php echo addslashes($message); ?>',
        type: '<?php echo $message_type; ?>',
        // Always use toast for ajax responses or when explicitly requested
        useToast: <?php echo (!empty($message) && (isset($show_toast_only) && $show_toast_only)) ? 'true' : 'false'; ?>
    };
</script>

<!-- Custom styles for inactive users -->
<style>
    tr.inactive-user {
        background-color: #f8f9fa;
        opacity: 0.7;
    }
    
    tr.inactive-user td {
        color: #6c757d;
    }
    
    tr.inactive-user:hover {
        opacity: 0.9;
    }
    
    .status-indicator {
        font-weight: bold;
    }
    
    .status-indicator.active {
        color: #28a745;
    }
    
    .status-indicator.inactive {
        color: #dc3545;
    }
</style>

<?php if (!empty($message) && empty($show_toast_only)): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <div><?php echo $message; ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Admin Users Table Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Admin Users</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive w-100">
            <table class="table table-hover table-custom user-table mb-0">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($admin_users as $user): ?>
                        <tr class="<?php echo !$user['is_active'] ? 'inactive-user' : ''; ?>">
                            <td>
                                <div class="fw-medium"><?php echo $user['username']; ?></div>
                                <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                                    <small class="text-muted">(You)</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                                
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <button class="btn btn-sm ms-2 toggle-active-btn" 
                                        data-user-id="<?php echo $user['user_id']; ?>"
                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                        data-status="<?php echo $user['is_active']; ?>"
                                        title="<?php echo $user['is_active'] ? 'Deactivate User' : 'Activate User'; ?>">
                                        <i class="fas fa-toggle-<?php echo $user['is_active'] ? 'on text-success' : 'off text-secondary'; ?>"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm d-inline-flex align-items-center justify-content-center">
                                    <a href="<?php echo APP_URL; ?>/app/views/admin/edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-outline-primary" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <a href="#" class="btn btn-outline-danger delete-user-btn" 
                                            title="Delete User"
                                            data-user-id="<?php echo $user['user_id']; ?>"
                                            data-username="<?php echo $user['username']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Agency Users Table Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Agency Users</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive w-100">
            <table class="table table-hover table-custom user-table mb-0">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Agency</th>
                        <th>Sector</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($agency_users as $user): ?>
                        <tr class="<?php echo !$user['is_active'] ? 'inactive-user' : ''; ?>">
                            <td>
                                <div class="fw-medium"><?php echo $user['username']; ?></div>
                            </td>
                            <td><?php echo $user['agency_name'] ?? '-'; ?></td>
                            <td><?php echo $user['sector_name'] ?? '-'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                                
                                <button class="btn btn-sm ms-2 toggle-active-btn" 
                                    data-user-id="<?php echo $user['user_id']; ?>"
                                    data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                    data-status="<?php echo $user['is_active']; ?>"
                                    title="<?php echo $user['is_active'] ? 'Deactivate User' : 'Activate User'; ?>">
                                    <i class="fas fa-toggle-<?php echo $user['is_active'] ? 'on text-success' : 'off text-secondary'; ?>"></i>
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm d-inline-flex align-items-center justify-content-center">
                                    <a href="<?php echo APP_URL; ?>/app/views/admin/edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-outline-primary" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-outline-danger delete-user-btn" 
                                        title="Delete User"
                                        data-user-id="<?php echo $user['user_id']; ?>"
                                        data-username="<?php echo $user['username']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Management Tips -->
<div class="user-management-tips">
    <h5><i class="fas fa-info-circle me-2 text-primary"></i>User Management Tips</h5>
    <ul class="mb-0">
        <li>Admin users can access all features of the dashboard</li>
        <li>Agency users can only submit data for their assigned sector</li>
        <li>Deactivated users cannot log in to the system</li>
        <li>You cannot deactivate your own account</li>
    </ul>
</div>

<!-- Form container for dynamic forms -->
<div id="formContainer"></div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>



