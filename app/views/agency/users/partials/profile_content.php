<?php
/**
 * User Profile Content Partial
 * Main content for the user profile page
 */

// Ensure necessary files are included
if (!isset($conn)) {
    global $conn;
    require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
}

require_once PROJECT_ROOT_PATH . 'app/lib/user_functions.php';

// Get current user data
$user_id = $_SESSION['user_id'];
$user = get_user_by_id($conn, $user_id);

if (!$user) {
    echo '<div class="alert alert-danger">Error: Could not load user data.</div>';
    return;
}

// Get agency name
$agency_name = get_agency_name_by_id($conn, $user['agency_id']);

// Get user initials for avatar
$user_initials = '';
if (!empty($user['fullname'])) {
    $names = explode(' ', $user['fullname']);
    $user_initials = substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : '');
} else {
    $user_initials = substr($user['username'], 0, 2);
}
$user_initials = strtoupper($user_initials);

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Format role display
$role_display = ucfirst($user['role']);
if ($user['role'] === 'focal') {
    $role_display = 'Focal Person';
}
?>

<main class="flex-fill">
    <div class="profile-container">
        <!-- Profile Card -->
        <div class="profile-card">
            <!-- Profile Header -->
            <div class="profile-card-header">
                <div class="profile-avatar">
                    <?php echo htmlspecialchars($user_initials); ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['fullname'] ?: $user['username']); ?></h2>
                    <span class="profile-role"><?php echo htmlspecialchars($role_display); ?></span>
                </div>
            </div>

            <!-- Profile Body -->
            <div class="profile-card-body">
                <!-- Account Information Display -->
                <div class="profile-section">
                    <h3 class="profile-section-title">
                        <i class="fas fa-info-circle"></i>
                        Account Information
                    </h3>
                    
                    <div class="account-info">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Agency</span>
                                <span class="value"><?php echo htmlspecialchars($agency_name); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">User Role</span>
                                <span class="value"><?php echo htmlspecialchars($role_display); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Account Created</span>
                                <span class="value"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Last Updated</span>
                                <span class="value"><?php echo date('M j, Y g:i A', strtotime($user['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="security-notice">
                    <i class="fas fa-shield-alt notice-icon"></i>
                    <p class="notice-text">
                        <strong>Security Notice:</strong> Only fill in the fields you want to update. 
                        Leave password fields empty if you don't want to change your password.
                    </p>
                </div>

                <!-- Profile Update Form -->
                <form id="profileForm" class="profile-form" action="<?php echo APP_URL; ?>/app/handlers/profile_handler.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <!-- Basic Information Section -->
                    <div class="profile-section">
                        <h3 class="profile-section-title">
                            <i class="fas fa-user-edit"></i>
                            Update Basic Information
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>"
                                       placeholder="Enter new username (3-50 characters)"
                                       autocomplete="username">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">
                                    Username can only contain letters, numbers, and underscores.
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>"
                                       placeholder="Enter new email address"
                                       autocomplete="email">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">
                                    A valid email address is required for notifications.
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="fullname" 
                                   name="fullname" 
                                   value="<?php echo htmlspecialchars($user['fullname'] ?: ''); ?>"
                                   placeholder="Enter your full name"
                                   autocomplete="name"
                                   maxlength="200">
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted">
                                Your full name as it should appear in reports and notifications.
                            </small>
                        </div>
                    </div>

                    <!-- Password Update Section -->
                    <div class="profile-section">
                        <h3 class="profile-section-title">
                            <i class="fas fa-lock"></i>
                            Change Password
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter new password (minimum 8 characters)"
                                       autocomplete="new-password">
                                <div class="invalid-feedback"></div>
                                <div id="passwordStrength" class="password-strength"></div>
                                <small class="form-text text-muted">
                                    Leave empty if you don't want to change your password.
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       placeholder="Confirm your new password"
                                       autocomplete="new-password">
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">
                                    Re-enter your new password to confirm.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="button" id="cancelBtn" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel Changes
                        </button>
                        <button type="submit" id="submitBtn" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<!-- Additional help text for mobile users -->
<div class="d-block d-md-none mt-3 text-center">
    <small class="text-muted">
        <i class="fas fa-mobile-alt"></i>
        Tip: Use Ctrl+S (or Cmd+S) to save changes quickly
    </small>
</div>
