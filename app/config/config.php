<?php
/**
 * Application configuration
 * 
 * Contains database credentials and other configuration settings.
 * This file should be excluded from version control.
 */

// Force session start immediately - fix for disabled sessions on production
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get host once for all configurations
$current_host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Database configuration - Dynamic based on environment
if ($current_host === 'www.sarawakforestry.com' || $current_host === 'sarawakforestry.com') {
    // Production database settings
    define('DB_HOST', 'localhost:3306');
    define('DB_USER', 'sarawak3_admin1');
    define('DB_PASS', 'attendance33**');
    define('DB_NAME', 'pcds2030_db');
} else {
    // Local development database settings
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root'); // Default for XAMPP/Laragon
    define('DB_PASS', ''); // Default for XAMPP/Laragon (empty)
    define('DB_NAME', 'pcds2030_db');
}

// Application settings
define('APP_NAME', 'PCDS2030 Dashboard Forestry Sector'); 

// Dynamic APP_URL detection for better cross-environment compatibility
if (!defined('APP_URL')) {
    // Check if we're running from command line
    if (php_sapi_name() === 'cli') {
        define('APP_URL', 'https://www.sarawakforestry.com/pcds2030'); // Default for CLI
    } else {
        // Detect the correct APP_URL based on current request
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        
        // Production cPanel detection first
        if ($current_host === 'www.sarawakforestry.com' || $current_host === 'sarawakforestry.com') {
            // Force production path regardless of directory name
            define('APP_URL', 'https://www.sarawakforestry.com/pcds2030');
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
                    $app_path = str_replace('\\\\', '/', $app_path); // Windows compatibility
                }
            }
            
            // Ensure app_path doesn't end with slash and starts with slash
            $app_path = '/' . trim($app_path, '/');
            if ($app_path === '/') {
                $app_path = '';
            }
            
            define('APP_URL', $protocol . '://' . $current_host . $app_path);
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
        define('BASE_URL', '/pcds2030'); // Production path for CLI
    } else {
        // Production cPanel detection first
        if ($current_host === 'www.sarawakforestry.com' || $current_host === 'sarawakforestry.com') {
            // Force production path regardless of directory name
            define('BASE_URL', '/pcds2030');
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
                    $base_path = str_replace('\\\\', '/', $base_path); // Windows compatibility
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
if ($current_host === 'localhost' || strpos($current_host, 'laragon') !== false) {
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
