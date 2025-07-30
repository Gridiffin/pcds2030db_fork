<?php
/**
 * Clear Session Message AJAX Endpoint
 * 
 * Clears any persistent session messages that might be causing toast spam
 */

// Define PROJECT_ROOT_PATH
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';

// Set JSON header
header('Content-Type: application/json');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clear any session messages
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
}
if (isset($_SESSION['message_type'])) {
    unset($_SESSION['message_type']);
}
if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Session messages cleared successfully'
]);
?> 