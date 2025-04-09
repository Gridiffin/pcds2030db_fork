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

// Additional scripts - fixed the script loading issue
$additionalScripts = [
    APP_URL . '/assets/js/admin/toast_manager.js',
    APP_URL . '/assets/js/admin/user_form_manager.js',
    APP_URL . '/assets/js/admin/user_table_manager.js',
    // Renamed from simple_users.js to users.js to match actual file
    APP_URL . '/assets/js/admin/users.js'
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
        'url' => APP_URL . '/views/admin/add_user.php', // Use absolute URL with APP_URL
        'text' => 'Add New User',
        'icon' => 'fas fa-user-plus',
        'class' => 'btn-primary'
    ]
];

// Include dashboard header component
require_once '../../includes/dashboard_header.php';
?>

<!-- Make APP_URL and other data available to JavaScript -->
<script>
    // Define APP_URL for JavaScript to fix the "APP_URL is not defined" error
    window.APP_URL = '<?php echo APP_URL; ?>';
    
    // Make sectors data available to the users.js script
    window.sectorsData = <?php echo $sectorsJson; ?>;
    
    // Store any success/error messages for toast notifications
    window.pageMessages = {
        message: '<?php echo addslashes($message); ?>',
        type: '<?php echo $message_type; ?>'
    };
</script>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <div><?php echo $message; ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Users Table Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">System Users</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive w-100">
            <table class="table table-hover table-custom mb-0" style="width: 100%;">
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
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="text-muted fst-italic">N/A</span>
                                <?php else: ?>
                                    <?php echo $user['agency_name'] ?? '-'; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="text-muted fst-italic">N/A</span>
                                <?php else: ?>
                                    <?php echo $user['sector_name'] ?? '-'; ?>
                                <?php endif; ?>
                            </td>
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
                                    <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-outline-primary" title="Edit User">
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

<!-- Form container for dynamic forms -->
<div id="formContainer"></div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
