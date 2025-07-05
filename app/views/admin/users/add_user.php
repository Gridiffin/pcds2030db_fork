<?php
/**
 * Add User Page
 * 
 * Admin interface for adding new user accounts.
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
$pageTitle = 'Add New User';

// Process form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the form submission
    $result = add_user($_POST);
    
    if (isset($result['success'])) {
        $_SESSION['message'] = 'User added successfully.';
        $_SESSION['message_type'] = 'success';
        $_SESSION['show_toast_only'] = true; // Add this flag to indicate we want only a toast notification
        header('Location: manage_users.php');
        exit;
    } else {
        $message = $result['error'] ?? 'Failed to add user.';
        $message_type = 'danger';
    }
}

$config = include __DIR__ . '/../../../config/db_names.php';
if (!$config || !isset($config['tables']['agency'])) {
    die('Config not loaded or missing agency table definition.');
}
$agencyTable = $config['tables']['agency'];
$agencyIdCol = $config['columns']['agency']['id'];
$agencyNameCol = $config['columns']['agency']['name'];
$agencies = get_all_agencies($conn);

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/admin/user_form.js'
];

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Add New User',
    'subtitle' => 'Create a new user account',
    'variant' => 'white',
    'actions' => [
        [
            'text' => 'Back to Users',
            'url' => APP_URL . '/app/views/admin/users/manage_users.php',
            'class' => 'btn-outline-primary',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Include the modern page header
require_once '../../layouts/page_header.php';
?>

<main class="flex-fill">
<script>
    // Make sure APP_URL is defined for JavaScript
    window.APP_URL = '<?php echo APP_URL; ?>';
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

<!-- Add User Form -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title m-0">User Information</h5>
    </div>
    <div class="card-body">
        <form action="<?php echo view_url('admin', 'users/add_user.php'); ?>" method="post" id="addUserForm">
            <input type="hidden" name="action" value="add_user">
            
            <!-- Basic Information -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Account Information</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <div class="form-text">The username will be used for login.</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="fullname" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" required>
                        <div class="form-text">Enter the user's full name.</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="form-text">Enter a valid email address. This will be used for notifications and password resets.</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="role" class="form-label">User Role *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Administrator</option>
                            <option value="agency">Agency user</option>
                            <option value="focal">Focal</option>
                        </select>
                        <div class="form-text">Select whether this user is an Admin, Agency user, or Focal.</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password *</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <span class="input-group-text toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <div class="form-text password-strength">Password should be at least 8 characters.</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <span class="input-group-text toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <div class="form-text">Re-enter the password to confirm.</div>
                    </div>
                </div>
            </div>
            
            <!-- Agency Information (shown only for agency role) -->
            <div id="agencyFields" class="mb-4" style="display: none;">
                <h6 class="fw-bold mb-3">Agency Information <span class="text-danger">(Required for Agency Users)</span></h6>
                <div class="row g-3">
                    <div class="col-md-6" id="agencyGroupField">
                        <label for="<?php echo $agencyIdCol; ?>" class="form-label">Agency</label>
                        <select class="form-select" id="<?php echo $agencyIdCol; ?>" name="<?php echo $agencyIdCol; ?>">
                            <option value="">Select Agency</option>
                            <?php foreach($agencies as $agency): ?>
                                <option value="<?php echo $agency[$agencyIdCol]; ?>"><?php echo htmlspecialchars($agency[$agencyNameCol]); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Select which group this agency belongs to. You can always edit this in the edit users page.</div>
                    </div>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Agency users can only submit data related to their assigned sector.
                </div>
            </div>
            
            <!-- Removed account status field since it doesn't make sense to create inactive users -->
            <input type="hidden" name="is_active" value="1">
            
            <!-- Action Buttons -->
            <div class="d-flex justify-content-end mt-4">
                <a href="<?php echo APP_URL; ?>/app/views/admin/manage_users.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus me-1"></i> Add User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Additional JavaScript for field toggling and validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle agency fields visibility based on role selection
    const roleSelect = document.getElementById('role');
    const agencyFields = document.getElementById('agencyFields');
    const agencyGroupField = document.getElementById('agencyGroupField');
    const agencyId = document.getElementById('<?php echo $agencyIdCol; ?>');
    
    // Set initial state - hide by default, show only for agency role
    if (roleSelect && agencyFields) {
        agencyFields.style.display = 'none';
          // Function to update agency group options based on sector
        function updateAgencyGroupOptions() {
            const selectedSectorId = sectorId.value;
            const agencies = <?php echo json_encode($agencies); ?>;
            
            // Clear current options except first one
            while (agencyId.options.length > 1) {
                agencyId.remove(1);
            }
            
            // Add filtered options
            agencies.forEach(agency => {
                // If no sector is selected, show all groups
                // If sector is selected, only show groups that belong to that sector
                if (!selectedSectorId || parseInt(agency[$agencyIdCol]) === parseInt(selectedSectorId)) {
                    const option = new Option(agency[$agencyNameCol], agency[$agencyIdCol]);
                    agencyId.add(option);
                }
            });
            
            // Enable the dropdown
            agencyId.disabled = false;
        }

        // Function to update required status and show/hide fields based on role
        const updateRequiredFields = function() {
            if (roleSelect.value === 'agency' || roleSelect.value === 'focal') {
                agencyFields.style.display = 'block';
                agencyId.setAttribute('required', '');
            } else {
                agencyFields.style.display = 'none';
                agencyId.removeAttribute('required');
            }
        };
        
        // Initial setup
        updateRequiredFields();
        
        // Listen for changes
        roleSelect.addEventListener('change', updateRequiredFields);
    }
    
    // Password toggle functionality
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            
            // Toggle password visibility
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<i class="far fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                this.innerHTML = '<i class="far fa-eye"></i>';
            }
        });
    });
    
    // Password strength and match validation
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const strengthIndicator = document.querySelector('.password-strength');
    
    // Helper function to manage feedback messages
    function setInputFeedback(inputElement, isValid, message = '') {
        // Remove any existing feedback first
        const existingFeedback = inputElement.parentNode.querySelector('.invalid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }
        
        // Update input validation state
        if (!isValid) {
            inputElement.classList.add('is-invalid');
            
            // Create new feedback element if we have a message
            if (message) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = message;
                inputElement.parentNode.appendChild(feedback);
            }
        } else {
            inputElement.classList.remove('is-invalid');
        }
    }
    
    if (passwordInput && strengthIndicator) {
        passwordInput.addEventListener('input', function() {
            if (this.value.length < 8) {
                strengthIndicator.className = 'form-text text-danger';
                strengthIndicator.textContent = `Password too short (${this.value.length}/8 characters)`;
            } else {
                strengthIndicator.className = 'form-text text-success';
                strengthIndicator.textContent = 'Password meets minimum length requirement';
            }
            
            // Check if passwords match when both have values
            if (confirmInput.value) {
                const passwordsMatch = this.value === confirmInput.value;
                setInputFeedback(confirmInput, passwordsMatch, passwordsMatch ? '' : 'Passwords do not match');
            }
        });
    }
    
    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            if (passwordInput.value) {
                const passwordsMatch = this.value === passwordInput.value;
                setInputFeedback(this, passwordsMatch, passwordsMatch ? '' : 'Passwords do not match');
            }
        });
    }
    
    // Form validation
    const form = document.getElementById('addUserForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Check password length
            if (passwordInput.value.length < 8) {
                isValid = false;
                setInputFeedback(passwordInput, false, 'Password must be at least 8 characters');
            } else {
                setInputFeedback(passwordInput, true);
            }
            
            // Check if passwords match
            if (passwordInput.value !== confirmInput.value) {
                isValid = false;
                setInputFeedback(confirmInput, false, 'Passwords do not match');
            } else {
                setInputFeedback(confirmInput, true);
            }
            
            // Additional validation for agency role
            if (roleSelect.value === 'agency' || roleSelect.value === 'focal') {
                if (!agencyId.value) {
                    isValid = false;
                    setInputFeedback(agencyId, false, 'Agency is required');
                } else {
                    setInputFeedback(agencyId, true);
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});
</script>
</main>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>




