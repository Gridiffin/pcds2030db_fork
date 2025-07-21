<?php
/**
 * PHPUnit Bootstrap File
 * 
 * This file is loaded before running tests.
 * It sets up the testing environment and includes necessary files.
 */

// Start output buffering to prevent header issues during testing
ob_start();

// Set error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define project root path
define('PROJECT_ROOT_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once PROJECT_ROOT_PATH . '/vendor/autoload.php';

// Set up test environment
$_ENV['APP_ENV'] = 'testing';

// Mock session for testing
if (!isset($_SESSION)) {
    $_SESSION = [];
}

// Mock database connection for testing
$GLOBALS['pdo'] = null;

// Include core files that are needed for testing
$coreFiles = [
    PROJECT_ROOT_PATH . '/app/lib/agencies/core.php',
    PROJECT_ROOT_PATH . '/app/lib/admins/core.php',
    PROJECT_ROOT_PATH . '/app/lib/session.php'
];

foreach ($coreFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

// Skip functions.php as it has database dependencies
// Skip audit_log.php as it has database dependencies
// These can be tested separately with proper database mocking

// Set up test database connection (if needed)
// For now, we'll mock database connections in individual tests

// Create test directories if they don't exist
$testDirs = [
    PROJECT_ROOT_PATH . '/coverage',
    PROJECT_ROOT_PATH . '/tests/php'
];

foreach ($testDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

echo "✅ PHPUnit Bootstrap loaded successfully\n";
