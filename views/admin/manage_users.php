<?php
/**
 * Manage Users Page
 * 
 * Admin interface for managing user accounts.
 * Using standard Bootstrap modals with fixes.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin_functions.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Manage Users';

// Process form submissions
$message = '';
$message_type = '';

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
                break;

            case 'delete_user':
                $result = delete_user($_POST['user_id']);
                if (isset($result['success'])) {
                    $message = 'User deleted successfully.';
                    $message_type = 'success';
                } else {
                    $message = $result['error'] ?? 'Failed to delete user.';
                    $message_type = 'danger';
                }
                break;
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

// Get all users
$users = get_all_users();

// Get all sectors for dropdown
$sectors = get_all_sectors();

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/admin.css'
];

// Additional scripts - updated to include modular JS files in the correct order
$additionalScripts = [
    APP_URL . '/assets/js/admin/toast_manager.js',
    APP_URL . '/assets/js/admin/user_form_manager.js',
    APP_URL . '/assets/js/admin/user_table_manager.js',
    APP_URL . '/assets/js/admin/simple_users.js'  // Make sure this is last
];

// Pass sectors data directly to JavaScript
$sectorsJson = json_encode($sectors);

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';
?>

<!-- Make sectors data available to JavaScript -->
<script>
    // Make sectors data available to the users.js script
    window.sectorsData = <?php echo $sectorsJson; ?>;
    
    // Store any success/error messages for toast notifications
    window.pageMessages = {
        message: '<?php echo addslashes($message); ?>',
        type: '<?php echo $message_type; ?>'
    };
</script>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">Manage Users</h1>
            <p class="text-muted">Create and manage user accounts</p>
        </div>
        <a href="#" class="btn btn-primary" id="addUserBtn">
            <i class="fas fa-plus-circle me-2"></i> Add New User
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <div><?php echo $message; ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">System Users</h5>
            <span class="badge bg-primary"><?php echo count($users); ?> Users</span>
        </div>
        <div class="card-body">
            <?php if (!empty($users)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Agency</th>
                                <th>Sector</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="fw-medium"><?php echo $user['username']; ?></div>
                                        <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                                            <small class="text-muted">(You)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
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
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="#" class="btn btn-outline-primary edit-user-btn" 
                                                title="Edit User"
                                                data-user-id="<?php echo $user['user_id']; ?>"
                                                data-username="<?php echo $user['username']; ?>"
                                                data-role="<?php echo $user['role']; ?>"
                                                data-agency="<?php echo $user['agency_name']; ?>"
                                                data-sector="<?php echo $user['sector_id']; ?>">
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
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-muted"></i>
                    </div>
                    <h5>No users found</h5>
                    <p class="text-muted">Get started by adding a new user</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- User Management Tips -->
    <div class="card bg-light border-0 shadow-sm">
        <div class="card-body">
            <h5><i class="fas fa-info-circle me-2 text-primary"></i>User Management Tips</h5>
            <ul class="mb-0">
                <li>Admin users can access all features of the dashboard</li>
                <li>Agency users can only submit data for their assigned sector</li>
                <li>Deactivated users cannot log in to the system</li>
                <li>You cannot deactivate your own account</li>
            </ul>
        </div>
    </div>
</div>

<!-- Form container for dynamic forms -->
<div id="formContainer"></div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
