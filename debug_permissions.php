<?php
// Quick permission debug
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_agency_assignments.php';

if (!is_agency()) {
    echo "Not logged in as agency user";
    exit;
}

echo "<h2>User Permission Debug</h2>";
echo "<p><strong>Current User:</strong> " . ($_SESSION['username'] ?? 'Unknown') . "</p>";
echo "<p><strong>User Role:</strong> " . ($_SESSION['role'] ?? 'Unknown') . "</p>";
echo "<p><strong>Agency ID:</strong> " . ($_SESSION['agency_id'] ?? 'Unknown') . "</p>";
echo "<p><strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'Unknown') . "</p>";

echo "<h3>Permission Function Tests:</h3>";
echo "<p><strong>is_focal_user():</strong> " . (is_focal_user() ? 'TRUE' : 'FALSE') . "</p>";

// Test a specific program (get first program from the screenshot - it shows program "sfc program 1")
$query = "SELECT program_id, program_name FROM programs WHERE program_name LIKE '%sfc program 1%' AND is_deleted = 0 LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $program = $result->fetch_assoc();
    $program_id = $program['program_id'];
    $program_name = $program['program_name'];
    
    echo "<h3>Testing Program: '$program_name' (ID: $program_id)</h3>";
    echo "<p><strong>is_program_owner($program_id):</strong> " . (is_program_owner($program_id) ? 'TRUE' : 'FALSE') . "</p>";
    echo "<p><strong>is_program_creator($program_id):</strong> " . (is_program_creator($program_id) ? 'TRUE' : 'FALSE') . "</p>";
    echo "<p><strong>can_edit_program($program_id):</strong> " . (can_edit_program($program_id) ? 'TRUE' : 'FALSE') . "</p>";
    echo "<p><strong>can_view_program($program_id):</strong> " . (can_view_program($program_id) ? 'TRUE' : 'FALSE') . "</p>";
    echo "<p><strong>get_user_program_role($program_id):</strong> " . (get_user_program_role($program_id) ?: 'NULL') . "</p>";
    
    $can_delete_old = is_focal_user() || is_program_owner($program_id);
    $can_delete_new = is_focal_user() || is_program_creator($program_id);
    echo "<h3>Delete Permission Comparison:</h3>";
    echo "<p><strong>OLD Logic (is_focal_user() || is_program_owner()):</strong> " . ($can_delete_old ? 'TRUE (DELETE BUTTON SHOWS)' : 'FALSE (DELETE BUTTON HIDDEN)') . "</p>";
    echo "<p><strong>NEW Logic (is_focal_user() || is_program_creator()):</strong> " . ($can_delete_new ? 'TRUE (DELETE BUTTON SHOWS)' : 'FALSE (DELETE BUTTON HIDDEN)') . "</p>";
    
    if ($can_delete_old !== $can_delete_new) {
        echo "<div class='alert alert-success'><strong>✅ FIXED!</strong> User will no longer see delete button for this program.</div>";
    } else {
        echo "<div class='alert alert-warning'><strong>⚠️ Same result</strong> - Check if user should have access or if there's another issue.</div>";
    }
    
    // Check program details
    $details_query = "SELECT created_by, agency_id FROM programs WHERE program_id = ?";
    $stmt = $conn->prepare($details_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $details = $stmt->get_result()->fetch_assoc();
    
    echo "<h3>Program Details:</h3>";
    echo "<p><strong>Created By User ID:</strong> " . ($details['created_by'] ?? 'NULL') . "</p>";
    echo "<p><strong>Program Agency ID:</strong> " . ($details['agency_id'] ?? 'NULL') . "</p>";
    echo "<p><strong>Current User is Creator:</strong> " . (($_SESSION['user_id'] ?? 0) == ($details['created_by'] ?? 0) ? 'TRUE' : 'FALSE') . "</p>";
    
} else {
    echo "<p>Could not find test program. Available programs:</p>";
    $all_query = "SELECT program_id, program_name FROM programs WHERE is_deleted = 0 LIMIT 5";
    $all_result = $conn->query($all_query);
    while ($row = $all_result->fetch_assoc()) {
        echo "<p>- {$row['program_name']} (ID: {$row['program_id']})</p>";
    }
}
?>
