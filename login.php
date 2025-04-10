<?php
/**
 * Login page
 * 
 * Handles user authentication.
 */

// Include necessary files
require_once 'config/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';
require_once 'includes/admin_functions.php'; // Add this line to include admin_functions.php

// Initialize variables
$username = '';
$error = '';

// Check if user is already logged in, redirect if true
if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password
    $username = sanitize_input($_POST["username"]);
    $password = $_POST["password"]; // No sanitization for password as it will be hashed
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        // Use the validate_login function which properly checks the is_active status
        $result = validate_login($username, $password);
        
        if (isset($result['success'])) {
            // Check user role using session variable (more robust than function)
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                header('Location: ' . APP_URL . '/views/admin/dashboard.php');
            } else {
                header('Location: ' . APP_URL . '/views/agency/dashboard.php');
            }
            exit;
        } else {
            $error = $result['error'] ?? "Invalid username or password.";
        }
    }
}

// Get error message from URL if present
if (isset($_GET['error']) && $_GET['error'] === 'invalid_session') {
    $error = "Your session has expired. Please log in again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-xl-10">
                <div class="card shadow unified-card">
                    <div class="copyright-text">© <?php echo date('Y'); ?> PCDS2030</div>
                    
                    <div class="row g-0">
                        <!-- Welcome Section (Left side of card) -->
                        <div class="col-md-6 welcome-section">
                            <div class="welcome-content">
                                <h1>Welcome to PCDS2030 Dashboard</h1>
                                <p class="lead">Streamlined quarterly reporting for better resource management</p>
                                
                                <div class="features">
                                    <div class="feature-item">
                                        <span class="feature-icon"><i class="fas fa-chart-line"></i></span>
                                        <span class="feature-text">Simplified Data Collection</span>
                                    </div>
                                    <div class="feature-item">
                                        <span class="feature-icon"><i class="fas fa-file-powerpoint"></i></span>
                                        <span class="feature-text">Automated Report Generation</span>
                                    </div>
                                    <div class="feature-item">
                                        <span class="feature-icon"><i class="fas fa-users"></i></span>
                                        <span class="feature-text">Agency Collaboration</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Login Section (Right side of card) -->
                        <div class="col-md-6 login-section">
                            <div class="login-content">
                                <div class="login-top-section">
                                    <div class="logo-container text-center mb-4">
                                        <img src="assets/images/logo.png" alt="PCDS Logo" class="logo-image">
                                    </div>
                                    
                                    <h3 class="login-title">Log In</h3>
                                    <p class="login-subtitle text-center mb-4">Please login to access your dashboard</p>
                                </div>
                                
                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <?php echo $error; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm">
                                    <div class="form-group mb-3">
                                        <label for="username">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo $username; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-4">
                                        <label for="password">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                                            <span class="input-group-text toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                                                <i class="far fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary material-btn position-relative" id="loginBtn">
                                            <span class="login-text">Login</span>
                                            <span class="spinner-border spinner-border-sm position-absolute d-none" id="loginSpinner" role="status" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/login.js"></script>
</body>
</html>
