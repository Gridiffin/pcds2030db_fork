
<?php
// Debug: log entry at start
file_put_contents(__DIR__ . '/profile_handler_debug.log', "[START] profile_handler.php\n", FILE_APPEND);
// Force JSON content type immediately
header('Content-Type: application/json');
// Debug: log any accidental output
ob_start(function($buffer) {
    if (trim($buffer) !== '') {
        file_put_contents(__DIR__ . '/profile_handler_debug.log', "[ACCIDENTAL OUTPUT] $buffer\n", FILE_APPEND);
    }
    return '';
});
// Start output buffering to prevent accidental output before JSON
ob_start();
/**
 * Profile Handler
 * Handles profile update requests and form processing
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/user_functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';

file_put_contents(__DIR__ . '/profile_handler_debug.log', "[INCLUDED FILES]\n", FILE_APPEND);

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    file_put_contents(__DIR__ . '/profile_handler_debug.log', "[ERROR] Unauthorized access\n", FILE_APPEND);
    http_response_code(401);
    header('Content-Type: application/json');
    if (ob_get_length()) ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    file_put_contents(__DIR__ . '/profile_handler_debug.log', "[ERROR] Method not allowed\n", FILE_APPEND);
    http_response_code(405);
    header('Content-Type: application/json');
    if (ob_get_length()) ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// CSRF Protection (basic implementation)
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    file_put_contents(__DIR__ . '/profile_handler_debug.log', "[ERROR] Invalid security token\n", FILE_APPEND);
    http_response_code(403);
    header('Content-Type: application/json');
    if (ob_get_length()) ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Unknown error', 'errors' => []];
file_put_contents(__DIR__ . '/profile_handler_debug.log', "[POST DATA] " . json_encode($_POST) . "\n", FILE_APPEND);


try {
    file_put_contents(__DIR__ . '/profile_handler_debug.log', "[TRY BLOCK ENTERED]\n", FILE_APPEND);
    // Validate and sanitize input data
    $update_data = [];
    $validation_errors = [];

    // Validate username
    if (isset($_POST['username']) && !empty(trim($_POST['username']))) {
        $username = trim($_POST['username']);
        $username_validation = validate_username_format($username);

        if (!$username_validation['valid']) {
            $validation_errors['username'] = $username_validation['message'];
        } else {
            // Check uniqueness
            if (!validate_username_unique($conn, $username, $user_id)) {
                $validation_errors['username'] = 'Username already exists';
            } else {
                $update_data['username'] = $username;
            }
        }
    }

    // Validate email
    if (isset($_POST['email']) && !empty(trim($_POST['email']))) {
        $email = trim($_POST['email']);

        if (!validate_email_format($email)) {
            $validation_errors['email'] = 'Invalid email format';
        } else {
            $update_data['email'] = $email;
        }
    }

    // Validate fullname
    if (isset($_POST['fullname'])) {
        $fullname = trim($_POST['fullname']);
        if (strlen($fullname) > 200) {
            $validation_errors['fullname'] = 'Full name must be less than 200 characters';
        } else {
            $update_data['fullname'] = $fullname;
        }
    }

    // Validate password if provided
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Check password strength
        $password_validation = validate_password_strength($password);
        if (!$password_validation['valid']) {
            $validation_errors['password'] = $password_validation['message'];
        } else {
            // Check password confirmation
            if ($password !== $confirm_password) {
                $validation_errors['confirm_password'] = 'Passwords do not match';
            } else {
                $update_data['password'] = $password;
            }
        }
    }

    file_put_contents(__DIR__ . '/profile_handler_debug.log', "[VALIDATION COMPLETE] " . json_encode($validation_errors) . "\n", FILE_APPEND);

    // If there are validation errors, return them
    if (!empty($validation_errors)) {
        $response['success'] = false;
        $response['message'] = 'Please correct the following errors:';
        $response['errors'] = $validation_errors;
        file_put_contents(__DIR__ . '/profile_handler_debug.log', "[VALIDATION ERRORS] " . json_encode($validation_errors) . "\n", FILE_APPEND);
    } else {
        // If no data to update
        if (empty($update_data)) {
            $response['success'] = false;
            $response['message'] = 'No changes detected';
            file_put_contents(__DIR__ . '/profile_handler_debug.log', "[NO CHANGES DETECTED]\n", FILE_APPEND);
        } else {
            // Update the user profile
            file_put_contents(__DIR__ . '/profile_handler_debug.log', "[UPDATING PROFILE] " . json_encode($update_data) . "\n", FILE_APPEND);
            $update_result = update_user_profile($conn, $user_id, $update_data);

            if ($update_result['success']) {
                // Update session data if username or fullname changed
                if (isset($update_data['username'])) {
                    $_SESSION['username'] = $update_data['username'];
                }
                if (isset($update_data['fullname'])) {
                    $_SESSION['fullname'] = $update_data['fullname'];
                }

                $response['success'] = true;
                $response['message'] = $update_result['message'];
                $response['updated_fields'] = array_keys($update_data);
                file_put_contents(__DIR__ . '/profile_handler_debug.log', "[UPDATE SUCCESS] " . json_encode($response) . "\n", FILE_APPEND);
            } else {
                $response['success'] = false;
                $response['message'] = $update_result['message'];
                file_put_contents(__DIR__ . '/profile_handler_debug.log', "[UPDATE FAILED] " . json_encode($update_result) . "\n", FILE_APPEND);
            }
        }
    }

} catch (Exception $e) {
    error_log("Profile handler error: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = 'An unexpected error occurred. Please try again.';
    file_put_contents(__DIR__ . '/profile_handler_debug.log', "[EXCEPTION] " . $e->getMessage() . "\n", FILE_APPEND);
}

file_put_contents(__DIR__ . '/profile_handler_debug.log', "[FINAL RESPONSE] " . json_encode($response) . "\n", FILE_APPEND);
// Return JSON response
if (ob_get_length()) ob_end_clean();
header('Content-Type: application/json');
echo json_encode($response);
exit;
