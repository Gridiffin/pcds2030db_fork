<?php
/**
 * Debug Delete Button Permissions
 * Test page to check permission functions for delete button visibility
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/program_agency_assignments.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get a program ID from URL for testing
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

if ($program_id) {
    echo "<h2>Debug Delete Button Permissions for Program ID: $program_id</h2>";
    
    echo "<h3>User Session Info:</h3>";
    echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
    echo "Agency ID: " . ($_SESSION['agency_id'] ?? 'Not set') . "<br>";
    echo "User Role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
    
    echo "<h3>Permission Function Results:</h3>";
    
    // Test is_focal_user()
    $is_focal = is_focal_user();
    echo "is_focal_user(): " . ($is_focal ? 'TRUE' : 'FALSE') . "<br>";
    
    // Test is_program_owner()
    $is_owner = is_program_owner($program_id);
    echo "is_program_owner($program_id): " . ($is_owner ? 'TRUE' : 'FALSE') . "<br>";
    
    // Test can_edit_program()
    $can_edit = can_edit_program($program_id);
    echo "can_edit_program($program_id): " . ($can_edit ? 'TRUE' : 'FALSE') . "<br>";
    
    // Test can_view_program()
    $can_view = can_view_program($program_id);
    echo "can_view_program($program_id): " . ($can_view ? 'TRUE' : 'FALSE') . "<br>";
    
    // Test get_user_program_role()
    $user_role = get_user_program_role($program_id);
    echo "get_user_program_role($program_id): " . ($user_role ?: 'NULL') . "<br>";
    
    echo "<h3>Final Delete Permission:</h3>";
    $can_delete = is_focal_user() || is_program_owner($program_id);
    echo "can_delete (is_focal_user() || is_program_owner()): " . ($can_delete ? 'TRUE (should show delete button)' : 'FALSE (should NOT show delete button)') . "<br>";
    
    // Get program details
    $query = "SELECT program_name, created_by, agency_id FROM programs WHERE program_id = ? AND is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $program = $result->fetch_assoc();
        echo "<h3>Program Details:</h3>";
        echo "Program Name: " . htmlspecialchars($program['program_name']) . "<br>";
        echo "Created By: " . ($program['created_by'] ?? 'Not set') . "<br>";
        echo "Program Agency ID: " . ($program['agency_id'] ?? 'Not set') . "<br>";
        echo "Current User is Creator: " . (($_SESSION['user_id'] ?? 0) == ($program['created_by'] ?? 0) ? 'TRUE' : 'FALSE') . "<br>";
    } else {
        echo "<h3>Program not found or deleted</h3>";
    }
    
} else {
    echo "<h2>Debug Delete Button Permissions</h2>";
    echo "<p>Please provide a program_id parameter in the URL.</p>";
    echo "<p>Example: debug_delete_permissions.php?program_id=123</p>";
    
    // Get some program IDs for testing
    $query = "SELECT program_id, program_name FROM programs WHERE is_deleted = 0 LIMIT 10";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        echo "<h3>Available Programs for Testing:</h3>";
        while ($row = $result->fetch_assoc()) {
            echo "<a href='?program_id=" . $row['program_id'] . "'>" . htmlspecialchars($row['program_name']) . " (ID: " . $row['program_id'] . ")</a><br>";
        }
    }
}
?>
