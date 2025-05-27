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
define('DB_NAME', 'pcds2030_dashboard'); // Updated to correct database name

// Application settings
define('APP_NAME', 'PCDS2030 Dashboard Forestry Sector'); 
define('APP_URL', 'http://localhost/pcds2030_dashboard');
define('APP_VERSION', '1.0.0'); // Example version
define('ASSET_VERSION', APP_VERSION); // Use APP_VERSION for asset versioning

// Path definitions
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', rtrim(dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Feature flags
define('MULTI_SECTOR_ENABLED', false); // Set to false to focus only on Forestry sector
define('FORESTRY_SECTOR_ID', 1);      // The ID of the Forestry sector in the database
define('ALLOW_OUTCOME_CREATION', true); // Set to false to disable creating new outcomes

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


// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
