<?php
/**
 * Development Environment Configuration
 * Copy of config.php modified for safe development testing
 * 
 * This file should be used during database migration testing
 * Switch database names as needed during different phases
 */

// Environment Detection
define('ENVIRONMENT', 'development');
define('DEBUG_MODE', true);

// Database Configuration - DEVELOPMENT
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Adjust for your Laragon setup
define('DB_PASS', '');     // Adjust for your Laragon setup

// Phase 1: Test current system with development copy
define('DB_NAME', 'pcds2030_dashboard_dev');

// Phase 2: After migration, switch to new structure
// define('DB_NAME', 'pcds2030_db_dev');

// Development-specific settings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Disable production features in development
define('SEND_EMAILS', false);
define('LOG_LEVEL', 'DEBUG');

// Create database connection with error handling
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Development Database Connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8");
    
    if (DEBUG_MODE) {
        echo "<!-- DEBUG: Connected to development database: " . DB_NAME . " -->\n";
    }
    
} catch (Exception $e) {
    die("Development Database Error: " . $e->getMessage());
}

// Development-specific functions
function dev_log($message) {
    if (ENVIRONMENT === 'development') {
        error_log("[DEV] " . date('Y-m-d H:i:s') . " - " . $message);
    }
}

// Function to switch database connection for testing
function switch_to_migrated_db() {
    global $conn;
    $conn->close();
    
    $new_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, 'pcds2030_db_dev');
    if ($new_conn->connect_error) {
        die("Failed to connect to migrated database: " . $new_conn->connect_error);
    }
    
    $new_conn->set_charset("utf8");
    return $new_conn;
}

dev_log("Development configuration loaded for database: " . DB_NAME);
?>
